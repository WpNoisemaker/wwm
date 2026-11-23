<?php
declare(strict_types=1);

/** The pages with no database behind them, plus pricing, which reads its figures from settings. */
final class PageController
{
    public function pricing(): void
    {
        View::render('page/pricing', [
            'title'   => 'Pricing',
            'desc'    => 'Plans for your wedding website: Free, Pro, Premium and High Society. You pay once per wedding, never monthly.',
            'tab'     => '',
            'css'     => ['pages'],
            'pro'     => (int) Site::setting('pro_price', '198000'),
            'premium' => (int) Site::setting('premium_price', '448000'),
            'cur'     => Fx::pick(),
            'fee'     => Site::setting('fee_gift', '4'),
        ]);
    }

    public function about(): void   { $this->simple('about',   'About us'); }
    public function help(): void    { $this->simple('help',    'Help'); }
    public function terms(): void      { $this->simple('terms',   'Terms & conditions'); }
    public function privacy(): void    { $this->simple('privacy', 'Privacy policy'); }
    public function refunds(): void    { $this->simple('refunds', 'Refund policy'); }
    public function guidelines(): void { $this->simple('community-guidelines', 'Community guidelines'); }
    public function cookies(): void    { $this->simple('cookies', 'Cookie policy'); }

    private function simple(string $tpl, string $title): void
    {
        View::render('page/' . $tpl, ['title' => $title, 'tab' => '', 'css' => ['pages']]);
    }
}
