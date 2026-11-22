<?php
/**
 * Aso-oke: the shared Studio sign, styled by assets/tpl/asooke.css.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
get_template_part( 'template-parts/tpl/studio/sign', null, $args + array( 'tpl' => 'asooke' ) );
