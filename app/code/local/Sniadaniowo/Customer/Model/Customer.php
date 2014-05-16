<?php
 
class Sniadaniowo_Customer_Model_Customer extends Mage_Customer_Model_Customer {
    
    public function getAbonamentExpiration($as_int = false){
        $_expires = $this->getData('abonament_expires');
        $_has_probation = $this->getData('abonament_probation');
        $result = 0;
        if($_expires == null){
            if($_has_probation == 0){
                $result = 7;
            }
            else{
                $result = 0;
            }
        }
        else{
            $result = new DateTime($_expires);
            $now = new DateTime();
            if($result<$now){
                $result=$now;
            }
        }
        
        $cart = Mage::getSingleton('checkout/cart');
        $abos = 0;
        foreach($cart->getQuote()->getAllItems() as $item){
            if($item->getProduct()->getSKU() == 'MK_AB'){
                $abos+=$item->getQty();
            }
        }
        
        if($abos > 0){
            if(is_int($result)){
                $result += $abos*30;
            }
            else{
                $result->add(new DateInterval('P'.($abos*30).'D'));
            }
        }
        if($as_int && $result instanceof DateTime){
            $diff = date_diff(new DateTime(), $result);
            $result = intval($diff->format('%R%a'));
        }
        else if(!$as_int && is_int($result)){
            $diff = $result;
            $result = new DateTime();
            $result->add(new DateInterval('P'.$diff.'D'));
        }
        return $result;
    }
    
    public static function getAbonamenExpirationForNotLogged($as_int = true){
        $result = 7;
        $cart = Mage::getSingleton('checkout/cart');
        $abos = 0;
        foreach($cart->getQuote()->getAllItems() as $item){
            if($item->getProduct()->getSKU() == 'MK_AB'){
                $abos+=$item->getQty();
            }
        }
        
        if($abos > 0){
            $result += $abos*30;
        }
        if(!$as_int && is_int($result)){
            $diff = $result;
            $result = new DateTime();
            $result->add(new DateInterval('P'.$diff.'D'));
        }
        return $result;
    }
    
    public function setAbonamentData($quote){
        //Zend_Debug::dump(get_class($quote));
    }
    
    public function getBoughtProducts(){
        $result = array();
        $orders = Mage::getResourceModel('sales/order_collection')
            ->addFieldToSelect('*')
            ->addFieldToFilter('customer_id', $this->getId())
            ->addAttributeToSort('created_at', 'DESC');
        foreach($orders as $o){
            $items = $o->getAllItems();
            if($this->isOrderInvalid($o)){
                continue;
            }
            foreach($items as $i){
                $status = $i->getStatusId();
                if($this->isStatusInvalid($status)){
                    continue;
                }
                $options = $i->getProductOptions();
                if(!empty($options['info_buyRequest']['day_week'])){
                    $dw = $options['info_buyRequest']['day_week'];
                    if(!isset($result[$dw])){
                        $result[$dw] = array(
                            'overpayment' => 0,
                            'items' => array()
                        );
                    }
                    if(!isset($options['pending_sub'])){
                    $options['pending_sub'] = 0;
                    }
                    if(!isset($options['sub'])){
                        $options['sub'] = 0;
                    }
                    $item_data = array(
                        'qty' => $i->getQtyOrdered()+$options['pending_sub'],
                        'sub' => -$options['pending_sub'],
                        'data' => $i
                    );
                    $result[$dw]['items'][] = $item_data;
                }
            }
        }
        return $result;
    }
    
    public function subItem($item_id, $quan){
        $orders = Mage::getResourceModel('sales/order_collection')
            ->addFieldToSelect('*')
            ->addFieldToFilter('customer_id', $this->getId())
            ->addAttributeToSort('created_at', 'DESC');
        foreach($orders as $o){
            $items = $o->getAllItems();
            if($this->isOrderInvalid($o)){
                continue;
            }
            foreach($items as $i){
                if($i->getId() != $item_id){
                    continue;
                }
                $status = $i->getStatusId();
                if($this->isStatusInvalid($status)){
                    continue;
                }
                $options = $i->getProductOptions();
                if(!isset($options['sub'])){
                    $options['sub'] = 0;
                }
                if(!isset($options['pending_sub'])){
                    $options['pending_sub'] = 0;
                }
                
                if($quan+$i->getQtyOrdered()+$options['sub']+$options['pending_sub'] < 0){
                    $quan = ($i->getQtyOrdered()+$options['sub']+$options['pending_sub'])*-1;
                }
                else if($quan+$options['pending_sub'] > 0){
                    $quan = -$options['pending_sub'];
                }
                $options['pending_sub']+=$quan;
                
                $i->setProductOptions($options);
                $i->save();
            }
        }
    }
    
