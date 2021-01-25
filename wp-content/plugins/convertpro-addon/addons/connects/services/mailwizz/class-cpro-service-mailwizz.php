<?php
/**
 * ConverPlug Service MailWizz
 *
 * @package Convert Pro Addon
 * @author Brainstorm Force
 */

/**
 * Helper class for the MailWizz API.
 *
 * @since 1.0.3
 */
final class CPRO_Service_MailWizz extends CPRO_Service {

	/**
	 * Initialize Constructor
	 *
	 * @since 1.0.3
	 */
	public function __construct() {
		if ( file_exists( CP_SERVICES_BASE_DIR . 'includes/vendor/MailWizzApi/Autoloader.php' ) ) {
			require_once CP_SERVICES_BASE_DIR . 'includes/vendor/MailWizzApi/Autoloader.php';
		}

		$this->components = array(
			'cache' => array(
				'class'     => 'MailWizzApi_Cache_File',
				'filesPath' => CP_SERVICES_BASE_DIR . 'includes\vendor\MailWizzApi\Cache\data\cache',
			),
		);
	}

	/**
	 * The ID for this service.
	 *
	 * @since 1.0.3
	 * @var string $id
	 */
	public $id = 'mailwizz';

	/**
	 * Default Custom field array.
	 *
	 * @since 1.0.3
	 * @var string $id
	 */
	public static $mapping_fields = array( 'FNAME', 'LNAME' );

	/**
	 * API URL.
	 *
	 * @since 1.0.3
	 * @var object $api_url
	 * @access private
	 */
	private $api_url = '';

	/**
	 * Components.
	 *
	 * @since 1.0.3
	 * @var object $components
	 * @access private
	 */
	private $components = array();

	/**
	 * Object.
	 *
	 * @since 1.0.3
	 * @var object $api_instance
	 * @access private
	 */
	private $api_instance = null;


	/**
	 * Get an instance of the API.
	 *
	 * @since 1.0.3
	 * @param string $credentials All credentials.
	 * @return object The API instance.
	 */
	public function get_api( $credentials ) {
		if ( $this->api_instance ) {
			return $this->api_instance;
		}
		if ( class_exists( 'CPRO_MailWizzApi_Autoloader' ) ) {
			$this->api_url = trailingslashit( $credentials['api_url'] );
			CPRO_MailWizzApi_Autoloader::register();
			if ( class_exists( 'MailWizzApi_Config' ) ) {
				$this->api_instance = new MailWizzApi_Config(
					array(
						'apiUrl'     => $this->api_url,
						'publicKey'  => $credentials['pub_key'],
						'privateKey' => $credentials['priv_key'],
						'components' => $this->components,
					)
				);
			}
		}
		return $this->api_instance;
	}

	/**
	 * Test the API connection.
	 *
	 * @since 1.0.3
	 * @param array $fields {.
	 *      @type string $pub_key A valid public Key.
	 *      @type string $priv_key A valid Private Key.
	 * }
	 * @return array{
	 *      @type bool|string $error The error message or false if no error.
	 *      @type array $data An array of data used to make the connection.
	 * }
	 */
	public function connect( $fields = array() ) {

		$response = array(
			'error' => false,
			'data'  => array(),
		);

		// Make sure we have an API URL.
		if ( ! isset( $fields['api_url'] ) || empty( $fields['api_url'] ) ) {
			$response['error'] = __( 'Error: You must provide an Base URL.', 'convertpro-addon' );
		} elseif ( ! isset( $fields['pub_key'] ) || empty( $fields['pub_key'] ) ) {
			// Make sure we have an Public Key.
			$response['error'] = __( 'Error: You must provide a Public Key.', 'convertpro-addon' );
		} elseif ( ! isset( $fields['priv_key'] ) || empty( $fields['priv_key'] ) ) {
			// Make sure we have an Private Key.
			$response['error'] = __( 'Error: You must provide a Private Key.', 'convertpro-addon' );
		} else {
			// Try to connect and store the connection data.
			try {
				$config = $this->get_api( $fields );

				MailWizzApi_Base::setConfig( $config );
				$endpoint = new MailWizzApi_Endpoint_Lists();
				$res      = $endpoint->getLists( 1, 1000 );
				$status   = $res->body->itemAt( 'status' );

				if ( 'success' !== $status ) {
					$response['error'] = __( 'Error: Please check your Public Key and Private Key.', 'convertpro-addon' );
				}
			} catch ( Exception $ex ) {
				$response['error'] = $ex->getMessage();
			}
		}
		return $response;
	}

	/**
	 * Renders the markup for the connection settings.
	 *
	 * @since 1.0.3
	 * @return string The connection settings markup.
	 */
	public function render_connect_settings() {
		ob_start();

		ConvertPlugHelper::render_input_html(
			'api_url',
			array(
				'class' => 'cp_mailwizz_api_url',
				'type'  => 'text',
				'label' => __( 'Base URL', 'convertpro-addon' ),
				'help'  => __( 'Your Base URL to your MailWizz account.', 'convertpro-addon' ),
			)
		);

		ConvertPlugHelper::render_input_html(
			'pub_key',
			array(
				'class' => 'cp_mailwizz_pub_key',
				'type'  => 'text',
				'label' => __( 'Public Key', 'convertpro-addon' ),
				'help'  => __( 'Your Public Key can be found in your MailWizz account under Dashboard > API Keys.', 'convertpro-addon' ),
			)
		);

		ConvertPlugHelper::render_input_html(
			'priv_key',
			array(
				'class' => 'cp_mailwizz_priv_key',
				'type'  => 'text',
				'label' => __( 'Secret Key', 'convertpro-addon' ),
				'help'  => __( 'Your Private key can be found in your MailWizz account under Dashboard > API Keys.', 'convertpro-addon' ),
			)
		);

		return ob_get_clean();
	}

