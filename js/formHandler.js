function newProfile() {
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

	$.ajax({
		url: "php/newProfile.php",
		type: 'post',
		data: {
			"descriptor": descriptor,
			"value": value,
			"firstName": firstName,
			"lastName": lastName,
			"company": company,
			"address": address,
			"city": city,
			"state": state,
			"zip": zip,
			"country": country,
			"phone": phone,
			"email": email
		},
		success: function(response) {
			document.getElementById("demo").innerHTML = response;
		},
		failure: function(error) {
			document.getElementById("demo").innerHTML = error;
		}
	});
}


function chargeCard() {
	var profileId = document.getElementById("profile").value;
	var paymentId = document.getElementById("payment").value;
	var amount = document.getElementById("amount").value;
	var temp = document.getElementById("items");
	var item = temp.options[temp.selectedIndex].text;
	var itemId = temp.options[temp.selectedIndex].value;
	$.ajax({
		url: "php/chargeCard.php",
		type: 'post',
		data: {
			"profileId": profileId,
			"paymentId": paymentId,
			"amount": amount,
			"item": item,
			"itemId": itemId
		},
		success: function(response) {
			document.getElementById("demo3").innerHTML =
				response;
		},
		failure: function(error) {
			document.getElementById("demo3").innerHTML = error;
		}
	});
}