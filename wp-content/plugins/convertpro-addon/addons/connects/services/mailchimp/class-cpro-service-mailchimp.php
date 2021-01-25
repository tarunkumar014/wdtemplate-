<?php
/**
 * Collects leads and subscribe to MailChimp
 *
 * @package Convert Pro Addon
 * @author Brainstorm Force
 */

/**
 * Helper class for the MailChimp API.
 *
 * @since 1.0.0
 */
final class CPRO_Service_MailChimp extends CPRO_Service {

	/**
	 * The ID for this service.
	 *
	 * @since 1.0.0
	 * @var string $id
	 */
	public $id = 'mailchimp';

	/**
	 * Default Custom field array.
	 * This is predefined custom fields array that Mailchimp
	 * has already defined. When Mailchimp releases the new
	 * set of fields, we need to update this array.
	 *
	 * @since 1.0.0
	 * @var string $id
	 */
	public static $mapping_fields = array( 'FNAME', 'LNAME' );

	/**
	 * Store API instance
	 *
	 * @since 1.0.0
	 * @var object $api_instance
	 * @access private
	 */
	private $api_instance = null;

	/**
	 * Get an instance of the API.
	 *
	 * @since 1.0.0
	 * @param string $api_key A valid API key.
	 * @return object The API instance.
	 */
	public function get_api( $api_key ) {

		if ( $this->api_instance ) {
			return $this->api_instance;
		}

		if ( file_exists( CP_SERVICES_BASE_DIR . 'includes/vendor/mailchimp/cp-v2-mailchimp.php' ) ) {
			require_once CP_SERVICES_BASE_DIR . 'includes/vendor/mailchimp/cp-v2-mailchimp.php';
		}

		if ( class_exists( 'CPRO_Mailchimp' ) ) {
			$this->api_instance = new CPRO_Mailchimp( $api_key );
		}

		return $this->api_instance;
	}
	/**
	 * Test the API connection.
	 *
	 * @since 1.0.0
	 * @param array $fields A valid API key.
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

		// Make sure we have an API key.
		if ( ! isset( $fields['api_key'] ) || empty( $fields['api_key'] ) ) {
			$response['error'] = __( 'Error: You must provide an API key.', 'convertpro-addon' );
		} else {
			// Try to connect and store the connection data.
			$api = $this->get_api( $fields['api_key'] );

			try {
				$connected = $api->connect();

				if ( false !== $connected['error'] ) {
					$response['error'] = $connected['error'];
				}

				$response['data'] = array(
					'api_key' => $fields['api_key'],
				);
			} catch ( Exception $e ) {
				$response['error'] = $e->getMessage();
			}
		}
		return $response;
	}

	/**
	 * Renders the markup for the connection settings.
	 *
	 * @since 1.0.0
	 * @return string The connection settings markup.
	 */
	public function render_connect_settings() {
		ob_start();

		ConvertPlugHelper::render_input_html(
			'api_key',
			array(
				'class' => '',
				'type'  => 'text',
				'label' => __( 'API Key', 'convertpro-addon' ),
				'help'  => __( 'Your API key can be found in your MailChimp account under Account > Extras > API Keys.', 'convertpro-addon' ),
			)
		);
		return ob_get_clean();
	}

	/**
	 * Returns the api_key in array format
	 *
	 * @since 1.0.0
	 * @param string $auth_meta $api_key A valid API key.
	 * @return array Array of api_key
	 */
	public function render_auth_meta( $auth_meta ) {
		return array(
			'api_key' => $auth_meta['api_key'],
		);
	}

