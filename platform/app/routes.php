<?php
/** @var Router $r */
$r->get('/', [HomeController::class, 'index']);
$r->get('/community', [CommunityController::class, 'index']);
