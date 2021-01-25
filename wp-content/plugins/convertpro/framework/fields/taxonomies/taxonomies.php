<?php
/**
 * Fields.
 *
 * @package ConvertPro
 */

// Add new input type "taxonomies".
if ( function_exists( 'cp_add_input_type' ) ) {
	cp_add_input_type( 'taxonomies', 'cp_v2_taxonomies_settings_field' );
}

/**
 * Function Name: cp_v2_taxonomies_settings_field.
 * Function Description: Function to handle new input type.
 *
 * @param string $name string parameter.
 * @param string $settings string parameter.
 * @param string $value string parameter.
 */
function cp_v2_taxonomies_settings_field( $name, $settings, $value ) {
	$input_name = $name;
	$type       = isset( $settings['type'] ) ? $settings['type'] : '';
	$class      = isset( $settings['class'] ) ? $settings['class'] : '';
	ob_start();
	?>
<select name="<?php echo esc_attr( $input_name ); ?>" id="cp_<?php echo esc_attr( $input_name ); ?>" class="select2-taxonomies-dropdown form-control cp-input <?php echo esc_attr( 'cp-' . $type . ' ' . $input_name . ' ' . $type . ' ' . $class ); ?>" multiple="multiple" style="width:260px;"> 
	<?php
	$args = array(
		'public'   => true,
		'_builtin' => false,
	);

	// names or objects, note names is the default.
	$output   = 'objects';
	$operator = 'and';

	$taxonomies = get_taxonomies( $args, $output, $operator );

	foreach ( $taxonomies as $taxonomy ) {
		?>
		<optgroup label="<?php echo esc_attr( ucwords( $taxonomy->label ) ); ?>">
		<?php
		$terms = get_terms(
			$taxonomy->name,
			array(
				'orderby'    => 'count',
				'hide_empty' => 0,
			)
		);

		foreach ( $terms as $term ) {
			?>
			<?php
			$val_arr  = explode( ',', $value );
			$selected = ( in_array( $term->term_id, $val_arr, true ) ) ? 1 : '';
			?>
	<option <?php selected( $selected, 1 ); ?> value="<?php echo esc_attr( $term->term_id ); ?>"><?php echo esc_html( $term->name ); ?></option>
			<?php
		}
	}
	?>
	</optgroup>
</select>
<script type="text/javascript">
	jQuery('select.select2-taxonomies-dropdown').cpselect2({
		placeholder: "<?php esc_html_e( 'Select posts from - taxonomies', 'convertpro' ); ?>",
	});
</script>
	<?php
	return ob_get_clean();
}