	/**
	 * Render the markup for service specific fields.
	 *
	 * @since 1.0.0
	 * @param string $account The name of the saved account.
	 * @param object $post_data Posted data.
	 * @return array {
	 *      @type bool|string $error The error message or false if no error.
	 *      @type string $html The field markup.
	 *      @type array $mapping_fields The field mapping array for mailchimp.
	 * }
	 */
	public function render_fields( $account, $post_data ) {
		$account_data = ConvertPlugServices::get_account_data( $account );

		$api                 = $this->get_api( $account_data['api_key'] );
		$response            = array(
			'error'          => false,
			'html'           => '',
			'mapping_fields' => self::$mapping_fields,
		);
		$post_data['isEdit'] = ( isset( $post_data['isEdit'] ) ) ? $post_data['isEdit'] : null;
		// Lists field.
		try {
			$resp  = $api->getList();
			$lists = $resp['lists'];
			if ( 'false' === $post_data['isEdit'] || null === $post_data['isEdit'] ) {

				if ( ! isset( $post_data['list_id'] ) ) {
					$response['html'] .= $this->render_list_field( $lists, $post_data );
				} else {
					$resp = $api->getGroups( $post_data['list_id'] );
					if ( false === $resp['error'] ) {
						$groups            = ( isset( $resp['groups'] ) ) ? $resp['groups'] : array();
						$response['html'] .= $this->render_groups_field( $groups, $post_data );
					}

					$resp = $api->getSegments( $post_data['list_id'] );
					if ( false === $resp['error'] ) {
						$segments          = ( isset( $resp['segments'] ) ) ? $resp['segments'] : array();
						$response['html'] .= $this->render_segments_field( $segments, $post_data );
					}
				}
			} else {
				$response['html'] .= $this->render_list_field( $lists, $post_data );

				if ( isset( $post_data['default']['mailchimp_list'] ) ) {
					$resp              = $api->getGroups( $post_data['default']['mailchimp_list'] );
					$groups            = ( isset( $resp['groups'] ) ) ? $resp['groups'] : array();
					$response['html'] .= $this->render_groups_field( $groups, $post_data );

					$resp              = $api->getSegments( $post_data['default']['mailchimp_list'] );
					$segments          = ( isset( $resp['segments'] ) ) ? $resp['segments'] : array();
					$response['html'] .= $this->render_segments_field( $segments, $post_data );
				}
			}

			$response['html'] .= $this->render_optin_field( $post_data );

		} catch ( Exception $e ) {
			$response['error'] = $e->getMessage();
		}
		return $response;
	}

	/**
	 * Render markup for the list field.
	 *
	 * @since 1.0.0
	 * @param array $lists List data from the API.
	 * @param array $settings Posted data.
	 * @return string The markup for the list field.
	 * @access private
	 */
	private function render_list_field( $lists, $settings ) {
		$options = array(
			'-1' => __( 'Choose...', 'convertpro-addon' ),
		);
		$default = '';

		foreach ( $lists as $list ) {
			$options[ $list->id ] = $list->name;
		}

		if ( isset( $settings['isEdit'] ) && $settings['isEdit'] ) {
			$default = ( isset( $settings['default'] ) && isset( $settings['default']['mailchimp_list'] ) ) ? $settings['default']['mailchimp_list'] : '';
		}

		ob_start();

		ConvertPlugHelper::render_input_html(
			'mailchimp_list',
			array(
				'class'   => '',
				'type'    => 'select',
				'label'   => __( 'Select a List', 'convertpro-addon' ),
				'help'    => '',
				'default' => $default,
				'options' => $options,
			)
		);
		return ob_get_clean();

	}

	/**
	 * Render markup for the list field.
	 *
	 * @since 1.0.0
	 * @param array $settings Posted data.
	 * @return string The markup for the list field.
	 * @access private
	 */
	private function render_optin_field( $settings ) {

		$default = '';
		if ( isset( $settings['isEdit'] ) && $settings['isEdit'] ) {
			$default = ( isset( $settings['default'] ) && isset( $settings['default']['mailchimp_double_optin'] ) ) ? $settings['default']['mailchimp_double_optin'] : '';
		}

		ob_start();

		ConvertPlugHelper::render_input_html(
			'mailchimp_double_optin',
			array(
				'class'   => '',
				'type'    => 'checkbox',
				'label'   => __( 'Enable Double Opt-in', 'convertpro-addon' ),
				'help'    => '',
				'default' => $default,
			)
		);

		return ob_get_clean();
	}

