<?php
/**
 * Payment data
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Extensions\NinjaForms
 */

namespace Pronamic\WordPress\Pay\Extensions\NinjaForms;

use Pronamic\WordPress\Pay\Payments\PaymentData as Pay_PaymentData;
use Pronamic\WordPress\Pay\Payments\Item;
use Pronamic\WordPress\Pay\Payments\Items;
use Pronamic\WordPress\Pay\Subscriptions\Subscription;


/**
 * Payment data
 *
 * @version 1.0.1
 * @since   1.0.0
 */
class PaymentData extends Pay_PaymentData {

	/**
	 * Form ID
	 *
	 * @var string
	 */
	private $form_id;

	/**
	 * Form data.
	 *
	 * @var array
	 */
	private $form_data;

	/**
	 * Action settings.
	 *
	 * @var array
	 */
	private $action_settings;

	/**
	 * Constructs and initializes a Ninja Forms payment data object.
	 *
	 * @param array  $action_settings Action settings.
	 * @param string $form_id         Form id.
	 * @param array  $data            Form data.
	 */
	public function __construct( $action_settings, $form_id, $data ) {
		parent::__construct();

		$this->action_settings = $action_settings;
		$this->form_id         = $form_id;
		$this->form_data       = $data;
	}

	/**
	 * Get items.
	 *
	 * @see Pronamic_Pay_PaymentDataInterface::get_items()
	 * @return Items
	 */
	public function get_items() {
		$items = new Items();

		// Item.
		// We only add one total item, because iDEAL cant work with negative price items (discount).
		$item = new Item();
		$item->set_number( $this->get_order_id() );
		$item->set_description( $this->get_description() );
		$item->set_price( $this->action_settings['payment_total'] );
		$item->set_quantity( 1 );

		$items->add_item( $item );

		return $items;
	}

	/**
	 * Get payment method.
	 *
	 * @return string|null
	 */
	public function get_payment_method() {
		$payment_method = null;

		// Get payment method from a payment method field if it exists.
		foreach ( $this->form_data['fields'] as $field ) {
			if ( 'pronamic_pay_payment_method' !== $field['type'] ) {
				continue;
			}

			$value = $field['value'];

			if ( ! empty( $value ) ) {
				$payment_method = $value;

				break;
			}
		}

		return $payment_method;
	}

	/**
	 * Get issuer ID.
	 *
	 * @return string|null
	 */
	public function get_issuer_id() {
		$issuer = null;

		// Get issuer from an issuers field if it exists.
		foreach ( $this->form_data['fields'] as $field ) {
			if ( 'pronamic_pay_issuer' !== $field['type'] ) {
				continue;
			}

			$issuer = $field['value'];

			break;
		}

		return $issuer;
	}

	/**
	 * Get source indicator.
	 *
	 * @see Pronamic_Pay_PaymentDataInterface::get_source()
	 * @return string
	 */
	public function get_source() {
		return 'ninja-forms';
	}

	/**
	 * Get source id.
	 *
	 * @see Pronamic_Pay_PaymentDataInterface::get_source_id()
	 * @return string
	 */
	public function get_source_id() {
		if ( isset( $this->form_data['actions']['save']['sub_id'] ) ) {
			return $this->form_data['actions']['save']['sub_id'];
		}

		return time();
	}

	/**
	 * Get currency.
	 *
	 * @see Pronamic_Pay_PaymentDataInterface::get_currency_alphabetic_code()
	 * @return string
	 */
	public function get_currency_alphabetic_code() {
		$form = Ninja_Forms()->form( $this->form_id )->get();

		$currency = $form->get_setting( 'currency' );

		if ( ! empty( $currency ) ) {
			// Return currency from form settings.
			return $currency;
		}

		// Return currency from Ninja Forms settings.
		return Ninja_Forms()->get_setting( 'currency' );
	}

	/**
	 * Get description.
	 *
	 * @see Pronamic_Pay_PaymentDataInterface::get_description()
	 * @return string
	 */
	public function get_description() {
		$description = $this->action_settings['pronamic_pay_description'];

		if ( empty( $description ) ) {
			$description = sprintf(
				'%s #%s',
				__( 'Submission', 'pronamic_ideal' ),
				$this->get_source_id()
			);
		}

		return $description;
	}

	/**
	 * Get order ID.
	 *
	 * @see Pronamic_Pay_PaymentDataInterface::get_order_id()
	 * @return string
	 */
	public function get_order_id() {
		return $this->get_source_id();
	}

	/**
	 * Get email.
	 *
	 * @return string
	 */
	public function get_email() {
	    If(!empty($this->action_settings['knit_pay_email'])){
	        return $this->action_settings['knit_pay_email'];
	    }
	}

	/**
	 * Get first name.
	 *
	 * @return string
	 */
	public function get_first_name() {
	    If(!empty($this->action_settings['knit_pay_fname'])){
	        return $this->action_settings['knit_pay_fname'];
	    }
	}

	/**
	 * Get last name.
	 *
	 * @return string
	 */
	public function get_last_name() {
	    If(!empty($this->action_settings['knit_pay_lname'])){
	        return $this->action_settings['knit_pay_lname'];
	    }
	}

	/**
	 * Get customer name.
	 *
	 * @return string
	 */
	public function get_customer_name() {
	    return $this->action_settings['knit_pay_fname'] . ' ' . $this->action_settings['knit_pay_lname'];
	}

	/**
	 * Get address.
	 *
	 * @return null|string
	 */
	public function get_address() {
	    return $this->action_settings['knit_pay_address'];
	}

	/**
	 * Get city.
	 *
	 * @return null|string
	 */
	public function get_city() {
	    return $this->action_settings['knit_pay_city'];
	}

	/**
	 * Get ZIP.
	 *
	 * @return null|string
	 */
	public function get_zip() {
	    return $this->action_settings['knit_pay_zip'];
	}

	/**
	 * Get telephone number.
	 *
	 * @return null|string
	 */
	public function get_telephone_number() {
	    return $this->action_settings['knit_pay_phone'];
	}

	/**
	 * Get subscription.
	 *
	 * @since 2.3.2
	 * @return Subscription|null
	 */
	public function get_subscription() {
	    if ('0' === $this->action_settings['knit_pay_frequency']) {
	        return;
	    }

	    $interval_period = $this->action_settings['knit_pay_interval_period'];

	    if ('0' === $interval_period) {
	        foreach ( $this->form_data['fields'] as $field ) {
	            if ( 'knit_pay_recurring_interval_period' !== $field['type'] ) {
	                continue;
	            }

	            $interval_period = $field['value'];

	            if ( empty( $interval_period ) ) {
	                return;
	            }
	            break;
	        }
	        return;
	    }

	    // Subscription.
	    $subscription = new Subscription();

	    $subscription->description     = $this->get_description();
	    $subscription->frequency       = $this->action_settings['knit_pay_frequency'];
	    $subscription->interval        = $this->action_settings['knit_pay_interval'];
	    $subscription->interval_period = $interval_period;
	    $subscription->set_total_amount( $this->get_amount() );

	    return $subscription;
	}
}
