<?php

test('starter kit frontend routes are unavailable', function () {
    $this->get('/dashboard')->assertNotFound();
    $this->get('/settings/profile')->assertNotFound();
    $this->get('/settings/security')->assertNotFound();
    $this->get('/login')->assertOk();
    $this->get('/register')->assertNotFound();
});
