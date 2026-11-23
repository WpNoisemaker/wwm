<?php
/**
 * The homepage. On a phone it opens like the reference screen: two squircle photos and the glass
 * card. On desktop the same pieces become a landing page, section after section, each one showing
 * a part of the platform rather than describing it.
 *
 * @var array $chart @var array $fresh @var array $vendors @var array $listings @var array $talks
 * @var array $wall @var array $posts @var array $stats @var array $samples
 */
$pro   = (int) Site::setting('pro_price', '198000');
$countries = $countries ?? [];
$prem  = (int) Site::setting('premium_price', '448000');
$cur   = Fx::pick();
$lead  = $samples['ivory'] ?? ($chart[0] ?? null);
$other = null;
foreach ($chart as $c) { if (!$lead || $c['id'] !== $lead['id']) { $other = $c; break; } }
$free  = $samples['free'] ?? null;
$premiumDesigns = array_filter(Design::ALL, static fn ($d) => $d[1] !== 'free');
?>
<section class="hero hero--brand wrap" id="start">
	<?= View::partial('partials/bunting') ?>
	<div class="hero__head hero__head--show">
		<p class="greet" aria-label="Congratulations, in nine languages">
			<?php foreach (['Barka da aure!', 'Ẹ kú ayẹyẹ!', 'Hongera kwa harusi!', 'Ayekoo!', 'Una go last o!', 'Halala!', 'Félicitations!', 'Congratulations!', 'Nnọọ!'] as $gi => $g): ?>
				<span style="--i:<?= $gi ?>" aria-hidden="true"><?= e($g) ?></span>
			<?php endforeach; ?>
		</p>
		<h1 class="hero__title"><?= t('home.title.the-african-weddings-everyone-is', 'Wedding websites for African couples') ?></h1>
		<p class="lede"><?= t('home.intro.make-a-beautiful-wedding-page', 'Make a website for your wedding: invitation, RSVP, gifts and livestream on one link. Find vendors, book stays and rides, and ask questions in the Anonymous Community. For couples in Africa and abroad.') ?></p>
		<form class="searchbox hero__search" method="get" action="<?= e(App::url('/weddings')) ?>" role="search">
			<?= icon('search') ?>
			<input class="input" type="search" name="q" placeholder="<?= t('home.search.find-a-couple', 'Find a couple, a city or a hashtag') ?>" aria-label="Search weddings">
		</form>
		<div class="hero__ctas">
			<a class="pill" href="<?= e(App::url('/join')) ?>"><?= t('home.button.create-your-page-free', 'Create your page') ?></a>
			<a class="pill pill--glass" href="<?= e(App::url('/weddings')) ?>"><?= t('home.button.browse-weddings', 'Browse weddings') ?></a>
		</div>
	</div>
	<?php $show = $top ?? []; if ($show): ?>
	<div class="showcase" aria-label="The top-ranked weddings">
		<?php foreach ($show as $n => $w): ?>
			<a class="card showcase__i" href="<?= e(App::url('/w/' . $w['slug'])) ?>">
				<span class="sq"><?= photo((string) $w['cover'], Wedding::names($w), $n < 2) ?: '<span class="fill-art" aria-hidden="true">' . initials($w) . '</span>' ?><span class="sq__rank" aria-label="Ranked <?= $n + 1 ?>">#<?= $n + 1 ?></span></span>
				<span class="card__cap"><span class="card__t"><?= e(Wedding::names($w)) ?></span><span class="card__s"><?= e(Wedding::place($w)) ?></span></span>
			</a>
		<?php endforeach; ?>
	</div>
	<p class="showcase__more"><a class="linkish" href="<?= e(App::url('/weddings?sort=top')) ?>"><?= t('home.button.see-the-full-ranking', 'See the full ranking') ?></a></p>
	<?php endif; ?>
</section>

