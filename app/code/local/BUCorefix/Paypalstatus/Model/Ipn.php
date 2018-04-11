<?php
/**
 * Rewrite the core fix an issue with IPN notifications of "failed" payments
 */
class BUCorefix_Paypalstatus_Model_Ipn extends Mage_Paypal_Model_Ipn
{

    /**
    * @see https://www.magentocommerce.com/bug-tracking/issue/index/id/1041
    * @see https://stackoverflow.com/a/37028929/884734
    */
    protected function _registerPaymentFailure()
    {
        $this->_importPaymentInformation();

        // If the order has uncanceled invoices, the call to registerCancellation() throws an exception.
        // An exception means and the status never gets changed, and defaults to "processing".
        foreach ($this->_order->getInvoiceCollection() as $invoice) {
            $invoice->cancel()->save();
        }

        // The code below is identical to the parent::_registerPaymentFailure() function.
        $this->_order
            ->registerCancellation($this->_createIpnComment(''), false)
            ->save();
    }
}
