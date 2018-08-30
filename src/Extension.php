<?php
/**
 * Extension
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2018 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Extensions\NinjaForms
 */

namespace Pronamic\WordPress\Pay\Extensions\NinjaForms;

use Pronamic\WordPress\Pay\Payments\Payment;

/**
 * Title: Ninja Forms extension
 * Description:
 * Copyright: Copyright (c) 2005 - 2018
 * Company: Pronamic
 *
 * @author Ruben Droogh
 */
class Extension {
	/**
	 * Slug
	 *
	 * @var string
	 */
	const SLUG = 'ninja-forms';

	/**
	 * Bootstrap.
	 */
	public static function bootstrap() {
		new self();
	}

	/**
	 * Construct.
	 */
	public function __construct() {
		add_action( 'pronamic_payment_status_update_' . self::SLUG, array( $this, 'update_status' ), 10, 2 );
		add_filter( 'pronamic_payment_source_text_' . self::SLUG, array( $this, 'source_text' ), 10, 2 );
		add_filter( 'pronamic_payment_source_description_' . self::SLUG, array( $this, 'source_description' ), 10, 2 );
		add_filter( 'pronamic_payment_source_url_' . self::SLUG, array( $this, 'source_url' ), 10, 2 );

		add_filter( 'ninja_forms_register_fields', array( $this, 'register_fields' ), 10, 3 );

		add_action( 'ninja_forms_register_actions', array( $this, 'register_actions' ) );
	}

	/**
	 * Register actions.
	 *
	 * @link https://github.com/wpninjas/ninja-forms/blob/v3.0.16/ninja-forms.php#L358-L361
	 * @param array $actions Actions array from Ninja Forms.
	 * @return array $actions
	 */
	public function register_actions( $actions ) {
		$actions['pronamic-pay-payment-action'] = new PaymentAction();

		return $actions;
	}

	/**
	 * Register custom fields
	 *
	 * @param array $fields Fields from Ninja Forms.
	 * @return array $fields
	 */
	public function register_fields( $fields ) {
		$fields['paymentmethods'] = new PaymentMethodsField();
		$fields['bankselect']     = new BankSelectField();

		return $fields;
	}

	/**
	 * Registered form actions.
	 *
	 * @param array $actions Formidable Forms form actions.
	 *
	 * @return array
	 */
	public function registered_form_actions( $actions ) {
		$actions['pronamic_pay'] = __NAMESPACE__ . '\PaymentAction';

		return $actions;
	}

	/**
	 * Update entry status of the specified payment.
	 *
	 * @param Payment $payment      Payment.
	 * @param bool    $can_redirect Whether or not to redirect.
	 */
	public function update_status( Payment $payment, $can_redirect = false ) {
	}

	/**
	 * Source URL.
	 *
	 * @param string  $url     Source URL.
	 * @param Payment $payment Payment.
	 *
	 * @return string
	 */
	public function source_url( $url, Payment $payment ) {
		$url = add_query_arg( array(
			'page'    => 'ninja-forms',
			'form_id' => $payment->get_source_id(),
		), admin_url( 'admin.php' ) );

		return $url;
	}

	/**
	 * Source text.
	 *
	 * @param string  $text    Source text.
	 * @param Payment $payment Payment.
	 *
	 * @return string
	 */
	public static function source_text( $text, Payment $payment ) {
		$text = __( 'Ninja Forms', 'pronamic_ideal' ) . '<br />';

		$text .= sprintf(
			'<a href="%s">%s</a>',
			add_query_arg( array(
				'page'       => 'ninja-forms',
				'frm_action' => 'show',
				'id'         => $payment->get_source_id(),
			), admin_url( 'admin.php' ) ),
			/* translators: %s: payment source id */
			sprintf( __( 'Form #%s', 'pronamic_ideal' ), $payment->get_source_id() )
		);

		return $text;
	}

	/**
	 * Source description.
	 *
	 * @return string|void
	 */
	public function source_description() {
		return __( 'Ninja Forms Form', 'pronamic_ideal' );
	}
}
