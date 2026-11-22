<?php
/**
 * Hand-drawn illustrations (inline SVG): Lagos lagoon skyline, the wedding convoy (bridal car, limo & SUVs),
 * the danfo, category art,
 * illustrated avatars for bridal trains, cover art for weddings without a photo & cloth patterns.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;

/**
 * Palette used by the illustrations.
 *
 * @return array
 */
function wd_tones() {
	return array(
		'ink'       => '#1C0B33',
		'cream'     => '#FFF6E5',
		'butter'    => '#FFD84D',
		'marigold'  => '#FFC21A',
		'lavender'  => '#CDB6FF',
		'sky'       => '#8ED0F9',
		'mint'      => '#8EE3B8',
		'coral'     => '#FF8E6E',
		'leaf'      => '#1E9E62',
		'jacaranda' => '#6B2BD9',
		'aubergine' => '#23094B',
		'terracotta' => '#E2683C',
		'indigo'    => '#2E3B8F',
		'white'     => '#FFFFFF',
	);
}

/**
 * Illustration markup.
 *
 * @param string $name  Illustration.
 * @param string $class Class.
 * @return string
 */
function wd_art( $name, $class = '' ) {
	$t   = wd_tones();
	$ink = $t['ink'];
	$s   = 'stroke="' . $ink . '" stroke-width="4" stroke-linejoin="round" stroke-linecap="round"';
	$svg = '';
	switch ( $name ) {
		case 'skyline':
			$svg = '<svg viewBox="0 0 1600 430" preserveAspectRatio="xMidYMax slice">';
			// Back row of towers.
			$back = array( array( 30, 150, 90, 'lavender' ), array( 130, 90, 70, 'sky' ), array( 250, 120, 110, 'butter' ), array( 420, 60, 80, 'mint' ), array( 520, 140, 90, 'lavender' ), array( 640, 100, 70, 'coral' ), array( 760, 160, 80, 'sky' ), array( 900, 120, 90, 'butter' ), array( 1380, 130, 80, 'lavender' ), array( 1490, 90, 110, 'mint' ) );
			foreach ( $back as $b ) {
				$svg .= sprintf( '<rect x="%d" y="%d" width="%d" height="%d" fill="%s" %s/>', $b[0], $b[1], $b[2], 330 - $b[1], $t[ $b[3] ], $s );
				for ( $wy = $b[1] + 18; $wy < 300; $wy += 30 ) {
					for ( $wx = $b[0] + 14; $wx < $b[0] + $b[2] - 18; $wx += 22 ) {
						$svg .= sprintf( '<rect x="%d" y="%d" width="10" height="14" rx="2" fill="%s" opacity=".85"/>', $wx, $wy, $t['cream'] );
					}
				}
			}
			// Civic-style glass tower with a slanted crown.
			$svg .= '<path d="M330 330V70l70-40v300z" fill="' . $t['jacaranda'] . '" ' . $s . '/><path d="M345 80l40-22M345 120l40-22M345 160l40-22M345 200l40-22M345 240l40-22M345 280l40-22" stroke="' . $t['lavender'] . '" stroke-width="5"/>';
			// Domed hall.
			$svg .= '<path d="M560 330V230h120v100z" fill="' . $t['cream'] . '" ' . $s . '/><path d="M560 232c0-44 120-44 120 0z" fill="' . $t['marigold'] . '" ' . $s . '/><path d="M620 190v-26" ' . $s . '/><circle cx="620" cy="160" r="6" fill="' . $t['marigold'] . '" ' . $s . '/><path d="M585 330v-60h20v60M635 330v-60h20v60" fill="' . $t['sky'] . '" ' . $s . '/>';
			// Billboard with a heart.
			$svg .= '<path d="M1010 180h170v80h-170z" fill="' . $t['white'] . '" ' . $s . '/><path d="M1040 260v70M1150 260v70" ' . $s . '/><path d="M1095 236s-24-14-24-30c0-9 7-15 15-15 4 0 7 2 9 5 2-3 5-5 9-5 8 0 15 6 15 15 0 16-24 30-24 30z" fill="' . $t['coral'] . '" ' . $s . '/><text x="1030" y="210" font-family="Adventor, sans-serif" font-weight="700" font-size="18" fill="' . $ink . '">Owambe</text><text x="1118" y="250" font-family="Adventor, sans-serif" font-weight="700" font-size="14" fill="' . $t['jacaranda'] . '">live</text>';
			// Cable-stayed bridge pylon & cables.
			$svg .= '<path d="M1262 330V40h26v290z" fill="' . $t['white'] . '" ' . $s . '/><path d="M1262 40l13-22 13 22" fill="' . $t['white'] . '" ' . $s . '/>';
			for ( $i = 0; $i < 7; $i++ ) {
				$svg .= sprintf( '<path d="M1275 %1$d L%2$d 318 M1275 %1$d L%3$d 318" stroke="%4$s" stroke-width="2.5"/>', 60 + $i * 14, 1100 - $i * 0 + $i * 22, 1450 - $i * 22, $ink );
			}
			// Palms.
			foreach ( array( 470, 1340, 1560 ) as $px ) {
				$svg .= '<path d="M' . $px . ' 330c4-50 0-90-14-130" fill="none" stroke="' . $t['terracotta'] . '" stroke-width="12" stroke-linecap="round"/><path d="M' . $px . ' 330c4-50 0-90-14-130" fill="none" ' . $s . ' stroke-dasharray="1 16"/>';
				$fx = $px - 14;
				foreach ( array( 'M0 0c-30-6-55 4-70 24 22-8 46-10 70-24z', 'M0 0c26-14 56-12 76 4-24-2-50 2-76-4z', 'M0 0c-10-26-34-38-58-36 20 8 40 20 58 36z', 'M0 0c14-24 40-32 62-26-22 6-42 14-62 26z', 'M0 0c-2 26-20 48-44 56 14-18 32-34 44-56z' ) as $leaf ) {
					$svg .= '<path transform="translate(' . $fx . ' 200)" d="' . $leaf . '" fill="' . $t['leaf'] . '" ' . $s . '/>';
				}
			}
			// Road deck, railing & lagoon.
			$svg .= '<rect x="-10" y="318" width="1620" height="18" fill="' . $t['white'] . '" ' . $s . '/><path d="M0 306h1600" ' . $s . '/>';
			for ( $rx = 10; $rx < 1600; $rx += 34 ) {
				$svg .= '<path d="M' . $rx . ' 306v12" stroke="' . $ink . '" stroke-width="3"/>';
			}
			$svg .= '<rect x="-10" y="336" width="1620" height="110" fill="' . $t['sky'] . '" ' . $s . '/>';
			for ( $px2 = 1150; $px2 < 1500; $px2 += 110 ) {
				$svg .= '<path d="M' . $px2 . ' 336v40h22v-40" fill="' . $t['white'] . '" ' . $s . '/>';
			}
			$svg .= '<path d="M60 380q20-10 40 0t40 0M300 400q20-10 40 0t40 0M620 372q20-10 40 0t40 0M900 404q20-10 40 0t40 0M1500 392q20-10 40 0t40 0" fill="none" stroke="' . $t['white'] . '" stroke-width="4" stroke-linecap="round"/>';
			// Canoe.
			$svg .= '<path d="M720 404h120c-10 18-26 22-60 22s-50-4-60-22z" fill="' . $t['terracotta'] . '" ' . $s . '/><path d="M770 404l18-48 4 48" fill="' . $t['butter'] . '" ' . $s . '/>';
			$svg .= '</svg>';
			break;

		case 'danfo':
			$svg = '<svg viewBox="0 0 300 140">';
			$svg .= '<path d="M14 52c0-12 8-20 20-20h170c20 0 34 6 46 22l26 34v24c0 6-4 10-10 10H24c-6 0-10-4-10-10z" fill="' . $t['marigold'] . '" ' . $s . '/>';
			$svg .= '<path d="M14 80h272M14 92h272" stroke="' . $ink . '" stroke-width="7"/>';
			$svg .= '<path d="M34 44h40v26H34zM84 44h40v26H84zM134 44h40v26h-40zM184 44h34v26h-34z" fill="' . $t['sky'] . '" ' . $s . '/><path d="M228 44h18l22 26h-40z" fill="' . $t['sky'] . '" ' . $s . '/>';
			$svg .= '<path d="M40 32V20h150v12" fill="none" ' . $s . '/><rect x="56" y="8" width="26" height="14" rx="2" fill="' . $t['coral'] . '" ' . $s . '/><rect x="96" y="6" width="22" height="16" rx="2" fill="' . $t['lavender'] . '" ' . $s . '/><rect x="130" y="10" width="34" height="12" rx="2" fill="' . $t['mint'] . '" ' . $s . '/><path d="M69 8v14M107 6v16M147 10v12" stroke="' . $ink . '" stroke-width="2"/>';
			$svg .= '<rect x="44" y="98" width="128" height="18" rx="4" fill="' . $t['white'] . '" ' . $s . '/><text x="108" y="112" text-anchor="middle" font-family="Adventor, sans-serif" font-weight="700" font-size="12" fill="' . $ink . '">JUST MARRIED</text>';
			$svg .= '<circle cx="64" cy="124" r="15" fill="' . $ink . '"/><circle cx="64" cy="124" r="6" fill="' . $t['cream'] . '"/><circle cx="236" cy="124" r="15" fill="' . $ink . '"/><circle cx="236" cy="124" r="6" fill="' . $t['cream'] . '"/>';
			$svg .= '<path d="M14 100c-14 6-20 14-30 14M14 108c-10 10-20 16-34 18" fill="none" stroke="' . $ink . '" stroke-width="2"/>';
			$svg .= '<rect x="-36" y="108" width="14" height="16" rx="2" fill="' . $t['cream'] . '" ' . $s . ' transform="rotate(-14 -29 116)"/><rect x="-24" y="120" width="14" height="16" rx="2" fill="' . $t['cream'] . '" ' . $s . ' transform="rotate(18 -17 128)"/>';
			$svg .= '<path d="M258 14s-12-7-12-15c0-4 3-7 7-7 2 0 4 1 5 3 1-2 3-3 5-3 4 0 7 3 7 7 0 8-12 15-12 15z" fill="' . $t['coral'] . '" ' . $s . ' transform="translate(0 16)"/><path d="M258 30c-2 10 2 16-2 26" fill="none" stroke="' . $ink . '" stroke-width="2"/>';
			$svg .= '</svg>';
			break;

		case 'bridal':
			/* The couple's car: a white vintage convertible with the bride & groom waving, a bow & cans on strings. */
			$s3   = 'stroke="' . $ink . '" stroke-width="3" stroke-linejoin="round" stroke-linecap="round"';
			$sk1  = '#8D5A35';
			$sk2  = '#6B3E26';
			$whl  = function ( $x ) use ( $t, $ink ) {
				return '<g class="cv-wheel"><circle cx="' . $x . '" cy="110" r="17" fill="' . $ink . '"/><circle cx="' . $x . '" cy="110" r="10.5" fill="' . $t['white'] . '" stroke="' . $ink . '" stroke-width="3"/><path d="M' . $x . ' 101.5v17M' . ( $x - 8.5 ) . ' 110h17" stroke="' . $ink . '" stroke-width="2.5"/><circle cx="' . $x . '" cy="110" r="3" fill="' . $ink . '"/></g>';
			};
			$svg  = '<svg viewBox="-52 0 300 130">';
			$svg .= '<path d="M12 92C-4 96-16 104-26 110M12 96C2 106-6 114-10 118" fill="none" stroke="' . $ink . '" stroke-width="2"/>';
			$svg .= '<g class="cv-can"><rect x="-38" y="106" width="13" height="16" rx="2" fill="' . $t['cream'] . '" ' . $s3 . '/></g><g class="cv-can cv-can--b"><rect x="-17" y="113" width="12" height="14" rx="2" fill="' . $t['lavender'] . '" ' . $s3 . '/></g>';
			$svg .= '<path d="M86 72c0-12 6-17 14-17s14 5 14 17z" fill="' . $t['white'] . '" ' . $s3 . '/><circle cx="100" cy="43" r="10" fill="' . $sk1 . '" ' . $s3 . '/><path d="M90.5 40c1-7 5-10 9.5-10s8.5 3 9.5 10c-5-4-14-4-19 0z" fill="' . $ink . '"/>';
			$svg .= '<circle cx="93" cy="33" r="3" fill="' . $t['coral'] . '" ' . $s3 . '/><circle cx="100" cy="30" r="3" fill="' . $t['marigold'] . '" ' . $s3 . '/><circle cx="107" cy="33" r="3" fill="' . $t['coral'] . '" ' . $s3 . '/>';
			$svg .= '<path d="M118 72c0-13 6-18 14-18s14 5 14 18z" fill="' . $ink . '" ' . $s3 . '/><path d="M128 55l4 9 4-9z" fill="' . $t['white'] . '"/><path d="M127.5 57l4.5 2.5-4.5 2.5zM136.5 57l-4.5 2.5 4.5 2.5z" fill="' . $t['marigold'] . '"/>';
			$svg .= '<circle cx="132" cy="42" r="10" fill="' . $sk2 . '" ' . $s3 . '/><path d="M122 40c0-7 4-11 10-11s10 4 10 11c-5-4-15-4-20 0z" fill="' . $ink . '"/>';
			$svg .= '<g class="cv-wave"><path d="M142 62l8-20" stroke="' . $ink . '" stroke-width="7" stroke-linecap="round"/><circle cx="151" cy="38" r="4.5" fill="' . $sk2 . '" ' . $s3 . '/></g>';
			$svg .= '<circle cx="114" cy="66" r="5" fill="' . $t['coral'] . '" ' . $s3 . '/><circle cx="121" cy="68" r="4" fill="' . $t['butter'] . '" ' . $s3 . '/><circle cx="108" cy="69" r="4" fill="' . $t['mint'] . '" ' . $s3 . '/>';
			$svg .= '<path d="M14 98V82c0-12 10-20 24-20h38c4 0 6 4 6 8h74l6-6h60c8 0 12 6 12 14v20z" fill="' . $t['white'] . '" ' . $s . '/>';
			$svg .= '<path d="M28 63c0-10 12-15 28-15 13 0 22 5 22 15z" fill="' . $t['cream'] . '" ' . $s3 . '/><path d="M38 56c10-3 24-3 34 0" fill="none" stroke="' . $ink . '" stroke-width="2"/>';
			$svg .= '<path class="cv-veil" d="M106 33c-12-8-34-6-58 8 14 2 30 8 42 18l4-14c4-6 8-10 12-12z" fill="' . $t['white'] . '" ' . $s3 . '/><path d="M60 42c10 0 20 4 28 10" fill="none" stroke="' . $t['lavender'] . '" stroke-width="2"/>';
			$svg .= '<path d="M156 70l-6-27h6l7 27z" fill="' . $t['sky'] . '" ' . $s3 . '/>';
			$svg .= $whl( 64 ) . $whl( 200 );
			$svg .= '<path d="M36 100c0-16 12-24 28-24s28 8 28 24zM172 100c0-16 12-24 28-24s28 8 28 24z" fill="' . $t['white'] . '" ' . $s . '/>';
			$svg .= '<rect x="88" y="96" width="88" height="6" rx="3" fill="' . $ink . '"/>';
			$svg .= '<rect x="227" y="60" width="10" height="38" rx="3" fill="' . $t['cream'] . '" ' . $s3 . '/><path d="M230 68h4M230 76h4M230 84h4" stroke="' . $ink . '" stroke-width="2"/><circle cx="220" cy="66" r="6" fill="' . $t['butter'] . '" ' . $s3 . '/>';
			$svg .= '<rect x="230" y="92" width="13" height="6" rx="3" fill="' . $t['cream'] . '" ' . $s3 . '/><rect x="5" y="90" width="12" height="6" rx="3" fill="' . $t['cream'] . '" ' . $s3 . '/>';
			$svg .= '<rect x="95" y="74" width="74" height="17" rx="4" fill="' . $t['lavender'] . '" ' . $s3 . '/><text x="132" y="86.5" text-anchor="middle" font-family="Adventor, sans-serif" font-weight="700" font-size="9.6" letter-spacing=".2" fill="' . $ink . '">JUST MARRIED</text>';
			$svg .= '<path d="M194 62l-7 12M194 62l7 12" stroke="' . $t['jacaranda'] . '" stroke-width="3" stroke-linecap="round"/><path d="M194 62c-10-12-18-6-12 2zM194 62c10-12 18-6 12 2z" fill="' . $t['jacaranda'] . '" ' . $s3 . '/><circle cx="194" cy="62" r="3.2" fill="' . $t['lavender'] . '" ' . $s3 . '/>';
			$svg .= '</svg>';
			break;

		case 'limo':
			/* Convoy stretch limousine, ribbon bow on the bonnet & hazard lights on. */
			$s3   = 'stroke="' . $ink . '" stroke-width="3" stroke-linejoin="round" stroke-linecap="round"';
			$whl  = function ( $x ) use ( $t, $ink ) {
				return '<g class="cv-wheel"><circle cx="' . $x . '" cy="110" r="17" fill="' . $ink . '"/><circle cx="' . $x . '" cy="110" r="8.5" fill="' . $t['cream'] . '" stroke="' . $ink . '" stroke-width="3"/><path d="M' . $x . ' 103v14M' . ( $x - 7 ) . ' 110h14" stroke="' . $ink . '" stroke-width="2.5"/></g>';
			};
			$svg  = '<svg viewBox="0 0 432 130">';
			$svg .= '<path d="M10 98V82c0-8 5-12 12-12h50l26-28h220l30 26h56c10 0 18 6 18 14v16z" fill="' . $t['aubergine'] . '" ' . $s . '/>';
			$svg .= '<path d="M93 48h53v16H78zM154 48h52v16h-52zM214 48h52v16h-52zM274 48h32v16h-32zM314 48h11l18 16h-29z" fill="' . $t['lavender'] . '" ' . $s3 . '/>';
			$svg .= '<path d="M18 77h396" stroke="' . $t['cream'] . '" stroke-width="3"/><path d="M150 72v24M210 72v24M270 72v24M310 72v24" stroke="' . $t['lavender'] . '" stroke-width="2" opacity=".45"/>';
			$svg .= '<path d="M58 98a24 24 0 0 1 48 0zM326 98a24 24 0 0 1 48 0z" fill="' . $ink . '"/>';
			$svg .= $whl( 82 ) . $whl( 350 );
			$svg .= '<rect x="7" y="72" width="7" height="11" rx="2" fill="' . $t['coral'] . '" ' . $s3 . '/><circle cx="414" cy="76" r="4.5" fill="' . $t['butter'] . '" ' . $s3 . '/>';
			$svg .= '<circle class="cv-blink" cx="417" cy="89" r="3.6" fill="' . $t['marigold'] . '"/><circle class="cv-blink" cx="14" cy="89" r="3.6" fill="' . $t['marigold'] . '"/>';
			$svg .= '<path d="M384 66l-8 12M384 66l8 12" stroke="' . $t['white'] . '" stroke-width="3" stroke-linecap="round"/><path d="M384 66c-10-12-18-6-12 2zM384 66c10-12 18-6 12 2z" fill="' . $t['white'] . '" ' . $s3 . '/><circle cx="384" cy="66" r="3.2" fill="' . $t['white'] . '" ' . $s3 . '/>';
			$svg .= '<path d="M340 58h7v6h-7z" fill="' . $ink . '"/><path d="M346 61c6 2 9 7 8 13" fill="none" stroke="' . $t['white'] . '" stroke-width="2.5" stroke-linecap="round"/>';
			$svg .= '</svg>';
			break;

		case 'suv':
			/* Convoy SUV with a spare wheel, ribbon bow & hazard lights on. */
			$s3   = 'stroke="' . $ink . '" stroke-width="3" stroke-linejoin="round" stroke-linecap="round"';
			$whl  = function ( $x ) use ( $t, $ink ) {
				return '<g class="cv-wheel"><circle cx="' . $x . '" cy="110" r="17" fill="' . $ink . '" stroke="' . $t['aubergine'] . '" stroke-width="2"/><circle cx="' . $x . '" cy="110" r="8.5" fill="' . $t['cream'] . '" stroke="' . $ink . '" stroke-width="3"/><path d="M' . $x . ' 103v14M' . ( $x - 7 ) . ' 110h14" stroke="' . $ink . '" stroke-width="2.5"/></g>';
			};
			$svg  = '<svg viewBox="-14 0 242 130">';
			$svg .= '<circle cx="4" cy="64" r="15" fill="' . $ink . '"/><circle cx="4" cy="64" r="7.5" fill="' . $t['cream'] . '" ' . $s3 . '/>';
			$svg .= '<path d="M30 26h110M40 30v-4M130 30v-4" stroke="' . $ink . '" stroke-width="5" stroke-linecap="round"/>';
			$svg .= '<path d="M12 98V40c0-6 4-10 10-10h134l24 32h32c6 0 10 4 10 10v26z" fill="' . $ink . '" ' . $s . '/>';
			$svg .= '<path d="M22 38h42v22H22zM72 38h44v22H72zM124 38h38l16 22h-54z" fill="' . $t['sky'] . '" ' . $s3 . '/>';
			$svg .= '<path d="M16 70h200" stroke="' . $t['cream'] . '" stroke-width="3"/><path d="M68 66v28M120 66v28" stroke="' . $t['lavender'] . '" stroke-width="2" opacity=".4"/><path d="M24 34h128" stroke="' . $t['lavender'] . '" stroke-width="2" opacity=".35"/>';
			$svg .= $whl( 54 ) . $whl( 180 );
			$svg .= '<rect x="9" y="44" width="6" height="14" rx="2" fill="' . $t['coral'] . '" ' . $s3 . '/><circle cx="214" cy="68" r="4.5" fill="' . $t['butter'] . '" ' . $s3 . '/><path d="M211 79h8M211 85h8" stroke="' . $t['cream'] . '" stroke-width="2.5" stroke-linecap="round"/>';
			$svg .= '<circle class="cv-blink" cx="217" cy="92" r="3.6" fill="' . $t['marigold'] . '"/><circle class="cv-blink" cx="15" cy="88" r="3.6" fill="' . $t['marigold'] . '"/>';
			$svg .= '<path d="M196 60l-7 11M196 60l7 11" stroke="' . $t['white'] . '" stroke-width="3" stroke-linecap="round"/><path d="M196 60c-10-12-18-6-12 2zM196 60c10-12 18-6 12 2z" fill="' . $t['white'] . '" ' . $s3 . '/><circle cx="196" cy="60" r="3.2" fill="' . $t['white'] . '" ' . $s3 . '/>';
			$svg .= '</svg>';
			break;

		case 'drum':
			$svg = '<svg viewBox="0 0 200 200"><path d="M58 30h84c-6 26-24 44-24 70s18 44 24 70H58c6-26 24-44 24-70S64 56 58 30z" fill="' . $t['terracotta'] . '" ' . $s . '/>';
			for ( $i = 0; $i < 7; $i++ ) {
				$x1   = 64 + $i * 12;
				$svg .= '<path d="M' . $x1 . ' 34L' . ( 86 + $i * 4.5 ) . ' 100L' . $x1 . ' 166" fill="none" stroke="' . $t['cream'] . '" stroke-width="3"/>';
			}
			$svg .= '<ellipse cx="100" cy="30" rx="44" ry="10" fill="' . $t['butter'] . '" ' . $s . '/><ellipse cx="100" cy="170" rx="44" ry="10" fill="' . $t['butter'] . '" ' . $s . '/><path d="M150 40c20-10 34-4 40 8l-50 54" fill="none" ' . $s . ' stroke-width="7"/><path d="M150 40c20-10 34-4 40 8l-50 54" fill="none" stroke="' . $t['marigold'] . '" stroke-width="3"/><path d="M30 120l-16 8M30 140H12M34 100l-14-8" ' . $s . '/></svg>';
			break;

		case 'cake':
			$svg = '<svg viewBox="0 0 200 200"><rect x="40" y="120" width="120" height="50" rx="6" fill="' . $t['white'] . '" ' . $s . '/><rect x="56" y="80" width="88" height="40" rx="6" fill="' . $t['lavender'] . '" ' . $s . '/><rect x="72" y="48" width="56" height="32" rx="6" fill="' . $t['white'] . '" ' . $s . '/><path d="M40 136c10 8 20 8 30 0s20-8 30 0 20 8 30 0 20-8 30 0M56 94c8 6 16 6 22 0s16-6 22 0 16 6 22 0 16-6 22 0" fill="none" ' . $s . '/><path d="M20 172h160" ' . $s . '/><path d="M90 48c-4-12 4-20 10-24 6 4 14 12 10 24" fill="' . $t['coral'] . '" ' . $s . '/><circle cx="64" cy="150" r="4" fill="' . $t['marigold'] . '"/><circle cx="100" cy="152" r="4" fill="' . $t['marigold'] . '"/><circle cx="136" cy="150" r="4" fill="' . $t['marigold'] . '"/></svg>';
			break;

		case 'lantern':
			$svg = '<svg viewBox="0 0 200 200"><path d="M100 14v18" ' . $s . '/><circle cx="100" cy="12" r="6" fill="' . $t['marigold'] . '" ' . $s . '/><path d="M74 46h52l-8-14H82z" fill="' . $t['marigold'] . '" ' . $s . '/><path d="M70 46h60c10 30 10 70 0 100H70c-10-30-10-70 0-100z" fill="' . $t['leaf'] . '" ' . $s . '/><path d="M86 56c-6 26-6 54 0 80M114 56c6 26 6 54 0 80M100 52v90" stroke="' . $t['butter'] . '" stroke-width="4"/><ellipse cx="100" cy="98" rx="14" ry="22" fill="' . $t['butter'] . '" opacity=".9"/><path d="M66 146h68l-8 16H74z" fill="' . $t['marigold'] . '" ' . $s . '/><path d="M164 50a26 26 0 1 1-30-34 20 20 0 1 0 30 34z" fill="' . $t['butter'] . '" ' . $s . '/><path d="M30 120l4 8 8 4-8 4-4 8-4-8-8-4 8-4z" fill="' . $t['white'] . '" ' . $s . ' stroke-width="3"/></svg>';
			break;

		case 'rings':
			$svg = '<svg viewBox="0 0 200 200"><circle cx="80" cy="116" r="44" fill="none" stroke="' . $ink . '" stroke-width="18"/><circle cx="80" cy="116" r="44" fill="none" stroke="' . $t['marigold'] . '" stroke-width="10"/><circle cx="124" cy="104" r="44" fill="none" stroke="' . $ink . '" stroke-width="18"/><circle cx="124" cy="104" r="44" fill="none" stroke="' . $t['butter'] . '" stroke-width="10"/><path d="M110 50l14-20 14 20-14 14z" fill="' . $t['sky'] . '" ' . $s . '/><path d="M40 40l3 7 7 3-7 3-3 7-3-7-7-3 7-3zM170 160l3 6 6 3-6 3-3 6-3-6-6-3 6-3z" fill="' . $t['white'] . '" ' . $s . ' stroke-width="3"/></svg>';
			break;

		case 'plane':
			$svg = '<svg viewBox="0 0 200 200"><circle cx="146" cy="58" r="28" fill="' . $t['butter'] . '" ' . $s . '/><path d="M20 150c40-20 120-20 160 0v30H20z" fill="' . $t['sky'] . '" ' . $s . '/><path d="M60 150c0-40 4-70 0-96" fill="none" stroke="' . $t['terracotta'] . '" stroke-width="10" stroke-linecap="round"/><path d="M60 54c-24-4-40 6-46 20 18-8 32-10 46-20zM60 54c20-10 40-8 52 4-18-2-34 0-52-4zM60 54c-4-20-20-30-36-30 14 8 26 18 36 30z" fill="' . $t['leaf'] . '" ' . $s . '/><path d="M96 104l70-36c8-4 16 0 12 8l-10 6-34 30-10 24-8 2 2-22-20 10-6 10-6 0 2-14-8-6 4-4 12 2z" fill="' . $t['white'] . '" ' . $s . '/></svg>';
			break;

		case 'globe':
			$svg = '<svg viewBox="0 0 200 200"><circle cx="100" cy="100" r="66" fill="' . $t['sky'] . '" ' . $s . '/><path d="M70 50c14 6 18 20 12 30s4 18 14 22-2 22-10 30-2 18 0 24M118 40c-8 12 4 18 14 22s6 16 18 18M130 120c10 6 24 6 32-4" fill="none" stroke="' . $ink . '" stroke-width="3"/><path d="M76 62c10-6 24 0 28 10s-2 16 6 24 4 20-4 28-14 6-16 18c-12-6-20-24-18-40s-8-26 4-40z" fill="' . $t['leaf'] . '" ' . $s . '/><path d="M100 22v-8M100 186v8" ' . $s . '/><path d="M150 36s-10-6-10-13c0-4 3-6 6-6 2 0 3 1 4 3 1-2 2-3 4-3 3 0 6 2 6 6 0 7-10 13-10 13z" fill="' . $t['coral'] . '" ' . $s . ' stroke-width="3"/><path d="M40 150s-10-6-10-13c0-4 3-6 6-6 2 0 3 1 4 3 1-2 2-3 4-3 3 0 6 2 6 6 0 7-10 13-10 13z" fill="' . $t['marigold'] . '" ' . $s . ' stroke-width="3"/><path d="M48 150C70 190 150 196 178 130" fill="none" stroke="' . $ink . '" stroke-width="3" stroke-dasharray="6 8"/></svg>';
			break;

		case 'house':
			$svg = '<svg viewBox="0 0 200 200"><path d="M30 100 100 40l70 60v76H30z" fill="' . $t['cream'] . '" ' . $s . '/><path d="M18 106 100 34l82 72" fill="none" ' . $s . ' stroke-width="8"/><path d="M18 106 100 34l82 72" fill="none" stroke="' . $t['terracotta'] . '" stroke-width="3"/><rect x="84" y="124" width="32" height="52" rx="3" fill="' . $t['jacaranda'] . '" ' . $s . '/><rect x="46" y="116" width="26" height="24" rx="3" fill="' . $t['sky'] . '" ' . $s . '/><rect x="128" y="116" width="26" height="24" rx="3" fill="' . $t['sky'] . '" ' . $s . '/><circle cx="110" cy="152" r="3" fill="' . $t['butter'] . '"/><path d="M100 96s-12-7-12-15c0-4 3-7 7-7 2 0 4 1 5 3 1-2 3-3 5-3 4 0 7 3 7 7 0 8-12 15-12 15z" fill="' . $t['coral'] . '" ' . $s . ' stroke-width="3"/></svg>';
			break;

		case 'car':
			$svg = '<svg viewBox="0 0 200 200"><path d="M20 128c0-10 6-16 16-18l22-30c4-6 10-8 16-8h56c6 0 12 2 16 8l18 28c10 2 16 10 16 20v14H20z" fill="' . $t['white'] . '" ' . $s . '/><path d="M64 84h30v26H48zM104 84h28l14 26h-42z" fill="' . $t['sky'] . '" ' . $s . '/><circle cx="56" cy="144" r="16" fill="' . $ink . '"/><circle cx="56" cy="144" r="6" fill="' . $t['cream'] . '"/><circle cx="148" cy="144" r="16" fill="' . $ink . '"/><circle cx="148" cy="144" r="6" fill="' . $t['cream'] . '"/><path d="M82 72c-6-14 6-22 18-14 12-8 24 0 18 14" fill="' . $t['coral'] . '" ' . $s . ' stroke-width="3"/><path d="M90 60l-20-30M110 60l22-28" stroke="' . $ink . '" stroke-width="2"/><circle cx="68" cy="28" r="10" fill="' . $t['butter'] . '" ' . $s . ' stroke-width="3"/><circle cx="134" cy="28" r="10" fill="' . $t['lavender'] . '" ' . $s . ' stroke-width="3"/></svg>';
			break;

		case 'camera':
			$svg = '<svg viewBox="0 0 200 200"><rect x="30" y="64" width="140" height="96" rx="14" fill="' . $t['lavender'] . '" ' . $s . '/><path d="M70 64l10-18h40l10 18" fill="' . $t['white'] . '" ' . $s . '/><circle cx="100" cy="112" r="32" fill="' . $t['white'] . '" ' . $s . '/><circle cx="100" cy="112" r="18" fill="' . $t['indigo'] . '" ' . $s . '/><circle cx="94" cy="106" r="5" fill="' . $t['white'] . '"/><rect x="140" y="76" width="18" height="10" rx="3" fill="' . $t['coral'] . '" ' . $s . ' stroke-width="3"/><circle cx="160" cy="40" r="10" fill="#FF4D4D" ' . $s . ' stroke-width="3"/><text x="132" y="45" text-anchor="end" font-family="Adventor, sans-serif" font-weight="700" font-size="16" fill="' . $ink . '">LIVE</text></svg>';
			break;

		case 'qr':
			$svg = '<svg viewBox="0 0 200 200"><rect x="40" y="26" width="120" height="150" rx="12" fill="' . $t['white'] . '" ' . $s . '/><rect x="58" y="44" width="84" height="84" rx="6" fill="' . $t['cream'] . '" ' . $s . ' stroke-width="3"/><path d="M66 52h22v22H66zM112 52h22v22h-22zM66 98h22v22H66zM100 100h8v8h-8zM116 108h8v8h-8zM100 116h8v8h-8zM124 96h10v10h-10zM96 60h8v8h-8zM96 80h12v8H96z" fill="' . $ink . '"/><path d="M60 146h80M72 160h56" ' . $s . ' stroke-width="3"/><path d="M168 150s-12-7-12-15c0-4 3-7 7-7 2 0 4 1 5 3 1-2 3-3 5-3 4 0 7 3 7 7 0 8-12 15-12 15z" fill="' . $t['coral'] . '" ' . $s . ' stroke-width="3"/></svg>';
			break;

		case 'gift':
			$svg = '<svg viewBox="0 0 200 200"><rect x="40" y="86" width="120" height="86" rx="6" fill="' . $t['coral'] . '" ' . $s . '/><rect x="30" y="64" width="140" height="28" rx="6" fill="' . $t['butter'] . '" ' . $s . '/><path d="M92 64h16v108H92z" fill="' . $t['jacaranda'] . '" ' . $s . '/><path d="M100 64c-10-24-38-34-44-18-4 12 20 18 44 18zM100 64c10-24 38-34 44-18 4 12-20 18-44 18z" fill="' . $t['lavender'] . '" ' . $s . '/><path d="M24 40l3 7 7 3-7 3-3 7-3-7-7-3 7-3zM176 30l3 6 6 3-6 3-3 6-3-6-6-3 6-3z" fill="' . $t['white'] . '" ' . $s . ' stroke-width="3"/></svg>';
			break;

		case 'fabric':
			$svg = '<svg viewBox="0 0 200 200"><path d="M62 92v70c14 10 26 10 40 0s26-10 40 0V92z" fill="' . $t['coral'] . '" ' . $s . '/><g fill="none" stroke="' . $t['butter'] . '" stroke-width="5"><circle cx="86" cy="118" r="11"/><circle cx="122" cy="140" r="11"/></g><circle cx="86" cy="118" r="3" fill="' . $t['butter'] . '"/><circle cx="122" cy="140" r="3" fill="' . $t['butter'] . '"/><path d="M118 104c6 6 6 14 0 20-6-6-6-14 0-20z" fill="' . $t['white'] . '" opacity=".75"/><rect x="26" y="50" width="148" height="46" rx="23" fill="' . $t['lavender'] . '" ' . $s . '/><path d="M60 62c26-6 70-6 100 0" fill="none" stroke="' . $t['white'] . '" stroke-width="4" stroke-linecap="round" opacity=".7"/><ellipse cx="48" cy="73" rx="14" ry="23" fill="' . $t['jacaranda'] . '" ' . $s . '/><ellipse cx="48" cy="73" rx="4" ry="8" fill="' . $t['butter'] . '" ' . $s . ' stroke-width="3"/><path d="M168 24l3 7 7 3-7 3-3 7-3-7-7-3 7-3z" fill="' . $t['white'] . '" ' . $s . ' stroke-width="3"/></svg>';
			break;

		case 'notes':
			$svg = '<svg viewBox="0 0 220 160">';
			foreach ( array( array( -16, 'mint' ), array( -4, 'lavender' ), array( 8, 'butter' ) ) as $i => $n ) {
				$svg .= '<g transform="rotate(' . $n[0] . ' 110 90)"><rect x="40" y="50" width="140" height="70" rx="6" fill="' . $t[ $n[1] ] . '" ' . $s . '/><circle cx="110" cy="85" r="18" fill="' . $t['white'] . '" ' . $s . ' stroke-width="3"/><text x="110" y="93" text-anchor="middle" font-family="Adventor, sans-serif" font-weight="700" font-size="22" fill="' . $ink . '">₦</text><path d="M54 64h20M146 106h20" stroke="' . $ink . '" stroke-width="3"/></g>';
			}
			$svg .= '</svg>';
			break;

		case 'calabash':
			$svg = '<svg viewBox="0 0 200 200"><path d="M40 92h120c0 44-26 74-60 74s-60-30-60-74z" fill="' . $t['terracotta'] . '" ' . $s . '/><path d="M40 92c10-10 110-10 120 0" fill="' . $t['butter'] . '" ' . $s . '/><path d="M56 118c14 8 74 8 88 0M64 140c12 6 60 6 72 0" fill="none" stroke="' . $t['cream'] . '" stroke-width="4"/><path d="M80 62c-4-14 2-28 14-32M120 62c4-14-2-28-14-32" fill="none" stroke="' . $t['white'] . '" stroke-width="5" stroke-linecap="round" opacity=".8"/><circle cx="148" cy="44" r="14" fill="' . $t['leaf'] . '" ' . $s . ' stroke-width="3"/><path d="M28 58l3 7 7 3-7 3-3 7-3-7-7-3 7-3z" fill="' . $t['white'] . '" ' . $s . ' stroke-width="3"/></svg>';
			break;

		case 'phone':
			$svg = '<svg viewBox="0 0 160 240"><rect x="20" y="10" width="120" height="220" rx="22" fill="' . $ink . '"/><rect x="28" y="18" width="104" height="204" rx="16" fill="' . $t['cream'] . '"/><rect x="60" y="22" width="40" height="10" rx="5" fill="' . $ink . '"/><rect x="38" y="44" width="84" height="60" rx="10" fill="' . $t['lavender'] . '"/><circle cx="80" cy="74" r="16" fill="' . $t['white'] . '" ' . $s . ' stroke-width="3"/><path d="M80 82s-8-5-8-10c0-3 2-5 4-5 2 0 3 1 4 2 1-1 2-2 4-2 2 0 4 2 4 5 0 5-8 10-8 10z" fill="' . $t['coral'] . '"/><rect x="38" y="114" width="84" height="12" rx="6" fill="' . $t['butter'] . '"/><rect x="38" y="134" width="60" height="8" rx="4" fill="' . $t['lavender'] . '"/><rect x="38" y="150" width="84" height="40" rx="10" fill="' . $t['mint'] . '"/><rect x="38" y="198" width="84" height="14" rx="7" fill="' . $t['jacaranda'] . '"/></svg>';
			break;
	}
	if ( ! $svg ) {
		return '';
	}
	return str_replace( '<svg ', '<svg class="art art--' . esc_attr( $name ) . ( $class ? ' ' . esc_attr( $class ) : '' ) . '" aria-hidden="true" focusable="false" ', $svg );
}

