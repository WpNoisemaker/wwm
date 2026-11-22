<?php
/**
 * One-time web installer.
 *
 * Open it in a browser once after uploading, fill in the database details cPanel gave you, and it
 * writes config/config.php, builds the 24 tables, and makes the first owner account. It refuses to
 * run again afterwards, and tells you to delete it.
 *
 * It deliberately has no dependencies and talks to PDO directly, because at the moment it runs
 * nothing else is configured yet.
 */
declare(strict_types=1);

const ROOT   = __DIR__;
const CONFIG = ROOT . '/config/config.php';
const SCHEMA = ROOT . '/db/schema.sql';

/* ---------------------------------------------------------------- already installed? ---------- */
$installed = false;
if (is_file(CONFIG)) {
    $c = @include CONFIG;
    $installed = is_array($c) && !empty($c['db']['name']);
}

function h(mixed $v): string { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); }

/* ---------------------------------------------------------------- environment check ----------- */
$checks = [
    ['PHP 8.1 or newer', PHP_VERSION_ID >= 80100, 'Found ' . PHP_VERSION . '. Set it in cPanel > Select PHP Version.'],
    ['PDO MySQL driver', extension_loaded('pdo_mysql'), 'Tick pdo_mysql in cPanel > Select PHP Version > Extensions.'],
    ['mbstring',         extension_loaded('mbstring'), 'Tick mbstring in the same place.'],
    ['JSON',             extension_loaded('json'),     'Tick json in the same place.'],
    ['config/ writable', is_writable(ROOT . '/config'), 'chmod the config folder to 755 in File Manager.'],
    ['Schema file present', is_file(SCHEMA), 'db/schema.sql is missing from the upload.'],
];
$ready = array_reduce($checks, static fn($ok, $c) => $ok && $c[1], true);

/* ---------------------------------------------------------------- do the install -------------- */
$errors    = [];
$done      = false;
$demo      = null;   // set to the demo summary when demo content was added
$demoError = '';

