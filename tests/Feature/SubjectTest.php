<?php

use App\Models\Course;
use App\Models\Subject;
use App\Models\User;
use App\Models\YearLevel;

test('authenticated users can manage subjects', function () {
    $this->actingAs(User::factory()->create());
    $course = Course::create(['course_code' => 'MATH-COURSE', 'name' => 'Mathematics Course']);
    $yearLevel = YearLevel::create(['year_level' => 1, 'name' => 'First Year']);

    $subjectData = [
        'subject_code' => 'MATH-101',
        'name' => 'Mathematics',
        'units' => 3,
        'description' => 'Foundations of mathematics.',
        'course_id' => $course->id,
        'year_level_id' => $yearLevel->id,
        'status' => 'active',
    ];

    $this->get(route('subjects.create'))->assertOk();
    $this->post(route('subjects.store'), $subjectData)
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('subjects.index'));

    $subject = Subject::where('subject_code', 'MATH-101')->firstOrFail();

    $this->get(route('subjects.index'))
        ->assertOk()
        ->assertSee('Mathematics')
        ->assertSee('3');
    $this->get(route('subjects.show', $subject))->assertOk()->assertSee('First Year');
    $this->get(route('subjects.edit', $subject))->assertOk();

    $this->from(route('subjects.create'))
        ->post(route('subjects.store'), $subjectData)
        ->assertSessionHasErrors('subject_code');

    $this->put(route('subjects.update', $subject), [
        ...$subjectData,
        'name' => 'Advanced Mathematics',
        'units' => 4,
    ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('subjects.index'));

    $this->assertDatabaseHas('subjects', [
        'id' => $subject->id,
        'name' => 'Advanced Mathematics',
        'units' => 4,
    ]);

    $this->delete(route('subjects.destroy', $subject))
        ->assertRedirect(route('subjects.index'));

    $this->assertDatabaseMissing('subjects', ['id' => $subject->id]);
});