	/**
	 * Renders the markup for the connection settings.
	 *
	 * @param object $authmeta Authentication meta.
	 * @since 1.0.3
	 * @return string The connection settings markup.
	 */
	public function render_auth_meta( $authmeta ) {
		return array(
			'api_url'  => $authmeta['api_url'],
			'pub_key'  => $authmeta['pub_key'],
			'priv_key' => $authmeta['priv_key'],
		);
	}

	/**
	 * Render the markup for service specific fields.
	 *
	 * @since 1.0.3
	 * @param string $account The name of the saved account.
	 * @param object $settings Saved module settings.
	 * @return array {
	 *      @type bool|string $error The error message or false if no error.
	 *      @type string $html The field markup.
	 * }
	 */
	public function render_fields( $account, $settings ) {

		$account_data = ConvertPlugServices::get_account_data( $account );
		$api          = $this->get_api( $account_data );
		$response     = array(
			'error'          => false,
			'html'           => '',
			'mapping_fields' => self::$mapping_fields,
		);

		MailWizzApi_Base::setConfig( $api );

		$endpoint = new MailWizzApi_Endpoint_Lists();
		$res      = $endpoint->getLists( 1, 1000 );
		$status   = $res->body->itemAt( 'status' );

		if ( 'success' !== $status ) {
			return array();
		}

		$campaigns = $res->body->itemAt( 'data' );
		if ( $campaigns['count'] > 0 ) {
			$lists = array();
			foreach ( $campaigns['records'] as $cm ) {
				$lists[ $cm['general']['list_uid'] ] = $cm['general']['name'];
			}
		}

		if ( ! empty( $lists ) ) {
			$response['html'] .= $this->render_list_field( $lists, $settings );
		} else {
			$response['error'] .= __( 'Error: No lists found in your MailWizz account.', 'convertpro-addon' );
		}
		return $response;
	}

	/**
	 * Render markup for the list field.
	 *
	 * @since 1.0.3
	 * @param array $lists List data from the API.
	 * @param array $settings Settings data from the API.
	 * @return string The markup for the list field.
	 */
	private function render_list_field( $lists, $settings ) {

		$default = '';
		if ( isset( $settings['isEdit'] ) && $settings['isEdit'] && isset( $settings['default'] ) && isset( $settings['default']['mailwizz_lists'] ) ) {
				$default = $settings['default']['mailwizz_lists'];
		}
		ob_start();

		$options = array(
			'-1' => __( 'Choose...', 'convertpro-addon' ),
		);
		foreach ( $lists as $id => $value ) {
			$options[ $id ] = $value;
		}
		ConvertPlugHelper::render_input_html(
			'mailwizz_lists',
			array(
				'class'   => 'cpro-select',
				'type'    => 'select',
				'label'   => _x( 'List', 'A list from your MailWizz Account.', 'convertpro-addon' ),
				'default' => $default,
				'options' => $options,
			),
			$settings
		);

		return ob_get_clean();
	}

	/**
	 * Mapping fields.
	 *
	 * @since 1.0.3
	 */
	public function render_mapping() {
		return self::$mapping_fields;
	}


	/**
	 * Subscribe an email address to MailWizz.
	 *
	 * @since 1.0.3
	 * @param object $settings A module settings object.
	 * @param string $email The email to subscribe.
	 * @return array {
	 *      @type bool|string $error The error message or false if no error.
	 * }
	 */
	public function subscribe( $settings, $email ) {

		$account  = ConvertPlugServices::get_account_data( $settings['api_connection'] );
		$api      = $this->get_api( $account );
		$response = array(
			'error' => false,
		);

		if ( ! $api ) {
			$response['error'] = __( 'There was an error subscribing to MailWizz! The account is no longer connected.', 'convertpro-addon' );
		} else {
			$custom_fields = array(
				'EMAIL' => $email,
			);

			foreach ( $settings['param'] as $key => $p ) {
				if ( 'email' !== $key && 'date' !== $key ) {
					if ( 'custom_field' === $settings['meta'][ $key ] ) {
						$custom_fields[ $settings['meta'][ $key . '-input' ] ] = $p;
					} else {
						$custom_fields[ $settings['meta'][ $key ] ] = $p;
					}
				}
			}

			MailWizzApi_Base::setConfig( $api );
			// Add subscribers.
			$endpoint = new MailWizzApi_Endpoint_ListSubscribers();
			$resp     = $endpoint->createUpdate( $settings['mailwizz_lists'], $custom_fields );
			$status   = $resp->body->itemAt( 'status' );
			if ( 'success' !== $status ) {
				$response['error'] = __( 'Something went wrong! Please try again.', 'convertpro-addon' );
			}
		}
		return $response;
	}
}