<section class="wrap stats reveal" aria-label="The platform today">
	<div class="tile"><?= dots((string) $stats['weddings'], 'dotnum--md') ?><span class="k">Wedding pages</span></div>
	<div class="tile"><?= dots((string) $stats['guests'], 'dotnum--md') ?><span class="k">Guests replied</span></div>
	<div class="tile"><?= dots((string) $stats['countries'], 'dotnum--md') ?><span class="k">Countries</span></div>
	<div class="tile"><?= dots((string) $stats['vendors'], 'dotnum--md') ?><span class="k">Vendors</span></div>
</section>

<section class="sec wrap">
	<div class="sec-head reveal">
		<span class="eyebrow"><?= t('home.eyebrow.one-platform-for-the-whole', 'What you can do here') ?></span>
		<h2><?= t('home.heading.everything-in-one-place', 'Everything for your wedding in one place') ?></h2>
		<p><?= tl('home.text.your-page-your-guests-your', 'Your page, your guests, your money, the people who make the day and the conversations around it.') ?></p>
	</div>
	<div class="mosaic feat reveal">
		<?php foreach ([
			['s-ring', t('home.tile.pages', 'Wedding pages'), t('home.tileline.pages', 'Your invitation, story, schedule, venue and dress code on one link you share with guests.'), '/templates'],
			['s-users', t('home.tile.rsvp', 'RSVP & guest list'), t('home.tileline.rsvp', 'Guests tell you if they are coming, without an account. You get the guest list and entry passes.'), '/pricing'],
			['s-gift', t('home.tile.gifts', 'Gifts & spraying'), t('home.tileline.gifts', 'Guests send cash gifts and spray you online, from any country.'), '/pricing'],
			['s-live', t('home.tile.live', 'Livestream'), t('home.tileline.live', 'Stream the ceremony on your page for family who cannot be there.'), '/pricing'],
			['s-crown', t('home.tile.chart', 'The Buzz Chart'), t('home.tileline.chart', 'A weekly ranking of wedding pages, based on RSVPs, wishes and shares.'), '/rankings'],
			['s-store', t('home.tile.vendors', 'Vendors'), t('home.tileline.vendors', 'Photographers, planners, caterers and gele artists, with reviews from couples.'), '/vendors'],
			['s-bag', t('home.tile.market', 'Marketplace'), t('home.tileline.market', 'Book aso-ebi, shortlets, rides and honeymoons. You message the seller and pay them directly.'), '/market'],
			['s-chat', t('home.tile.community', 'Anonymous Community'), t('home.tileline.community', 'Ask questions and share worries about relationships, weddings, marriage and family, under any pen name.'), '/community'],
		] as [$i, $tt, $sub, $href]): ?>
			<a class="tile tile--w2" href="<?= e(App::url($href)) ?>"><?= icon($i) ?><span class="v"><?= $tt ?></span><span class="k"><?= $sub ?></span></a>
		<?php endforeach; ?>
	</div>
</section>

<?= View::partial('partials/near', ['eyebrow' => t('home.eyebrow.near-you', 'Near you'), 'heading' => t('home.heading.weddings-in-your-city', 'Weddings in your city')]) ?>

<?php if ($trending): $ranks = Wedding::ranks(); ?>
<section class="sec wrap" id="ranked">
	<div class="sec-head reveal">
		<span class="eyebrow"><?= t('home.eyebrow.updated-as-guests-reply', 'Most active this week') ?></span>
		<h2><?= t('home.heading.trending-now', 'Trending now') ?></h2>
		<p><?= t('home.text.pages-climb-with-rsvps-wishes', 'Ranked by RSVPs, wishes and shares in the last 14 days.') ?></p>
	</div>
	<div class="rail reveal">
		<?php foreach ($trending as $w): ?>
			<?= wedding_card($w, trim((string) $w['city'] . ' · ' . Wedding::short($w), ' ·'), Site::tier((string) $w['plan']), '', $ranks[(int) $w['id']] ?? 0) ?>
		<?php endforeach; ?>
	</div>
	<p class="sec-more reveal"><a class="pill pill--glass pill--sm" href="<?= e(App::url('/weddings')) ?>"><?= t('home.button.explore-every-wedding', 'Explore every wedding') ?> <?= icon('arrow') ?></a> <a class="pill pill--glass pill--sm" href="<?= e(App::url('/rankings')) ?>"><?= t('home.button.see-the-buzz-chart', 'The Buzz Chart') ?></a></p>
