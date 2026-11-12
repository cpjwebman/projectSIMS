<?php

it('returns a successful response', function () {
    $this->get('/')
        ->assertRedirect(route('login', absolute: false));

});
