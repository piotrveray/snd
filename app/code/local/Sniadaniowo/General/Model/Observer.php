<?php

class Sniadaniowo_General_Model_Observer {

    public function setDiscount($observer) {
        $quote=$observer->getEvent()->getQuote();
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $discount = $customer->addDiscountToQuote();
        return;
    }
    
    public function updateCustomerAfterOrder($observer){
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $quote = $observer->getEvent()->getOrder()->getQuote();
        $customer->setAbonamentData($quote);
        $customer->saveSubstraction();
    }
    
    public function checkCartChanges($observer){
        $quote=$observer->getEvent()->getQuote();
        if(count($quote->getAllItems()) > 0){
            Mage::getSingleton('core/session')->setCartWasNotEmpty(true);
        }
        else{
            Mage::getSingleton('core/session')->setCartWasNotEmpty(false); 
        } 
    }

}

?>