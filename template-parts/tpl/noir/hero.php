<?php
/**
 * Noir: the shared Studio hero, styled by assets/tpl/noir.css.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
get_template_part( 'template-parts/tpl/studio/hero', null, $args + array( 'tpl' => 'noir' ) );
