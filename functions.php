<?php
/**
 * Wedding Websites & More: Weddings. A wedding page platform & directory.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;

define( 'WD_VER', '2.0.0' );
define( 'WD_DIR', get_template_directory() );

foreach ( array( 'core', 'aurora', 'art', 'data', 'plans', 'ranking', 'payments', 'market', 'connect', 'social', 'talk', 'chat', 'mail', 'instagram', 'api', 'squad', 'auth', 'account', 'hq', 'help', 'blog', 'news', 'seed', 'seed-market', 'seo', 'custom', 'tpl', 'front-editor', 'upgrade' ) as $wd_file ) {
	require_once WD_DIR . '/inc/' . $wd_file . '.php';
}
