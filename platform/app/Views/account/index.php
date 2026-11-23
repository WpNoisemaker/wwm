<?php
/**
 * The couple's dashboard: the night control panel, tile for tile, drawn with their real numbers.
 * @var array $w @var array $tally @var array $dash @var ?array $next @var array $todo
 */
$design  = Design::forWedding($w);
$public  = (string) $w['visibility'] === 'public';
$rsvpPct = $dash['guests'] > 0 ? (int) round($dash['coming'] / $dash['guests'] * 100) : 0;
$giftPct = $dash['target'] > 0 ? min(100, (int) round($tally['raised'] / $dash['target'] * 100)) : 0;
$days    = Wedding::daysTo($w);
$url     = App::url('/w/' . $w['slug']);
?>
<?= View::partial('account/_head', ['h' => Wedding::names($w), 'w' => $w, 'sub' => Wedding::when($w), 'now' => 'overview']) ?>
	<div class="split-2">
		<div>
			<div class="mosaic panel">
				<a class="tile tile--glyph tile--sq<?= $public ? ' is-on' : '' ?>" href="<?= e(App::url('/account/page#visibility')) ?>"><?= icon('s-power') ?><span><?= $public ? 'Live' : 'Private' ?></span></a>
				<a class="tile tile--glyph tile--sq" href="<?= e(App::url('/account/page#story')) ?>"><?= icon('s-book') ?><span>Story</span></a>
				<?= kv('Plan', Site::tier((string) $w['plan']), 'tile--w2') ?>
				<a class="tile tile--glyph tile--sq" href="<?= e(App::url('/account/programs')) ?>"><?= icon('s-calendar') ?><span><?= (int) $dash['programs'] ?> events</span></a>
				<a class="tile tile--glyph tile--sq" href="<?= e(App::url('/account/squad')) ?>"><?= icon('s-users') ?><span>Squad <?= (int) $dash['squad'] ?></span></a>
				<?= meter($giftPct, 'Gifts funded') ?>
				<?= kv('Design', $design['name'], 'tile--w2') ?>
				<div class="tile tile--w2 tile--h2 tile--ticks">
					<div><span class="k">Guests coming</span><span class="v"><?= number_format($dash['heads']) ?></span><span class="k gap-k">Replies</span><span class="v"><?= number_format($dash['guests']) ?></span><span class="k gap-k">Can't make it</span><span class="v"><?= number_format($dash['declined']) ?></span></div>
					<div class="ticks" aria-hidden="true"><?php for ($i = 0; $i < 12; $i++): ?><i<?= $i === (int) round($rsvpPct / 100 * 11) ? ' class="is-on"' : '' ?>></i><?php endfor; ?></div>
				</div>
				<?= meter($rsvpPct, 'RSVPs in') ?>
				<?= kv('Gifts', money(short_money((int) $tally['raised']), (string) $w['currency']), '', true) ?>
				<?= kv('This week', money(short_money((int) $dash['week']), (string) $w['currency']), '', true) ?>
				<a class="tile tile--glyph tile--sq" href="<?= e(App::url('/w/' . $w['slug'] . '#wall')) ?>"><?= icon('s-heart') ?><span><?= (int) $dash['notes'] ?> wishes</span></a>
				<a class="tile tile--glyph tile--sq" href="<?= e(App::url('/w/' . $w['slug'] . '#photos')) ?>"><?= icon('s-camera') ?><span><?= (int) $dash['photos'] ?> photos</span></a>
				<?= kv('Page views', number_format((int) $w['views']), 'tile--w2') ?>
				<div class="tile tile--w2 tile--num"><div><span class="k">Days to go</span><span class="v v--sm"><?= e(Wedding::short($w)) ?></span></div><?= $days !== null && $days >= 0 ? dots((string) $days, 'dotnum--sm') : '<span class="v">Done</span>' ?></div>
			</div>

			<div class="gap-t">
				<div class="block-head"><h2 class="h2">What is left</h2></div>
				<div class="rows">
					<?php foreach ($todo as [$label, $href, $done]): ?>
						<a class="row-item" href="<?= e(App::url($href)) ?>">
							<span class="row-item__ico"><?= icon($done ? 'check' : 'plus') ?></span>
							<span><span class="row-item__t"><?= e($label) ?></span></span>
							<span class="row-item__end"><?= $done ? '<span class="badge badge--solid">Done</span>' : '<span class="badge">Add</span>' ?></span>
						</a>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
		<aside>
			<div class="glass glass--tight linkcard">
				<div><span class="k">Your page</span><span class="v v--sm linkcard__url"><?= e(preg_replace('#^https?://#', '', $url)) ?></span></div>
				<button class="cbtn" type="button" data-share="<?= e($url) ?>" aria-label="Share your page"><?= icon('share') ?></button>
			</div>
			<div class="quick">
				<a href="<?= e($url) ?>"><span class="cbtn cbtn--lg"><?= icon('eye') ?></span>View</a>
				<a href="<?= e(App::url('/account/page')) ?>"><span class="cbtn cbtn--lg"><?= icon('pen') ?></span>Edit</a>
				<a href="<?= e(App::url('/account/guests')) ?>"><span class="cbtn cbtn--lg"><?= icon('people') ?></span>Guests</a>
				<a href="<?= e(App::url('/account/money')) ?>"><span class="cbtn cbtn--lg"><?= icon('wallet') ?></span>Money</a>
			</div>
			<?php if (!$public): ?>
				<p class="note"><?= icon('lock') ?><span>Your page is private, so nobody can find it yet. <a href="<?= e(App::url('/account/page#visibility')) ?>">Make it public</a> when you are ready.</span></p>
			<?php endif; ?>
			<?php if (Wedding::has($w, 'gallery')): $df = Drive::folder((int) $w['id']); ?>
				<p class="note"><?= icon('image') ?><span><?php if ($df): ?>Every photo your guests add is saved, full size, to your Google Drive folder: <a href="<?= e((string) $df['link']) ?>" target="_blank" rel="noopener"><?= e((string) $df['name']) ?></a>. Ask us to share it with your Google account.<?php else: ?>Your guests' photos are kept safe and go, full size, to your own Google Drive folder as soon as we set it up for you.<?php endif; ?></span></p>
			<?php endif; ?>
			<?php if ($next): $u = Wedding::until($next['when']); ?>
				<div class="tile next-up"><span class="k">Next up</span><span class="v"><?= e($next['label']) ?></span><span class="sub">in <?= (int) $u['d'] ?> day<?= (int) $u['d'] === 1 ? '' : 's' ?><?= $next['venue'] ? ' · ' . e($next['venue']) : '' ?></span></div>
			<?php endif; ?>
			<form method="post" action="<?= e(App::url('/sign-out')) ?>" class="gap-t-sm center"><?= Guard::field() ?><button class="linkish" type="submit">Sign out</button></form>
		</aside>
	</div>
</div>
