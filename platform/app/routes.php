<?php
/** @var Router $r */
$r->get('/',          [SiteController::class, 'home']);
$r->get('/account',   [HomeController::class, 'index']);
$r->get('/community', [CommunityController::class, 'index']);
$r->get('/looks',     [LooksController::class, 'index']);
$r->get('/looks/{look}', [LooksController::class, 'show']);
