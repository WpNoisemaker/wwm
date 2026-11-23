<?php
/**
 * Wedding bunting for the front page: small flags of African countries, in their own colours,
 * hanging from a string across the top of the hero and swaying gently. It sits above the words,
 * never over them, and holds fewer flags on a phone so it never crowds.
 */
$flags = [
	'Nigeria' => '<rect width="10" height="20" fill="#008751"/><rect x="10" width="10" height="20" fill="#fff"/><rect x="20" width="10" height="20" fill="#008751"/>',
	'Ghana' => '<rect width="30" height="7" fill="#CE1126"/><rect y="7" width="30" height="6" fill="#FCD116"/><rect y="13" width="30" height="7" fill="#006B3F"/><path d="M15 7.2l1 3h3.1l-2.5 1.8 1 3-2.6-1.9-2.6 1.9 1-3-2.5-1.8h3.1z" fill="#000"/>',
	'Kenya' => '<rect width="30" height="6" fill="#000"/><rect y="6" width="30" height="1.6" fill="#fff"/><rect y="7.6" width="30" height="4.8" fill="#BB0000"/><rect y="12.4" width="30" height="1.6" fill="#fff"/><rect y="14" width="30" height="6" fill="#006600"/><ellipse cx="15" cy="10" rx="3" ry="6.2" fill="#BB0000" stroke="#000" stroke-width="1"/><path d="M15 4.6v10.8" stroke="#fff" stroke-width=".7"/>',
	'South Africa' => '<rect width="30" height="10" fill="#E03C31"/><rect y="10" width="30" height="10" fill="#001489"/><path d="M0 0l12 10L0 20z" fill="#FFB81C"/><path d="M0 2.2L9.6 10 0 17.8z" fill="#000"/><path d="M0 0h4l12 7.6H30v4.8H16L4 20H0l12-10z" fill="#fff"/><path d="M0 1.4h2.6l12 8H30v1.2H14.6l-12 8H0L11.2 10z" fill="#007749"/>',
	'Rwanda' => '<rect width="30" height="10" fill="#00A1DE"/><rect y="10" width="30" height="5" fill="#FAD201"/><rect y="15" width="30" height="5" fill="#20603D"/><circle cx="24" cy="4.6" r="2.4" fill="#FAD201"/>',
	'Uganda' => '<rect width="30" height="20" fill="#000"/><rect y="3.33" width="30" height="3.33" fill="#FCDC04"/><rect y="6.66" width="30" height="3.34" fill="#D90000"/><rect y="13.33" width="30" height="3.33" fill="#FCDC04"/><rect y="16.66" width="30" height="3.34" fill="#D90000"/><circle cx="15" cy="10" r="3.4" fill="#fff"/>',
	'Senegal' => '<rect width="10" height="20" fill="#00853F"/><rect x="10" width="10" height="20" fill="#FDEF42"/><rect x="20" width="10" height="20" fill="#E31B23"/><path d="M15 7l.9 2.7h2.8l-2.3 1.7.9 2.7-2.3-1.7-2.3 1.7.9-2.7-2.3-1.7h2.8z" fill="#00853F"/>',
	'Ethiopia' => '<rect width="30" height="6.7" fill="#078930"/><rect y="6.7" width="30" height="6.6" fill="#FCDD09"/><rect y="13.3" width="30" height="6.7" fill="#DA121A"/><circle cx="15" cy="10" r="3.6" fill="#0F47AF"/>',
	"Côte d'Ivoire" => '<rect width="10" height="20" fill="#F77F00"/><rect x="10" width="10" height="20" fill="#fff"/><rect x="20" width="10" height="20" fill="#009E60"/>',
	'Cameroon' => '<rect width="10" height="20" fill="#007A5E"/><rect x="10" width="10" height="20" fill="#CE1126"/><rect x="20" width="10" height="20" fill="#FCD116"/><path d="M15 7l.9 2.7h2.8l-2.3 1.7.9 2.7-2.3-1.7-2.3 1.7.9-2.7-2.3-1.7h2.8z" fill="#FCD116"/>',
	'Tanzania' => '<rect width="30" height="20" fill="#00A3DD"/><path d="M0 0h30L0 20z" fill="#1EB53A"/><path d="M0 16.4L24.6 0H30v3.6L5.4 20H0z" fill="#FCD116"/><path d="M0 18.2L27.3 0H30v1.8L2.7 20H0z" fill="#000"/>',
	'Sierra Leone' => '<rect width="30" height="6.7" fill="#1EB53A"/><rect y="6.7" width="30" height="6.6" fill="#fff"/><rect y="13.3" width="30" height="6.7" fill="#0072C6"/>',
];
$names = array_keys($flags);
$n = count($names);
?>
<div class="bunting" aria-hidden="true">
	<svg class="bunting__line" viewBox="0 0 1000 60" preserveAspectRatio="none"><path d="M-10 8 Q500 70 1010 8"/></svg>
	<ul class="bunting__flags">
		<?php foreach ($names as $i => $name): $f = ($i + 0.5) / $n; $y = 8 + 124 * $f * (1 - $f); ?>
			<li style="--x:<?= round($f * 100, 2) ?>%;--y:<?= round($y, 1) ?>;--d:<?= round(($i % 5) * -0.7, 2) ?>s;--s:<?= 2.6 + ($i % 3) * 0.5 ?>s" title="<?= e($name) ?>">
				<svg viewBox="0 0 30 20"><?= $flags[$name] ?></svg>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
