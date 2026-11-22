<?php
/**
 * Blog categories, tags & dates use the blog layout.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
if ( is_category() ) {
	require WD_DIR . '/page-templates/blog.php';
	return;
}
require WD_DIR . '/index.php';
