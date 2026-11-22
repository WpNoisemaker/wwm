<?php
/** @var Router $r */
$r->get('/', [HomeController::class, 'index']);
$r->get('/community', [CommunityController::class, 'index']);
$r->get('/looks', [LooksController::class, 'index']);
$r->get('/looks/{look}', [LooksController::class, 'show']);
