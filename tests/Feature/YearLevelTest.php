<?php

use App\Models\User;
use App\Models\YearLevel;

test('authenticated users can manage year levels', function () {
    $this->actingAs(User::factory()->create());

    $this->get(route('year-levels.create'))->assertOk();
    $this->post(route('year-levels.store'), [
        'name' => 'First Year',
        'year_level' => 1,
    ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('year-levels.index'));

    $yearLevel = YearLevel::where('year_level', 1)->firstOrFail();

    $this->get(route('year-levels.index'))
        ->assertOk()
        ->assertSee('First Year')
        ->assertSee('Year 1');
    $this->get(route('year-levels.edit', $yearLevel))->assertOk();

    $this->from(route('year-levels.create'))
        ->post(route('year-levels.store'), [
            'name' => 'Duplicate Year',
            'year_level' => 1,
        ])
        ->assertSessionHasErrors('year_level');

    $this->put(route('year-levels.update', $yearLevel), [
        'name' => 'Second Year',
        'year_level' => 2,
    ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('year-levels.index'));

    $this->assertDatabaseHas('year_levels', [
        'id' => $yearLevel->id,
        'name' => 'Second Year',
        'year_level' => 2,
    ]);

    $this->delete(route('year-levels.destroy', $yearLevel))
        ->assertRedirect(route('year-levels.index'));

    $this->assertDatabaseMissing('year_levels', ['id' => $yearLevel->id]);
});
