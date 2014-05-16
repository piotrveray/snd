<?php

class Sniadaniowo_General_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function checkCart(){
        
        //todo: sprawdzenie czy dany produkt jest dostepny w dany dzien
        $cart = Mage::getSingleton('checkout/cart');
        foreach($cart->getQuote()->getAllItems() as $ai){
            $_item_day_week_ob = $ai->getOptionByCode('day_week');
            if(is_object($_item_day_week_ob)){
                $_item_day_week = $_item_day_week_ob->getValue();
                $item_date = $this->getDayDateObject($_item_day_week);
                $deadline = new DateTime('T20:00');
                $deadline->add(new DateInterval('P1D'));
                if($item_date < $deadline){
                    $cart->removeItem($ai->getItemId());
                }
            }
            else{
                if($ai->getProduct()->getSKU() != 'MK_AB'){
                    $cart->removeItem($ai->getItemId());
                }
            }
        }
        $cart->save();
    }
    
    public function getDayName($day){
        $dob = $this->getDayDateObject($day);
        $day_names = $this->getDaysNames();
        $month_names = $this->getMonthsNames();
        return $day_names[intval($dob->format('N'))].' | '.$dob->format('j').' '.$month_names[intval($dob->format('n'))];
    }
    
    public function getDayDate($day){
        if($day instanceof DateTime){
            $day = $day->format('Y-m-d');
        }
        return $day;
    }
    
    public function getDayDateObject($day){
        $ob = new DateTime();
        try{
            $ob = new DateTime($day);
        }
        catch(Exception $e){
        }
        return $ob;
    }
    
    public function getDaysNames(){
        return array(
            1 => 'PN',
            2 => 'WT',
            3 => 'SR',
            4 => 'CZ',
            5 => 'PT',
            6 => 'SO',
            7 => 'ND',
        );
    }
    
    public function getMonthsNames(){
        return array(
            1 => 'Styczenia',
            2 => 'Lutego',
            3 => 'Marca',
            4 => 'Kwietnia',
            5 => 'Maja',
            6 => 'Czerwca',
            7 => 'Lipca',
            8 => 'Siepnia',
            9 => 'Wrzesnia',
            10 => 'Października',
            11 => 'Listopada',
            12 => 'Grudnia',
        );
    }
    
    
}

?>
