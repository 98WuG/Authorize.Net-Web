# Payment on the Web using Authorize.Net's API

PCI-Compliant credit card and ACH payments using Authorize.Net. The `index.html` page contains a demo with two client-side forms. Each of these forms connects with corresponding server-side `.php` files that use the Authorize.Net PHP API. Additional info regarding usage of the Authorize.Net sandbox is located in the initial information section in `index.html`.

## Constants

Relevant constants are stored in `php/constants.php`. To update API credentials, please update the keys here, as well as in the `data-apiLoginID` and the `data-clientKey` fields in the first submit button in `index.html`. 
