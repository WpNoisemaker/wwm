<?php
/** A code the reader must keep (an access code), big and easy to read out at a gate. @var string $code @var string $label */
$f = "'Urbanist', 'Helvetica Neue', Helvetica, Arial, sans-serif";
?>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:6px 0 20px;"><tr>
	<td align="center" style="padding:20px 16px;border-radius:22px;background:#2A3242;border:1px solid #E9B949;">
		<p style="margin:0 0 6px;font-family:<?= $f ?>;font-size:12px;letter-spacing:.12em;text-transform:uppercase;color:#E9B949;"><?= e($label) ?></p>
		<p style="margin:0;font-family:<?= $f ?>;font-size:38px;font-weight:500;letter-spacing:.18em;color:#FFFFFF;"><?= e($code) ?></p>
	</td>
</tr></table>