</section>
<?php endif; ?>

<?php if (Auth::check()): ?>
<section class="sec wrap" id="designs">
	<div class="sec-head reveal">
		<span class="eyebrow"><?= t('home.eyebrow.ten-premium-designs-and-counting', 'Page designs') ?></span>
		<h2><?= t('home.heading.a-design-for-every-kind', 'Designs for your page') ?></h2>
		<p><?= t('home.text.every-page-is-built-from', 'Free pages are black and white. Pro pages use your colours. Premium pages choose from ten designs.') ?></p>
	</div>
	<div class="rail rail--designs reveal">
		<?php foreach ($samples as $k => $w): if (in_array($k, ['free', 'pro'], true)) { continue; } $d = Design::get($k); ?>
			<a class="card design-card" href="<?= e(App::url('/w/' . $w['slug'])) ?>" style="--s1:<?= e($d['swatch'][0]) ?>;--s2:<?= e($d['swatch'][1]) ?>">
				<span class="sq"><?= photo((string) $w['cover'], '') ?><span class="sq__tag"><?= e($d['name']) ?></span></span>
				<span class="card__cap"><span class="card__t"><?= e(Wedding::names($w)) ?></span><span class="card__s"><?= e($d['plan'] === 'custom' ? 'Custom design' : 'Premium') ?></span></span>
			</a>
		<?php endforeach; ?>
	</div>
	<p class="sec-more reveal"><a class="pill pill--glass pill--sm" href="<?= e(App::url('/templates')) ?>">Compare every design <?= icon('arrow') ?></a></p>
</section>
<?php endif; ?>

<?php if (count($countries) > 1): ?>
<section class="sec wrap" id="world">
	<div class="sec-head reveal">
		<span class="eyebrow"><?= t('home.eyebrow.wherever-the-family-is', 'Weddings by country') ?></span>
		<h2><?= t('home.heading.african-weddings-around-the-world', 'African weddings in every country') ?></h2>
		<p><?= tl('home.text.lagos-abuja-accra-nairobi-and', 'Lagos, Abuja, Accra, Nairobi and Kigali. London, Houston, Toronto, Melbourne, Auckland and Dubai. Every page in its own currency.') ?></p>
	</div>
	<div class="mosaic world reveal">
		<?php foreach ($countries as $c): ?>
			<?php $slug = Wedding::placeSlug((string) $c['country']); ?>
			<a class="tile world__i" href="<?= e(App::url($slug !== '' ? '/weddings/' . $slug : '/weddings?country=' . urlencode((string) $c['country']))) ?>"><span class="k"><?= (int) $c['n'] ?> page<?= (int) $c['n'] === 1 ? '' : 's' ?></span><span class="v"><?= e($c['country']) ?></span></a>
		<?php endforeach; ?>
	</div>
</section>
<?php endif; ?>

