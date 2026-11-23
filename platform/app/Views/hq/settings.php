<?= View::partial('hq/_head', ['h' => 'Settings', 'sub' => 'These drive the pricing page and the fee on every gift', 'now' => 'settings']) ?>
	<div class="split-2">
		<form method="post" class="form glass glass--plain glass--tight acct-card">
			<?= Guard::field() ?>
			<h2 class="h3">Plans</h2>
			<div class="form-grid form-grid--2">
				<label class="field"><span>Pro price</span><input class="input" name="pro_price" type="number" min="0" inputmode="numeric" value="<?= e($v['pro_price']) ?>"></label>
				<label class="field"><span>Premium price</span><input class="input" name="premium_price" type="number" min="0" inputmode="numeric" value="<?= e($v['premium_price']) ?>"></label>
			</div>
			<label class="field"><span>Service fee on cash gifts (%)</span><input class="input" name="fee_gift" type="number" min="0" max="20" step="0.1" value="<?= e($v['fee_gift']) ?>"></label>
			<p class="help">Applied to Free and Pro pages. Premium and above pay none.</p>
			<h2 class="h3 gap-t-sm">Exchange rates</h2>
			<p class="help">Naira for one unit of each currency. Visitors abroad see plan prices converted at these rates on the pricing page.</p>
			<div class="form-grid form-grid--2">
				<?php foreach ($fx as $k => $rate): if ($k === 'NGN') { continue; } ?>
					<label class="field"><span><?= e(trim(cur_sign($k))) ?> <?= e(Fx::NAMES[$k]) ?></span><input class="input" name="fx[<?= e($k) ?>]" inputmode="decimal" value="<?= e(rtrim(rtrim(number_format((float) $rate, 2, '.', ''), '0'), '.')) ?>"></label>
				<?php endforeach; ?>
			</div>
			<h2 class="h3 gap-t-sm">Contact</h2>
			<label class="field"><span>Contact email</span><input class="input" name="email" type="email" value="<?= e($v['email']) ?>"></label>
			<label class="field"><span>WhatsApp number</span><input class="input" name="whatsapp" value="<?= e($v['whatsapp']) ?>" placeholder="2348000000000"></label>
			<p class="help">Used for the High Society quote link only. It does not switch on WhatsApp notifications; those stay off until a provider is configured.</p>
			<h2 class="h3 gap-t-sm">Email confirmation</h2>
			<label class="check"><span>Ask new sign-ups to confirm their email</span><input type="checkbox" name="email_verify" value="1" <?= $verify['on'] ? 'checked' : '' ?>></label>
			<p class="help">Off until you decide. When it is on, anyone who signs up from that moment must follow a link before making their page public or asking for a payout. Existing accounts are never asked.<?php if ($verify['on'] && $verify['since'] !== ''): ?> On since <?= e(date('j M Y, g:ia', (int) strtotime($verify['since']))) ?>.<?php endif; ?></p>
			<button class="pill pill--block" type="submit">Save</button>
		</form>
		<aside class="acct-list">
			<form method="post" action="<?= e(App::url('/hq/test-mail')) ?>" class="form glass glass--plain glass--tight">
				<?= Guard::field() ?>
				<h2 class="h3">Check that email works</h2>
				<p class="help">Sends a test to <?= e((string) Auth::user()['email']) ?>. If it does not arrive, leave confirmation off.</p>
				<button class="pill pill--glass pill--block" type="submit">Send me a test email</button>
			</form>
			<div class="form glass glass--plain glass--tight" id="packs">
				<h2 class="h3">From the old site</h2>
				<?php foreach (Pack::ALL as $pk => $pn): $here = Pack::present($pk); ?>
					<div class="row-item row-item--plain">
						<span><span class="row-item__t"><?= e($pn) ?></span><span class="row-item__s"><?= $here ? e(Pack::ABOUT[$pk][1] ?? 'On this site') : 'Not added yet' ?></span></span>
						<?php if ($here): ?><a class="pill pill--sm pill--glass" href="<?= e(App::url($pk === 'market' ? '/market' : '/w/' . $pk)) ?>">Open</a>
						<?php elseif (Auth::is('owner')): ?><form method="post" action="<?= e(App::url('/hq/packs/' . $pk)) ?>"><?= Guard::field() ?><button class="pill pill--sm" type="submit">Restore</button></form><?php endif; ?>
					</div>
				<?php endforeach; ?>
				<p class="help">Adds each page, seller and account exactly as the old site had them. It never overwrites anything already here.</p>
			</div>
			<div class="form glass glass--plain glass--tight">
				<h2 class="h3">Demo content</h2>
				<?php if ($demo['present']): ?>
					<p class="help">Installed: <?= (int) ($demo['counts']['weddings'] ?? 0) ?> wedding pages, <?= (int) ($demo['counts']['guests'] ?? 0) ?> guests, <?= (int) ($demo['counts']['vendors'] ?? 0) ?> vendors, <?= (int) ($demo['counts']['listings'] ?? 0) ?> listings, <?= (int) ($demo['counts']['talks'] ?? 0) ?> community posts and <?= (int) ($demo['counts']['users'] ?? 0) ?> accounts.</p>
					<p class="note note--bad"><?= icon('lock') ?><span>The demo accounts share the published password <b><?= e($demo['password']) ?></b>. Remove the demo before you launch.</span></p>
					<div class="rows">
						<?php foreach ($demo['logins'] as $l): ?><div class="row-item row-item--plain"><span><span class="row-item__t"><?= e($l['email']) ?></span><span class="row-item__s"><?= e($l['who']) ?></span></span></div><?php endforeach; ?>
					</div>
					<?php if (Auth::is('owner')): ?>
						<form method="post" action="<?= e(App::url('/hq/demo/remove')) ?>" data-confirm="Delete all the demo content? This removes the demo pages, vendors, listings, community posts and every demo account. Anything real is not touched."><?= Guard::field() ?><button class="pill pill--danger pill--block" type="submit">Remove all demo content</button></form>
					<?php endif; ?>
				<?php else: ?>
					<p class="help">No demo content on this site. Adding it gives you a wedding page for every design and plan, vendors, listings, the community and accounts to sign in with.</p>
					<?php if (Auth::is('owner')): ?><form method="post" action="<?= e(App::url('/hq/demo/add')) ?>"><?= Guard::field() ?><button class="pill pill--glass pill--block" type="submit">Add demo content</button></form><?php endif; ?>
				<?php endif; ?>
			</div>
		</aside>
	</div>
</div>
