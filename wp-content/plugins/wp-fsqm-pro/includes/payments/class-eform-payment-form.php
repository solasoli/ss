<?php
/**
 * eForm Payment Form Handler
 *
 * Provides a use-anywhere solution to provide with Payment Form with proper
 * providers
 *
 *
 * @package eForm - WordPress Form Builder
 * @subpackage Payments\Form
 * @author Swashata Ghosh <swashata@wpquark.com>
 */
class EForm_Payment_Form {
	/**
	 * Form ID variable
	 * @var        int
	 */
	protected $form_id;

	/**
	 * Name Prefix for the form
	 *
	 * @var        string
	 */
	protected $name_prefix;
	/**
	 * The UI Variable
	 * @var IPT_Plugin_UIF_Base
	 */
	protected $ui;

	/**
	 * Payment Element Data
	 * @var        array
	 */
	protected $data;

	/**
	 * Payment Element Key
	 *
	 * @var        array
	 */
	protected $key;

	/**
	 * Payment related settings
	 *
	 * @var        array
	 */
	protected $settings;

	public function __construct( $form_id, $name_prefix, $ui, $element_data, $element_key, $settings ) {
		// Set the variables
		$this->form_id = $form_id;
		$this->name_prefix = $name_prefix;
		$this->ui = $ui;
		$this->data = $element_data;
		$this->key = $element_key;
		$this->settings = $settings;
	}

	/**
	 * Show the payment form
	 */
	public function show_form() {
		$payment_types = array();
		$payment_selections = $this->get_payment_gateways();

		if ( true == $this->settings['paypal']['enabled'] && '' != $this->settings['paypal']['d_settings']['client_id'] && true == $this->settings['paypal']['allow_direct'] ) {
			$payment_types[] = array(
				'value' => 'paypal_d',
				'label' => $this->settings['paypal']['label_paypal_d'],
				'data' => array(
					'condid' => 'ipt_fsqm_form_' . $this->form_id . '_' . $this->key . '_payment_cc',
				),
			);
			$payment_selections['paypal_d'] = true;
		}
		if ( true == $this->settings['paypal']['enabled'] && '' != $this->settings['paypal']['d_settings']['client_id'] ) {
			$payment_types[] = array(
				'value' => 'paypal_e',
				'label' => $this->settings['paypal']['label_paypal_e'],
				'data' => array(
					'condid' => 'ipt_fsqm_form_' . $this->form_id . '_' . $this->key . '_payment_pp',
				),
			);
			$payment_selections['paypal_e'] = true;
		}
		if ( true == $this->settings['stripe']['enabled'] ) {
			$payment_types[] = array(
				'value' => 'stripe',
				'label' => $this->settings['stripe']['label_stripe'],
				'data' => array(
					'condid' => 'ipt_fsqm_form_' . $this->form_id . '_' . $this->key . '_payment_stripe_wrap',
				),
			);
			$payment_selections['stripe'] = true;
		}
		if ( true == $this->settings['authorizenet']['enabled'] ) {
			$payment_types[] = array(
				'value' => 'authorizenet',
				'label' => $this->settings['authorizenet']['label'],
				'data' => array(
					'condid' => 'ipt_fsqm_form_' . $this->form_id . '_' . $this->key . '_payment_cc',
				),
			);
			$payment_selections['authorizenet'] = true;
		}

		$sparams = array( $this->name_prefix . '[pmethod]', $payment_types, $this->settings['type'], array(
			'required' => true,
		), 'random', true );
		$this->ui->column_head( '', 'full', false, array( 'eform-checkout-gateways-radio' ) );
		$this->ui->question_container( $this->name_prefix . '[pmethod]', $this->data['settings']['ptitle'], '', array( array( $this->ui, 'radios' ), $sparams ), true, false, false, '', array( 'ipt_fsqm_payment_method_radio' ), array(
			'iptfsqmpp' => $this->settings['type'],
		) );
		$this->ui->column_tail();

		$this->ui->column_head( '', 'full', false, array( 'eform-checkout-gateways' ) );
		if ( true == $payment_selections['paypal_d'] || true == $payment_selections['authorizenet'] ) {
			$this->cc_direct_ui();
		}

		if ( true == $payment_selections['paypal_e'] ) {
			$this->paypal_express_ui();
		}

		if ( true == $payment_selections['stripe'] ) {
			$this->stripe_ui();
		}
		$this->ui->column_tail();
	}

