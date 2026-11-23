<?php
/**
 * Every email the site sends, dressed in the brand: the night palette, Urbanist, the ampersand
 * monogram and a thin band of African flag colours, like the bunting on the front page. Built from
 * tables with inline styles, because that is what email programs read; dark by design, and marked
 * so the ones that recolour email leave it alone.
 *
 * @var string $title @var string $body (HTML, with its button already in place) @var string $pre @var string $unsub @var string $why
 */
$home = App::url('/');
$f = "'Urbanist', 'Helvetica Neue', Helvetica, Arial, sans-serif";
$flags = ['#008751', '#FCD116', '#CE1126', '#00A1DE', '#FAD201', '#078930', '#F77F00', '#FFFFFF', '#009E60', '#E31B23', '#1EB53A', '#0072C6'];
?><!doctype html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="color-scheme" content="dark light">
<meta name="supported-color-schemes" content="dark light">
<title><?= e($title) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Urbanist:wght@300;400;500&amp;display=swap" rel="stylesheet">
<style>
	body { margin: 0; padding: 0; background: #161B23; }
	a { color: #A9C2FF; }
	.btn a:hover { background: #FFFFFF !important; }
	@media (max-width: 620px) { .card { border-radius: 26px !important; } .pad { padding-left: 24px !important; padding-right: 24px !important; } .h1 { font-size: 26px !important; } }
</style>
</head>
<body style="margin:0;padding:0;background:#161B23;">
<div style="display:none;max-height:0;overflow:hidden;opacity:0;color:#161B23;font-size:1px;line-height:1px;"><?= e($pre) ?>&#8199;&#65279;&#847;&#8199;&#65279;&#847;&#8199;&#65279;&#847;&#8199;&#65279;&#847;</div>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#161B23;background-image:linear-gradient(135deg,#181D25 0%,#1E242E 35%,#232D40 65%,#2A3A5E 100%);">
	<tr><td align="center" style="padding:36px 14px 40px;">
		<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;">
			<!-- the monogram and the name -->
			<tr><td align="center" style="padding:0 0 22px;">
				<a href="<?= e($home) ?>" style="text-decoration:none;color:#EEF1F6;">
					<table role="presentation" cellpadding="0" cellspacing="0" border="0"><tr>
						<td width="44" height="44" align="center" valign="middle" style="width:44px;height:44px;border-radius:22px;border:1px solid rgba(255,255,255,.22);background:rgba(255,255,255,.06);font-family:<?= $f ?>;font-size:22px;font-weight:300;line-height:44px;color:#EEF1F6;">&amp;</td>
						<td style="padding-left:12px;font-family:<?= $f ?>;font-size:17px;font-weight:400;letter-spacing:.01em;color:#EEF1F6;">Wedding Websites &amp; More</td>
					</tr></table>
				</a>
			</td></tr>
			<!-- the card -->
			<tr><td class="card" style="background:#212836;border:1px solid #2F384A;border-radius:34px;overflow:hidden;">
				<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
					<!-- the bunting: a thin band of African flag colours -->
					<tr><td style="padding:0;font-size:0;line-height:0;">
						<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"><tr>
							<?php foreach ($flags as $c): ?><td height="4" style="height:4px;background:<?= $c ?>;font-size:0;line-height:0;">&nbsp;</td><?php endforeach; ?>
						</tr></table>
					</td></tr>
					<tr><td class="pad" style="padding:38px 44px 8px;">
						<h1 class="h1" style="margin:0;font-family:<?= $f ?>;font-size:30px;font-weight:300;line-height:1.2;letter-spacing:-.01em;color:#EEF1F6;"><?= e($title) ?></h1>
					</td></tr>
					<tr><td class="pad" style="padding:14px 44px 6px;font-family:<?= $f ?>;font-size:16px;font-weight:300;line-height:1.6;color:#C3CAD6;">
						<?= $body ?>
					</td></tr>
					<tr><td class="pad" style="padding:26px 44px 36px;">
						<p style="margin:0;font-family:<?= $f ?>;font-size:15px;font-weight:300;color:#C3CAD6;">&mdash; <span style="color:#EEF1F6;">The Wedding Websites &amp; More team</span></p>
					</td></tr>
				</table>
			</td></tr>
			<!-- the foot -->
			<tr><td align="center" style="padding:26px 20px 0;font-family:<?= $f ?>;font-size:12.5px;line-height:1.7;color:#8A93A5;">
				<a href="<?= e(App::url('/weddings')) ?>" style="color:#C3CAD6;text-decoration:none;">Weddings</a> &nbsp;·&nbsp;
				<a href="<?= e(App::url('/market')) ?>" style="color:#C3CAD6;text-decoration:none;">Marketplace</a> &nbsp;·&nbsp;
				<a href="<?= e(App::url('/community')) ?>" style="color:#C3CAD6;text-decoration:none;">Community</a> &nbsp;·&nbsp;
				<a href="<?= e(App::url('/help')) ?>" style="color:#C3CAD6;text-decoration:none;">Help</a>
				<br><?= e($why) ?><?php if ($unsub !== ''): ?> <a href="<?= e($unsub) ?>" style="color:#C3CAD6;">Stop these emails</a>.<?php endif; ?>
				<br>Wedding Websites &amp; More &nbsp;·&nbsp; Made in Abuja
			</td></tr>
		</table>
	</td></tr>
</table>
</body>
</html>
