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

        // This is the fix allowing order to get the cancelled status
        foreach ($this->_order->getInvoiceCollection() as $invoice) {
            $invoice->cancel()->save();
        }

        $this->_order
            ->registerCancellation($this->_createIpnComment(''), false)
            ->save();
    }
}
