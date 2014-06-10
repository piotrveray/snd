<?php

class Sniadaniowo_General_Model_Observer {

    public function setDiscount($observer) {
        $quote=$observer->getEvent()->getQuote();
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $discount = $customer->addDiscountToQuote();
        return;
    }
    
    public function updateCustomerAfterOrder($observer){
        return; //todo: sprawdzić działanie
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $order = $observer->getEvent()->getOrder();
        $customer->setOverPayment($order);
        $customer->setAbonamentData($order);
        $customer->saveSubstraction();
        $customer->save();
    }
    
    public function checkCartChanges($observer){
        $quote=$observer->getEvent()->getCheckoutSession()->getQuote();
        if(count($quote->getAllItems()) > 0){
            Mage::getSingleton('core/session')->setCartWasNotEmpty(1);
        }
        else{
            Mage::getSingleton('core/session')->setCartWasNotEmpty(0); 
        } 
    }

}

?>