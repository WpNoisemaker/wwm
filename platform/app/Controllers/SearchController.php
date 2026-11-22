<?php
declare(strict_types=1);

/** One box over the three things a visitor might be looking for. */
final class SearchController
{
    public function index(): void
    {
        $q = trim((string) ($_GET['q'] ?? ''));
        $weddings = $vendors = $listings = [];
        if ($q !== '') {
            $weddings = Wedding::search(['q' => $q, 'sort' => 'buzz'], 8);
            $vendors  = Vendor::search(['q' => $q], 6);
            $listings = Listing::search(['q' => $q], 6);
        }
        View::render('search/index', [
            'title'    => $q !== '' ? 'Search: ' . $q : 'Search',
            'tab'      => '',
            'css'      => ['pages'],
            'q'        => $q,
            'weddings' => $weddings,
            'vendors'  => $vendors,
            'listings' => $listings,
            'total'    => count($weddings) + count($vendors) + count($listings),
        ]);
    }
}