/**
 * Illustrated avatar for bridal-train members & guests without a photo.
 *
 * @param string $seed Seed (name).
 * @param string $kind f (gele) | m (fila) | any.
 * @param string $bg   Background colour.
 * @param string $wrap Gele/fila colour.
 * @return string
 */
function wd_avatar( $seed, $kind = 'any', $bg = '', $wrap = '' ) {
	$t     = wd_tones();
	$h     = abs( crc32( $seed ) );
	$skins = array( '#6B3E26', '#8D5A35', '#A86B3F', '#5A3420', '#7B4A2C', '#9A6440' );
	$cols  = array( $t['jacaranda'], $t['terracotta'], $t['leaf'], $t['marigold'], $t['indigo'], $t['coral'] );
	$bgs   = array( $t['butter'], $t['lavender'], $t['sky'], $t['mint'], $t['coral'], $t['cream'] );
	$skin  = $skins[ $h % 6 ];
	$wrap  = $wrap ? $wrap : $cols[ ( $h >> 3 ) % 6 ];
	$bg    = $bg ? $bg : $bgs[ ( $h >> 6 ) % 6 ];
	$cloth = $cols[ ( $h >> 9 ) % 6 ];
	$kind  = 'any' === $kind ? ( ( $h >> 2 ) % 2 ? 'f' : 'm' ) : $kind;
	$ink   = $t['ink'];
	$s     = 'stroke="' . $ink . '" stroke-width="3" stroke-linejoin="round" stroke-linecap="round"';
	$v     = ( $h >> 12 ) % 3;
	$o     = '<svg class="avatar-art" viewBox="0 0 120 120" aria-hidden="true" focusable="false"><circle cx="60" cy="60" r="60" fill="' . $bg . '"/>';
	$o    .= '<path d="M16 122c3-24 22-36 44-36s41 12 44 36z" fill="' . $cloth . '" ' . $s . '/>';
	$o    .= '<path d="M52 72h16v16c-2 4-14 4-16 0z" fill="' . $skin . '" ' . $s . '/>';
	if ( 'f' === $kind ) {
		$o .= '<path d="M46 92l14 12 14-12" fill="none" stroke="' . $t['butter'] . '" stroke-width="3"/>';
	} else {
		$o .= '<path d="M50 90l10 14 10-14" fill="none" ' . $s . '/><path d="M60 104v14" ' . $s . '/>';
	}
	$o .= '<ellipse cx="60" cy="58" rx="20" ry="23" fill="' . $skin . '" ' . $s . '/>';
	if ( 'f' === $kind ) {
		if ( 2 === $v ) {
			$o .= '<circle cx="38" cy="40" r="12" fill="#1E120C" ' . $s . '/><circle cx="82" cy="40" r="12" fill="#1E120C" ' . $s . '/><path d="M40 52c0-18 10-26 20-26s20 8 20 26c-8-8-30-8-40 0z" fill="#1E120C" ' . $s . '/>';
		} else {
			$o .= '<path d="M30 48c-6-22 12-38 30-32 16-8 38 6 32 32-8-10-20-14-32-12-12-2-22 2-30 12z" fill="' . $wrap . '" ' . $s . '/><path d="M44 22c6 8 10 16 10 24M74 20c-4 8-6 16-4 24M60 16v20" fill="none" stroke="' . $ink . '" stroke-width="2"/>';
			$o .= 1 === $v ? '<path d="M84 34c10-6 18-2 20 6-8-2-14 0-20 4z" fill="' . $wrap . '" ' . $s . '/>' : '';
		}
		$o .= '<circle cx="40" cy="66" r="4" fill="' . $t['marigold'] . '" ' . $s . ' stroke-width="2"/><circle cx="80" cy="66" r="4" fill="' . $t['marigold'] . '" ' . $s . ' stroke-width="2"/>';
	} else {
		if ( 1 === $v ) {
			$o .= '<path d="M40 50c0-14 8-22 20-22s20 8 20 22c-6-6-14-8-20-8s-14 2-20 8z" fill="#1E120C" ' . $s . '/>';
		} else {
			$o .= '<path d="M38 44c2-16 14-24 26-22 12 2 20 10 18 20l-2 6c-14-6-28-8-42-4z" fill="' . $wrap . '" ' . $s . '/><path d="M66 22c6 0 16 4 16 12" fill="none" stroke="' . $t['cream'] . '" stroke-width="2"/>';
		}
		if ( 2 === $v ) {
			$o .= '<path d="M42 64c2 14 8 20 18 20s16-6 18-20c-4 6-10 8-18 8s-14-2-18-8z" fill="#1E120C"/>';
		}
	}
	$o .= '<ellipse cx="52" cy="59" rx="2.6" ry="3.2" fill="' . $ink . '"/><ellipse cx="68" cy="59" rx="2.6" ry="3.2" fill="' . $ink . '"/><path d="M53 69q7 6 14 0" fill="none" stroke="' . ( 'm' === $kind && 2 === $v ? $t['cream'] : $ink ) . '" stroke-width="2.6" stroke-linecap="round"/><circle cx="47" cy="66" r="3.5" fill="' . $t['coral'] . '" opacity=".45"/><circle cx="73" cy="66" r="3.5" fill="' . $t['coral'] . '" opacity=".45"/>';
	return $o . '</svg>';
}

