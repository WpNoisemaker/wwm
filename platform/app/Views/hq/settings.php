<?= View::partial('hq/_head', ['h' => 'Settings', 'sub' => 'These drive the pricing page and the fee on every gift', 'now' => 'settings']) ?>
	<div class="split-2">
		<form method="post" class="form glass glass--plain glass--tight acct-card">
			<?= Guard::field() ?>
			<h2 class="h3">Plans</h2>
			<div class="form-grid form-grid--2">
				<label class="field"><span>Pro price</span><input class="input" name="pro_price" type="number" min="0" inputmode="numeric" value="<?= e($v['pro_price']) ?>"></label>
				<label class="field"><span>Premium price</span><input class="input" name="premium_price" type="number" min="0" inputmode="numeric" value="<?= e($v['premium_price']) ?>"></label>
				<label class="field"><span>Premium seller, per month (own colours, font and logo on invoices). 0 = ask us on WhatsApp</span><input class="input" name="seller_premium_price" type="number" min="0" inputmode="numeric" value="<?= e(Site::setting('seller_premium_price', '0')) ?>"></label>
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
			<h2 class="h3 gap-t-sm">Bachs: bank transfers</h2>
			<p class="help">Each couple gets their own account number for gifts and spraying. Guests transfer, and the page shows it the moment it lands. <?= Bachs::on() ? '<b>Connected' . (str_starts_with(Bachs::key(), 'sk_live_') ? ' (live)' : ' (sandbox)') . '.</b>' : '<b>Not connected.</b>' ?></p>
			<label class="field"><span>Secret key (sk_sandbox_… or sk_live_…)<?= Bachs::on() ? ' · saved; type to replace' : '' ?></span><input class="input" name="bachs_key" type="password" autocomplete="new-password"></label>
			<label class="field"><span>Webhook signing secret (whsec_…)<?= Site::setting('bachs_whsec') !== '' ? ' · saved; type to replace' : '' ?></span><input class="input" name="bachs_whsec" type="password" autocomplete="new-password"></label>
			<label class="field"><span>Connected-account header</span><select class="input" name="bachs_acct_header"><?php foreach (['X-Connected-Account-ID', 'X-Account-Id'] as $hh): ?><option<?= Site::setting('bachs_acct_header', 'X-Connected-Account-ID') === $hh ? ' selected' : '' ?>><?= $hh ?></option><?php endforeach; ?></select></label>
			<p class="help">In Bachs, add a webhook for <b>collection.succeeded</b> to <code><?= e(App::url('/webhooks/bachs')) ?></code>. Card payments stay off for now.</p>
			<h2 class="h3 gap-t-sm">Ads</h2>
			<p class="help">Turn ads on or off, choose where they show and change every ad in <a class="linkish" href="<?= e(App::url('/hq/ads')) ?>">Ads</a>.</p>
			<button class="pill pill--block" type="submit">Save</button>
		</form>
		<aside class="acct-list">
			<form method="post" action="<?= e(App::url('/hq/test-mail')) ?>" class="form glass glass--plain glass--tight">
				<?= Guard::field() ?>
				<h2 class="h3">Check that email works</h2>
				<p class="help">Sends a test to <?= e((string) Auth::user()['email']) ?>. If it does not arrive, leave confirmation off.</p>
				<button class="pill pill--glass pill--block" type="submit">Send me a test email</button>
				<a class="pill pill--glass pill--block" href="<?= e(App::url('/hq/emails')) ?>"><?= icon('mail') ?>See what our emails look like</a>
			</form>
			<div class="form glass glass--plain glass--tight" id="drive">
				<div class="row row--between"><h2 class="h3">Google Drive for guest photos</h2><?php if (Drive::connected()): ?><span class="badge badge--solid"><?= icon('check') ?>Connected</span><?php endif; ?></div>
				<?php $dc = Drive::counts(); ?>
				<?php if (Drive::connected()): ?>
					<p class="help">Connected as <b><?= e(Site::setting('drive_email', 'your Google account')) ?></b>. Each wedding saves its guests' photos, full size, to its own folder; give weddings their folder in <a class="linkish" href="<?= e(App::url('/hq/weddings')) ?>">Weddings</a>.</p>
					<p class="help"><?= (int) $dc['sent'] ?> sent · <?= (int) $dc['waiting'] ?> waiting<?= $dc['failed'] ? ' · ' . (int) $dc['failed'] . ' could not be sent' : '' ?></p>
					<form method="post" action="<?= e(App::url('/hq/drive')) ?>" class="form-actions"><?= Guard::field() ?><button class="pill pill--sm" type="submit" name="do" value="sync">Send waiting photos now</button><button class="pill pill--sm pill--glass" type="submit" name="do" value="disconnect" data-confirm="Disconnect Google Drive? New photos wait on the server until you connect again.">Disconnect</button></form>
				<?php else: ?>
					<p class="help">Guests' photos are kept on the server until Google Drive is connected<?= $dc['waiting'] ? ' (' . (int) $dc['waiting'] . ' waiting now)' : '' ?>.</p>
				<?php endif; ?>
				<details class="faq__q"<?= Drive::configured() ? '' : ' open' ?>>
					<summary><span><?= Drive::configured() ? 'Google keys and options' : 'Set it up' ?></span><i class="faq__i" aria-hidden="true"></i></summary>
					<form method="post" action="<?= e(App::url('/hq/drive')) ?>" class="form">
						<?= Guard::field() ?><input type="hidden" name="do" value="keys">
						<p class="help">In Google Cloud Console, make a project, turn on the Google Drive API, and create an OAuth client of type <b>Web application</b> with this redirect address:</p>
						<p><code class="code-line"><?= e(Drive::redirect()) ?></code> <button class="pill pill--sm pill--glass" type="button" data-copy="<?= e(Drive::redirect()) ?>">Copy</button></p>
						<label class="field"><span>Client ID</span><input class="input" name="client_id" value="<?= e(Site::setting('drive_client_id')) ?>" autocomplete="off"></label>
						<label class="field"><span>Client secret<?= Site::setting('drive_client_secret') !== '' ? ' (saved; type to replace)' : '' ?></span><input class="input" name="client_secret" type="password" autocomplete="new-password"></label>
						<label class="field"><span>Parent folder for new wedding folders (optional)</span><input class="input" name="parent" value="<?= e(Site::setting('drive_parent')) ?>" placeholder="Paste a Drive folder link"></label>
						<label class="check"><span>Let the platform use folders I make myself in Drive (asks Google for full Drive access)</span><input type="checkbox" name="full" value="1"<?= Drive::full() ? ' checked' : '' ?>></label>
						<p class="help">Left off, the platform only touches the folders it makes for each wedding, and Google does not need to review the app.</p>
						<button class="pill pill--block" type="submit">Save</button>
					</form>
				</details>
				<?php if (Drive::configured()): ?><a class="pill pill--block<?= Drive::connected() ? ' pill--glass' : '' ?>" href="<?= e(App::url('/hq/drive/connect')) ?>" data-no-prefetch><?= Drive::connected() ? 'Reconnect Google Drive' : 'Connect Google Drive' ?></a><?php endif; ?>
			</div>
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