<section class="sec wrap" id="plans">
	<div class="sec-head reveal">
		<span class="eyebrow"><?= t('home.eyebrow.pay-once-per-wedding', 'Prices') ?></span>
		<h2><?= t('home.heading.pick-your-page', 'Plans') ?></h2>
		<p><?= t('home.text.free-pages-are-black-white', 'You pay once per wedding. No monthly fees.') ?></p>
		<?= View::partial('partials/currency', ['cur' => $cur, 'compact' => true]) ?>
	</div>
	<div class="plans reveal">
		<div class="glass glass--plain plan plan--mono" data-tone="mono">
			<p class="plan__name"><?= t('home.text.free', 'Free') ?></p>
			<p class="plan__price"><?= Fx::show(0, $cur) ?></p>
			<p class="plan__pitch"><?= t('home.text.everything-you-need-to-get', 'The basics: invitation, RSVP, wishes and gifts.') ?></p>
			<div class="plan__swatch" aria-hidden="true"><i style="background:#111"></i><i style="background:#fff"></i></div>
			<ul>
				<li><?= icon('check') ?><?= t('home.item.invitation-event-details', 'Invitation & event details') ?></li>
				<li><?= icon('check') ?><?= t('home.item.your-love-story', 'Your love story') ?></li>
				<li><?= icon('check') ?><?= t('home.item.rsvp-wall-of-wishes', 'RSVP & wall of wishes') ?></li>
				<li><?= icon('check') ?><?= t('home.item.cash-gift-registry', 'Cash & gift registry') ?></li>
			</ul>
			<a class="pill pill--glass pill--block" href="<?= e(App::url($free ? '/w/' . $free['slug'] : '/join')) ?>"><?= t('home.button.see-a-free-page', 'See a free page') ?></a>
		</div>
		<div class="glass plan">
			<p class="plan__name"><?= t('home.text.pro', 'Pro') ?></p>
			<p class="plan__price"><?= Fx::show($pro, $cur) ?></p>
			<p class="plan__pitch"><?= t('home.text.your-colours-your-squad-guest', 'Your colours, your squad, guest photos and entry passes. No ads.') ?></p>
			<div class="plan__swatch" aria-hidden="true"><i style="background:#6EAAE6"></i><i style="background:#E3DABF"></i><i style="background:#7B2D43"></i><i style="background:#C9A24B"></i></div>
			<ul>
				<li><?= icon('check') ?><?= t('home.item.everything-in-free-in-colour', 'Everything in Free, in colour') ?></li>
				<li><?= icon('check') ?><?= t('home.item.bridesmaids-groomsmen', 'Bridesmaids & groomsmen') ?></li>
				<li><?= icon('check') ?><?= t('home.item.guest-photo-gallery', 'Guest photo gallery') ?></li>
				<li><?= icon('check') ?><?= t('home.item.entry-passes-checkin', 'Entry passes & check-in') ?></li>
			</ul>
			<a class="pill pill--glass pill--block" href="<?= e(App::url(isset($samples['pro']) ? '/w/' . $samples['pro']['slug'] : '/pricing')) ?>"><?= t('home.button.see-a-pro-page', 'See a Pro page') ?></a>
		</div>
		<div class="glass plan plan--best">
			<p class="plan__name">Premium <span class="badge badge--solid">Full owambe</span></p>
			<p class="plan__price"><?= Fx::show($prem, $cur) ?></p>
			<p class="plan__pitch"><?= t('home.text.every-feature-and-any-of', 'Every feature and any Premium design. Our team sets up your page and keeps it updated. No fees on gifts.') ?></p>
			<div class="plan__swatch" aria-hidden="true"><?php foreach (array_slice($premiumDesigns, 1, 6) as $d): ?><i style="background:linear-gradient(135deg,<?= e($d[6][0]) ?>,<?= e($d[6][1]) ?>)"></i><?php endforeach; ?></div>
			<ul>
				<li><?= icon('check') ?><?= t('home.item.we-set-up-your-page', 'We set up your page and keep it updated') ?></li>
				<li><?= icon('check') ?><?= t('home.item.ten-premium-designs', 'Ten Premium designs') ?></li>
				<li><?= icon('check') ?><?= t('home.item.background-music-on-your-page', 'Background music on your page') ?></li>
				<li><?= icon('check') ?><?= t('home.item.voice-notes-on-your-wall', 'Voice notes on your wall of love') ?></li>
				<li><?= icon('check') ?><?= t('home.item.livestream-on-your-page', 'Livestream on your page') ?></li>
				<li><?= icon('check') ?><?= t('home.item.spraying-from-the-seat', 'Spraying from the seat') ?></li>
				<li><?= icon('check') ?><?= t('home.item.no-charge-on-gifts-or', 'No charge on gifts or spraying') ?></li>
			</ul>
			<a class="pill pill--block" href="<?= e(App::url($lead ? '/w/' . $lead['slug'] : '/pricing')) ?>"><?= t('home.button.see-a-premium-page', 'See a Premium page') ?></a>
		</div>
	</div>
	<p class="sec-more reveal"><a class="linkish" href="<?= e(App::url('/pricing')) ?>"><?= t('home.button.compare-every-plan-including-high', 'Compare every plan, including High Society') ?></a> · <a class="linkish" href="<?= e(App::url('/pricing#currency')) ?>"><?= t('home.button.see-prices-in-another-currency', 'See prices in another currency') ?></a></p>