/**
 * Cloth pattern as a CSS url() for backgrounds.
 *
 * @param string $type  adire|kente|ankara|mudcloth.
 * @param string $c1    Main colour.
 * @param string $c2    Second colour.
 * @param string $c3    Third colour.
 * @return string
 */
function wd_pattern_url( $type, $c1 = '#23094B', $c2 = '#FFC21A', $c3 = '#FFF6E5' ) {
	switch ( $type ) {
		case 'kente':
			$svg = '<svg xmlns="http://www.w3.org/2000/svg" width="120" height="120"><rect width="120" height="120" fill="' . $c1 . '"/><rect y="0" width="120" height="14" fill="' . $c2 . '"/><rect y="20" width="120" height="6" fill="' . $c3 . '"/><rect y="60" width="120" height="14" fill="' . $c2 . '"/><rect y="80" width="120" height="6" fill="' . $c3 . '"/><rect x="0" y="30" width="30" height="24" fill="' . $c2 . '"/><rect x="60" y="30" width="30" height="24" fill="' . $c3 . '" opacity=".6"/><rect x="30" y="90" width="30" height="24" fill="' . $c3 . '" opacity=".6"/><rect x="90" y="90" width="30" height="24" fill="' . $c2 . '"/><path d="M8 42h14M68 42h14M38 102h14M98 102h14" stroke="' . $c1 . '" stroke-width="3"/></svg>';
			break;
		case 'ankara':
			$svg = '<svg xmlns="http://www.w3.org/2000/svg" width="140" height="140"><rect width="140" height="140" fill="' . $c1 . '"/><g fill="none" stroke-width="6"><circle cx="35" cy="35" r="26" stroke="' . $c2 . '"/><circle cx="35" cy="35" r="14" stroke="' . $c3 . '"/><circle cx="105" cy="105" r="26" stroke="' . $c2 . '"/><circle cx="105" cy="105" r="14" stroke="' . $c3 . '"/></g><circle cx="35" cy="35" r="5" fill="' . $c2 . '"/><circle cx="105" cy="105" r="5" fill="' . $c2 . '"/><path d="M105 18c10 10 10 24 0 34-10-10-10-24 0-34zM35 88c10 10 10 24 0 34-10-10-10-24 0-34z" fill="' . $c3 . '" opacity=".7"/></svg>';
			break;
		case 'mudcloth':
			$svg = '<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100"><rect width="100" height="100" fill="' . $c1 . '"/><g stroke="' . $c3 . '" stroke-width="4" stroke-linecap="round"><path d="M10 10h30M10 18h30M60 60h30M60 68h30"/><path d="M70 10l10 10M80 10l-10 10M20 60l10 10M30 60l-10 10"/></g><g fill="' . $c2 . '"><circle cx="25" cy="40" r="4"/><circle cx="75" cy="40" r="4"/><circle cx="25" cy="90" r="4"/><circle cx="75" cy="90" r="4"/></g></svg>';
			break;
		case 'none':
			return 'none';
		default:
			$svg = '<svg xmlns="http://www.w3.org/2000/svg" width="96" height="96"><rect width="96" height="96" fill="' . $c1 . '"/><g fill="none" stroke="' . $c3 . '" stroke-width="3" opacity=".9"><circle cx="24" cy="24" r="16"/><circle cx="24" cy="24" r="8"/><circle cx="72" cy="72" r="16"/><circle cx="72" cy="72" r="8"/></g><g fill="' . $c2 . '"><circle cx="72" cy="24" r="4"/><circle cx="24" cy="72" r="4"/><circle cx="60" cy="12" r="2.5"/><circle cx="84" cy="36" r="2.5"/><circle cx="12" cy="60" r="2.5"/><circle cx="36" cy="84" r="2.5"/></g></svg>';
	}
	return 'url("data:image/svg+xml,' . rawurlencode( $svg ) . '")';
}

