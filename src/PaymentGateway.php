<?php
/**
 * Payment gateway
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Extensions\NinjaForms
 */

namespace Pronamic\WordPress\Pay\Extensions\NinjaForms;

use NF_Abstracts_PaymentGateway;
use Pronamic\WordPress\Pay\Plugin;
use Pronamic\WordPress\Pay\Core\PaymentMethods;

/**
 * Payment gateway
 *
 * @version 1.0.3
 * @since   1.0.0
 */
final class PaymentGateway extends NF_Abstracts_PaymentGateway {
	/**
	 * Slug.
	 *
	 * @var string
	 */
	protected $_slug = 'pronamic_pay';

	/**
	 * Name.
	 *
	 * @var string
	 */
	protected $_name = '';

	/**
	 * Settings.
	 *
	 * @var array
	 */
	protected $_settings = array();

	/**
	 * Constructor for the payment gateway.
	 */
	public function __construct() {
		$this->_name = __( 'Knit Pay', 'pronamic_ideal' );

		$this->_settings = $this->action_settings();
	}

	/**
	 * Processing form.
	 *
	 * @param array  $action_settings Action settings.
	 * @param string $form_id Form id.
	 * @param array  $data Form data.
	 * @return array|bool
	 */
	public function process( $action_settings, $form_id, $data ) {
		$config_id = get_option( 'pronamic_pay_config_id' );
		if (!empty($action_settings['knit_pay_config_id'])){
		    $config_id = $action_settings['knit_pay_config_id'];
		}

		// A valid configuration ID is needed.
		if ( false === $config_id ) {
			return false;
		}

		$payment_data = new PaymentData( $action_settings, $form_id, $data );

		$payment_method = $payment_data->get_payment_method();

		$gateway = Plugin::get_gateway( $config_id );

		if ( ! $gateway ) {
			return false;
		}

		// Set default payment method if neccessary.
		if ( empty( $payment_method ) && ( null !== $payment_data->get_issuer() || $gateway->payment_method_is_required() ) ) {
			$payment_method = PaymentMethods::IDEAL;
		}

		// Only start payments for known/active payment methods.
		if ( is_string( $payment_method ) && ! PaymentMethods::is_active( $payment_method ) ) {
			return false;
		}

		try {
			$payment = Plugin::start( $config_id, $gateway, $payment_data, $payment_method );

			// Save form and action ID in payment meta for use in redirect URL.
			$payment->set_meta( 'ninjaforms_payment_action_id', $action_settings['id'] );
			$payment->set_meta( 'ninjaforms_payment_form_id', $form_id );

			$data['actions']['redirect'] = $payment->get_pay_redirect_url();
		} catch ( \Exception $e ) {
			$message = sprintf( '%1$s: %2$s', $e->getCode(), $e->getMessage() );

			$data['errors']['form']['pronamic-pay']         = Plugin::get_default_error_message();
			$data['errors']['form']['pronamic-pay-gateway'] = esc_html( $message );
		}

		return $data;
	}

	/**
	 * Action settings.
	 *
	 * @return array
	 */
	public function action_settings() {
		$settings = array();
		$payment_configs = Plugin::get_config_select_options();
		foreach ( $payment_configs as $key => $payment_config ) {
		    $payment_config_options[] = array(
		        'label' => $payment_config,
		        'value' => $key,
		    );
		}

		// Description.
		$settings['description'] = array(
			'name'           => 'pronamic_pay_description',
			'type'           => 'textbox',
			'group'          => 'primary',
			'label'          => __( 'Transaction Description', 'pronamic_ideal' ),
			'placeholder'    => '',
			'value'          => '',
			'width'          => 'full',
			'use_merge_tags' => array(
				'include' => array(
					'calcs',
				),
			),
		);

		$settings['knit_pay_config_id'] = array(
		    'name' => 'knit_pay_config_id',
		    'type'     => 'select',
		    'group'       => 'primary',
		    'label'    => __( 'Configuration', 'pronamic_ideal' ),
		    'options'  => $payment_config_options,
		    'default'  => get_option( 'pronamic_pay_config_id' ),
		);

		// User Information Fields
		$settings['knit_pay_fname'] = $this->add_user_info_action_setting('knit_pay_fname', __( 'First Name', 'knit-pay' ));
		$settings['knit_pay_lname'] = $this->add_user_info_action_setting('knit_pay_lname', __( 'Last Name', 'knit-pay' ));
		$settings['knit_pay_phone'] = $this->add_user_info_action_setting('knit_pay_phone', __( 'Phone', 'knit-pay' ));
		$settings['knit_pay_email'] = $this->add_user_info_action_setting('knit_pay_email', __( 'Email', 'knit-pay' ));
		$settings['knit_pay_address'] = $this->add_user_info_action_setting('knit_pay_address', __( 'Address', 'knit-pay' ));
		$settings['knit_pay_city'] = $this->add_user_info_action_setting('knit_pay_city', __( 'City', 'knit-pay' ));
		$settings['knit_pay_state'] = $this->add_user_info_action_setting('knit_pay_state', __( 'State', 'knit-pay' ));
		$settings['knit_pay_country'] = $this->add_user_info_action_setting('knit_pay_country', __( 'Country', 'knit-pay' ));
		$settings['knit_pay_zip'] = $this->add_user_info_action_setting('knit_pay_zip', __( 'Zip', 'knit-pay' ));

		/*
		 * Status pages.
		 */
		$options = array(
			array(
				'label' => __( '— Select —', 'pronamic_ideal' ),
			),
		);

		foreach ( \get_pages() as $page ) {
			$options[] = array(
				'label' => $page->post_title,
				'value' => $page->ID,
			);
		}

		// Add settings fields.
		foreach ( \pronamic_pay_plugin()->get_pages() as $id => $label ) {
			$settings[ $id ] = array(
				'name'        => $id,
				'type'        => 'select',
				'group'       => 'pronamic_pay_status_pages',
				'label'       => $label,
				'placeholder' => '',
				'value'       => '',
				'width'       => 'full',
				'options'     => $options,
			);
		}

		return $settings;
	}

	private function add_user_info_action_setting ($name, $label) {
	    return array(
	        'name'           => $name,
	        'type'           => 'textbox',
	        'group'          => 'knit_pay_user_info',
	        'label'          => $label,
	        'placeholder'    => '',
	        'value'          => '',
	        'width'          => 'one-half',
	        'use_merge_tags' => array(
	            'include' => array(
	                'calcs',
	            ),
	        ),
	    );
	}

}
