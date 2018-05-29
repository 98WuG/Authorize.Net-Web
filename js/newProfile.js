/**
 * submitPost Function
 *
 * Submits a POST request to the Authorize.Net server with the Opaque Data. This POST
 * request may vary based on intended use. Currently, it is set to authorize the card
 * with Authorize.Net.
 *
 */
function submitPost() {

	// Get initial data from HTML form
	var descriptor = document.getElementById("dataDescriptor").value;
	var value = document.getElementById("dataValue").value;
	var firstName = document.getElementById("firstName").value;
	var lastName = document.getElementById("lastName").value;
	var company = document.getElementById("company").value;
	var address = document.getElementById("address").value;
	var city = document.getElementById("city").value;
	var state = document.getElementById("state").value;
	var zip = document.getElementById("zip").value;
	var country = document.getElementById("country").value;
	var phone = document.getElementById("phone").value;
	var email = document.getElementById("email").value;

	// Log important data in console for debugging
	console.log("Descriptor: " + descriptor + "\nValue: " + value);

	// Create Customer Profile Request JSON
	function createJSON() {
		var json =
			{
				"createCustomerProfileRequest": [{
					"merchantAuthentication": [{
						"name": name,
						"transactionKey": transactionKey
					}],
					"profile": [{
						"email": email,
						"paymentProfiles": [{
							"billTo": [{
								"firstName": firstName,
								"lastName": lastName,
								"company": company,
								"address": address,
								"city": city,
								"state": state,
								"zip": zip,
								"country": country,
								"phoneNumber": phone
							}],
							"payment": [{
								"opaqueData": [{
									"dataDescriptor": descriptor,
									"dataValue": value
								}]
							}]
						}]
					}]
				}]
			};
		return json;
	}
	var request = createJSON();
			

	// Delay time in seconds - IMPORTANT
	// Must wait for authorize.net's records to update before attempting a transaction
	var delay = 7;

	// Update webpage to inform user that it is processing
	document.getElementById("test").innerHTML = "Processing... (Please allow up to " + (delay + 3) + " seconds)";

	// Post the transaction request
	setTimeout(function () {

		// Declare new profile and payment ID to store data later
		var profileId = "";
		var paymentId = "";

		// Submit POST request with JQuery/AJAX
		$.ajax({
			type: "POST",
			url: "https://apitest.authorize.net/xml/v1/request.api",
			data: JSON.stringify(request),
			contentType: "application/json; charset=UTF-8",
			dataType: "json",
			// If successful, log response in console, and print out relevant data
			success: function(data){
				console.log(data);
				// Update webpage with relevant information
				document.getElementById("demo").innerHTML
					= "Profile creation result:<br>"
					+ data.messages.resultCode + " " + data.messages.message[0].code + ": " + data.messages.message[0].text

				// If no syntactical error AND no service error (i.e. denied credit card), then print out relevant profile information
				if(data.messages.resultCode === "Ok") {

					// Update profile/payment ID's with proper data retrieved from Authorize.Net
					profileId = data.customerProfileId;
					paymentId = data.customerPaymentProfileIdList[0];

					// Update webpage to inform user
					document.getElementById("demo").innerHTML = document.getElementById("demo").innerHTML + "<br>"
						+ "Profile ID: " + profileId + "<br>"
						+ "Payment ID: " + paymentId;
					
					// Call validation method on the newly submitted payment
					validate(profileId, paymentId);
				}
			},
			// If there is error, print out relevant data
			failure: function(errMsg) {
				// Syntactical error of some sort
				document.getElementById("demo").innerHTML = JSON.stringify(errMsg);
			}
		});
	},delay * 1000);
}

/**
 * validate function
 *
 * Test the validity of a newly added payment method
 *
 * @param profileId - The Profile ID that the payment method belongs to
 * @param paymentId - The Payment ID to verify
 */
function validate(profileId, paymentId) {
	
	// Validation request
	var validateReq = 
		{
			"validateCustomerPaymentProfileRequest": [{
				"merchantAuthentication": [{
					"name": name,
					"transactionKey": transactionKey
				}],
				"customerProfileId": profileId,
				"customerPaymentProfileId": paymentId,
				"cardCode": "123",
				"validationMode": "liveMode"
			}]
		};
	
	// Submit POST request with JQuery/AJAX
	$.ajax({
		type: "POST",
		url: "https://apitest.authorize.net/xml/v1/request.api",
		data: JSON.stringify(validateReq),
		contentType: "application/json; charset=UTF-8",
		dataType: "json",
		// If successful, then print out relevant information
		success: function(data){
			document.getElementById("demo2").innerHTML
				= "Validate payment method result:<br>" +
				data.messages.resultCode + " " + data.messages.message[data.messages.message.length - 1].code + ": " + data.messages.message[0].text;
			document.getElementById("profile").value = profileId;
			document.getElementById("payment").value = paymentId;
			console.log(data);
		},
		// Otherwise, print out error
		failure: function(errMsg) {
			document.getElementById("demo2").innerHTML = JSON.stringify(errMsg);
		}
	});
}