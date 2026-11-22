<?php
declare(strict_types=1);

final class CommunityController
{
    public function index(): void
    {
        View::render('community', ['title' => 'Community', 'tab' => 'community']);
    }
}