	/**
	 * Render markup for the groups field.
	 *
	 * @since 1.0.0
	 * @param array  $groups An array of group data.
	 * @param object $settings Saved module settings.
	 * @return string The markup for the group field.
	 * @access private
	 */
	private function render_groups_field( $groups, $settings ) {
		if ( ! is_array( $groups ) || 0 === count( $groups ) ) {
			return;
		}

		$options = array(
			'-1' => __( 'Choose...', 'convertpro-addon' ),
		);
		$default = '';

		foreach ( $groups as $key => $group ) {
			$options[ $key ] = $group;
		}

		if ( isset( $settings['isEdit'] ) && $settings['isEdit'] ) {
			$default = ( isset( $settings['default'] ) && isset( $settings['default']['mailchimp_groups'] ) ) ? $settings['default']['mailchimp_groups'] : '';
		}

		ob_start();

		ConvertPlugHelper::render_input_html(
			'mailchimp_groups',
			array(
				'class'   => '',
				'type'    => 'multi-select',
				'label'   => __( 'Select a Group', 'convertpro-addon' ),
				'help'    => '',
				'default' => $default,
				'options' => $options,
			)
		);

		return ob_get_clean();
	}

	/**
	 * Render markup for the groups field.
	 *
	 * @since 1.0.0
	 * @param array  $segments An array of segment data.
	 * @param object $settings Saved module settings.
	 * @return string The markup for the group field.
	 * @access private
	 */
	private function render_segments_field( $segments, $settings ) {
		if ( ! is_array( $segments ) || 0 === count( $segments ) ) {
			return;
		}

		$options = array(
			'-1' => __( 'Choose...', 'convertpro-addon' ),
		);
		$default = '';

		foreach ( $segments as $segment ) {
			// Condition for getting the Tags only.
			if ( 'static' === $segment->type ) {
				$options[ $segment->name ] = $segment->name;
			}
		}

		if ( isset( $settings['isEdit'] ) && $settings['isEdit'] ) {
			$default = ( isset( $settings['default'] ) && isset( $settings['default']['mailchimp_segments'] ) ) ? $settings['default']['mailchimp_segments'] : '';
		}

		ob_start();

		ConvertPlugHelper::render_input_html(
			'mailchimp_segments',
			array(
				'class'   => '',
				'type'    => 'multi-select',
				'label'   => __( 'Select a Tags', 'convertpro-addon' ),
				'help'    => '',
				'default' => $default,
				'options' => $options,
			)
		);
		return ob_get_clean();
	}

	/**
	 * Mapping fields.
	 *
	 * @since 1.0.0
	 */
	public function render_mapping() {
		return self::$mapping_fields;
	}