if (!$installed && $ready && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $host  = trim((string) ($_POST['host'] ?? 'localhost'));
    $name  = trim((string) ($_POST['name'] ?? ''));
    $user  = trim((string) ($_POST['user'] ?? ''));
    $pass  = (string) ($_POST['pass'] ?? '');
    $url   = rtrim(trim((string) ($_POST['url'] ?? '')), '/');
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $pw    = (string) ($_POST['pw'] ?? '');
    $who   = trim((string) ($_POST['who'] ?? ''));

    if ($name === '' || $user === '')              { $errors[] = 'Database name and user are both required.'; }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = 'That does not look like an email address.'; }
    if (strlen($pw) < 10)                           { $errors[] = 'Give the owner account a password of at least 10 characters.'; }
    if ($url === '' || !preg_match('~^https?://~', $url)) { $errors[] = 'Site address must start with http:// or https://'; }

    $pdo = null;
    if (!$errors) {
        try {
            $pdo = new PDO(
                "mysql:host={$host};dbname={$name};charset=utf8mb4",
                $user,
                $pass,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_EMULATE_PREPARES => false]
            );
        } catch (Throwable $e) {
            $errors[] = 'Could not connect to the database: ' . $e->getMessage();
        }
    }

    if (!$errors && $pdo) {
        try {
            // Run the schema. Comment lines are stripped FIRST: the tables are introduced by
            // banner comments, and testing whether a chunk begins with "--" would skip the
            // statement underneath the banner along with it. Splitting on a semicolon that ends a
            // line is safe here because schema.sql holds no stored routines.
            $sql = (string) file_get_contents(SCHEMA);
            $sql = preg_replace('/^\s*--[^\n]*$/m', '', $sql) ?? $sql;
            $ran = 0;
            foreach (preg_split('/;\s*\R/', $sql) as $stmt) {
                $stmt = trim($stmt);
                if ($stmt !== '') {
                    $pdo->exec($stmt);
                    $ran++;
                }
            }
            if ($ran < 20) {
                throw new RuntimeException("Only {$ran} statements ran from the schema; it looks truncated. Re-upload db/schema.sql.");
            }

            // The first account owns the place.
            $ins = $pdo->prepare('INSERT INTO users (email, pass, name, role) VALUES (?, ?, ?, "owner")
                                  ON DUPLICATE KEY UPDATE pass = VALUES(pass), role = "owner"');
            $ins->execute([$email, password_hash($pw, PASSWORD_DEFAULT), $who !== '' ? $who : 'Owner']);

            // Sensible starting settings so the pricing page is not blank on day one.
            $set = $pdo->prepare('INSERT INTO settings (k, v) VALUES (?, ?) ON DUPLICATE KEY UPDATE v = VALUES(v)');
            foreach ([
                'pro_price'     => '198000',
                'premium_price' => '448000',
                'fee_gift'      => '4',
                'email'         => $email,
            ] as $k => $v) {
                $set->execute([$k, $v]);
            }

            // The four how-tos double as the product's own explanation, so a fresh site is not
            // empty on day one. Optional: if the file is missing the install still succeeds.
            $starter = ROOT . '/db/starter.sql';
            if (is_file($starter)) {
                try {
                    foreach (preg_split('/;\s*\R/', (string) file_get_contents($starter)) as $stmt) {
                        $stmt = trim($stmt);
                        if ($stmt !== '') { $pdo->exec($stmt); }
                    }
                } catch (Throwable $ignored) {
                    // Starter content is a nicety, never a reason to fail an install.
                }
            }

            // Mail has to appear to come from the site's own domain. An owner address at gmail.com would
            // be rejected or sent to spam by anyone checking that the sending server may speak for it.
            // Local test addresses (an IP, "localhost") have no domain to use, so they fall back to the owner.
            // Named $siteHost, NOT $host: $host already holds the DATABASE host from the form, and reusing
            // it here overwrote that with the site's domain just before the config was written.
            $siteHost = strtolower((string) (parse_url($url, PHP_URL_HOST) ?: ''));
            $siteHost = (string) preg_replace('/^www\./', '', $siteHost);
            $mailFrom = ($siteHost !== '' && str_contains($siteHost, '.') && !filter_var($siteHost, FILTER_VALIDATE_IP))
                      ? 'noreply@' . $siteHost
                      : $email;

            $cfg = "<?php\n"
                 . "/** Written by install.php. Keep it out of version control. */\n"
                 . "return array(\n"
                 . "\t'db'    => array('host' => " . var_export($host, true) . ", 'name' => " . var_export($name, true)
                 . ", 'user' => " . var_export($user, true) . ", 'pass' => " . var_export($pass, true) . ", 'port' => 3306),\n"
                 . "\t'url'   => " . var_export($url, true) . ",\n"
                 . "\t'name'  => 'Wedding Websites & More',\n"
                 . "\t'key'   => " . var_export(bin2hex(random_bytes(24)), true) . ",\n"
                 . "\t'mail'  => array('from' => " . var_export($mailFrom, true) . ", 'name' => 'Wedding Websites & More'),\n"
                 . "\t'debug' => false,\n"
                 . "\t'tz'    => 'Africa/Lagos',\n"
                 . ");\n";

            if (@file_put_contents(CONFIG, $cfg) === false) {
                $errors[] = 'Everything else worked, but config/config.php could not be written. Make the config folder writable and try again.';
            } else {
                @chmod(CONFIG, 0640);
                $done = true;

                // Demo content, if they asked for it. It runs through the app itself, which is why it
                // waits until the config exists. A failure here never fails the install: the site is
                // already usable, and the same thing can be done later from HQ.
                require_once ROOT . '/app/Core/App.php';
                App::boot(ROOT);
                // Real couples' pages carried over from the old site. Never fails the install.
                foreach (array_keys(Pack::ALL) as $packKey) {
                    try { Pack::install($packKey); } catch (Throwable $e) { error_log('[wwm] pack ' . $packKey . ': ' . $e->getMessage()); }
                }
                if (!empty($_POST['demo'])) {
                    try {
                        $demo = Demo::install();
                    } catch (Throwable $e) {
                        $demoError = $e->getMessage();
                    }
                }
            }
        } catch (Throwable $e) {
            $errors[] = 'Install failed: ' . $e->getMessage();
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Install — Wedding Websites &amp; More</title>
<style>
	:root { --sun:#FFD426; --ink:#160E22; --bg:#F4F3F1; --card:#fff; --muted:#6E6680; --bad:#C0392B; --ok:#1E9E62; }
	* { box-sizing:border-box; }
	body { margin:0; padding:28px 16px 60px; background:var(--bg); color:var(--ink);
	       font:16px/1.55 ui-sans-serif,system-ui,-apple-system,"Segoe UI",Roboto,sans-serif; }
	.w { max-width:640px; margin:0 auto; }
	.mark { display:grid; place-items:center; width:46px; height:46px; border-radius:15px;
	        background:var(--ink); color:var(--sun); font-size:24px; font-weight:700; }
	h1 { font-size:1.9rem; letter-spacing:-.03em; margin:18px 0 6px; font-weight:500; }
	p.lede { color:var(--muted); margin:0 0 22px; }
	.card { background:var(--card); border-radius:20px; padding:22px; margin-bottom:14px;
	        box-shadow:0 1px 2px rgba(22,14,34,.05), 0 14px 34px -22px rgba(22,14,34,.3); }
	h2 { font-size:1rem; margin:0 0 12px; font-weight:600; }
	label { display:block; font-size:.86rem; font-weight:500; margin:12px 0 5px; }
	input { width:100%; padding:11px 13px; border:1px solid #DDD9E2; border-radius:11px;
	        font:inherit; background:#FCFCFB; }
	input:focus { outline:2px solid var(--sun); outline-offset:1px; border-color:transparent; }
	small { display:block; color:var(--muted); font-size:.78rem; margin-top:4px; }
	button { margin-top:18px; width:100%; padding:14px; border:0; border-radius:999px;
	         background:var(--sun); color:var(--ink); font:inherit; font-weight:600; cursor:pointer; }
	button:disabled { opacity:.45; cursor:not-allowed; }
	ul.checks { list-style:none; margin:0; padding:0; }
	ul.checks li { display:flex; gap:10px; padding:7px 0; border-bottom:1px solid #EFEDF1; font-size:.92rem; }
	ul.checks li:last-child { border:0; }
	.tick { color:var(--ok); font-weight:700; }
	.cross { color:var(--bad); font-weight:700; }
	.hint { color:var(--muted); font-size:.82rem; }
	.bad { background:#FDEDEB; border-left:3px solid var(--bad); padding:12px 14px; border-radius:10px; margin-bottom:12px; font-size:.9rem; }
	.ok  { background:#EAF7F0; border-left:3px solid var(--ok);  padding:14px 16px; border-radius:10px; font-size:.95rem; }
	label.check { display:flex; gap:10px; align-items:flex-start; font-weight:400; margin:12px 0 5px; cursor:pointer; }
	label.check input { width:auto; margin-top:4px; flex:none; }
	table.logins { width:100%; border-collapse:collapse; font-size:.86rem; margin:10px 0 4px; }
	table.logins th, table.logins td { text-align:left; padding:7px 8px 7px 0; border-bottom:1px solid #EFEDF1; vertical-align:top; }
	table.logins th { font-size:.74rem; letter-spacing:.06em; text-transform:uppercase; color:var(--muted); font-weight:600; }
	.warn { background:#FFF7DB; border-left:3px solid #E5B400; padding:12px 14px; border-radius:10px; margin:14px 0; font-size:.88rem; }
	code { background:#F3F1F6; padding:2px 6px; border-radius:6px; font-size:.86em; }
	ol { padding-left:20px; } ol li { margin:8px 0; }
</style>
</head>
<body>
<div class="w">
	<div class="mark">&amp;</div>

	<?php if ($installed): ?>
		<h1>Already installed</h1>
		<p class="lede">This site is set up. For safety, delete <code>install.php</code> from your server now.</p>
		<div class="card"><a href="./">Open the site &rarr;</a></div>

	<?php elseif ($done): ?>
		<h1>Done.</h1>
		<div class="card">
			<div class="ok"><strong>Your site is live.</strong> One job left, and it matters:</div>
			<ol>
				<li><strong>Delete <code>install.php</code></strong> from the server. Anyone who finds it could otherwise point your site at their own database.</li>
				<li>Sign in at <code>/sign-in</code> with the owner email you just set.</li>
				<li>If you are moving from WordPress, run the importer over a <em>copy</em> of your old database — see <code>INSTALL.md</code>.</li>
			</ol>
			<?php if ($demo): ?>
				<h2 style="margin-top:20px">Demo logins</h2>
				<p class="hint">Sign in at <code>/sign-in</code>. Every demo account uses the same password:</p>
				<p><code style="font-size:1rem;padding:4px 9px"><?= h(Demo::PASSWORD) ?></code></p>
				<table class="logins">
					<tr><th>Email</th><th>Who</th></tr>
					<?php foreach ($demo['logins'] as $l): ?>
						<tr><td><code><?= h($l['email']) ?></code></td><td><b><?= h($l['who']) ?></b><br><span class="hint"><?= h($l['shows']) ?></span></td></tr>
					<?php endforeach; ?>
				</table>
				<p class="hint">Your own account (the one you just made) is the owner: it has full access to HQ.</p>
				<div class="warn"><b>Remove the demo before launch.</b> These accounts share a published password. In HQ &rarr; Settings, <em>Demo content</em> &rarr; <em>Remove all demo content</em> deletes the demo pages and every demo account, and nothing else.</div>
			<?php elseif ($demoError !== ''): ?>
				<div class="bad">Your site is installed, but the demo content could not be added: <?= h($demoError) ?>. You can try again from HQ &rarr; Settings.</div>
			<?php endif; ?>
			<a href="./"><button type="button">Open the site</button></a>
		</div>

	<?php else: ?>
		<h1>Install</h1>
		<p class="lede">Two minutes. Have your cPanel database details to hand.</p>

		<div class="card">
			<h2>Server check</h2>
			<ul class="checks">
				<?php foreach ($checks as [$label, $pass, $fix]): ?>
					<li>
						<span class="<?= $pass ? 'tick' : 'cross' ?>"><?= $pass ? '&check;' : '&times;' ?></span>
						<span><?= h($label) ?><?php if (!$pass): ?><br><span class="hint"><?= h($fix) ?></span><?php endif; ?></span>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>

		<?php foreach ($errors as $e): ?><div class="bad"><?= h($e) ?></div><?php endforeach; ?>

		<form method="post" class="card">
			<h2>Database</h2>
			<p class="hint">In cPanel, go to <strong>MySQL&reg; Databases</strong>, create a database and a user, then add that user to the database with <strong>All Privileges</strong>. Paste the full names below — on Namecheap they usually start with your cPanel username and an underscore.</p>

			<label for="host">Host</label>
			<input id="host" name="host" value="<?= h($_POST['host'] ?? 'localhost') ?>">
			<small>Almost always <code>localhost</code> on shared hosting.</small>

			<label for="name">Database name</label>
			<input id="name" name="name" required value="<?= h($_POST['name'] ?? '') ?>" placeholder="cpanelu_wwm">

			<label for="user">Database user</label>
			<input id="user" name="user" required value="<?= h($_POST['user'] ?? '') ?>" placeholder="cpanelu_wwm">

			<label for="pass">Database password</label>
			<input id="pass" name="pass" type="password" value="">

			<h2 style="margin-top:22px">Your site</h2>
			<label for="url">Site address</label>
			<input id="url" name="url" required value="<?= h($_POST['url'] ?? (($_SERVER['HTTPS'] ?? '') ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? '')) ?>">
			<small>No trailing slash. Use https if you have an SSL certificate — Namecheap gives a free one under cPanel &gt; SSL/TLS Status.</small>

			<h2 style="margin-top:22px">Owner account</h2>
			<label for="who">Your name</label>
			<input id="who" name="who" value="<?= h($_POST['who'] ?? '') ?>">

			<label for="email">Email</label>
			<input id="email" name="email" type="email" required value="<?= h($_POST['email'] ?? '') ?>">

			<label for="pw">Password</label>
			<input id="pw" name="pw" type="password" required minlength="10">
			<small>At least 10 characters. This account can reach HQ, so make it a real one.</small>

			<h2 style="margin-top:22px">Demo content</h2>
			<label class="check"><input type="checkbox" name="demo" value="1" <?= (!isset($_POST['email']) || !empty($_POST['demo'])) ? 'checked' : '' ?>>
				<span><b>Add demo content</b> &mdash; a wedding page for every design and plan, vendors, marketplace listings and the anonymous community, plus accounts to sign in and look around.</span></label>
			<small>The demo accounts share a published password, so take the demo content out before you launch for real. It is one button in HQ &rarr; Settings, and it removes only the demo.</small>

			<button type="submit"<?= $ready ? '' : ' disabled' ?>>Install</button>
			<?php if (!$ready): ?><small style="text-align:center">Fix the red items above first, then reload this page.</small><?php endif; ?>
		</form>
	<?php endif; ?>
</div>
</body>
</html>
