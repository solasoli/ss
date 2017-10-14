<?php
// Use the packages
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Api\ExecutePayment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\CreditCard;
use PayPal\Api\FundingInstrument;

function ipt_fsqm_get_paypal_api_context( $clientId, $clientSecret, $mode, $partner ) {
	// Create the API context
	$apiContext = new ApiContext(
		new OAuthTokenCredential(
			$clientId,
			$clientSecret
		)
	);

	// Sandbox if needed
	if ( $mode == 'sandbox' ) {
		$apiContext->setConfig(
			array(
				'mode' => 'sandbox',
				'log.LogEnabled' => true,
				'log.FileName' => ABSPATH . '/PayPal.log',
				'log.LogLevel' => 'FINE', // PLEASE USE `FINE` LEVEL FOR LOGGING IN LIVE ENVIRONMENTS
				'cache.enabled' => true,
			)
		);
	} else {
		$apiContext->setConfig(
			array(
				'mode' => 'live',
				'log.LogEnabled' => false,
				'cache.enabled' => true,
			)
		);
	}

	// Add partner if needed
	if ( $partner != '' ) {
		$apiContext->setConfig( array(
			'http.headers.PayPal-Partner-Attribution-Id' => $partner,
		) );
		$apiContext->addRequestHeader( 'PayPal-Partner-Attribution-Id', $partner );
	}

	return $apiContext;
}

function ipt_fsqm_paypal_ec_url( $apiContext, $name, $sku, $invoiceid, $description, $currency, $total_amount, $turl ) {
	// Initiate the return status
	$payment_status = array(
		'success' => true,
		'redirect_url' => false,
	);

	$payer = new Payer();
	$payer->setPaymentMethod( "paypal" );

	// Itemized information
	// (Optional) Lets you specify item wise information
	$item1 = new Item();
	$item1->setName( $name )
		->setCurrency( $currency )
		->setQuantity( 1 )
		->setSku( $sku ) // Similar to `item_number` in Classic API
		->setPrice( $total_amount );

	$itemList = new ItemList();
	$itemList->setItems(array($item1));

	// Lets you specify a payment amount. You can also specify additional details such as shipping, tax.
	$amount = new Amount();
	$amount->setCurrency( $currency )
		->setTotal( $total_amount );

	// Transaction
	// A transaction defines the contract of a payment - what is the payment for and who is fulfilling it.
	$transaction = new Transaction();
	$transaction->setAmount( $amount )
		->setItemList( $itemList )
		->setDescription( $description )
		->setInvoiceNumber( get_bloginfo( 'name' ) . '-' . $invoiceid );

	// Redirect urls
	// Set the urls that the buyer must be redirected to after payment approval/ cancellation.
	$redirectUrls = new RedirectUrls();
	$redirectUrls->setReturnUrl( add_query_arg( array(
		'psuccess' => 'true',
		'action' => 'payment',
		'mode' => 'paypal_e',
	), $turl ) )
		->setCancelUrl( add_query_arg( array(
			'psuccess' => 'false',
			'action' => 'payment',
			'mode' => 'paypal_e',
		), $turl ) );

	// Payment
	// A Payment Resource; create one using the above types and intent set to 'sale'
	$payment = new Payment();
	$payment->setIntent("sale")
	    ->setPayer($payer)
	    ->setRedirectUrls($redirectUrls)
	    ->setTransactions(array($transaction));


	// Create Payment
	// Create a payment by calling the 'create' method passing it a valid apiContext. (See bootstrap.php for more on ApiContext) The return object contains the state and the url to which the buyer must be redirected to for payment approval
	try {
	    $payment->create($apiContext);
	    // Get redirect url
	    // The API response provides the url that you must redirect the buyer to. Retrieve the url from the $payment->getApprovalLink() method
	    $payment_status['redirect_url'] = $payment->getApprovalLink();
	} catch (Exception $ex) {
		$payment_status['success'] = false;
	}

	return $payment_status;
}

function ipt_fsqm_paypal_e_execute_payment( $apiContext, $paymentId, $payerId, $payment_data ) {
	try {
		$payment = Payment::get( $paymentId, $apiContext );
	} catch ( Exception $e ) {
		return false;
	}

	$execution = new PaymentExecution();
	$execution->setPayerId( $payerId );

	$transaction = new Transaction();
	$amount = new Amount();

	$amount->setCurrency( $payment_data->currency )
		->setTotal( $payment_data->amount );

	$transaction->setAmount( $amount );

	$execution->addTransaction( $transaction );

	try {
		$result = $payment->execute( $execution, $apiContext );
		return $result;
	} catch( Exception $e ) {
		return false;
	}
}

function ipt_fsqm_paypal_direct_payment( $apiContext, $name, $sku, $invoiceid, $description, $currency, $total_amount, $cc ) {
	// Create CC
	$card = new CreditCard();
	$card->setType( $cc['type'] )
		->setNumber( $cc['number'] )
		->setExpireMonth( $cc['em'] )
		->setExpireYear( $cc['ey'] )
		->setCvv2( $cc['cvv'] )
		->setFirstName( $cc['fname'] )
		->setLastName( $cc['lname'] );

	// Create funding instrument
	$fi = new FundingInstrument();
	$fi->setCreditCard( $card );

	// Create the payer
	$payer = new Payer();
	$payer->setPaymentMethod( 'credit_card' )
		->setFundingInstruments( array( $fi ) );

	// Itemized information
	// (Optional) Lets you specify item wise information
	$item1 = new Item();
	$item1->setName( $name )
		->setCurrency( $currency )
		->setQuantity( 1 )
		->setSku( $sku ) // Similar to `item_number` in Classic API
		->setPrice( $total_amount );

	$itemList = new ItemList();
	$itemList->setItems(array($item1));

	// Lets you specify a payment amount. You can also specify additional details such as shipping, tax.
	$amount = new Amount();
	$amount->setCurrency( $currency )
		->setTotal( $total_amount );

	// Transaction
	// A transaction defines the contract of a payment - what is the payment for and who is fulfilling it.
	$transaction = new Transaction();
	$transaction->setAmount( $amount )
		->setItemList( $itemList )
		->setDescription( $description )
		->setInvoiceNumber( get_bloginfo( 'name' ) . '-' . $invoiceid );

	// Create new payment
	$payment = new Payment();
	$payment->setIntent( "sale" )
	    ->setPayer( $payer )
	    ->setTransactions( array( $transaction ) );

	try {
		$return = $payment->create( $apiContext );
		return $return;
	} catch ( Exception $e ) {
		return false;
	}
}


