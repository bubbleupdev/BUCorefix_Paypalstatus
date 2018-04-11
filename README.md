# BUCorefix_Paypalstatus
This module fixes an issue where PayPal e-check failures don't set the proper order status, which can cause unpaid orders to be shipped.

## Installation and Setup Instructions
1. Download [the zip file](https://github.com/bubbleupdev/BUCorefix_Paypalstatus/archive/master.zip), or `git clone` the repo
2. Copy all of the contents from the `BUCorefix_Paypalstatus` directory into your Magento Root directory. This can be done with drag-and-drop. You do not need to copy the `LICENSE` and `README.md` files.
3. Clear caches if enabled.

## Testing
Testing this would be very tedious, since you'd need to reproduce a "Failed" PayPal e-check payment. Luckily, it appears that the Magento core devs who developed this function had testing in mind, and used a dependency injection pattern to make it trivial. Here's some example code to get you started:
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
  'invoice' => '100058137', // Put a Magento increment_id here
  'txn_id' => '04S87540L2309371A', // Put that order's PayPal transaction ID here
  'payment_status' => 'Failed'
  // Not sure how many other fields Magento requires.
  // Try getting the IPN data from a successful sandbox request using Mage::log(var_export($thatData));
  // Then just change the payment_status field.
);

Mage::getModel('paypal/ipn')->processIpnRequest($ipnPayload, new MockHttpClient()); // This is what Magento's controller calls during a normal IPN request.
```
