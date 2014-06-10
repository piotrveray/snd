<?php

class Sniadaniowo_Reports_ReportsController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $snhelper = Mage::helper('general');
        $first_order = $snhelper->getFirstPossibleOrderDay();
        $orders = Mage::getResourceModel('sales/order_collection')
            ->addFieldToSelect('*');
        $list = array();
        foreach($orders as $o){
            if(!$this->isOrderValid($o)){
                continue;
            }
            $items = $o->getAllItems();
            foreach($items as $i){
                if(!$this->isStatusValid($i)){
                    continue;
                }
                
                $options = $i->getProductOptions();
                $item_dw = isset($options['info_buyRequest']['day_week']) ? $options['info_buyRequest']['day_week'] : null;
                $item_dwo = $snhelper->getDayDateObject($item_dw, true);
                $sub = isset($options['sub']) ? $options['sub'] : 0;
                $qty = $i->getQtyOrdered() - $sub;
                $catid = $i->getProduct()->getCategoryIds();
                if(empty($item_dw) || $item_dwo === false || empty($catid) || $qty < 0 || !($item_dwo < $first_order)){
                    continue;
                }
                
                if(!isset($list[$item_dw])){
                    $list_item_dw = array(
                        'all_items' => 0,
                        'all_sum' => 0
                    );
                    foreach($this->getCategories() as $cat){
                        $list_item_dw[$cat->getId()] = array(
                            'items' => 0,
                            'sum' => 0
                        );
                    }
                    $list[$item_dw] = $list_item_dw;
                }
                
                foreach($catid as $cid){
                    if(!isset($list[$item_dw][$cid])){ // to nie powinno sie zdarzyć
                        continue 2;
                    }
                }
                
                $list[$item_dw]['all_items'] += $qty;
                $list[$item_dw]['all_sum'] += $i->getPrice()*$qty;
                foreach($catid as $cid){
                    $list[$item_dw][$cid]['items'] += $qty;
                    $list[$item_dw][$cid]['sum'] += $i->getPrice()*$qty;
                }
                
            }
        }
        
        krsort($list);
        
        //Zend_Debug::dump($list);
        
        $this->loadLayout();
        $block = $this->getLayout()->createBlock('adminhtml/template')->setTemplate('reports/index.phtml');
        $block->assign('list', $list);         
        $block->assign('categories', $this->getCategories());         
        $this->_addContent($block);
        
        $this->renderLayout();
    }
    
    public function daysummaryAction()
    {
        $day_week = $this->getRequest()->getDayweek();
        
        $snhelper = Mage::helper('general');
        
        $dwo = $snhelper->getDayDateObject($day_week, true);
        $orders = Mage::getResourceModel('sales/order_collection')
            ->addFieldToSelect('*');
        $list = array();
        foreach($orders as $o){
            if(!$this->isOrderValid($o)){
                continue;
            }
            $items = $o->getAllItems();
            foreach($items as $i){
                if(!$this->isStatusValid($i)){
                    continue;
                }
                
                $options = $i->getProductOptions();
                $item_dw = isset($options['info_buyRequest']['day_week']) ? $options['info_buyRequest']['day_week'] : null;
                $item_dwo = $snhelper->getDayDateObject($item_dw, true);
                $sub = isset($options['sub']) ? $options['sub'] : 0;
                $qty = $i->getQtyOrdered() - $sub;
                $catids = $i->getProduct()->getCategoryIds();
                if(empty($item_dw) || $item_dwo === false || empty($catids) || $qty < 0 || !($item_dwo != $dwo)){
                    continue;
                }
                
                $catid = $catids[0];
                $p = $i->getProduct();
                
                if(!isset($list[$catid])){
                    $list[$catid] = array(
                        'items' => array()
                    );
                }
                
                if(!isset($list[$catid][$p->getId()])){
                    $list[$catid][$p->getId()] = array(
                        'qty' => 0,
                        'sum' => 0
                    );
                }
                
                $list[$catid][$p->getId()]['qty'] += $qty;
                $list[$catid][$p->getId()]['sum'] += $i->getPrice()*$qty;
                
            }
        }
        
        Zend_Debug::dump($list);
    }
    
    public function dayordersAction()
    {
        $day_week = $this->getRequest()->getDayweek();
        
        $snhelper = Mage::helper('general');
        
        $dwo = $snhelper->getDayDateObject($day_week, true);
        $orders = Mage::getResourceModel('sales/order_collection')
            ->addFieldToSelect('*');
        $list = array();
        foreach($orders as $o){
            if(!$this->isOrderValid($o)){
                continue;
            }
            $items = $o->getAllItems();
            foreach($items as $i){
                if(!$this->isStatusValid($i)){
                    continue;
                }
                
                $options = $i->getProductOptions();
                $item_dw = isset($options['info_buyRequest']['day_week']) ? $options['info_buyRequest']['day_week'] : null;
                $item_dwo = $snhelper->getDayDateObject($item_dw, true);
                $sub = isset($options['sub']) ? $options['sub'] : 0;
                $qty = $i->getQtyOrdered() - $sub;
                $catids = $i->getProduct()->getCategoryIds();
                if(empty($item_dw) || $item_dwo === false || empty($catids) || $qty < 0 || !($item_dwo != $dwo)){
                    continue;
                }
                
                $catid = $catids[0];
                $p = $i->getProduct();
                
                if(!isset($list[$catid])){
                    $list[$catid] = array(
                        'items' => array()
                    );
                }
                
                if(!isset($list[$catid][$p->getId()])){
                    $list[$catid][$p->getId()] = array(
                        'qty' => 0,
                        'sum' => 0
                    );
                }
                
                $list[$catid][$p->getId()]['qty'] += $qty;
                $list[$catid][$p->getId()]['sum'] += $i->getPrice()*$qty;
                
            }
        }
        
        Zend_Debug::dump($list);
    }
    
    protected function getCategories(){
        $categories = Mage::getModel('catalog/category')
            ->getCollection()
            ->addAttributeToSelect('*')
            ->addAttributeToSort('position', 'ASC')
            ->addIsActiveFilter();
        return $categories;
    }
    
    /*
     * sprawdzenie płatności
     */
    
    protected function isOrderValid($order){
        return true;
    }
    
    protected function isStatusValid($item){
        $status = $item->getStatusId();
        return !($status == Mage_Sales_Model_Order_Item::STATUS_CANCELED || $status == Mage_Sales_Model_Order_Item::STATUS_RETURNED);
    }
}

?>