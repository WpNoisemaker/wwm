<?php
declare(strict_types=1);

final class RankingsController
{
    public function index(): void
    {
        $city = trim((string) ($_GET['city'] ?? ''));
        $args = [];
        $where = "visibility = 'public'";
        if ($city !== '') {
            $where .= ' AND city = ?';
            $args[] = $city;
        }
        $args[] = 50;
        $rows = Db::all(
            "SELECT w.*, (SELECT COUNT(*) FROM guests g WHERE g.wedding_id = w.id AND g.coming='yes' AND g.status='approved') guests
             FROM weddings w WHERE $where ORDER BY buzz DESC, views DESC, id LIMIT ?",
            $args
        );

        View::render('rankings/index', [
            'title'  => 'The Buzz Chart' . ($city !== '' ? ' — ' . $city : ''),
            'desc'   => 'The Nigerian and African wedding pages getting the most attention this month, ranked by what guests actually did.',
            'tab'    => 'rankings',
            'css'    => ['pages'],
            'rows'   => $rows,
            'city'   => $city,
            'cities' => Wedding::cityList(14),
        ]);
    }
}
