<?php
require 'vendor/autoload.php';
require ('constants.php');

use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;

define("AUTHORIZENET_LOG_FILE", "phplog");

if(isset($_POST['email']) && isset($_POST['descriptor']) && isset($_POST['value'])) {
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
    $billTo->setFirstName("Ellen");
    $billTo->setLastName("Johnson");
    $billTo->setCompany("Souveniropolis");
    $billTo->setAddress("14 Main Street");
    $billTo->setCity("Pecan Springs");
    $billTo->setState("TX");
    $billTo->setZip("44628");
    $billTo->setCountry("USA");
    $billTo->setPhoneNumber("888-888-8888");
    $billTo->setfaxNumber("999-999-9999");


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
    $customerProfile->setMerchantCustomerId("M_" . time());
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

    if (($response != null) && ($response->getMessages()->getResultCode() == "Ok")) {
        echo "Succesfully created customer profile : " . $response->getCustomerProfileId() . "\n";
        $paymentProfiles = $response->getCustomerPaymentProfileIdList();
        echo "SUCCESS: PAYMENT PROFILE ID : " . $paymentProfiles[0] . "\n";
    } else {
        echo "ERROR :  Invalid response\n";
        $errorMessages = $response->getMessages()->getMessage();
        echo "Response : " . $errorMessages[0]->getCode() . "  " .$errorMessages[0]->getText() . "\n";
    }
    return $response;
}
?>