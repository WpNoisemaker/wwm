<?php
/**
 * Wedding bunting across the top of the front page: the flag of every African country, and of the
 * UK, the US, Canada, Australia and Switzerland, where so many of our couples live. The flags hang
 * from a string that drifts slowly from right to left and starts again without a gap. It sits
 * above the words, never over them. Flag artwork: flag-icons (MIT licence), in assets/img/flags.
 */
$flags = [
	'ng' => 'Nigeria', 'gh' => 'Ghana', 'ke' => 'Kenya', 'za' => 'South Africa', 'gb' => 'United Kingdom', 'rw' => 'Rwanda',
	'ug' => 'Uganda', 'sn' => 'Senegal', 'et' => 'Ethiopia', 'ci' => 'Côte d’Ivoire', 'cm' => 'Cameroon', 'tz' => 'Tanzania',
	'us' => 'United States', 'sl' => 'Sierra Leone', 'dz' => 'Algeria', 'ao' => 'Angola', 'bj' => 'Benin', 'bw' => 'Botswana',
	'ca' => 'Canada', 'bf' => 'Burkina Faso', 'bi' => 'Burundi', 'cv' => 'Cabo Verde', 'cf' => 'Central African Republic', 'td' => 'Chad',
	'km' => 'Comoros', 'cg' => 'Congo', 'au' => 'Australia', 'cd' => 'DR Congo', 'dj' => 'Djibouti', 'eg' => 'Egypt',
	'gq' => 'Equatorial Guinea', 'er' => 'Eritrea', 'sz' => 'Eswatini', 'ch' => 'Switzerland', 'ga' => 'Gabon', 'gm' => 'The Gambia',
	'gn' => 'Guinea', 'gw' => 'Guinea-Bissau', 'ls' => 'Lesotho', 'lr' => 'Liberia', 'ly' => 'Libya', 'mg' => 'Madagascar',
	'mw' => 'Malawi', 'ml' => 'Mali', 'mr' => 'Mauritania', 'mu' => 'Mauritius', 'ma' => 'Morocco', 'mz' => 'Mozambique',
	'na' => 'Namibia', 'ne' => 'Niger', 'st' => 'São Tomé and Príncipe', 'sc' => 'Seychelles', 'so' => 'Somalia', 'ss' => 'South Sudan',
	'sd' => 'Sudan', 'tg' => 'Togo', 'tn' => 'Tunisia', 'zm' => 'Zambia', 'zw' => 'Zimbabwe',
];
$row = static function (array $flags): string {
	$h = '';
	$i = 0;
	foreach ($flags as $code => $name) {
		$h .= '<li style="--d:' . round(($i % 5) * -0.7, 2) . 's;--s:' . (2.6 + ($i % 3) * 0.5) . 's"><img src="' . e(App::asset('assets/img/flags/' . $code . '.svg')) . '" alt="" title="' . e($name) . '" width="30" height="20" decoding="async"></li>';
		$i++;
	}
	return $h;
};
$one = $row($flags);
?>
<div class="bunting" aria-hidden="true">
	<div class="bunting__track">
		<ul class="bunting__flags"><?= $one ?></ul>
		<ul class="bunting__flags"><?= $one ?></ul>
	</div>
</div>
