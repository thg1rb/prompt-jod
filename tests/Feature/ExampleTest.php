<?php

it('redirects unauthenticated users from home', function () {
    $response = $this->get('/');

    $response->assertRedirect(route('login'));
});
