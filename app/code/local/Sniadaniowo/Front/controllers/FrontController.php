<?php

class Sniadaniowo_Front_FrontController extends Mage_Core_Controller_Front_Action
{    
    public function indexAction()
    {
        
    }
    
    public function verifyaddressAction()
    {
        
        $p = $_POST;
        
        if(isset($_POST['create_shipping_address'])){
            $city = $_POST['shipping_city'];
            $street = $_POST['shipping_street'];
            $number = $_POST['shipping_number'];
        }
        else if(isset($_POST['change_shipping_address'])){
            $city = $_POST['shipping']['city'];
            $street = $_POST['shipping']['street'][0];
            $number = $_POST['shipping']['number'];
        }
        else{
            $city = $_POST['city'];
            $street = $_POST['street'];
            $number = $_POST['number'];
        }
        
        $address_data  = array(
            'city' => $city,
            'street' => $street,
            'number' => $number,
        );
        
        $city = strtolower($city);
        $street = strtolower($street);
        $number = strtolower($number);
        
        Mage::getSingleton('core/session')->setCheckAddressData($address_data);
        
        
        
        $csv = Mage::getBaseDir() .'/var/csv/adresy.csv';
        $adr = array();
        
        if (($handle = fopen($csv, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 0, ";")) !== FALSE) {
                $adr[] = array(
                    'city' => strtolower($data[0]),
                    'street' => strtolower($data[1]),
                    'numbers' => strtolower($data[2]),
                );
            }
            fclose($handle);
        }
        
        $result = array(
            'ok' => 0
        );
        
        foreach($adr as $a){
            if($a['city'] == $city && $a['street'] == $street){
                foreach(explode(',', $a['numbers']) as $n){
                    if($n == $number){
                        $result['ok'] = '1';
                        break 2;
                    }
                }
            }
        }
        
        echo json_encode($result);
    }
    
    public function autocompleteAction()
    {
        
        $csv = Mage::getBaseDir() .'/var/csv/adresy_wszystkie.csv';
        $adr = array();
        $result = array();
        
        $t = strtolower($_GET['term']);
        if(empty($t)) return;
        
        if (($handle = fopen($csv, "r")) !== FALSE) {
            $first = true;
            while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {
                if($first){
                    $first = false;
                    continue;
                }
                $adr[] = array(
                    'city' => $data[0],
                    'street' => $data[2],
                    'number' => $data[3],
                );
            }
            fclose($handle);
        }
        
        switch($_GET['type']){
            case 'city' :
                foreach($adr as $a){
                    if(strpos(strtolower($a['city']), $t) !== false){
                        $result[] = $a['city'];
                    }
                }
                break;
            case 'street' :
                $city = strtolower($_GET['city']);
                foreach($adr as $a){
                    if(((strlen($city) && strtolower($a['city']) == $city) || strlen($city) == 0) && strpos(strtolower($a['street']), $t) !== false){
                        $result[] = $a['street'];
                    }
                }
                
                break;
            case 'number' :
                $city = strtolower($_GET['city']);
                $street = strtolower($_GET['street']);
                foreach($adr as $a){
                    if(((strlen($city) && strtolower($a['city']) == $city)) && ((strlen($street) && strtolower($a['street']) == $street)) && strpos(strtolower($a['number']), $t) !== false){
                        $result[] = $a['number'];
                    }
                }
                break;
        }
        
        $result = array_unique($result);
        sort($result);
        
        echo json_encode($result);
    }
    
    public function gdziedostarczamyAction(){
        $this->loadLayout();
        $this->renderLayout();
    }
}

?>