/**
 * Cover art for a wedding without a photo: cloth pattern, a medallion with initials & the date.
 *
 * @param array  $d    Wedding data.
 * @param string $seed Seed.
 * @return string
 */
function wd_cover_art( $d, $seed = '' ) {
	$t     = wd_tones();
	$sets  = array(
		array( $t['jacaranda'], $t['marigold'], $t['lavender'], 'adire' ),
		array( $t['leaf'], $t['butter'], $t['mint'], 'kente' ),
		array( $t['terracotta'], $t['butter'], $t['cream'], 'ankara' ),
		array( $t['indigo'], $t['sky'], $t['cream'], 'adire' ),
		array( $t['aubergine'], $t['coral'], $t['lavender'], 'mudcloth' ),
	);
	$h     = abs( crc32( $seed ? $seed : wd_names( $d ) ) );
	$set   = $sets[ $h % count( $sets ) ];
	if ( ! empty( $d['design']['primary'] ) && '#6B2BD9' !== $d['design']['primary'] ) {
		$set[0] = $d['design']['primary'];
		$set[1] = $d['design']['accent'];
		if ( '#111111' === $set[0] ) {
			$set[2] = '#FFFFFF'; // Free pages: black & white.
		}
	}
	$pat   = esc_attr( wd_pattern_url( $set[3], $set[0], $set[1], $set[2] ) );
	$ini   = esc_html( wd_initials( $d ) );
	$date  = esc_html( wd_date( $d['date'], 'j . n . Y' ) );
	return '<div class="cover-art" style="background-image:' . $pat . '" aria-hidden="true"><div class="cover-art__medal" style="--c1:' . esc_attr( $set[0] ) . ';--c2:' . esc_attr( $set[1] ) . '"><span class="cover-art__ini">' . $ini . '</span><span class="cover-art__date">' . $date . '</span></div></div>';
}

