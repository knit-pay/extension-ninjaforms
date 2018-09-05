<?php
/**
 * Issuers Field.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2018 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Extensions\NinjaForms
 */

namespace Pronamic\WordPress\Pay\Extensions\NinjaForms;

use NF_Abstracts_List;
use Pronamic\WordPress\Pay\Plugin;
use Pronamic\WordPress\Pay\Core\PaymentMethods;

/**
 * Class IssuersField.
 */
class IssuersField extends NF_Abstracts_List {

	/**
	 * Name.
	 *
	 * @var string
	 */
	protected $_name = 'pronamic_pay_issuer';

	/**
	 * Type.
	 *
	 * @var string
	 */
	protected $_type = 'pronamic_pay_issuer';

	/**
	 * Nice name for display.
	 *
	 * @var string
	 */
	protected $_nicename = 'Issuer';

	/**
	 * Section.
	 *
	 * @var string
	 */
	protected $_section = 'pricing';

	/**
	 * Icon.
	 *
	 * @var string
	 */
	protected $_icon = 'bank';

	/**
	 * Template.
	 *
	 * @var string
	 */
	protected $_templates = 'listselect';

	/**
	 * Old classname for earlier versions.
	 *
	 * @var string
	 */
	protected $_old_classname = 'list-select';

	/**
	 * Settings.
	 *
	 * @var array
	 */
	protected $_settings = array();

	/**
	 * Constructs and initializes the field object.
	 */
	public function __construct() {
		parent::__construct();

		$this->_nicename = __( 'Issuer', 'pronamic_ideal' );

		$this->_settings['options']['value'] = array();

		// Actions.
		add_action( 'ninja_forms_render_options_' . $this->_type, array( $this, 'render_options' ), 10, 2 );
	}

	/**
	 * Get settings.
	 *
	 * @return array
	 */
	public function get_settings() {
		// Hide options settings in form builder.
		if ( is_admin() ) {
			unset( $this->_settings['options'] );
		}

		return $this->_settings;
	}

	/**
	 * Get options.
	 *
	 * @param array $options  Field select options.
	 * @param array $settings Field settings.
	 *
	 * @return array
	 */
	public function render_options( $options, $settings ) {
		$options = array();
		$order   = 0;

		$config_id = get_option( 'pronamic_pay_config_id' );
		$gateway   = Plugin::get_gateway( $config_id );

		$gateway->set_payment_method( PaymentMethods::IDEAL );

		$issuers = $gateway->get_issuers();

		if ( empty( $issuers ) ) {
			return $options;
		}

		foreach ( $issuers[0]['options'] as $value => $label ) {
			$options[] = array(
				'label'    => $label,
				'value'    => $value,
				'calc'     => '',
				'selected' => 0,
				'order'    => ++$order,
			);
		}

		return $options;
	}
}
