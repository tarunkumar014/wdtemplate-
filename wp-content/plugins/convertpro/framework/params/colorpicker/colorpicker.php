<?php
/**
 * Fields.
 *
 * @package ConvertPro
 */

$text_attributes = array(
	'id'      => 'cp_colorpicker_par',
	'type'    => 'colorpicker',
	'scripts' => '',
	'styles'  => '',
);

echo wp_json_encode( $text_attributes );