/**
 * Cover image or art for a wedding card/hero.
 *
 * @param int    $id   Wedding.
 * @param array  $d    Data.
 * @param string $size Image size.
 * @return string
 */
function wd_cover( $id, $d, $size = 'wd-card' ) {
	$src = $d['cover'] ? wd_img_url( $d['cover'], $size ) : '';
	if ( $src ) {
		return '<img class="cover-img" src="' . esc_url( $src ) . '" alt="' . esc_attr( wd_names( $d ) ) . '" loading="lazy" decoding="async">';
	}
	return wd_cover_art( $d, (string) $id );
}

/**
 * Fine ornaments for the Premium templates: crest (with initials), sprig & rule.
 * Drawn in currentColor so each page's own colours apply.
 *
 * @param string $name  crest|sprig|rule.
 * @param string $text  Initials for the crest.
 * @param string $class Extra class.
 * @return string SVG.
 */
function wd_orn( $name, $text = '', $class = '' ) {
	$c = 'orn orn--' . $name . ( $class ? ' ' . $class : '' );
	if ( 'rule' === $name ) {
		return '<svg class="' . esc_attr( $c ) . '" viewBox="0 0 220 16" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1"><path d="M0 8h88M132 8h88"/><path d="m110 2 6 6-6 6-6-6z" fill="currentColor"/><circle cx="95" cy="8" r="1.6" fill="currentColor"/><circle cx="125" cy="8" r="1.6" fill="currentColor"/></svg>';
	}
	if ( 'sprig' === $name ) {
		$leaves = '';
		for ( $i = 0; $i < 9; $i++ ) {
			$y    = 280 - $i * 30;
			$x    = 60 + sin( $i * 0.9 ) * 10;
			$side = $i % 2 ? 1 : -1;
			$len  = 26 - $i * 1.4;
			$leaves .= sprintf( '<path d="M%1$.1f %2$.1f Q%3$.1f %4$.1f %5$.1f %6$.1f Q%7$.1f %8$.1f %1$.1f %2$.1f Z"/>', $x, $y, $x + $side * $len * 0.95, $y - $len * 0.1, $x + $side * $len, $y - $len * 0.72, $x + $side * $len * 0.12, $y - $len * 0.7 );
		}
		$buds = '<circle cx="62" cy="22" r="6"/><circle cx="62" cy="22" r="2" fill="currentColor"/><circle cx="44" cy="58" r="4"/><circle cx="80" cy="96" r="4"/>';
		return '<svg class="' . esc_attr( $c ) . '" viewBox="0 0 120 300" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.1" stroke-linecap="round"><path d="M60 300 C 48 230, 74 170, 60 110 S 66 40, 62 28"/>' . $leaves . $buds . '</svg>';
	}
	// Crest: double ring, small crown & laurel, with the couple's initials.
	$laurel = '';
	foreach ( array( -1, 1 ) as $side ) {
		for ( $i = 0; $i < 8; $i++ ) {
			$a  = deg2rad( 90 + $side * ( 38 + $i * 16 ) );
			$cx = 80 + cos( $a ) * 70;
			$cy = 84 + sin( $a ) * 70;
			$r  = rad2deg( $a ) + ( $side > 0 ? 60 : -60 );
			$laurel .= sprintf( '<ellipse cx="%.1f" cy="%.1f" rx="7" ry="2.6" transform="rotate(%.1f %.1f %.1f)"/>', $cx, $cy, $r, $cx, $cy );
		}
	}
	return '<svg class="' . esc_attr( $c ) . '" viewBox="0 0 160 168" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.2">'
		. '<circle cx="80" cy="84" r="56"/><circle cx="80" cy="84" r="50" stroke-width=".6"/>'
		. '<path d="M64 26 68 12l6 8 6-12 6 12 6-8 4 14z" fill="currentColor" stroke-linejoin="round"/>'
		. $laurel
		. '<text x="80" y="96" text-anchor="middle" class="orn__text" fill="currentColor" stroke="none">' . esc_html( $text ) . '</text></svg>';
}