	/**
	 * Subscribe an email address to MailChimp.
	 *
	 * @since 1.0.0
	 * @param object $settings A module settings object.
	 * @param string $email The email to subscribe.
	 * @return array {
	 *      @type bool|string $error The error message or false if no error.
	 * }
	 */
	public function subscribe( $settings, $email ) {

		$account_data = ConvertPlugServices::get_account_data( $settings['api_connection'] );

		$response = array(
			'error' => false,
		);

		if ( ! $account_data ) {
			$response['error'] = __( 'There was an error subscribing to MailChimp! The account is no longer connected.', 'convertpro-addon' );
		} else {
			$api = $this->get_api( $account_data['api_key'] );

			$data = array();

			$data['merge_fields'] = array();

			$fields        = array();
			$custom_fields = array();
			$cust_fields   = array();

			foreach ( $settings['param'] as $key => $p ) {

				if ( 'email' !== $key && 'date' !== $key && isset( $settings['meta'][ $key ] ) ) {
					if ( 'custom_field' !== $settings['meta'][ $key ] ) {

						$fields[ $settings['meta'][ $key ] ] = $p;

					} else {

						$fields[ $settings['meta'][ $key . '-input' ] ] = $p;
						$custom_fields                                  = array(
							'name'  => $settings['meta'][ $key . '-input' ],
							'value' => $p,
						);
						array_push( $cust_fields, $custom_fields );
					}
				}
			}

			// Map fields and custom fields.
			$default_fields = self::$mapping_fields;
			foreach ( $default_fields as $val ) {

				if ( isset( $fields[ $val ] ) ) {

					$data['merge_fields'][ $val ] = $fields[ $val ];
				}
			}

			if ( ! empty( $cust_fields ) ) {

				foreach ( $cust_fields as $key => $field_val ) {

					$data['merge_fields'][ $field_val['name'] ] = $field_val['value'];
				}
			}

			$data['email_address'] = $email;
			$data['status']        = ( ! isset( $settings['mailchimp_double_optin'] ) ) ? 'subscribed' : 'pending';

			if ( isset( $settings['mailchimp_groups'] ) && ! empty( $settings['mailchimp_groups'] ) ) {
				$group_arr = array();
				foreach ( $settings['mailchimp_groups'] as $g ) {
					$group_arr[ $g ] = true;
				}
				$data['interests'] = $group_arr;
			}

			if ( isset( $settings['mailchimp_segments'] ) && -1 !== $settings['mailchimp_segments'] ) {
				$tag_arr = array();
				$i       = 0;
				foreach ( $settings['mailchimp_segments'] as $g ) {
					$tag_arr[ $i ] = $g;
					$i++;
				}
				$data['segments'] = $tag_arr;
			}

			// Subscribe.
			try {
				// Already subscribed user for double opt-in case.
				$check_already_subscribe_user = false;

				if ( 'pending' === $data['status'] ) {
					$check_already_subscribe_user = $this->cpro_mc_check_already_subscribe_user( $account_data['api_key'], $settings['mailchimp_list'], $email, $data );
				}

				// Check the status for the user, if already subscribed (in double opt-in case ) then just update the user data.
				if ( $check_already_subscribe_user ) {
					$data['status'] = 'subscribed';
					$response       = $api->subscribe( $settings['mailchimp_list'], $email, $data );
				} else {
					$response = $api->subscribe( $settings['mailchimp_list'], $email, $data );
				}
			} catch ( Exception $e ) {
				$response['error'] = sprintf(
					/* translators: %s Error Message */
					__( 'There was an error subscribing to MailChimp! %s', 'convertpro-addon' ),
					$e->getMessage()
				);
			}
		}

		return $response;
	}

	/**
	 * In Double-opt-in case check already subscribe user.
	 *
	 * @since 1.3.6
	 * @param string $api_key MailChimp API key.
	 * @param string $list_id MailChimp List ID.
	 * @param string $email user Email ID.
	 * @param array  $data user data.
	 * @return bool.
	 */
	public function cpro_mc_check_already_subscribe_user( $api_key, $list_id, $email, $data ) {

		$opts = array(
			'headers' => array(
				'Content-Type'  => 'application/json',
				'Authorization' => 'apikey ' . $api_key,
			),
			'body'    => $data,
		);

		$dash_position = strpos( $api_key, '-' );

		$req_url = 'https://' . substr( $api_key, $dash_position + 1 ) . '.api.mailchimp.com/3.0/lists/' . $list_id . '/members/' . md5( $email );

		$result       = wp_remote_get( $req_url, $opts );
		$response_arr = json_decode( $result['body'] );

		// Check the status for the user, if already subscribed then update the user data.
		if ( isset( $response_arr->status ) && 'subscribed' === $response_arr->status ) {
			$data['status'] = 'subscribed';
			return true;
		} else {
			return false;
		}
	}
}
