<?php

// ini_set('display_errors', 1); 
// error_reporting(E_ALL);

class WPVQMailingAPI
{

	private $service;
	private $meta;
	private $playerData;

	function __construct($service, $meta, $playerData)
	{
		$this->service 		=  $service;
		$this->meta 		=  $meta;
		$this->playerData 	=  $playerData;

		$this->syncPlayer();
	}

	/**
	 * Sync the player with the choosen provider
	 * @return [type] [description]
	 */
	private function syncPlayer()
	{
		if (!session_id()) { session_start(); }
		
		// Mailing API
		require_once 'vendor/autoload.php';

		if ($this->service == 'mailchimp') {
			$this->syncPlayerMailchimp();	
		}

		if ($this->service == 'aweber') {
			require_once 'vendor/aweber/aweber/aweber_api/aweber_api.php';
			$this->syncPlayerAweber();	
		}

		if ($this->service == 'activecampaign') {
			$this->syncPlayerActiveCampaign();	
		}
	}

	/**
	 * Sync a player on Mailchimp
	 * @return [type] [description]
	 */
	private function syncPlayerMailchimp()
	{
		if (!isset($this->meta['mailchimp']['apiKey']) || $this->meta['mailchimp']['apiKey'] == '' ||
			!isset($this->meta['mailchimp']['listId']) || $this->meta['mailchimp']['listId'] == '') {
			echo 'Error: need more mailchimp parameters.';
			return;
		}

		// Mailchimp Field Name
		$firstNameField  		= isset($this->meta['mailchimp']['firstNameField']) ? $this->meta['mailchimp']['firstNameField']:'FNAME';
		$resultField 	 		= isset($this->meta['mailchimp']['resultField']) ? $this->meta['mailchimp']['resultField']:'RESULT';
		$statusDoubleOptin 		= (isset($this->meta['mailchimp']['doubleOptin']) && $this->meta['mailchimp']['doubleOptin'] == 1) ? 'pending':'subscribed';

		// Connect to mailchimp
		try 
		{
			$mc = new Mailchimp\Mailchimp($this->meta['mailchimp']['apiKey']);	
		} 
		catch (Exception $e) 
		{
			echo "Can't connect to MC.";
			return;
		}

		// Post to add member
		try 
		{
			$jsonReturn = $mc->post("lists/{$this->meta['mailchimp']['listId']}/members", array(
				'email_address' => $this->playerData['email'],
				'status' 		=> $statusDoubleOptin,
				'merge_fields' 	=> array(
					$firstNameField => $this->playerData['nickname'],
					$resultField 	=> $this->playerData['result']
				),
			));	
		} 
		catch (Exception $e) 
		{
			echo "Can't add to MC.";
			return;
		}
		

		$jsonReturn = json_decode($jsonReturn);
		echo $jsonReturn->id;
	}

	/**
	 * Sync a player on Activecampaign
	 * @return [type] [description]
	 */
	private function syncPlayerActivecampaign()
	{
		if (!isset($this->meta['activecampaign']['apiKey']) || $this->meta['activecampaign']['apiKey'] == '' ||
			!isset($this->meta['activecampaign']['apiUrlEndpoint']) || $this->meta['activecampaign']['apiUrlEndpoint'] == '' ||
			!isset($this->meta['activecampaign']['listId']) || $this->meta['activecampaign']['listId'] == '') {
			echo 'Error: need more activecampaign parameters.';
			return;
		}

		// Mailchimp Field Name
		$apiKey  			= $this->meta['activecampaign']['apiKey'];
		$apiUrlEndpoint  	= $this->meta['activecampaign']['apiUrlEndpoint'];
		
		$listId  			= $this->meta['activecampaign']['listId'];
		$tags  				= isset($this->meta['activecampaign']['tags']) ? $this->meta['activecampaign']['tags']:'';
		
		$resultField 	 	= trim(isset($this->meta['activecampaign']['resultField']) ? $this->meta['activecampaign']['resultField']:'');

		$ac = new ActiveCampaign($apiUrlEndpoint, $apiKey);
		if (!(int)$ac->credentials_test()) {
			echo "Access denied: Invalid credentials (URL and/or API key).";
			return;
		}

		$contact = array(
			"email"              		=> $this->playerData['email'],
			"first_name"         		=> $this->playerData['nickname'],
			"last_name"          		=> "",
			"p[{$listId}]"      		=> $listId,
			"tags" 						=> $tags,
			"status[{$list_id}]" 		=> 1, // "Active" status
		);

		if ($resultField != '') {
			$contact["field[%{$resultField}%,0]"] = $this->playerData['result'];
		}

		$contact_sync = $ac->api("contact/sync", $contact);

		if (!(int)$contact_sync->success) {
			// request failed
			echo "Syncing contact failed. Error returned: " . $contact_sync->error ;
			return;
		}

		$contact_id = (int)$contact_sync->subscriber_id;
		echo $contact_id;
	}

	public function syncPlayerAweber()
	{
		$accessKeys 	 =  explode('|', $this->meta['aweber']['accessKeys']);
        $consumerKey 	 =  $accessKeys[0];
        $consumerSecret  =  $accessKeys[1];
        $accessKey 		 =	$accessKeys[2];
        $accessSecret 	 =  $accessKeys[3];
        $listId 		 =  str_replace('awlist', '', $this->meta['aweber']['listId']);

        $application = new AWeberAPI($consumerKey, $consumerSecret);
        $account = $application->getAccount($accessKey, $accessSecret);

        try 
        {
            $listUrl = "/accounts/{$account->id}/lists/$listId";
            $list = $account->loadFromUrl($listUrl);

            $subscriber = array(
			    'email' => $this->playerData['email'],
			    'name'  => $this->playerData['nickname'],
			);

            // Custom field RESULT ?
            if (isset($this->meta['aweber']['resultField']) && $this->meta['aweber']['resultField'] != '') {
            	$subscriber['custom_fields'][$this->meta['aweber']['resultField']] = $this->playerData['result'];
            }

            $newSubscriber = $list->subscribers->create($subscriber);
            echo "<pre>";
            echo "— DEBUG —";
            print_r($newSubscriber);
            echo "</pre>";
            die();
        }
        catch(Exception $exc) {
            print $exc;
            die();
        }

	}

	/**
	 * Generate Aweber Creds via Ajax
	 * @return json with status=OK or FAIL
	 */
	public static function generateAweberCreds()
	{
		require_once 'vendor/aweber/aweber/aweber_api/aweber_api.php';

		$authCode 	=  htmlspecialchars((isset($_POST['authCode'])) ? $_POST['authCode']:'');
		$values 	=  array('status' => 'FAIL', 'code' => $authCode);

		$credentials 				=  AWeberAPI::getDataFromAweberID($authCode);
		$values['consumerKey'] 		=  $credentials[0];
        $values['consumerSecret'] 	=  $credentials[1];
        $values['accessKey'] 		=  $credentials[2];
        $values['accessSecret'] 	=  $credentials[3];

        if ($values['consumerKey'] != NULL) {
        	$values['status'] = 'OK';
        }

        die(json_encode($values));
	}
}

?>