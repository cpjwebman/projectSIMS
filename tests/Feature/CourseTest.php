<?php

use App\Models\Course;
use App\Models\User;

test('authenticated users can manage courses', function () {
    $this->actingAs(User::factory()->create());

    $courseData = [
        'course_code' => 'CS-101',
        'name' => 'Computer Science',
        'description' => 'Introduction to computing.',
    ];

    $this->get(route('courses.create'))->assertOk();
    $this->post(route('courses.store'), $courseData)
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('courses.index'));

    $course = Course::where('course_code', 'CS-101')->firstOrFail();

    $this->get(route('courses.index'))
        ->assertOk()
        ->assertSee('Computer Science');
    $this->get(route('courses.edit', $course))->assertOk();

    $this->from(route('courses.create'))
        ->post(route('courses.store'), $courseData)
        ->assertSessionHasErrors('course_code');

    $this->put(route('courses.update', $course), [
        ...$courseData,
        'name' => 'Advanced Computer Science',
    ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('courses.index'));

    $this->assertDatabaseHas('courses', [
        'id' => $course->id,
        'name' => 'Advanced Computer Science',
    ]);

    $this->delete(route('courses.destroy', $course))
        ->assertRedirect(route('courses.index'));

    $this->assertDatabaseMissing('courses', ['id' => $course->id]);
});
