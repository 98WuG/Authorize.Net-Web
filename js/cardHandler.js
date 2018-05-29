// Global vars
var name = "9Wq3Hvxt252";
var transactionKey = "4eM7tE29yw5UH2sP";

// Disable Enter key on form
window.addEventListener('keydown',function(e){if(e.keyIdentifier=='U+000A'||e.keyIdentifier=='Enter'||e.keyCode==13){if(e.target.nodeName=='INPUT'&&e.target.type!='textarea'){e.preventDefault();return false;}}},true);

/**
 * ResponseHandler Function
 *
 * Handles the response from the server - Stops and prints error to console if there's an error.
 * Otherwise, continues with payment with paymentFormUpdate
 *
 * @param response - JSON/XML response returned by the Authorize.Net server
 *
 */
function responseHandler(response) {
	// Reset demo outputs to blank
	document.getElementById("demo").innerHTML = "";
	document.getElementById("demo2").innerHTML = "";

	// If no email is filled in, stop immediately
	if (document.getElementById("email").value === "") {
		document.getElementById("demo").innerHTML = "Error: No email provided.";
	// Else, if the response is an error, stop and print error to console
	} else if (response.messages.resultCode === "Error") {
		var i = 0;
		while (i < response.messages.message.length) {
			console.log(
				response.messages.message[i].code + ": " +
				response.messages.message[i].text
			);
			i = i + 1;
		}
	// Else, everything checks out. Continue to Payment Form.
	} else {
		paymentFormUpdate(response.opaqueData);
	}
}

/**
 * paymentFormUpdate Function
 * 
 * Sets the invisible input fields to the Opaque Data for later use by the 
 * submitPost() function. Calls submit() on the form.
 *
 * @param opaqueData - The Opaque Data (One-Time-Use Token) returned by Authorize.Net.
 *                     Passed in by the responseHandler function.
 *
 */
function paymentFormUpdate(opaqueData) {
	document.getElementById("dataDescriptor").value = opaqueData.dataDescriptor;
	document.getElementById("dataValue").value = opaqueData.dataValue;

	document.getElementById("paymentForm").submit();
}
