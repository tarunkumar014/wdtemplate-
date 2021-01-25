<?php
/**
 * Fields.
 *
 * @package ConvertPro
 */

// Add new input type "textfield".
if ( function_exists( 'cp_add_input_type' ) ) {
	cp_add_input_type( 'cp_text', 'cp_text_settings_field' );
}

/**
 * Function Name: cp_v2_label_settings_field.
 * Function Description: Function to handle new input type.
 *
 * @param string $name string parameter.
 * @param string $settings string parameter.
 * @param string $sections string parameter.
 * @param string $value string parameter.
 * @param string $default_value string parameter.
 */
function cp_text_settings_field( $name, $settings, $sections, $value, $default_value ) {
	$data_json = array(
		'id'         => $name,
		'title'      => $settings['title'],
		'sections'   => $sections,
		'resize'     => $settings['resize'],
		'has_editor' => isset( $settings['editor'] ) ? true : false,
	);
	$data      = wp_json_encode( $data_json );

	$input_name = $name;

	$output  = "<div class='fields-panel'>";
	$output .= "<div class='cp-droppable-item list-group-item draggable' data-field-title='" . $settings['title'] . "' data-type='cp_text' data-name='" . $input_name . "' data-value='" . $settings['value'] . "' data-json='" . $data . "' data-resize='" . $settings['resize'] . "'>
        <div class='cp-panel-content-icon'><i class='dashicons dashicons-editor-spellcheck'></i></div>
        <div class='cp-element-title-wrapper'><span class='cp-element-title'>" . $settings['title'] . "</span></div>
        <input style='display:none' type='text' class='cp-customizer-target cp-text' placeholder='" . __( 'Placeholder Text', 'convertpro' ) . "' value=''/>
    </div>";
	$output .= '</div>';

	return $output;
}
