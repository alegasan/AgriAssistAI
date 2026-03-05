<?php

it('throttles repeated registration attempts for the same email', function () {
    foreach (range(1, 5) as $attempt) {
        $this->post(route('register.store'), [
            'name' => 'ab',
            'username' => "rate-limit-user-{$attempt}",
            'email' => 'register-limit@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertSessionHasErrors('name');
    }

    $this->post(route('register.store'), [
        'name' => 'ab',
        'username' => 'rate-limit-user-6',
        'email' => 'register-limit@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertTooManyRequests();
});

it('uses separate throttle buckets for different registration emails', function () {
    foreach (range(1, 5) as $attempt) {
        $this->post(route('register.store'), [
            'name' => 'ab',
            'username' => "register-bucket-a-{$attempt}",
            'email' => 'bucket-a@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertSessionHasErrors('name');
    }

    $this->post(route('register.store'), [
        'name' => 'ab',
        'username' => 'register-bucket-a-6',
        'email' => 'bucket-a@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertTooManyRequests();

    $this->post(route('register.store'), [
        'name' => 'ab',
        'username' => 'register-bucket-b-1',
        'email' => 'bucket-b@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertSessionHasErrors('name');
});
