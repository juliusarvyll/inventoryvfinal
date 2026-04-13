<?php

it('redirects guests from the root URL to the admin login page', function () {
    $response = $this->get('/');

    $response->assertRedirect('/admin/login');
});