	protected function stripe_ui() {
		echo '<div class="ipt_uif_question" id="ipt_fsqm_form_' . $this->form_id . '_' . $this->key . '_payment_stripe_wrap">';
		$this->ui->question_container( $this->name_prefix, $this->data['settings']['ctitle'], __( 'we do not store any information you provide', 'ipt_fsqm' ), array( $this, 'stripe_elements' ), true );
		echo '</div>';
	}

	public function stripe_elements() {
		// Custom Validation for the namefield
		$validation = $this->ui->get_cc_validation();
		$address_validations = $this->ui->get_address_validations();
		$ccparams = $this->get_cc_params();
		echo '<div class="eform-stripe-checkout">';
		// Name field
		$this->ui->column_head( '', 'full', true, array( 'eform-stripe-checkout-name' ) );
		$this->ui->text( $this->name_prefix . '[stripe][name]', '', '', 'none', 'normal', array( 'ipt_uif_cc_name ipt_fsqm_sayt_exclude' ), $validation, false, array(
			'placeholder' => $ccparams[2]['name'],
		) );
		$this->ui->column_tail();

		// This is mounted by Stripe.js
		$this->ui->column_head( '', 'full', true, array( 'eform-stripe-checkout-elements' ) );
		echo '<div class="input-field"><div class="eform-stripe-elements" data-stripe-pub-key="' . esc_attr( $this->settings['stripe']['pub'] ) . '" id="ipt_fsqm_form_' . $this->form_id . '_payment_stripe"></div></div>';
		$this->ui->column_tail();

		// Country and ZIP
		$this->ui->column_head( '', 'half', true, array( 'no_margin_right', 'eform-stripe-checkout-country' ) );
		$this->ui->select( $this->name_prefix . '[stripe][country]', $this->ui->get_countries( $ccparams[2]['country'] ), $ccparams[1]['country'], $address_validations['country'], false, true, false, false, $ccparams[2]['country'] );
		$this->ui->column_tail();
		$this->ui->column_head( '', 'half', true, array( 'no_margin_right', 'eform-stripe-checkout-zip' ) );
		$this->ui->text( $this->name_prefix . '[stripe][zip]', '',  '', 'none', 'normal', array( 'ipt_uif_cc_zip ipt_fsqm_sayt_exclude' ), $address_validations['zip'], false, array(
			'placeholder' => $ccparams[2]['zip'],
		) );
		$this->ui->column_tail();
		$this->ui->clear();
		echo '</div>';
	}

	/**
	 * UI for PayPal Express Checkout
	 */
	protected function paypal_express_ui() {
		echo '<div class="ipt_uif_question" id="ipt_fsqm_form_' . $this->form_id . '_' . $this->key . '_payment_pp">';
		$this->ui->msg_okay( $this->data['settings']['ppmsg'], true, __( 'Pay through PayPal', 'ipt_fsqm' ) );
		echo '</div>';
	}

	/**
	 * eForm Managed Credit Card form
	 *
	 * Used by PayPal Direct Payment and Authorized.net
	 */
	protected function cc_direct_ui() {
		echo '<div class="ipt_uif_question" id="ipt_fsqm_form_' . $this->form_id . '_' . $this->key . '_payment_cc">';
		$ccparams = $this->get_cc_params();

		$this->ui->question_container( $this->name_prefix, $this->data['settings']['ctitle'], __( 'we do not store any information you provide', 'ipt_fsqm' ), array( array( $this->ui, 'creditcard' ), $ccparams ), true );
		echo '</div>';
	}

	protected function get_payment_gateways() {
		return IPT_FSQM_Form_Elements_Static::get_valid_payment_selections();
	}

	protected function get_cc_params() {
		return array(
			$this->name_prefix . '[cc]',
			array(
				'name' => '',
				'number' => '',
				'expiry' => '',
				'cvc' => '',
				'ctype' => '',
				'address' => '',
				'country' => $this->data['settings']['country'],
				'zip' => '',
			),
			array(
				'name' => __( 'Cardholder\'s name', 'ipt_fsqm' ),
				'number' => __( 'Card number', 'ipt_fsqm' ),
				'expiry' => __( 'MM/YY', 'ipt_fsqm' ),
				'cvc' => __( 'CVC', 'ipt_fsqm' ),
				'address' => __( 'Address', 'ipt_fsqm' ),
				'country' => __( 'Country', 'ipt_fsqm' ),
				'zip' => __( 'ZIP/Postal Code', 'ipt_fsqm' ),
			),
		);
	}
}
