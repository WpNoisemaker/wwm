<?php
/** The main link of an email, as a button, with the address underneath for programs that hide buttons. @var array $button [label, url] */
$f = "'Urbanist', 'Helvetica Neue', Helvetica, Arial, sans-serif";
?>
<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:6px 0 10px;"><tr>
	<td class="btn" style="border-radius:999px;background:#EEF1F6;">
		<a href="<?= e($button[1]) ?>" style="display:inline-block;padding:16px 30px;border-radius:999px;background:#EEF1F6;font-family:<?= $f ?>;font-size:16px;font-weight:500;color:#1A2030;text-decoration:none;"><?= e($button[0]) ?> &rarr;</a>
	</td>
</tr></table>
<p style="margin:0 0 18px;font-family:<?= $f ?>;font-size:12px;line-height:1.5;color:#7F899B;">Or paste this into your browser: <a href="<?= e($button[1]) ?>" style="color:#A9C2FF;word-break:break-all;"><?= e($button[1]) ?></a></p>
