<?php
/**
 * Indigo: the shared Studio sign, styled by assets/tpl/indigo.css.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
get_template_part( 'template-parts/tpl/studio/sign', null, $args + array( 'tpl' => 'indigo' ) );