/**
 * Pearl's monogram: the couple's initials inside a ring of pearls.
 *
 * @param string $text  Initials.
 * @param string $class CSS class.
 * @return string SVG.
 */
function wd_pe_mono( $text, $class = 'pe-mono' ) {
	$o = '<svg class="' . esc_attr( $class ) . '" viewBox="0 0 120 120" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"><circle class="pe-mono__ring" cx="60" cy="60" r="44"/>';
	for ( $i = 0; $i < 36; $i++ ) {
		$r  = 0 === $i % 9 ? 3.2 : ( 0 === $i % 3 ? 2.3 : 1.6 );
		$o .= '<circle class="pe-mono__pearl" cx="' . round( 60 + 52 * cos( $i * M_PI / 18 - M_PI / 2 ), 2 ) . '" cy="' . round( 60 + 52 * sin( $i * M_PI / 18 - M_PI / 2 ), 2 ) . '" r="' . $r . '"/>';
	}
	return $o . '<text class="pe-mono__text" x="60" y="69" text-anchor="middle">' . esc_html( $text ) . '</text></svg>';
}

/**
 * Pearl's divider: a fine line strung with five pearls.
 *
 * @return string HTML.
 */
function wd_pe_pearls() {
	return '<p class="pe-pearls" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i></p>';
}

/**
 * Small picture of each Premium template for the template picker.
 *
 * @param string $key royal|pearl|garden.
 * @return string SVG.
 */