</section>

<?php if ($fresh): ?>
<section class="sec wrap">
	<div class="split split--list">
		<div class="sec-head sec-head--side reveal">
			<span class="eyebrow"><?= t('home.eyebrow.coming-up', 'Coming up') ?></span>
			<h2><?= t('home.heading.weddings-this-season', 'Upcoming weddings') ?></h2>
			<p><?= tl('home.text.every-public-page-soonest-first', 'Every public page, soonest first. Open one, RSVP if you are invited, or just enjoy the story.') ?></p>
			<a class="pill pill--glass pill--sm" href="<?= e(App::url('/weddings')) ?>">Browse every page <?= icon('arrow') ?></a>
		</div>
		<div class="rows reveal">
			<?php foreach (array_slice($fresh, 0, 5) as $w): ?>
				<a class="row-item" href="<?= e(App::url('/w/' . $w['slug'])) ?>">
					<?= sq((string) $w['cover'], '', 'row-item__pic', false, initials($w)) ?>
					<span><span class="row-item__t"><?= e(Wedding::names($w)) ?></span><span class="row-item__s"><?= e(trim((string) $w['city'] . ' · ' . Site::tier((string) $w['plan']), ' ·')) ?></span></span>
					<span class="row-item__end"><?= e(Wedding::short($w)) ?><span class="sub"><?php $dl = Wedding::daysTo($w); echo $dl !== null && $dl >= 0 ? e($dl . ($dl === 1 ? ' day' : ' days')) : ''; ?></span></span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<?php endif; ?>

<?php if ($listings): ?>
<section class="sec wrap" id="market">
	<div class="sec-head reveal">
		<span class="eyebrow"><?= t('home.eyebrow.marketplace', 'Marketplace') ?></span>
		<h2><?= t('home.heading.asoebi-stays-rides', 'Aso-ebi, shortlets & rides') ?></h2>
	</div>
	<div class="rail reveal">
		<?php foreach (array_slice($listings, 0, 4) as $l): ?>
			<a class="card" href="<?= e(App::url('/market/' . $l['slug'])) ?>">
				<?= sq(first_photo((string) $l['photos']), (string) $l['title']) ?>
				<span class="card__cap"><span class="card__t"><?= e($l['title']) ?></span><span class="card__s"><?= (int) $l['on_ask'] ? 'Price on request' : money(short_money((int) $l['price'])) . ' ' . e((string) $l['price_unit']) ?><?= !empty($l['seller']) ? ' · ' . e((string) $l['seller']) : ((string) $l['city'] !== '' ? ' · ' . e($l['city']) : '') ?></span></span>
			</a>
		<?php endforeach; ?>
	</div>
	<p class="sec-more reveal"><a class="pill pill--glass pill--sm" href="<?= e(App::url('/market')) ?>">Open the marketplace <?= icon('arrow') ?></a></p>
</section>
<?php endif; ?>

