<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<script type="text/javascript"
				src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js">
		</script>
	</head>
	<body>
	<?php
	require 'vendor/autoload.php';
	require ('constants.php');
	use net\authorize\api\contract\v1 as AnetAPI;
	use net\authorize\api\controller as AnetController;

	define("AUTHORIZENET_LOG_FILE", "phplog");

	function getAnAcceptPaymentPage()
	{
		/* Create a merchantAuthenticationType object with authentication details
		retrieved from the constants file */
		$merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
		$merchantAuthentication->setName(MERCHANT_LOGIN_ID);
		$merchantAuthentication->setTransactionKey(TRANSACTION_KEY);

		// Set the transaction's refId
		$refId = 'ref' . time();


		//create a transaction
		$transactionRequestType = new AnetAPI\TransactionRequestType();
		$transactionRequestType->setTransactionType("authOnlyTransaction");
		$transactionRequestType->setAmount("0.01");

		//$profileId = "1913969757";
		$profileId = "";

		// Set up profile if exists
		if (strlen($profileId) != 0) {
			$profile = new ANetAPI\CustomerProfilePaymentType();
			$profile->setCustomerProfileId($profileId);
			$transactionRequestType->setProfile($profile);
		}

		// Set Hosted Form options
		$setting1 = new AnetAPI\SettingType();
		$setting1->setSettingName("hostedPaymentButtonOptions");
		$setting1->setSettingValue("{\"text\": \"Pay\"}");

		$setting2 = new AnetAPI\SettingType();
		$setting2->setSettingName("hostedPaymentOrderOptions");
		$setting2->setSettingValue("{\"show\": false}");

		$setting3 = new AnetAPI\SettingType();
		$setting3->setSettingName("hostedPaymentPaymentOptions");
		$setting3->setSettingValue("{\"cardCodeRequired\": false, \"showCreditCard\": true, \"showBankAccount\": false}");

		$setting4 = new AnetAPI\SettingType();
		$setting4->setSettingName("hostedPaymentReturnOptions");
		$setting4->setSettingValue(
			"{\"url\": \"https://mysite.com/receipt\", \"cancelUrl\": \"https://mysite.com/cancel\", \"showReceipt\": false}"
			//"{\"url\": \"https://mysite.com/receipt\", \"cancelUrl\": \"https://mysite.com/cancel\", \"showReceipt\": true}"
		);

		$setting5 = new ANetAPI\SettingType();
		$setting5->setSettingName("hostedPaymentBillingAddressOptions");
		$setting5->setSettingvalue("{\"show\": true}");

		// Build transaction request
		$request = new AnetAPI\GetHostedPaymentPageRequest();
		$request->setMerchantAuthentication($merchantAuthentication);
		$request->setRefId($refId);
		$request->setTransactionRequest($transactionRequestType);

		$request->addToHostedPaymentSettings($setting1);
		$request->addToHostedPaymentSettings($setting2);
		$request->addToHostedPaymentSettings($setting3);
		$request->addToHostedPaymentSettings($setting4);
		$request->addToHostedPaymentSettings($setting5);

		//execute request
		$controller = new AnetController\GetHostedPaymentPageController($request);
		$response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);

		if (($response != null) && ($response->getMessages()->getResultCode() == "Ok")) {
			//echo $response->getToken()."\n";
		} else {
			echo "ERROR :  Failed to get hosted payment page token\n";
			$errorMessages = $response->getMessages()->getMessage();
			echo "RESPONSE : " . $errorMessages[0]->getCode() . "  " .$errorMessages[0]->getText() . "\n";
		}
		return $response;
	}
	function getAcceptCustomerProfilePage($customerprofileid = "1913969757")
	{
		/* Create a merchantAuthenticationType object with authentication details
		   retrieved from the constants file */
		$merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
		$merchantAuthentication->setName(MERCHANT_LOGIN_ID);
		$merchantAuthentication->setTransactionKey(TRANSACTION_KEY);
		
		// Set the transaction's refId
		$refId = 'ref' . time();
		
		  // Use an existing payment profile ID for this Merchant name and Transaction key
		  
		  $setting1 = new AnetAPI\SettingType();
		  $setting1->setSettingName("hostedProfilePageBorderVisible");
		  $setting1->setSettingValue("false");

		  $setting2 = new AnetAPI\SettingType();
		  $setting2->setSettingName("hostedProfileValidationMode");
		  $setting2->setSettingValue("liveMode");

		  $setting3 = new AnetAPI\SettingType();
		  $setting3->setSettingName("hostedProfileBillingAddressRequired");
		  $setting3->setSettingValue("false");
		  
		  $setting4 = new AnetAPI\SettingType();
		  $setting4->setSettingName("hostedProfileManageOptions");
		  $setting4->setSettingValue("showPayment");

		  $setting5 = new AnetAPI\SettingType();
		  $setting5->setSettingName("hostedProfileIFrameCommunicatorUrl");
		  $setting5->setSettingValue("https://localhost/php/IFrameCommunicator.html");
		  //$alist = new AnetAPI\ArrayOfSettingType();
		  //$alist->addToSetting($setting);
		  
		  $request = new AnetAPI\GetHostedProfilePageRequest();
		  $request->setMerchantAuthentication($merchantAuthentication);
		  $request->setCustomerProfileId($customerprofileid);
		  $request->addToHostedProfileSettings($setting1);
		  $request->addToHostedProfileSettings($setting2);
		  $request->addToHostedProfileSettings($setting3);
		  $request->addToHostedProfileSettings($setting4);
		  $request->addToHostedProfileSettings($setting5);
		  
		  $controller = new AnetController\GetHostedProfilePageController($request);
		  $response = $controller->executeWithApiResponse( \net\authorize\api\constants\ANetEnvironment::SANDBOX);
		  
		  if (($response != null) && ($response->getMessages()->getResultCode() == "Ok") )
		  {
			  //echo $response->getToken()."\n";
		   }
		  else
		  {
			  echo "ERROR :  Failed to get hosted profile page\n";
			  $errorMessages = $response->getMessages()->getMessage();
			  echo "Response : " . $errorMessages[0]->getCode() . "  " .$errorMessages[0]->getText() . "\n";
		  }
		  return $response;
	  }
	$hostedPaymentResponse = getAnAcceptPaymentPage();
	$hostedProfileResponse = getAcceptCustomerProfilePage();
	//echo $hostedPaymentResponse->getToken();
	?>
	<center>
		<script type="text/javascript">
		window.CommunicationHandler = {};
		function parseQueryString(str) {
			var vars = [];
			var arr = str.split('&');
			var pair;
			for (var i = 0; i < arr.length; i++) {
				pair = arr[i].split('=');
				vars[pair[0]] = unescape(pair[1]);
			}
			return vars;
		}
		CommunicationHandler.onReceiveCommunication = function (argument) {
			params = parseQueryString(argument.qstr)
			parentFrame = argument.parent.split('/')[4];
			console.log(params);
			console.log(parentFrame);
			//alert(params['height']);
			$frame = null;
			switch(parentFrame){
				case "manage" 		: $frame = $("#load_profile");break;
				case "payment"		: $frame = $("#load_payment");break;
			}
			switch(params['action']){
				case "resizeWindow" :
					if( parentFrame== "manage" && parseInt(params['height'])<750) params['height']=750;
				  	if( parentFrame== "payment" && parseInt(params['height'])<1000) params['height']=1000;
				  	$frame.outerHeight(parseInt(params['height']));
				  	break;
			}
		}
		</script>
		<iframe id="load_profile"  class="embed-responsive-item" name="load_profile" width="100%" height="750px" scrolling="no" frameborder="0">
		</iframe>
		<form name="profileForm" action="https://test.authorize.net/customer/manage" method="post" target="load_profile">
			<input type="hidden" name="token" value="<?php echo $hostedProfileResponse->getToken() ?>" />
		</form>
		<!--
		<iframe id="load_payment"  class="embed-responsive-item" name="load_payment" width="600" height="1000" frameborder="1" scrolling="yes">
		</iframe>
		<form id="send_hptoken" name="authForm" action="https://test.authorize.net/payment/payment" method="post" target="load_payment" >
			<input type="hidden" name="token" value="<?php echo $hostedPaymentResponse->getToken() ?>" />
		</form>
		-->
		<script>
			document.profileForm.submit();
			//document.authForm.submit();
		</script>
	</center>
	</body>
</html>