    public function removeSubstraction($item_id){
        $orders = Mage::getResourceModel('sales/order_collection')
            ->addFieldToSelect('*')
            ->addFieldToFilter('customer_id', $this->getId())
            ->addAttributeToSort('created_at', 'DESC');
        foreach($orders as $o){
            $items = $o->getAllItems();
            if($this->isOrderInvalid($o)){
                continue;
            }
            foreach($items as $i){
                if($i->getId() != $item_id){
                    continue;
                }
                $status = $i->getStatusId();
                if($this->isStatusInvalid($status)){
                    continue;
                }
                $options = $i->getProductOptions();
                $options['pending_sub'] = 0;
                $i->setProductOptions($options);
                $i->save();
            }
        }
    }
    
    public function saveSubstraction(){
        $orders = Mage::getResourceModel('sales/order_collection')
            ->addFieldToSelect('*')
            ->addFieldToFilter('customer_id', $this->getId())
            ->addAttributeToSort('created_at', 'DESC');
        foreach($orders as $o){
            $items = $o->getAllItems();
            if($this->isOrderInvalid($o)){
                continue;
            }
            foreach($items as $i){
                $status = $i->getStatusId();
                if($this->isStatusInvalid($status)){
                    continue;
                }
                $options = $i->getProductOptions();
                if(!isset($options['pending_sub'])){
                    $options['pending_sub'] = 0;
                }
                if(!isset($options['sub'])){
                    $options['sub'] = 0;
                }
                $options['sub']+= $options['pending_sub'];
                $options['pending_sub'] = 0;
                $i->setProductOptions($options);
                $i->save();
            }
        }
    }
    
    public function getDayOverpay($day_week, $total = false){
        $result = 0;
        $orders = Mage::getResourceModel('sales/order_collection')
            ->addFieldToSelect('*')
            ->addFieldToFilter('customer_id', $this->getId())
            ->addAttributeToSort('created_at', 'DESC');
        foreach($orders as $o){
            $items = $o->getAllItems();
            if($this->isOrderInvalid($o)){
                continue;
            }
            foreach($items as $i){
                $status = $i->getStatusId();
                if($this->isStatusInvalid($status)){
                    continue;
                }
                $options = $i->getProductOptions();
                if(isset($options['info_buyRequest']['day_week']) && $options['info_buyRequest']['day_week'] == $day_week){
                    if(!isset($options['pending_sub'])){
                        $options['pending_sub'] = 0;
                    }
                    if(!isset($options['sub'])){
                        $options['sub'] = 0;
                    }
                    $to_sub = $total ? $options['sub']+$options['pending_sub'] : $options['pending_sub'];
                    $result += ($i->getPrice()*$to_sub)*-1;
                }
            }
        }
        return $result;
    }
    
    public function getOverpay($total = false){
        $result = 0;
        $orders = Mage::getResourceModel('sales/order_collection')
            ->addFieldToSelect('*')
            ->addFieldToFilter('customer_id', $this->getId())
            ->addAttributeToSort('created_at', 'DESC');
        foreach($orders as $o){
            $items = $o->getAllItems();
            if($this->isOrderInvalid($o)){
                continue;
            }
            foreach($items as $i){
                $status = $i->getStatusId();
                if($this->isStatusInvalid($status)){
                    continue;
                }
                $options = $i->getProductOptions();
                if(!isset($options['pending_sub'])){
                    $options['pending_sub'] = 0;
                }
                if(!isset($options['sub'])){
                    $options['sub'] = 0;
                }
                $to_sub = $total ? $options['sub']+$options['pending_sub'] : $options['pending_sub'];
                $result += ($i->getPrice()*$to_sub)*-1;
            }
        }
        return $result;
    }
    
    public function getCanBoughtProductBeAdded($product_id, $day_week){
        $orders = Mage::getResourceModel('sales/order_collection')
            ->addFieldToSelect('*')
            ->addFieldToFilter('customer_id', $this->getId())
            ->addAttributeToSort('created_at', 'DESC');
        foreach($orders as $o){
            $items = $o->getAllItems();
            if($this->isOrderInvalid($o)){
                continue;
            }
            foreach($items as $i){
                $status = $i->getStatusId();
                if($this->isStatusInvalid($status)){
                    continue;
                }
                $pid = $i->getProduct()->getId();
                if($product_id == $pid){
                    $options = $i->getProductOptions();
                    $item_dw = isset($options['info_buyReques']['day_week']) ? $options['info_buyReques']['day_week'] : null;
                    if(!isset($options['pending_sub'])){
                        $options['pending_sub'] = 0;
                    }
                    if(!isset($options['sub'])){
                        $options['sub'] = 0;
                    }
                    if($day_week == $item_dw && $options['sub']+$options['pending_sub'] < 0){
                        return $i->getId();
                    }
                }
            }
        }
        return false;
    }
    
