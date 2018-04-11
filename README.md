# BUCorefix_Paypalstatus
This module fixes an issue where PayPal e-check failures don't set the proper order status, which can cause unpaid orders to be shipped.

## Installation and Setup Instructions
1. Download [the zip file](https://github.com/bubbleupdev/BUCorefix_Paypalstatus/archive/master.zip), or `git clone` the repo
2. Copy all of the contents from the `BUCorefix_Paypalstatus` directory into your Magento Root directory. This can be done with drag-and-drop. You do not need to copy the `LICENSE` and `README.md` files.
3. Clear caches if enabled.

## Testing
In theory, once installed, the module will automatically start working. This doesn't mean you shouldn't test. Testing this "manually" would be very tedious, since you'd need to somehow trigger a "Failed" PayPal notification. As a developer, you can use PHP to verify that this is working.

We can't simply send an HTTP request to the IPN endpoing, because Magento will try to verify it by making an API call to PayPal. Obviously this will fail if it's a fake IPN request. Luckily, it appears that the Magento core devs who developed this function had testing in mind, and used a dependency injection pattern to make it trivial. Here's some example code to get you started:
```
<?php

class MockHttpClient extends Varien_Http_Adapter_Curl {
    function read() {
        // Make Magento think that PayPal said "VERIFIED", no matter what they actually said...
        return "HTTP/1.1 200 OK\n\nVERIFIED";
    }
}

require_once('app/Mage.php');
Mage::app();

$ipnPayload = array (
  'invoice' => '100058137', // Put a Magento increment_id here, from an order that used PayPal as the payment method.
  'txn_id' => '04S87540L2309371A', // Put that order's PayPal transaction ID here.
  'payment_status' => 'Failed'
  // Not sure how many other fields Magento requires.
  // If this doesn't work, try getting the IPN data from a successful sandbox request by placing an e-check order with XDEBUG running.
  // Or if you don't have XDEBUG, Mage::log(var_export($_POST));
  // Then just change the payment_status field.
);

Mage::getModel('paypal/ipn')->processIpnRequest($ipnPayload, new MockHttpClient()); // This is what Magento's controller calls during a normal IPN request.
```