function wd_tpl_thumb( $key ) {
	if ( 'ivory' === $key ) {
		return '<svg viewBox="0 0 160 100" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"><defs><linearGradient id="ivf" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#e6d0a0"/><stop offset=".3" stop-color="#b8924a"/><stop offset=".5" stop-color="#f3e4bd"/><stop offset=".75" stop-color="#9a7634"/><stop offset="1" stop-color="#dcc188"/></linearGradient></defs>'
			. '<rect width="160" height="100" fill="#eee4d3"/><rect x="50" y="8" width="60" height="84" fill="#fbf7ef" stroke="url(#ivf)" stroke-width="1.4"/><rect x="54" y="12" width="52" height="76" fill="none" stroke="#c9a865" stroke-width=".5"/>'
			. '<rect x="66" y="20" width="28" height="2" fill="#a9845a" opacity=".6"/><rect x="60" y="28" width="40" height="6" rx="1" fill="url(#ivf)"/><text x="80" y="42" text-anchor="middle" font-family="Georgia, serif" font-style="italic" font-size="7" fill="#9a7634">&amp;</text><rect x="58" y="45" width="44" height="6" rx="1" fill="url(#ivf)"/>'
			. '<path d="M66 57h10M84 57h10" stroke="#c9a865" stroke-width=".6"/><path d="M80 54l3 3-3 3-3-3z" fill="#1f8a66" stroke="#c9a865" stroke-width=".5"/>'
			. '<rect x="60" y="64" width="10" height="9" fill="#a9845a" opacity=".35"/><rect x="75" y="64" width="10" height="9" fill="#a9845a" opacity=".35"/><rect x="90" y="64" width="10" height="9" fill="#a9845a" opacity=".35"/>'
			. '<rect x="64" y="79" width="32" height="5" fill="#7a5c3a"/>'
			. '<rect x="10" y="30" width="30" height="44" rx="2" fill="#4a2e1f"/><rect x="13" y="33" width="24" height="38" fill="none" stroke="url(#ivf)" stroke-width=".7"/><circle cx="25" cy="58" r="4" fill="none" stroke="url(#ivf)" stroke-width=".7"/>'
			. '<rect x="120" y="30" width="30" height="44" fill="#fbf7ef" stroke="#c9a865" stroke-width=".6"/><circle cx="135" cy="42" r="6" fill="#eee4d3" stroke="#c9a865" stroke-width=".5"/><rect x="126" y="52" width="18" height="3" fill="#a9845a" opacity=".6"/><rect x="128" y="58" width="14" height="2" fill="#a9845a" opacity=".4"/></svg>';
	}
	$t = wd_templates();
	if ( ! isset( $t[ $key ] ) ) {
		return '';
	}
	list( $p, $a, $bg, $ink ) = $t[ $key ][2];
	if ( ! empty( wd_tpl_extras()[ $key ]['base'] ) ) {
		return wd_st_thumb( $key, $p, $a, $bg, $ink );
	}
	$o = '<svg viewBox="0 0 160 100" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">';
	if ( 'royal' === $key ) {
		$kente = '';
		for ( $x = 0; $x < 160; $x += 12 ) {
			$kente .= '<rect x="' . $x . '" width="6" height="4" fill="' . $a . '"/><rect x="' . ( $x + 6 ) . '" width="2" height="4" fill="#15110c"/><rect x="' . ( $x + 8 ) . '" width="4" height="4" fill="#1f6b45"/>';
		}
		$o .= '<rect width="160" height="100" fill="' . $p . '"/>'
			. '<g>' . $kente . '</g><g transform="translate(0 96)">' . $kente . '</g>'
			. '<circle cx="46" cy="18" r="6" fill="none" stroke="' . $a . '" stroke-width=".9"/><path d="M43 11l1.5-3 1.5 2 1.5-2 1.5 3z" fill="' . $a . '"/>'
			. '<rect x="24" y="30" width="44" height="8" rx="1" fill="' . $a . '"/><text x="46" y="47" text-anchor="middle" font-family="Georgia, serif" font-style="italic" font-size="8" fill="' . $bg . '">&amp;</text><rect x="20" y="50" width="52" height="8" rx="1" fill="' . $a . '"/>'
			. '<path d="M18 67h18M56 67h18" stroke="' . $a . '" stroke-width=".7"/><rect x="40" y="62" width="12" height="9" fill="' . $a . '"/>'
			. '<rect x="27" y="78" width="12" height="7" fill="' . $bg . '"/><rect x="41" y="78" width="12" height="7" fill="' . $a . '"/><rect x="55" y="78" width="10" height="7" fill="none" stroke="' . $a . '" stroke-width=".7"/>'
			. '<rect x="96" y="14" width="50" height="68" fill="' . $a . '"/><rect x="99" y="17" width="44" height="62" fill="' . $bg . '" opacity=".4"/><path d="M96 10l4 4-4 4-4-4zM146 78l4 4-4 4-4-4z" fill="' . $a . '"/>';
	} elseif ( 'pearl' === $key ) {
		$pearl = static function ( $x, $y, $r ) use ( $a, $p ) {
			return '<circle cx="' . $x . '" cy="' . $y . '" r="' . $r . '" fill="' . $a . '" stroke="' . $p . '" stroke-opacity=".5" stroke-width=".4"/>';
		};
		$ring = '';
		for ( $i = 0; $i < 16; $i++ ) {
			$ring .= $pearl( round( 117 + 9 * cos( $i * M_PI / 8 ), 2 ), round( 22 + 9 * sin( $i * M_PI / 8 ), 2 ), .9 );
		}
		$o .= '<rect width="160" height="100" fill="' . $bg . '"/><rect width="160" height="44" fill="' . $a . '" opacity=".22"/>'
			. '<rect x="20" y="16" width="56" height="72" rx="8" fill="none" stroke="' . $p . '" stroke-opacity=".45" stroke-width=".6"/>'
			. '<rect x="14" y="10" width="56" height="72" rx="8" fill="' . $p . '" opacity=".38"/>'
			. $ring . '<text x="117" y="25" text-anchor="middle" font-family="Georgia, serif" font-size="7" fill="' . $p . '">A&amp;B</text>'
			. '<rect x="98" y="38" width="38" height="5" rx="1" fill="' . $ink . '" opacity=".8"/><circle cx="117" cy="48" r="3" fill="none" stroke="' . $p . '" stroke-width=".5"/><text x="117" y="49.6" text-anchor="middle" font-family="Georgia, serif" font-style="italic" font-size="4" fill="' . $p . '">&amp;</text><rect x="100" y="53" width="34" height="5" rx="1" fill="' . $ink . '" opacity=".8"/>'
			. '<path d="M98 64h10M126 64h10" stroke="' . $p . '" stroke-opacity=".6" stroke-width=".5"/>' . $pearl( 111, 64, 1 ) . $pearl( 114, 64, 1.3 ) . $pearl( 117, 64, 1.8 ) . $pearl( 120, 64, 1.3 ) . $pearl( 123, 64, 1 )
			. '<rect x="104" y="69" width="26" height="2.4" rx="1" fill="' . $ink . '" opacity=".5"/>'
			. '<rect x="98" y="76" width="8" height="8" rx="2" fill="#fff" stroke="' . $p . '" stroke-opacity=".5" stroke-width=".5"/><rect x="108" y="76" width="8" height="8" rx="2" fill="#fff" stroke="' . $p . '" stroke-opacity=".5" stroke-width=".5"/><rect x="118" y="76" width="8" height="8" rx="2" fill="#fff" stroke="' . $p . '" stroke-opacity=".5" stroke-width=".5"/><rect x="128" y="76" width="8" height="8" rx="2" fill="#fff" stroke="' . $p . '" stroke-opacity=".5" stroke-width=".5"/>'
			. '<rect x="101" y="88" width="15" height="5" rx="2.5" fill="' . $p . '"/><rect x="118" y="88" width="15" height="5" rx="2.5" fill="' . $a . '"/>';
	} else {
		$leaf = static function ( $x, $y, $r ) use ( $p ) {
			return '<path d="M0 0C3-4 9-4 12 0C9 4 3 4 0 0Z" transform="translate(' . $x . ' ' . $y . ') rotate(' . $r . ')" fill="none" stroke="' . $p . '" stroke-width=".7"/>';
		};
		$o .= '<rect width="160" height="100" fill="' . $bg . '"/><ellipse cx="80" cy="0" rx="110" ry="60" fill="' . $a . '" opacity=".18"/>'
			. '<rect x="10" y="6" width="140" height="50" rx="11" fill="' . $p . '" opacity=".4"/>'
			. '<path d="M14 72c3-8 7-14 12-22" stroke="' . $p . '" stroke-width=".8" fill="none"/>' . $leaf( 18, 62, -60 ) . $leaf( 22, 55, 200 ) . $leaf( 24, 49, -70 )
			. '<path d="M146 72c-3-8-7-14-12-22" stroke="' . $p . '" stroke-width=".8" fill="none"/>' . $leaf( 142, 62, 240 ) . $leaf( 138, 55, -20 ) . $leaf( 136, 49, 250 )
			. '<rect x="36" y="38" width="88" height="58" rx="9" fill="#fff"/><rect x="39.5" y="41.5" width="81" height="51" rx="6.5" fill="none" stroke="' . $p . '" stroke-opacity=".35" stroke-width=".6"/>'
			. '<rect x="68" y="46" width="24" height="4" rx="2" fill="' . $a . '" opacity=".7"/>'
			. '<rect x="47" y="55" width="25" height="7" rx="2" fill="' . $ink . '" opacity=".8"/><text x="80" y="63" text-anchor="middle" font-family="Great Vibes, cursive" font-size="13" fill="' . $a . '">&amp;</text><rect x="88" y="55" width="25" height="7" rx="2" fill="' . $ink . '" opacity=".8"/>'
			. '<path d="M57 69h10M93 69h10" stroke="' . $p . '" stroke-width=".7"/><rect x="69" y="67.5" width="22" height="3" fill="' . $ink . '" opacity=".55"/>'
			. '<rect x="60" y="74" width="9" height="8" rx="2.5" fill="none" stroke="' . $p . '" stroke-width=".6"/><rect x="71" y="74" width="9" height="8" rx="2.5" fill="none" stroke="' . $p . '" stroke-width=".6"/><rect x="82" y="74" width="9" height="8" rx="2.5" fill="none" stroke="' . $p . '" stroke-width=".6"/><rect x="93" y="74" width="9" height="8" rx="2.5" fill="none" stroke="' . $p . '" stroke-width=".6"/>'
			. '<rect x="62" y="85" width="17" height="5" rx="2.5" fill="' . $p . '"/><rect x="81" y="85" width="17" height="5" rx="2.5" fill="' . $a . '" opacity=".75"/>';
	}
	return $o . '</svg>';
}


/**
 * Small picture of each hero layout for the layout picker.
 *
 * @param string $key left|right|top|names.
 * @return string SVG.
 */