<?php if ($vendors): ?>
<section class="sec wrap">
	<div class="split split--list">
		<div class="sec-head sec-head--side reveal">
			<span class="eyebrow"><?= t('home.eyebrow.rated-by-couples', 'Rated by couples') ?></span>
			<h2><?= t('home.heading.the-people-behind-the-day', 'Wedding vendors') ?></h2>
			<p><?= tl('home.text.photographers-planners-caterers-gele-and', 'Photographers, planners, caterers, gele and makeup, every one reviewed by a couple who booked them.') ?></p>
			<a class="pill pill--glass pill--sm" href="<?= e(App::url('/vendors')) ?>">Find a vendor <?= icon('arrow') ?></a>
		</div>
		<div class="rows reveal">
			<?php foreach (array_slice($vendors, 0, 4) as $v): ?>
				<a class="row-item" href="<?= e(App::url('/vendors/' . $v['slug'])) ?>">
					<?= sq((string) $v['logo'], '', 'row-item__pic') ?>
					<span><span class="row-item__t"><?= e($v['name']) ?><?php if ((int) $v['verified']): ?> <span class="badge"><?= icon('check') ?>Verified</span><?php endif; ?></span><span class="row-item__s"><?= e($v['category'] . ' · ' . $v['city']) ?></span></span>
					<span class="row-item__end"><?= (float) $v['rating'] > 0 ? e(number_format((float) $v['rating'], 1)) : '' ?><span class="sub"><?= (int) $v['reviews'] ?> reviews</span></span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<?php endif; ?>

<section class="sec wrap">
	<div class="split">
		<div class="quote-cta reveal">
			<div class="sec-head">
				<span class="eyebrow"><?= t('home.eyebrow.community-no-account-needed', 'Anonymous Community · no account needed') ?></span>
				<h2><?= t('home.heading.say-it-here-instead', 'Ask, share and get advice') ?></h2>
				<p><?= tl('home.text.textonly-posts-on-colour-under', 'Text-only posts on colour, under any pen name you like. Swipe through what other people are carrying.') ?></p>
			</div>
			<a class="pill" href="<?= e(App::url('/community')) ?>"><?= t('home.button.open-the-community', 'Open the Anonymous Community') ?></a>
		</div>
		<?php if ($talks): ?>
			<div class="home-deck reveal">
				<div class="deck" data-deck data-more="<?= e(App::url('/community')) ?>" tabindex="0" aria-label="Community posts. Swipe to move between them.">
					<?php foreach (array_slice($talks, 0, 3) as $k => $t): ?>
						<?php if ($k === 2): ?><?= Promo::html('home', 1) ?><?php endif; ?>
						<article class="post <?= e(talk_colour((string) $t['bg'])) ?>" data-id="<?= (int) $t['id'] ?>" data-href="<?= e(App::url('/community/' . $t['id'])) ?>" data-like="<?= e(App::url('/community/' . $t['id'] . '/like')) ?>" data-likes="<?= (int) $t['likes'] ?>" data-replies="<?= (int) $t['replies'] ?>" data-liked="0">
							<span class="post__topic"><?= e(Talk::topic((string) $t['topic'])) ?></span>
							<div class="post__body"><p class="post__text<?= mb_strlen((string) $t['body']) > 140 ? ' post__text--long' : '' ?>"><?= e(mb_strimwidth((string) $t['body'], 0, 240, '…')) ?></p><span class="post__mark"><i aria-hidden="true">&amp;</i><?= e(Share::host()) ?></span></div>
							<span class="post__meta"><?= e($t['pen_name'] ?: 'Someone') ?> · <?= (int) $t['replies'] ?> replies</span>
						</article>
					<?php endforeach; ?>
				</div>
				<p class="deck-pos" data-deck-pos aria-live="polite"><?= t('home.text.1-of-3-swipe-for', '1 of 3 · swipe for more') ?></p>
				<div class="actions">
					<div class="act"><button class="cbtn cbtn--lg" type="button" data-deck-go="-1" aria-label="Previous post"><?= icon('back') ?></button><span class="act__l">Back</span></div>
					<div class="act"><button class="cbtn cbtn--lg" type="button" data-post-like aria-label="Love this post" aria-pressed="false"><?= icon('heart') ?><span class="act__n" data-count="likes"><?= (int) $talks[0]['likes'] ?></span></button><span class="act__l">Love</span></div>
					<div class="act"><a class="cbtn cbtn--lg" href="<?= e(App::url('/community/' . $talks[0]['id'])) ?>" data-post-open aria-label="Read and write replies"><?= icon('chat') ?><span class="act__n" data-count="replies"><?= (int) $talks[0]['replies'] ?></span></a><span class="act__l">Reply</span></div>
					<div class="act"><button class="cbtn cbtn--lg" type="button" data-share="<?= e(App::url('/community/' . $talks[0]['id'])) ?>" data-post-share aria-label="Share this post"><?= icon('share') ?></button><span class="act__l">Share</span></div>
					<div class="act"><button class="cbtn cbtn--lg" type="button" data-deck-go="1" aria-label="Next post"><?= icon('next') ?></button><span class="act__l">Next</span></div>
				</div>
				<?= Guard::field() ?>
			</div>
		<?php endif; ?>
	</div>
