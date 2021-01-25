<?php
/**
 * Fields.
 *
 * @package ConvertPro
 */

// Add new input type "textfield".
if ( function_exists( 'cp_add_input_type' ) ) {
	cp_add_input_type( 'cp_sub_heading', 'cp_sub_heading_settings_field' );
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
function cp_sub_heading_settings_field( $name, $settings, $sections, $value, $default_value ) {
	$data_json = array(
		'id'         => $name,
		'title'      => $settings['title'],
		'sections'   => $sections,
		'resize'     => $settings['resize'],
		'has_editor' => isset( $settings['editor'] ) ? true : false,
	);

		$tags = isset( $settings['tags'] ) ? $settings['tags'] : false;

		$data = wp_json_encode( $data_json );

		$input_name = $name;

		$output = "<div class='fields-panel'>
    <div class='cp-droppable-item list-group-item draggable' data-type='cp_sub_heading' data-title='" . $settings['title'] . "' data-value='" . $settings['value'] . "' data-json='" . $data . "' data-resize='" . $settings['resize'] . "'>
    	<div class='cp-element-title-wrapper'>
        <div class='cp-panel-content-icon'><h2> S </h2></div>
    	<span class='cp-element-title'>" . __( 'Subheading', 'convertpro' ) . '</span>
        </div>
    </div>';

	if ( isset( $settings['presets'] ) ) {
		$output .= cp_render_presets( 'cp_heading', $settings['title'], $settings['presets'], $tags, $settings['resize'] );
	}

						$output .= '</div>';

				return $output;
}