    public function addDiscountToQuote(){
        $quote = Mage::getSingleton('checkout/cart')->getQuote();
        $discountAmount = $this->getOverpay();
        $total=$quote->getBaseSubtotal();
        $total_with_discount = $total - $discountAmount;
        if($total_with_discount < 0) $total_with_discount = 0;
        //Zend_Debug::dump($total_with_discount);
        $quote->setSubtotalWithDiscount($total_with_discount);
        $quote->setBaseSubtotalWithDiscount($total_with_discount);
        $quote->save();
        $canAddItems = $quote->isVirtual()? ('billing') : ('shipping');
        foreach ($quote->getAllAddresses() as $address) {
            
//            $address->setGrandTotal($address->getGrandTotal() - $discountAmount);
//            $address->setBaseGrandTotal($address->getBaseGrandTotal() - $discountAmount);
//            $address->save();
//            continue;
            $address->setSubtotal(0);
            $address->setBaseSubtotal(0);

            $address->setGrandTotal(0);
            $address->setBaseGrandTotal(0);

            $address->collectTotals();

            $quote->setSubtotal((float) $quote->getSubtotal() + $address->getSubtotal());
            $quote->setBaseSubtotal((float) $quote->getBaseSubtotal() + $address->getBaseSubtotal());

            $quote->setSubtotalWithDiscount(
                    (float) $quote->getSubtotalWithDiscount() + $address->getSubtotalWithDiscount()
            );
            $quote->setBaseSubtotalWithDiscount(
                    (float) $quote->getBaseSubtotalWithDiscount() + $address->getBaseSubtotalWithDiscount()
            );

            $quote->setGrandTotal((float) $quote->getGrandTotal() + $address->getGrandTotal());
            $quote->setBaseGrandTotal((float) $quote->getBaseGrandTotal() + $address->getBaseGrandTotal());

            $quote->save();

            $quote->setGrandTotal($quote->getBaseSubtotal() - $discountAmount)
                    ->setBaseGrandTotal($quote->getBaseSubtotal() - $discountAmount)
                    ->setSubtotalWithDiscount($quote->getBaseSubtotal() - $discountAmount)
                    ->setBaseSubtotalWithDiscount($quote->getBaseSubtotal() - $discountAmount)
                    ->save();


            if ($address->getAddressType() == $canAddItems) {
                //echo $address->setDiscountAmount; exit;
                $address->setSubtotalWithDiscount((float) $address->getSubtotalWithDiscount() - $discountAmount);
                $address->setGrandTotal((float) $address->getGrandTotal() - $discountAmount);
                $address->setBaseSubtotalWithDiscount((float) $address->getBaseSubtotalWithDiscount() - $discountAmount);
                $address->setBaseGrandTotal((float) $address->getBaseGrandTotal() - $discountAmount);
                if (false && $address->getDiscountDescription()) {
                    $address->setDiscountAmount(-($address->getDiscountAmount() - $discountAmount));
                    $address->setDiscountDescription($address->getDiscountDescription() . ', nadpłata');
                    $address->setBaseDiscountAmount(-($address->getBaseDiscountAmount() - $discountAmount));
                } else {
                    $address->setDiscountAmount(-($discountAmount));
                    $address->setDiscountTitle('nadpłata');
                    $address->setDiscountDescription('nadpłata');
                    $address->setBaseDiscountAmount(-($discountAmount));
                }
                $address->save();
            }//end: if
        } //end: foreach
        //var_dump(array_keys($quote->getTotals()));
    }
    
    protected function isStatusInvalid($status){
        //return false;
        return ($status == Mage_Sales_Model_Order_Item::STATUS_CANCELED || $status == Mage_Sales_Model_Order_Item::STATUS_RETURNED);
    }
    
    protected function isStateInvalid($state){
        return ($state == Mage_Sales_Model_Order::STATE_NEW || $state == Mage_Sales_Model_Order::STATE_PENDING_PAYMENT);
    }
    
    protected function isOrderInvalid($order){
        return false; //todo: zmienic na faktyczne sprawdzenie
        return $order->getBaseTotalDue() > 0;
    }
    
}

?>