function wd_layout_thumb( $key ) {
	$pic  = '#B9A3E8';
	$ink  = '#1C0B33';
	$line = static function ( $x, $y, $w, $h = 4, $o = '.75' ) use ( $ink ) {
		return '<rect x="' . $x . '" y="' . $y . '" width="' . $w . '" height="' . $h . '" rx="2" fill="' . $ink . '" opacity="' . $o . '"/>';
	};
	$o = '<svg viewBox="0 0 120 76" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"><rect width="120" height="76" fill="#FFF6E5"/>';
	if ( 'left' === $key || 'right' === $key ) {
		$px = 'left' === $key ? 8 : 66;
		$tx = 'left' === $key ? 66 : 8;
		$o .= '<rect x="' . $px . '" y="8" width="46" height="60" rx="6" fill="' . $pic . '"/>' . $line( $tx, 22, 40, 7, '.85' ) . $line( $tx, 33, 30 ) . $line( $tx, 41, 36, 3, '.4' ) . '<rect x="' . $tx . '" y="50" width="20" height="7" rx="3.5" fill="#6B2BD9"/>';
	} elseif ( 'top' === $key ) {
		$o .= '<rect x="8" y="6" width="104" height="36" rx="6" fill="' . $pic . '"/>' . $line( 30, 48, 60, 7, '.85' ) . $line( 40, 59, 40, 3, '.4' ) . '<rect x="50" y="65" width="20" height="6" rx="3" fill="#6B2BD9"/>';
	} else {
		$o .= $line( 22, 22, 76, 10, '.85' ) . $line( 36, 38, 48, 4, '.5' ) . $line( 44, 46, 32, 3, '.35' ) . '<rect x="50" y="55" width="20" height="7" rx="3.5" fill="#6B2BD9"/>';
	}
	return $o . '</svg>';
}

/**
 * Picker thumbnail for a Studio design: its layout in miniature, in its starting colours.
 *
 * @param string $key Design.
 * @param string $p   Main colour.
 * @param string $a   Second colour.
 * @param string $bg  Background.
 * @param string $ink Text colour.
 * @return string SVG.
 */
function wd_st_thumb( $key, $p, $a, $bg, $ink ) {
	$r     = static function ( $x, $y, $w, $h, $fill, $extra = '' ) {
		return '<rect x="' . $x . '" y="' . $y . '" width="' . $w . '" height="' . $h . '" fill="' . $fill . '" ' . $extra . '/>';
	};
	$photo = '#9fb3c8';
	$names = static function ( $cx, $y, $fill, $amp ) use ( $r ) {
		return $r( $cx - 20, $y, 40, 7, $fill, 'rx="1.5"' ) . '<text x="' . $cx . '" y="' . ( $y + 15 ) . '" text-anchor="middle" font-family="Georgia, serif" font-style="italic" font-size="8" fill="' . $amp . '">&amp;</text>' . $r( $cx - 18, $y + 18, 36, 7, $fill, 'rx="1.5"' );
	};
	$btns  = static function ( $cx, $y, $c1, $c2 ) use ( $r ) {
		return $r( $cx - 19, $y, 18, 6, $c1, 'rx="3"' ) . $r( $cx + 1, $y, 18, 6, $c2, 'rx="3"' );
	};
	$o = '<svg viewBox="0 0 160 100" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">' . $r( 0, 0, 160, 100, $bg );
	switch ( $key ) {
		case 'noir':
			$o .= '<path d="M10 0v100M150 0v100" stroke="' . $p . '" stroke-opacity=".4" stroke-width=".6"/>' . $names( 50, 26, $ink, $p ) . '<path d="M36 58h10M54 58h10" stroke="' . $p . '" stroke-width=".6"/><path d="M50 55l3 3-3 3-3-3z" fill="' . $p . '"/>' . $r( 34, 66, 32, 3, $ink, 'opacity=".5"' ) . $btns( 50, 76, $p, $a )
				. $r( 92, 10, 52, 80, 'none', 'stroke="' . $p . '" stroke-width=".7"' ) . $r( 96, 14, 44, 72, $photo );
			break;
		case 'coral':
			for ( $x = 4; $x < 160; $x += 6 ) {
				$o .= '<circle cx="' . $x . '" cy="5" r="2" fill="' . $p . '"/>';
			}
			$o .= '<circle cx="44" cy="52" r="32" fill="none" stroke="' . $p . '" stroke-width="2.4" stroke-dasharray="1.6 2.4"/><circle cx="44" cy="52" r="27" fill="' . $photo . '"/>';
			$o .= $r( 92, 26, 44, 8, $ink, 'rx="1.5"' ) . '<text x="96" y="44" font-family="Georgia, serif" font-style="italic" font-size="8" fill="' . $p . '">&amp;</text>' . $r( 92, 48, 38, 8, $ink, 'rx="1.5"' );
			for ( $x = 94; $x < 130; $x += 5 ) {
				$o .= '<circle cx="' . $x . '" cy="64" r="1.8" fill="' . $p . '"/>';
			}
			$o .= $r( 92, 74, 18, 6, $p, 'rx="3"' ) . $r( 112, 74, 18, 6, $a, 'rx="3"' );
			break;
		case 'indigo':
			$o .= '<circle cx="12" cy="10" r="16" fill="none" stroke="' . $p . '" stroke-opacity=".3" stroke-width="2" stroke-dasharray="1 3"/>' . $r( 12, 8, 136, 46, $photo, 'rx="5"' ) . $r( 40, 44, 80, 50, $p, 'rx="5"' );
			for ( $x = 44; $x < 118; $x += 5 ) {
				for ( $y = 48; $y < 92; $y += 5 ) {
					$o .= '<circle cx="' . $x . '" cy="' . $y . '" r=".5" fill="' . $a . '" opacity=".6"/>';
				}
			}
			$o .= $names( 80, 52, '#fff', $a ) . $btns( 80, 82, '#fff', $a );
			break;
		case 'asooke':
			for ( $y = 0; $y < 100; $y += 8 ) {
				$o .= $r( 76, $y, 8, 5, $p ) . $r( 76, $y + 5, 8, 1.5, $a ) . $r( 76, $y + 6.5, 8, 1.5, $ink );
			}
			$o .= $r( 10, 14, 56, 72, $photo, 'rx="1"' ) . $r( 14, 18, 56, 72, 'none', 'stroke="' . $a . '" stroke-width="3" opacity=".9"' )
				. $r( 92, 22, 54, 12, $ink ) . '<text x="94" y="47" font-family="Arial, sans-serif" font-weight="700" font-size="10" fill="' . $a . '">&amp;</text>' . $r( 92, 52, 46, 12, $p )
				. $r( 92, 70, 30, 4, $a ) . $r( 92, 80, 18, 6, $p, 'rx="1"' ) . $r( 112, 80, 18, 6, $a, 'rx="1"' );
			break;
		case 'keepsake':
			for ( $y = 8; $y < 100; $y += 8 ) {
				$o .= '<path d="M0 ' . $y . 'h160" stroke="' . $p . '" stroke-opacity=".12" stroke-width=".5"/>';
			}
			$o .= $names( 46, 28, $ink, $p ) . '<path d="M46 66c-4-3-7-5-7-8a3 3 0 0 1 7-1 3 3 0 0 1 7 1c0 3-3 5-7 8z" fill="' . $a . '" stroke="' . $ink . '" stroke-width=".6"/>' . $btns( 46, 76, $a, $p )
				. '<g transform="rotate(4 118 50)">' . $r( 94, 14, 48, 62, '#fff', 'stroke="#000" stroke-opacity=".1" stroke-width=".5"' ) . $r( 98, 18, 40, 40, $photo ) . $r( 104, 64, 28, 3, $ink, 'opacity=".4"' ) . '</g>'
				. $r( 90, 10, 22, 7, $a, 'opacity=".7" transform="rotate(-30 101 13)"' ) . $r( 126, 10, 22, 7, $a, 'opacity=".7" transform="rotate(30 137 13)"' );
			break;
		case 'savannah':
			$o .= '<defs><linearGradient id="svsky" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="' . $a . '"/><stop offset="1" stop-color="' . $bg . '"/></linearGradient></defs>' . $r( 0, 0, 160, 100, 'url(#svsky)' )
				. '<circle cx="128" cy="70" r="9" fill="' . $p . '" opacity=".45"/><path d="M0 88c30-8 60-9 90-5s50 4 70-2v19H0z" fill="' . $p . '" opacity=".18"/>'
				. '<path d="M78 90V76M66 74c8-4 18-5 26-3-8 2-17 3-26 3z" stroke="' . $ink . '" stroke-width="1.2" fill="' . $ink . '"/>'
				. $r( 14, 10, 50, 62, $photo, 'rx="9"' ) . $names( 110, 18, $ink, $p ) . '<circle cx="110" cy="50" r="2.6" fill="' . $p . '"/>' . $btns( 110, 58, $p, $a );
			break;
		default:
			$o .= $r( 0, 0, 160, 100, $a, 'opacity=".3"' );
			$dots = array( array( 8, 8 ), array( 30, 92 ), array( 150, 12 ), array( 140, 90 ), array( 78, 6 ), array( 6, 60 ), array( 154, 54 ) );
			foreach ( $dots as $i => $d ) {
				$o .= '<circle cx="' . $d[0] . '" cy="' . $d[1] . '" r="2.6" fill="' . ( $i % 2 ? $p : $a ) . '"/>';
			}
			$o .= $r( 20, 10, 120, 42, $photo, 'rx="6" stroke="' . $ink . '" stroke-width="1.5"' ) . $r( 58, 56, 44, 6, '#fff', 'rx="3" stroke="' . $ink . '" stroke-width=".8"' )
				. $r( 52, 66, 56, 8, $p, 'rx="1.5"' ) . $r( 56, 77, 48, 8, $p, 'rx="1.5"' ) . $r( 58, 89, 20, 7, $p, 'rx="3.5" stroke="' . $ink . '" stroke-width="1"' ) . $r( 82, 89, 20, 7, $a, 'rx="3.5" stroke="' . $ink . '" stroke-width="1"' );
	}
	return $o . '</svg>';
}
