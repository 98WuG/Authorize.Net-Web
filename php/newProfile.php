<?php
require 'vendor/autoload.php';
require ('constants.php');

use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;

define("AUTHORIZENET_LOG_FILE", "phplog");

if(isset($_POST['email']) && isset($_POST['descriptor']) && isset($_POST['value']))
{
    createCustomerProfile($_POST['email']);
}

function createCustomerProfile($email)
{
    /* Create a merchantAuthenticationType object with authentication details
    retrieved from the constants file */
    $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
    $merchantAuthentication->setName(MERCHANT_LOGIN_ID);
    $merchantAuthentication->setTransactionKey(TRANSACTION_KEY);

    // Set the transaction's refId
    $refId = 'ref' . time();

    // Create a Customer Profile Request
    //  1. (Optionally) create a Payment Profile
    //  2. (Optionally) create a Shipping Profile
    //  3. Create a Customer Profile (or specify an existing profile)
    //  4. Submit a CreateCustomerProfile Request
    //  5. Validate Profile ID returned


    // Create the payment object for a payment nonce
    $opaqueData = new AnetAPI\OpaqueDataType();
    $opaqueData->setDataDescriptor($_POST['descriptor']);
    $opaqueData->setDataValue($_POST['value']);
    $paymentOpaque = new AnetAPI\PaymentType();
    $paymentOpaque->setOpaqueData($opaqueData);


    // Create the Bill To info for new payment type
    $billTo = new AnetAPI\CustomerAddressType();
    $billTo->setFirstName($_POST['firstName']);
    $billTo->setLastName($_POST['lastName']);
    $billTo->setCompany($_POST['company']);
    $billTo->setAddress($_POST['address']);
    $billTo->setCity($_POST['city']);
    $billTo->setState($_POST['state']);
    $billTo->setZip($_POST['zip']);
    $billTo->setCountry($_POST['country']);
    $billTo->setPhoneNumber($_POST['phone']);


    // Create a new CustomerPaymentProfile object
    $paymentProfile = new AnetAPI\CustomerPaymentProfileType();
    $paymentProfile->setCustomerType('individual');
    $paymentProfile->setBillTo($billTo);
    $paymentProfile->setPayment($paymentOpaque);
    $paymentProfile->setDefaultpaymentProfile(true);
    $paymentProfiles[] = $paymentProfile;


    // Create a new CustomerProfileType and add the payment profile object
    $customerProfile = new AnetAPI\CustomerProfileType();
    $customerProfile->setDescription("Customer 2 Test PHP");
    $customerProfile->setEmail($email);
    $customerProfile->setpaymentProfiles($paymentProfiles);


    // Assemble the complete transaction request
    $request = new AnetAPI\CreateCustomerProfileRequest();
    $request->setMerchantAuthentication($merchantAuthentication);
    $request->setRefId($refId);
    $request->setProfile($customerProfile);

    // Create the controller and get the response
    $controller = new AnetController\CreateCustomerProfileController($request);
    $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);

    if (($response != null) && ($response->getMessages()->getResultCode() == "Ok"))
    {
        echo "Succesfully created customer profile : " . $response->getCustomerProfileId() . "<br>";
        $paymentProfiles = $response->getCustomerPaymentProfileIdList();
        echo "SUCCESS:<br>PAYMENT PROFILE ID : " . $paymentProfiles[0] . "<br>";
        echo "<br>Validating payment:<br>";
        validateCustomerPaymentProfile($response->getCustomerProfileId(),$paymentProfiles[0]);
    }
    else
    {
        echo "ERROR :  Invalid response<br>";
        $errorMessages = $response->getMessages()->getMessage();
        echo "Response : " . $errorMessages[0]->getCode() . "  " .$errorMessages[0]->getText() . "<br>";
    }
    return $response;
}

function validateCustomerPaymentProfile($profileId, $paymentId)
{
    /* Create a merchantAuthenticationType object with authentication details
    retrieved from the constants file */
    $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
    $merchantAuthentication->setName(MERCHANT_LOGIN_ID);
    $merchantAuthentication->setTransactionKey(TRANSACTION_KEY);

    // Set the transaction's refId
    $refId = 'ref' . time();

    // Use an existing payment profile ID for this Merchant name and Transaction key
    //validationmode tests , does not send an email receipt
    $validationmode = "testMode";

    $request = new AnetAPI\ValidateCustomerPaymentProfileRequest();

    $request->setMerchantAuthentication($merchantAuthentication);
    $request->setCustomerProfileId($profileId);
    $request->setCustomerPaymentProfileId($paymentId);
    $request->setValidationMode($validationmode);

    $controller = new AnetController\ValidateCustomerPaymentProfileController($request);
    $response = $controller->executeWithApiResponse( \net\authorize\api\constants\ANetEnvironment::SANDBOX);

    if (($response != null) && ($response->getMessages()->getResultCode() == "Ok") )
    {
        $validationMessages = $response->getMessages()->getMessage();
        echo "Response : " . $validationMessages[0]->getCode() . "  " .$validationMessages[0]->getText() . "\n";
    }
    else
    {
        echo "ERROR :  Validate Customer Payment Profile: Invalid response\n";
        $errorMessages = $response->getMessages()->getMessage();
        echo "Response : " . $errorMessages[0]->getCode() . "  " .$errorMessages[0]->getText() . "\n";
    }
    return $response;
}
?>