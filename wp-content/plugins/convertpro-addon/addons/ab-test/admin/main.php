<?php
/**
 * Convert Pro Addon A/B Test Main file
 *
 * @package Convert Pro Addon
 * @author Brainstorm Force
 */

// Prohibit direct script loading.
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

$popups      = array();
$styles_data = array();

$ga_data = get_option( 'cp_ga_analytics_data' );

$ab_test_inst = CP_V2_AB_Test::get_instance();
$tests        = $ab_test_inst->get_all_tests( array( 0, 1, 2 ) );

$popups      = $ab_test_inst->get_launch_styles();
$style_count = count( (array) $popups );

?>
<div class="wrap about-wrap ab-test-cp bend">
	<div class="cp-flex-center">
		<div class="cp-button-row cp-camp-head">
			<h2 class="cp-sub-head"><?php esc_html_e( 'A/B Tests', 'convertpro-addon' ); ?> </h2>
		</div>
		<?php if ( $style_count >= 2 ) { ?>
		<div class="cp-design-btn">
			<a href="javascript:void(0);" data-styles="" class="cp-btn-primary cp-md-btn cp-button-style create-test-link" ><?php esc_html_e( 'Create New Test', 'convertpro-addon' ); ?></a>
			<input type="hidden" name="cp-delete-test-nonce" id="cp-delete-test-nonce" value="<?php echo esc_attr( wp_create_nonce( 'cp-delete-test-nonce' ) ); ?>" />
		</div><!-- Add New Button -->
		<?php } ?>
	</div>

		<div class="cp-analytics-wraper">
		<div class="cp-style-container">
			<div class="cp-accordion">
				<?php
				if ( $style_count < 2 ) {
					$create_cta_url = add_query_arg(
						array(
							'page' => CP_PRO_SLUG . '-create-new',
						),
						admin_url( 'admin.php' )
					);
					?>
					<p><?php esc_html_e( 'You need minimum 2 call-to-actions of type Modal Popup/ Info Bar/ Slide In to create A/B test. Create a new call-to-action ', 'convertpro-addon' ); ?><a href=<?php echo esc_url( $create_cta_url ); ?>><?php esc_html_e( ' here.', 'convertpro-addon' ); ?></a></p>
					<?php
				} else {
					$is_empty = ( ( ! is_array( $tests ) || empty( $tests ) ) ) ? true : false;
					?>
				<p class="no-tests <?php echo ( ! $is_empty ) ? 'cp-hidden' : ''; ?>"><?php esc_html_e( 'You have not compared any call-to-actions. You can create an A/B test ', 'convertpro-addon' ); ?><a href='javascript:void(0);' class='create-test-link'><?php esc_html_e( 'here.', 'convertpro-addon' ); ?></a></p>
				<div class="cp-accordion-section">
					<div id="cp-ab-edit-dropdown" class="cp-edit-content cp-edit-above">
						<a class="cp-edit-action update-ab-test-link" href="javascript:void(0);">
							<span class="cp-question-icon"><i class="dashicons dashicons-edit"></i></span>
							<span class="cp-question-title"><?php esc_html_e( 'Edit', 'convertpro-addon' ); ?></span>
						</a>
						<a class="cp-duplicate-action cp-stop-test-action" href="javascript:void(0);">
							<span class="cp-question-icon"><i class="dashicons dashicons-admin-page"></i></span>
							<span class="cp-question-title"><?php esc_html_e( 'Stop', 'convertpro-addon' ); ?></span>
						</a>
						<a class="cp-delete-action remove-test" href="javascript:void(0);">
							<span class="cp-question-icon"><i class="dashicons dashicons-trash"></i></span>
							<span class="cp-question-title"><?php esc_html_e( 'Delete', 'convertpro-addon' ); ?></span>
						</a>
					</div>
					<div class="cp-accordion-section-content <?php echo ( ! $is_empty ) ? 'open' : ''; ?>"><!-- Accordion labels row -->
						<div class="cp-row cp-abtest-row"><?php echo ( ( is_array( $tests ) && ! empty( $tests ) ) ) ? CPRO_ABTest_Helper::cp_get_ab_test_row_header() : '';  //PHPCS:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
						<?php
						if ( is_array( $tests ) && ! empty( $tests ) ) {
							foreach ( $tests as $test ) {
								echo CPRO_ABTest_Helper::cp_get_ab_test_row( $test, $styles_data ); //PHPCS:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							}
						}
						?>
					</div><!--end .accordion-section-content-->
				</div><!--end .accordion-section-->
					<?php
				}
				?>
				<!-- AB Test modal content-->
				<?php require_once 'abtest-modal.php'; ?>
				<div class="cp-md-overlay"></div>
			</div>
		</div><!-- Analytics container -->
	</div><!-- End Wrapper -->
</div>
<?php
if ( false !== $ga_data ) {
	require_once 'abtest-analytics.php';
}
