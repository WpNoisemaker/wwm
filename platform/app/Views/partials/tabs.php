<?php $now = $tab ?? ''; ?>
<nav class="tabs" aria-label="Main">
	<a href="<?= e(App::url('/')) ?>"<?= $now === 'home' ? ' class="is-on" aria-current="page"' : '' ?>><?= icon('home', 21) ?><span>Home</span></a>
	<a href="<?= e(App::url('/weddings')) ?>"<?= $now === 'weddings' ? ' class="is-on" aria-current="page"' : '' ?>><?= icon('rings', 21) ?><span>Weddings</span></a>
	<a class="tabs__fab" href="<?= e(App::url('/join')) ?>" aria-label="Create a wedding page"><?= icon('plus', 22) ?></a>
	<a href="<?= e(App::url('/community')) ?>"<?= $now === 'community' ? ' class="is-on" aria-current="page"' : '' ?>><?= icon('talk', 21) ?><span>Community</span></a>
	<a href="<?= e(App::url(Auth::check() ? '/account' : '/join')) ?>"<?= $now === 'account' ? ' class="is-on" aria-current="page"' : '' ?>><?= icon('user', 21) ?><span><?= Auth::check() ? 'You' : 'Join' ?></span></a>
</nav>