</section>

<?php if ($wall): ?>
<section class="sec wrap">
	<div class="sec-head reveal">
		<span class="eyebrow"><?= t('home.eyebrow.left-on-real-pages-this', 'Messages guests left this week') ?></span>
		<h2><?= t('home.heading.wall-of-love', 'Wall of love') ?></h2>
	</div>
	<div class="rail rail--wishes reveal">
		<?php foreach ($wall as $n): if ((string) $n['kind'] !== 'note' || trim((string) $n['body']) === '') { continue; } ?>
			<a class="wish wish--card" href="<?= e(App::url('/w/' . $n['slug'] . '#wall')) ?>">
				<p><?= e(mb_strimwidth((string) $n['body'], 0, 140, '…')) ?></p>
				<span><?= e($n['name'] ?: 'A guest') ?> · for <?= e($n['partner_a'] . ' & ' . $n['partner_b']) ?></span>
			</a>
		<?php endforeach; ?>
	</div>
</section>
<?php endif; ?>

<?php if ($posts): ?>
<section class="sec wrap">
	<div class="sec-head reveal">
		<span class="eyebrow"><?= t('home.eyebrow.from-the-blog', 'From the blog') ?></span>
		<h2><?= t('home.heading.guides-news-interviews', 'Guides, news & interviews') ?></h2>
	</div>
	<div class="rail reveal">
		<?php foreach ($posts as $p): ?>
			<a class="card" href="<?= e(App::url('/blog/' . $p['slug'])) ?>">
				<?= sq((string) $p['cover'], '', 'sq--wide') ?>
				<span class="card__cap"><span class="card__t"><?= e($p['title']) ?></span><span class="card__s"><?= e(Blog::KIND[(string) ($p['kind'] ?? 'guide')] ?? 'Guide') ?> · <?= Blog::minutes($p) ?> min read</span></span>
			</a>
		<?php endforeach; ?>
	</div>
	<p class="sec-more reveal"><a class="pill pill--glass pill--sm" href="<?= e(App::url('/blog')) ?>">Read the blog <?= icon('arrow') ?></a></p>
</section>
<?php endif; ?>

<?php $finalePic = timg('home.image.finale', ''); ?>
<section class="sec wrap">
	<div class="glass finale reveal<?= $finalePic !== '' ? ' finale--pic' : '' ?>"<?= $finalePic !== '' ? ' style="--finale:url(\'' . e(img_url($finalePic)) . '\')"' : '' ?>>
		<span class="eyebrow"><?= t('home.eyebrow.it-takes-about-ten-minutes', 'It takes about ten minutes') ?></span>
		<h2><?= t('home.heading.make-your-page-tonight', 'Make your wedding page') ?></h2>
		<p><?= tl('home.text.start-free-in-black-white', 'Answer a few questions and your page is ready to share. You can move to Pro or Premium at any time.') ?></p>
		<div class="hero__ctas">
			<a class="pill" href="<?= e(App::url('/join')) ?>"><?= t('home.button.create-your-page-free-2', 'Create your page') ?></a>
			<a class="pill pill--glass" href="<?= e(App::url('/pricing')) ?>"><?= t('home.button.see-pricing', 'See pricing') ?></a>
		</div>
	</div>
</section>
