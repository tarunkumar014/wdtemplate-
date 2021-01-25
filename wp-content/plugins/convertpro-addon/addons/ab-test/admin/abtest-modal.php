<?php
/**
 * A/B Test Modal Popup.
 *
 * @package Convert Pro Addon
 */

// Prohibit direct script loading.
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

?>

<div id="cp-dashboard-modal" class="cp-common-modal cp-abtest-modal">
	<div class="cp-form-error cp-notification-message">
		<label class="cp-error"></label>
	</div>
	<div class="cp-md-content cp-save-animate-container">
		<div class="cp-close-wrap"><i class="dashicons dashicons-no-alt"></i></div>
		<div class="cp-modal-header">
			<?php
			if ( $style_count < 2 ) {
				?>
			<h3 class="cp-md-modal-title cp-empty-set <?php ( $style_count < 2 ) ? 'cp-hidden' : ''; ?>">
				<?php esc_html_e( 'Minimum 2 Call-to-actions Required', 'convertpro-addon' ); ?>
			</h3>
				<?php
			} else {
				?>
			<h3 class="cp-md-modal-title"><?php esc_html_e( 'Create New Test', 'convertpro-addon' ); ?></h3>
				<?php
			}
			?>
		</div>
		<?php
		if ( $style_count < 2 ) {
			/* translators: %s link */
			$new_cta_url = add_query_arg(
				array(
					'page' => CP_PRO_SLUG . '-create-new',
				),
				admin_url( 'admin.php' )
			);
			?>
			<p><?php esc_html_e( 'You need minimum 2 call-to-actions to create A/B test. Create a new call-to-action', 'convertpro-addon' ); ?><a href=<?php echo esc_url( $new_cta_url ); ?>><?php esc_html_e( ' here.', 'convertpro-addon' ); ?></a></p>
			<?php
		} else {
			?>
		<div class="cp-dashboard-modal-content">
			<form id="cp-create-ab-test" method="post">
				<div class="cp-dash-txt-field">
					<div class="cp-form-input">
						<p class="cp-abtest-title"><?php esc_html_e( 'A/B Test Title', 'convertpro-addon' ); ?></p>
						<input type="text" value="" required="required" name="test_title" id="test_title" oninvalid="this.setCustomValidity('Please name the test.');"  oninput="this.setCustomValidity('');" placeholder="<?php esc_attr_e( 'Title', 'convertpro-addon' ); ?>" >
					</div>
				</div>
				<div class="cp-abtest-wrap">
					<p class="cp-abtest-title"><?php esc_html_e( 'Choose the call-to-actions you wish to compare', 'convertpro-addon' ); ?></p>
					<div class="cp-style-list">
						<!-- styles list goes here -->
						<select name="cp_styles" class="select2-ex-dropdown" multiple="multiple"></select>
					</div>
				</div>
				<div class="cp-abtest-wrap cp-abtest-parent-wrap">
					<p class="cp-abtest-title"><?php esc_html_e( 'Choose a parent call-to-action to inherit configuration settings', 'convertpro-addon' ); ?> <i class="dashicons dashicons-editor-help cp-abtest-tooltip"> <span class="cp-abtest-tooltip-text"><?php esc_html_e( 'The other call-to-actions will inherit the configuration settings of the parent call-to-action selected here.', 'convertpro-addon' ); ?></span></i></p>

					<div class="cp-parent-style">
						<!-- styles list goes here -->
						<select name="cp_parent_style" class="cp-parent-style"><option value="-1"><?php esc_html_e( '--Select--', 'convertpro-addon' ); ?></option></select>
					</div>
				</div>
				<div class="cp-abtest-wrap">
					<p class="cp-abtest-title"><?php esc_html_e( 'Select a time period for the test', 'convertpro-addon' ); ?></p>
					<div class="cp-flex-center">
						<div class="cp-datepicker-group"> 
							<div class="form-group cp-datetime-picker">
								<div class="cp-dash-txt-field">
									<div class="input-group date cp-form-input">
										<input required="required" type="text" name="test_sdate" class="form-control" id="cp-test-sdate" value="" placeholder="<?php esc_attr_e( 'Start Date', 'convertpro-addon' ); ?>" />
										<label class="cp-datepicker-label"><?php esc_html_e( 'Start Date', 'convertpro-addon' ); ?></label> 
									</div>
								</div>
							</div>
						</div>
						<div class="cp-datepicker-group">
							<div class="form-group cp-datetime-picker">
								<div class="cp-dash-txt-field">
									<div class="input-group date cp-form-input">
										<input type="text" required="required" name="test_edate" class="form-control" id="cp-test-edate" value="" placeholder="<?php esc_attr_e( 'End Date', 'convertpro-addon' ); ?>" />
										<label class="cp-datepicker-label"><?php esc_html_e( 'End Date', 'convertpro-addon' ); ?></label>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="cp-dash-txt-field">
					<input type="checkbox" id="cp_winner_check" name="cp_winner_check">
					<label for="cp_winner_check"><?php esc_html_e( 'Automatically publish the winner call-to-action after test period', 'convertpro-addon' ); ?></label>
					<p class="cp_winner_note"><?php esc_html_e( 'Note: The other call-to-actions will be paused', 'convertpro-addon' ); ?></p>
				</div>

				<input type="hidden" name="cp-save-ab-test-nonce" id="cp-save-ab-test-nonce" value="<?php echo esc_attr( wp_create_nonce( 'cp-save-ab-test-nonce' ) ); ?>" />

				<div class="cp-action-row cp-ab-button">
					<div class="cp-cancel-btn cp-sm-btn cp-button-style" href="#"><?php esc_html_e( 'Cancel', 'convertpro-addon' ); ?></div>
					<button class="cp-next-ab cp-btn-primary cp-sm-btn cp-button-style save-ab-test" type="submit" ><?php esc_html_e( 'Create Test', 'convertpro-addon' ); ?></button>
				</div>
			</form>
		</div><!-- End Wrapper -->
			<?php
		}
		?>
	</div>
</div><!-- end of modal -->
