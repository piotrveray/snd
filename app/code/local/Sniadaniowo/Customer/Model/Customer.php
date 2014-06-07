<?php
 
class Sniadaniowo_Customer_Model_Customer extends Mage_Customer_Model_Customer {
    
    public function getAbonamentExpiration($as_int = false){
        $_expires = $this->getData('abonament_expires');
        $_has_probation = $this->getData('abonament_probation');
        $snhelper = Mage::helper('general');
        $result = $snhelper->getFirstPossibleOrderDay();
        if($_expires == null){
            if($_has_probation == 0){
                $result->add(new DateInterval('P7D'));
            }
        }
        else{
            $expires = new DateTime($_expires);
            if($expires>$result){
                $result=$expires;
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
            $diff = date_diff($snhelper->getFirstPossibleOrderDay(), $result);
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
        $snhelper = Mage::helper('general');
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
            $result = $snhelper->getFirstPossibleOrderDay();
            $result->add(new DateInterval('P'.$diff.'D'));
        }
        return $result;
    }
    
    public function getAbonamentPeriod(){
        $cart = Mage::getSingleton('checkout/cart');
        $abos = 0;
        $snhelper = Mage::helper('general');
        $snhelper->checkCart();
        foreach($cart->getQuote()->getAllItems() as $item){
            if($item->getProduct()->getSKU() == 'MK_AB'){
                $abos+=$item->getQty();
            }
        }
        $_expires = $this->getData('abonament_expires');
        $_expires_date = $snhelper->getDayDateObject($_expires);
        $first_day = $snhelper->getFirstPossibleOrderDay();
        if($this->getLeftFreeDays() > 0){
            $first_day->add(new DateInterval('P'.($this->getLeftFreeDays()).'D'));
        }
        if($_expires_date<$first_day){
            $_expires_date = $first_day;
        }
        $last_day = clone $first_day;
        $last_day->add(new DateInterval('P'.(($abos*30)).'D'));
        return ''.$first_day->format('d.m.y').' - '.$last_day->format('d.m.y').'';
    }
    
    public static function getAbonamentPeriodForNotLogged(){
        $cart = Mage::getSingleton('checkout/cart');
        $abos = 0;
        $snhelper = Mage::helper('general');
        $snhelper->checkCart();
        foreach($cart->getQuote()->getAllItems() as $item){
            if($item->getProduct()->getSKU() == 'MK_AB'){
                $abos+=$item->getQty();
            }
        }
        $first_day = $snhelper->getFirstPossibleOrderDay();
        $first_day->add(new DateInterval('P7D'));
        $last_day = clone $first_day;
        $last_day->add(new DateInterval('P'.(($abos*30)+1+7).'D')); //todo sprawdzenie czy przed 20
        return ''.$first_day->format('d.m.y').' - '.$last_day->format('d.m.y').'';
    }
    
    public function getLeftFreeDays(){
        $_has_probation = $this->getData('abonament_probation');
        if(!$_has_probation) return 7;
        $_probation_expires_val = $this->getData('probation_expires');
        if(!empty($_probation_expires_val)){
            try{
                $probation_expires = new DateTime($_probation_expires_val);
            }
            catch(Exception $e){
                $probation_expires = new DateTime();
            }
        }
        else{
            $probation_expires = new DateTime();
        }
        
        $diff = date_diff(new DateTime(), $probation_expires);
        $result = intval($diff->format('%R%a'));
        if($result < 0) $result = 0;
        if($result > 7) $result = 7;
        return $result;
        
    }
    
    public function setOverPayment($order){
        if($this->isOrderInvalid($order)){
            return false;
        }
        
        $items = $order->getAllItems();
        $customer_overpayment = floatval($this->getOverpayment());
        foreach($items as $i){
            $status = $i->getStatusId();
            if($this->isStatusInvalid($status)){
                continue;
            }
            $options = $i->getProductOptions();
            if(isset($options['pending_sub']) && is_int($options['pending_sub'])){
                $price = $i->getPrice();
                $customer_overpayment += $price*$options['pending_sub']*-1;
            }
        }
        $this->setOverpayment($customer_overpayment);
    }
    
    public function setAbonamentData($order){
        if($this->isOrderInvalid($order)){
            return false;
        }
        
        $snhelper = Mage::helper('general');
        $first_order = $snhelper->getFirstPossibleOrderDay();
        
        $_expires_val = $this->getData('abonament_expires');
        $_probation_expires_val = $this->getData('probation_expires');
        $_has_probation = $this->getData('abonament_probation');
        
        if(!empty($_expires_val)){
            try{
                $expires = new DateTime($_expires_val);
            }
            catch(Exception $e){
                $expires = new DateTime();
            }
        }
        else{
            $expires = new DateTime();
        }
        
        if(!empty($_probation_expires_val)){
            try{
                $probation_expires = new DateTime($_probation_expires_val);
            }
            catch(Exception $e){
                $probation_expires = new DateTime();
            }
        }
        else{
            $probation_expires = new DateTime();
        }
        
        if($probation_expires < $first_order){
            $probation_expires = clone $first_order;
        }
        
        $items = $o->getAllItems();
        $abo_days = 0;
        $has_items = false;
        
        foreach($items as $i){
            $product = $i->getProduct();
            if($product->getSKU() == 'MK_AB'){
                $abo_days += 30*$i->getQtyOrdered();
            }
            else{
                $has_items = true;
            }
        }
        
        if($has_items && $_has_probation == 0){
            $probation_expires->add(new DateInterval('P7D'));
            $this->setData('probation_expires', $probation_expires->format('Y-m-d'));
        }
        
        if($abo_days > 0){
            if($expires < $probation_expires){
                $expires = clone $first_order;
            }
            $expires->add(new DateInterval('P'.$abo_days.'D'));
            $this->setData('abonament_expires', $expires->format('Y-m-d'));
        }
        
        if($has_items || $abo_days > 0){
            $this->setData('abonament_probation', 1);
        }
        
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
    
    public function removeUnusedOverpayment($snhelper){
        if(!($snhelper instanceof Sniadaniowo_General_Helper_Data)){
            throw new Exception('Application error - invalid helper');
        }
        
        $first_day = $snhelper->getFirstPossibleOrderDay();
        
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
                $item_dw = isset($options['info_buyReques']['day_week']) ? $options['info_buyReques']['day_week'] : null;
                $item_day = $snhelper->getDayDateObject($item_dw);
                if($item_day<$first_day){
                    $options['pending_sub'] = 0;
                    $i->setProductOptions($options);
                    $i->save();
                }
            }
        }
        
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
