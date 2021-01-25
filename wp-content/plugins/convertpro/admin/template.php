<?php
/**
 * Cloud Template File.
 *
 * @package ConvertPro
 */

// Prohibit direct script loading.
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

if ( isset( $_REQUEST['cpro_admin_page_menu_nonce'] ) && ! wp_verify_nonce( $_REQUEST['cpro_admin_page_menu_nonce'], 'cpro_admin_page_menu' ) ) {
	die( 'No direct script access allowed!' );
}

$popup_type      = isset( $_GET['type'] ) ? sanitize_text_field( ( $_GET['type'] ) ) : '';
$template_loaded = get_site_option( '_cp_v2_cloud_templates' );

if ( empty( $template_loaded[ $popup_type ] ) ) {
	$result = CP_V2_Cloud_Templates::reset_cloud_transient( $popup_type );
	CP_V2_Cloud_Templates::update_popup_categories( false );
}

$templates    = CP_V2_Cloud_Templates::get_cloud_templates( $popup_type );
$templates[0] = array(
	'ID'              => 0,
	'post_title'      => __( 'Blank', 'convertpro' ),
	'screenshot_url'  => CP_V2_BASE_URL . 'assets/admin/img/blank-template.jpg',
	'download_status' => 'success',
	'popup_category'  => '',
);

ksort( $templates );
?>

<div class="wrap cp-new-popup cp-templates about-wrap bend">
	<div class="cp-flex-center cp-clearfix">
		<h2 class="cp-sub-head cp-about-header"><?php esc_html_e( 'Select a Template', 'convertpro' ); ?></h2>
		<div class="cp-template-goal">
			<select name="cp-template-sort" class="cp-template-sort">
				<?php
				$popup_categories = CP_V2_Cloud_Templates::get_popup_categories();

										$output = '';

				if ( is_array( $popup_categories ) ) {

					foreach ( $popup_categories as $key => $value ) {
						$output .= '<option value="' . $key . '">' . $value . '</option>';
					}
				}

					echo $output; // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
				?>
			</select>
		</div>
	</div>
	<div class="cp-popup-container">
		<?php
		if ( is_array( $templates ) && count( $templates ) > 0 ) {
			foreach ( $templates as $key => $template ) {
				$temp_popup_category = '';

				if ( is_array( $template['popup_category'] ) ) {
					$temp_popup_category = implode( ' ', $template['popup_category'] );
				}
				?>
		<div class="cp-col-4 cp-template-style" data-preview-url="<?php echo esc_attr( esc_url( $template['screenshot_url'] ) ); ?>" data-template-name="<?php echo esc_attr( $template['post_title'] ); ?>" data-modal-type="<?php echo esc_attr( $popup_type ); ?>" data-id="<?php echo esc_attr( $template['ID'] ); ?>"  data-popup-category="<?php echo esc_attr( $temp_popup_category ); ?>" data-loader="<?php echo esc_attr( esc_url( CP_V2_BASE_URL . 'assets/admin/img/loadingAnimation.gif' ) ); ?>">
			<div class="cp-template-screenshot">
				<?php if ( $template['ID'] > 0 ) { ?>
				<div class="cp-templated-error cp-hidden"></div>
				<?php } ?>
				<div class="cp-template-panel">
					<img alt="" src="<?php echo esc_attr( esc_url( $template['screenshot_url'] ) ); ?>" style="display: block;">
				</div>
			</div>
			<div class="cp-style-item-box">
				<?php
					$button_data = 'data-download=no';
					$button_text = __( 'Select', 'convertpro' );
					wp_nonce_field( 'cpro_download_cloud', 'cpro_download_cloud_nonce' );
				?>
				<?php
				if ( isset( $template['download_status'] ) && 'success' === $template['download_status'] ) {
					$button_data = 'data-download=yes';
				}
				?>

				<button class="cp-save-mdl cp-template-select cp-btn-primary cp-sm-btn cp-button-style" <?php echo esc_attr( $button_data ); ?>>
					<span><?php echo esc_html( $button_text ); ?></span>
				</button>
			</div>
		</div>
		<?php } ?>
			<?php
		}
		?>
	</div><!-- cp-popup-container -->
	<!-- Save modal content-->
	<div id="cp-dashboard-modal" class="cp-common-modal cp-create-template-modal cp-save-modal">
		<div class="cp-md-content cp-save-animate-container"> 
			<div class="cp-form-error">
				<label class="cp-error"></label>
			</div><!-- Error Message -->
			<div class="cp-modal-header">
				<h3 class="cp-md-modal-title"><?php esc_html_e( 'Name Your Call-to-action', 'convertpro' ); ?></h3>
			</div>
			<div class="cp-save-style-content">
				<div class="cp-dash-txt-field">
					<div class="cp-form-input">
						<input type="text" name="cp_style_title" id="cp_style_title" required value="" />
						<label class="cp-field-label"><?php echo esc_html__( 'Call-to-action Name', 'convertpro' ); ?></label>
					</div>
				</div><!-- .cp-dash-txt-field -->                

				<div class="cp-action-row cp-ab-button cp-save-btn">                    
					<span id="cp-save-settings" class="progress-btn" data-style="" data-progress-style="fill-back">
						<div class="cp-cancel-btn cp-sm-btn cp-button-style"><?php esc_html_e( 'Cancel', 'convertpro' ); ?>                        
						</div>
						<div id="cp-btn-status" class="cp-create-template-popup cp-btn-primary cp-sm-btn cp-button-style cp-btn" data-type="<?php echo esc_attr( $popup_type ); ?>">
							<span class="cp-create-popup-btn"><?php echo esc_html__( 'Create', 'convertpro' ); ?></span>
							<?php wp_nonce_field( 'cpro_create_new', 'cpro_create_new_nonce' ); ?>
						</div>
					</span>
				</div>
			</div>
		</div>  
	</div><!-- Modal -->
	<div class="cp-md-overlay"></div> <!-- CP Overlay -->
</div>
