<?php

use App\Models\StudentEnrollment;
use App\Models\Subject;

test('student enrollment total units sum the assigned subjects', function () {
    $enrollment = new StudentEnrollment;
    $enrollment->setRelation('subjects', collect([
        new Subject(['units' => 3]),
        new Subject(['units' => 2]),
    ]));

    expect($enrollment->total_units)->toBe(5);
});

test('student enrollment total units are zero when no subjects are assigned', function () {
    $enrollment = new StudentEnrollment;
    $enrollment->setRelation('subjects', collect());

    expect($enrollment->total_units)->toBe(0);
});
