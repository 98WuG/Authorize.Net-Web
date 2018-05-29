/**
 * charge function
 *
 * Charges a user-specified amount to a card (also belonging to a user
 * specified profile/payment).
 */
function charge() {

	// Initial data from HTML form
	var profileId = document.getElementById("profile").value;
	var paymentId = document.getElementById("payment").value;
	var cost = document.getElementById("amount").value;
	var itemId = document.getElementById("items").value;
	var itemName = document.getElementById("items").text;

	// Charge credit card request
	var chargeReq =
		{
			"createTransactionRequest": [{
				"merchantAuthentication": [{
					"name": name,
					"transactionKey": transactionKey
				}],
				"transactionRequest": [{
					"transactionType": "authCaptureTransaction",
					"amount": cost,
					"profile": [{
						"customerProfileId": profileId,
						"paymentProfile": [{
							"paymentProfileId": paymentId,
						}]
					}]/*,
					"lineItems": [{
						"lineItem": [{
							"itemId": itemId,
							"name": itemName,
							"description": "",
							"quantity": "1",
							"unitPrice": cost
						}]
					}]
					*/
				}]
			}]
		};

	// Submit POST request with JQuery/AJAX
	$.ajax({
		type: "POST",
		url: "https://apitest.authorize.net/xml/v1/request.api",
		data: JSON.stringify(chargeReq),
		contentType: "application/json; charset=UTF-8",
		dataType: "json",
		// If successful, update page with relevant information
		success: function(data){
			document.getElementById("demo3").innerHTML
				= data.messages.resultCode + " " + data.messages.message[0].code + ": " + data.messages.message[0].text + "<br>"
			// If successful AND successful transaction, then give information about the card/auth code of successful transaction
			if(data.messages.resultCode === "Ok") {
  				document.getElementById("demo3").innerHTML = document.getElementById("demo3").innerHTML
					+ "Card: " + data.transactionResponse.accountType + " " + data.transactionResponse.accountNumber + "<br>"
					+ "Auth Code: " + data.transactionResponse.authCode;
			}
			console.log(data);
		},
		// Otherwise, update page with error information
		failure: function(errMsg) {
			document.getElementById("demo2").innerHTML = JSON.stringify(errMsg);
		}
	});
}