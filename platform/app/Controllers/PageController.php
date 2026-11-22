<?php
declare(strict_types=1);

/** The pages with no database behind them, plus pricing, which reads its figures from settings. */
final class PageController
{
    public function pricing(): void
    {
        View::render('page/pricing', [
            'title'   => 'Pricing',
            'desc'    => 'Start free. Pro, Premium and High Society are paid once per wedding, never monthly.',
            'tab'     => '',
            'css'     => ['pages'],
            'pro'     => number_format((int) Site::setting('pro_price', '198000')),
            'premium' => number_format((int) Site::setting('premium_price', '448000')),
            'fee'     => Site::setting('fee_gift', '4'),
        ]);
    }

    public function about(): void   { $this->simple('about',   'About us'); }
    public function help(): void    { $this->simple('help',    'Help'); }
    public function terms(): void   { $this->simple('terms',   'Terms of use'); }
    public function privacy(): void { $this->simple('privacy', 'Privacy'); }

    private function simple(string $tpl, string $title): void
    {
        View::render('page/' . $tpl, ['title' => $title, 'tab' => '', 'css' => ['pages']]);
    }
}
