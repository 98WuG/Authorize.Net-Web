<?php
require 'vendor/autoload.php';
require ('constants.php');
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;

define("AUTHORIZENET_LOG_FILE", "phplog");

if (isset($_POST['profileId']) && isset($_POST['paymentId']) && isset($_POST['amount'])){
	chargeCustomerProfile($_POST['profileId'], $_POST['paymentId'], $_POST['amount']);
}

function chargeCustomerProfile($profileid, $paymentprofileid, $amount)
{
	/* Create a merchantAuthenticationType object with authentication details
	retrieved from the constants file */
	$merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
	$merchantAuthentication->setName(MERCHANT_LOGIN_ID);
	$merchantAuthentication->setTransactionKey(TRANSACTION_KEY);

	// Set the transaction's refId
	$refId = 'ref' . time();

	$profileToCharge = new AnetAPI\CustomerProfilePaymentType();
	$profileToCharge->setCustomerProfileId($profileid);
	$paymentProfile = new AnetAPI\PaymentProfileType();
	$paymentProfile->setPaymentProfileId($paymentprofileid);
	$profileToCharge->setPaymentProfile($paymentProfile);

	$order = new AnetAPI\OrderType();
	$invoice = rand(0,1000000);
	$order->setInvoiceNumber($invoice);

	/*
	$lineItem = new AnetAPI\LineItemType();
	$lineItem->setItemId($_POST['itemId']);
	$lineItem->setName($_POST['item']);
	$lineItem->setQuantity(1);
	$lineItem->setUnitPrice($amount);
	$lineItem_Array[] = $lineItem;
	 */

	$transactionRequestType = new AnetAPI\TransactionRequestType();
	$transactionRequestType->setTransactionType( "authCaptureTransaction"); 
	$transactionRequestType->setAmount($amount);
	$transactionRequestType->setProfile($profileToCharge);
	$transactionRequestType->setOrder($order);
	//$transactionRequestType->setLineItems($lineItem_Array);

	$request = new AnetAPI\CreateTransactionRequest();
	$request->setMerchantAuthentication($merchantAuthentication);
	$request->setRefId( $refId);
	$request->setTransactionRequest( $transactionRequestType);
	$controller = new AnetController\CreateTransactionController($request);
	$response = $controller->executeWithApiResponse( \net\authorize\api\constants\ANetEnvironment::SANDBOX);

	if ($response != null)
	{
		if($response->getMessages()->getResultCode() == "Ok")
		{
			$tresponse = $response->getTransactionResponse();

			if ($tresponse != null && $tresponse->getMessages() != null)   
			{
				echo " Transaction Response code : " . $tresponse->getResponseCode() . "<br>";
				echo  "Charge Customer Profile APPROVED  :" . "<br>";
				echo " Charge Customer Profile AUTH CODE : " . $tresponse->getAuthCode() . "<br>";
				echo " Charge Customer Profile TRANS ID  : " . $tresponse->getTransId() . "<br>";
				echo "Credit Card: " . $tresponse->getAccountType() . " " . $tresponse->getAccountNumber() . "<br>";
				echo " Code : " . $tresponse->getMessages()[0]->getCode() . "<br>"; 
				echo " Description : " . $tresponse->getMessages()[0]->getDescription() . "<br>";
			}
			else
			{
				echo "Transaction Failed (1) <br>";
				if($tresponse->getErrors() != null)
				{
					echo " Error code  : " . $tresponse->getErrors()[0]->getErrorCode() . "<br>";
					echo " Error message : " . $tresponse->getErrors()[0]->getErrorText() . "<br>";            
				}
			}
		}
		else
		{
			echo "Transaction Failed (2) <br>";
			$tresponse = $response->getTransactionResponse();
			if($tresponse != null && $tresponse->getErrors() != null)
			{
				echo " Error code  : " . $tresponse->getErrors()[0]->getErrorCode() . "<br>";
				echo " Error message : " . $tresponse->getErrors()[0]->getErrorText() . "<br>";                      
			}
			else
			{
				echo " Error code  : " . $response->getMessages()->getMessage()[0]->getCode() . "<br>";
				echo " Error message : " . $response->getMessages()->getMessage()[0]->getText() . "<br>";
			}
		}
	}
	else
	{
		echo  "No response returned \n";
	}

	return $response;
}

?>