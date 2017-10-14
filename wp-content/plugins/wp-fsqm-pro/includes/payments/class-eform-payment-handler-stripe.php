<?php
/**
 * eForm Stripe Payment Handlers
 *
 * Provides some shortcuts to handle payments through Stripe system
 *
 * 1. Gets the token and processes the payment
 *
 * This is a singleton class
 *
 * @package eForm - WordPress Form Builder
 * @subpackage Payments\Stripe
 * @author Swashata Ghosh <swashata@wpquark.com>
 */
class EForm_Payment_Handler_Stripe {
	/**
	 * Singleton instance variable
	 *
	 * @var        EForm_Payment_Handler_Stripe
	 */
	private static $instance = null;

	/**
	 * Stripe Secret API Key
	 *
	 * @var        string
	 */
	protected $api_key = null;

	/**
	 * Get the instance of this singleton class
	 *
	 * @return     EForm_Payment_Handler_Stripe  The instance of the class
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new static();
		}
		return self::$instance;
	}

	/**
	 * Constructor made private so that instance has to be called to get an instance
	 *
	 * We may change the implementation in future. This makes sure our code is
	 * future proof
	 */
	private function __construct() {

	}

	/**
	 * Sets the stripe secret api key.
	 *
	 * @param      string  $api_key  The stripe secret API key
	 */
	public function set_api_key( $api_key ) {
		$this->api_key = $api_key;
	}

	/**
	 * Gets the api key.
	 *
	 * @return     string  The api key.
	 */
	public function get_api_key() {
		return $this->api_key;
	}

	/**
	 * Creates a Stripe Charge and returns the output. On error it returns false
	 *
	 * @param      string                  $token     The token
	 * @param      array                   $product   The product
	 * @param      string                  $email     The email
	 * @param      string                  $currency  The currency
	 * @param      float                   $amount    The amount
	 *
	 * @return     boolean|\Stripe\Charge  Returns the Charge object on success, false on failure
	 */
	public function charge( $token, $product, $email, $currency, $amount ) {
		// First set the API Key
		\Stripe\Stripe::setApiKey( $this->api_key );
		// Check the token
		if ( ! isset( $token['token'] ) || isset( $token['error'] ) ) {
			return false;
		}
		// Now create the charge object
		$charge = array(
			'amount' => $amount,
			'currency' => $currency,
			'source' => $token['token']['id'],
			'description' => $product['description'],
			'metadata' => $product['metadata'],
		);
		// Add the email if valid
		if ( is_email( $email ) && ! empty( $email ) ) {
			$charge['receipt_email'] = $email;
		}
		try {
			return \Stripe\Charge::create( $charge );
		} catch ( Exception $e ) {
			if ( defined( 'WP_DEBUG' ) && true == WP_DEBUG ) {
				error_log( $e->getMessage() );
			}
			return false;
		}
	}
}
