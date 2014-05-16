<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Checkout
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Shopping cart controller
 */
class Mage_Checkout_CartController extends Mage_Core_Controller_Front_Action
{
    /**
     * Action list where need check enabled cookie
     *
     * @var array
     */
    protected $_cookieCheckActions = array('add');

    /**
     * Retrieve shopping cart model object
     *
     * @return Mage_Checkout_Model_Cart
     */
    protected function _getCart()
    {
        return Mage::getSingleton('checkout/cart');
    }

    /**
     * Get checkout session model instance
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Get current active quote instance
     *
     * @return Mage_Sales_Model_Quote
     */
    protected function _getQuote()
    {
        return $this->_getCart()->getQuote();
    }

    /**
     * Set back redirect url to response
     *
     * @return Mage_Checkout_CartController
     * @throws Mage_Exception
     */
    protected function _goBack()
    {
        $returnUrl = $this->getRequest()->getParam('return_url');
        if ($returnUrl) {

            if (!$this->_isUrlInternal($returnUrl)) {
                throw new Mage_Exception('External urls redirect to "' . $returnUrl . '" denied!');
            }

            $this->_getSession()->getMessages(true);
            $this->getResponse()->setRedirect($returnUrl);
        } elseif (!Mage::getStoreConfig('checkout/cart/redirect_to_cart')
            && !$this->getRequest()->getParam('in_cart')
            && $backUrl = $this->_getRefererUrl()
        ) {
            $this->getResponse()->setRedirect($backUrl);
        } else {
            if (($this->getRequest()->getActionName() == 'add') && !$this->getRequest()->getParam('in_cart')) {
                $this->_getSession()->setContinueShoppingUrl($this->_getRefererUrl());
            }
            $this->_redirect('checkout/cart');
        }
        return $this;
    }

    /**
     * Initialize product instance from request data
     *
     * @return Mage_Catalog_Model_Product || false
     */
    protected function _initProduct()
    {
        $productId = (int) $this->getRequest()->getParam('product');
        if ($productId) {
            $product = Mage::getModel('catalog/product')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($productId);
            if ($product->getId()) {
                return $product;
            }
        }
        return false;
    }

    /**
     * Shopping cart display action
     */
    public function indexAction()
    {
        $cart = $this->_getCart();
        if ($cart->getQuote()->getItemsCount()) {
            $cart->init();
            $cart->save();

            if (!$this->_getQuote()->validateMinimumAmount()) {
                $minimumAmount = Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())
                    ->toCurrency(Mage::getStoreConfig('sales/minimum_order/amount'));

                $warning = Mage::getStoreConfig('sales/minimum_order/description')
                    ? Mage::getStoreConfig('sales/minimum_order/description')
                    : Mage::helper('checkout')->__('Minimum order amount is %s', $minimumAmount);

                $cart->getCheckoutSession()->addNotice($warning);
            }
        }

        // Compose array of messages to add
        $messages = array();
        foreach ($cart->getQuote()->getMessages() as $message) {
            if ($message) {
                // Escape HTML entities in quote message to prevent XSS
                $message->setCode(Mage::helper('core')->escapeHtml($message->getCode()));
                $messages[] = $message;
            }
        }
        $cart->getCheckoutSession()->addUniqueMessages($messages);

        /**
         * if customer enteres shopping cart we should mark quote
         * as modified bc he can has checkout page in another window.
         */
        $this->_getSession()->setCartWasUpdated(true);

        Varien_Profiler::start(__METHOD__ . 'cart_display');
        $this
            ->loadLayout()
            ->_initLayoutMessages('checkout/session')
            ->_initLayoutMessages('catalog/session')
            ->getLayout()->getBlock('head')->setTitle($this->__('Shopping Cart'));
        $this->renderLayout();
        Varien_Profiler::stop(__METHOD__ . 'cart_display');
    }

    /**
     * Add product to shopping cart action
     *
     * @return Mage_Core_Controller_Varien_Action
     * @throws Exception
     */
    public function addAction()
    {
        $cart   = $this->_getCart();
        $params = $this->getRequest()->getParams();
        if($params['isAjax'] == 1){
            $hour = date('h');
            $time_zone = date('a');
            $hour = $hour + 1;

        if( $hour > 7 && $time_zone == 'pm' ){
            $response['datetime'] = TRUE;
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
            return;
        }else{
                

            $nav = $_GET['nav'];

            $response = array();
            try {

                $weeks_arg = array();
                /** Split weeks from calendar **/
                if( $nav == 2 || $nav == 3){
                    $weeks_arg[0] = $_GET['weeks'];
                }else{
                    
                    
                    $single_variable = explode("&", $_GET['weeks'] ); //split out between weeks%5B%5D=PN-10-Marzec & weeks%5B%5D=WT-11-Marzec

                    $response['single_variable'] = $single_variable;

                    for( $i = 0; $i < count( $single_variable ); $i++ ){

                        $variable = explode("=", $single_variable[$i] ); //split out between weeks%5B%5D = PN-10-Marzec

                        $weeks_arg[$i] = $variable[1];

                    }
                }
                /** / End Split weeks from calendar **/

                $myData = Mage::getSingleton('core/session')->getWeeksCalendar();

                if( $nav == 3){ //Remove item from calendar and cart
                    $arg_array = array(
                        'weeks' => $_GET['weeks'],
                        'id_product' => $_GET['id_product'],
                        'price' => $_GET['price'],
                        'sum' => $_GET['sum'],
                        'count' => $_GET['count'],
                    );

                    $session= Mage::getSingleton('checkout/session');
                    $cartHelper = Mage::helper('checkout/cart');
                    $array_session = array();


                    foreach($session->getQuote()->getAllItems() as $item)
                    {

                       $productid = $item->getProductId();
                       $productsku = $item->getSku();
                       $productname = $item->getName();
                       $productqty = $item->getQty();
                       $price = $item->getBaseCalculationPrice();

                       if( $productid == $_GET['id_product'] ){

                            $quote_count = $_GET['count'];

                                if( $item->getQty() == 1 ){
                                    $cartHelper->getCart()->removeItem($item->getItemId())->save();
                                }
                                else if($item->getQty() > 1){
                                    $item->setQty($item->getQty() - $quote_count);
                                    $cartHelper->getCart()->save();
                                }

                            $itemQty = $item->getQty();

                            $array_session['productid'] = $item->getProductId();
                            $array_session['productsku'] = $item->getSku();
                            $array_session['productname'] = $item->getName();
                            $array_session['productqty'] = $item->getQty();
                            $array_session['price'] = $item->getBaseCalculationPrice();
                       }

                    }
                    for($i = 0; $i < count($myData); $i++ ){
                        if( $myData[$i]['data'] == $_GET['weeks'] ){

                            for( $k = 0; $k < count( $myData[$i]['product'] ); $k++ ){
                                if( $myData[$i]['product'][$k]['id'] == $_GET['id_product'] ){
                                    if( $myData[$i]['product'][$k]['count'] > 0 ){
                                        $myData[$i]['product'][$k]['count'] = $myData[$i]['product'][$k]['count'] - $_GET['count'];
                                    }
                                }
                            }

                            $myData[$i]['sum'] = $myData[$i]['sum'] - ( $_GET['price'] * $_GET['count'] );

                        }
                    }
               
                    $array_weeks = $myData;

                    Mage::getSingleton('core/session')->setWeeksCalendar($array_weeks);

                    $response['arg'] = $arg_array;
                    $response['myData'] = $myData;
                    $response['arg_session'] = $array_session;
                    $response['product_url'] = $_GET['product_url'];
                    $response['toplink'] = $toplink;
                    $response['sidebar'] = $sidebar;
                    $response['product_date'] = $array_weeks;

                    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
                    return;
                }


                

                if (isset($params['qty'])) {
                    $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                    );
                    $params['qty'] = $filter->filter($params['qty']);
                }
 
                $product = $this->_initProduct();
                $related = $this->getRequest()->getParam('related_product');
                
                
                /**
                 * Check product availability
                 */

                if (!$product) {
                    $response['status'] = 'ERROR';
                    $response['message'] = $this->__('Unable to find Product ID');
                }

                for( $i = 0; $i < count($weeks_arg); $i++ ){
                    $cart->addProduct($product, $params);   
                }
                
                if (!empty($related)) {
                    $cart->addProductsByIds(explode(',', $related));
                }
 
                $cart->save();
                
                $this->_getSession()->setCartWasUpdated(true);
        
                /**
                 * @todo remove wishlist observer processAddToCart
                 */
                Mage::dispatchEvent('checkout_cart_add_product_complete',
                array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
                );
                
                if (!$cart->getQuote()->getHasError()){

                    $mk_product_id = Mage::helper('core')->htmlEscape($product->getId());
                    $mk_product_name = Mage::helper('core')->htmlEscape($product->getName());
                    $mk_product_price = Mage::helper('core')->htmlEscape($product->getPrice());


                    $message = $this->__('%s was added to your shopping cart.', Mage::helper('core')->htmlEscape($product->getName()));
                    $response['status'] = 'SUCCESS';
                    $response['message'] = $message;
                    //New Code Here
                    $this->loadLayout();
                    $toplink = $this->getLayout()->getBlock('top.links')->toHtml();
                    $sidebar_block = $this->getLayout()->getBlock('cart_sidebar');
                    Mage::register('referrer_url', $this->_getRefererUrl());
                    $sidebar = $sidebar_block->toHtml();
                  
                /** Insert Value to Session Weeks **/
                $ilosc = count($myData);

                if( count( $myData ) == 0 ){

                    $byl = 'NIE';
                    for( $i = 0, $j = 0; $i < count($weeks_arg); $i++ ){

                        $weeks_session_arg[$i] = array(

                            'data' => $weeks_arg[$i],
                                'product' => array(
                                    $j => array(
                                        'id' => $mk_product_id,
                                        'name' => $mk_product_name,
                                        'price' => $mk_product_price,
                                        'count' => 1,
                                        'product_url' => $_GET['product_url'],
                                    ),
                                ),
                            'sum' => $mk_product_price,

                        );
                    }

                   $array_weeks = $weeks_session_arg;

                }else{
                    
                    $sum_price = 0;
                    $sum_price_new = 0;
                    $sum_count = 1;
                    $count = 0;
                    $days_myData = array(); //help tab checking if weeks_arg aren't in myData

                    $new_row = TRUE; // if date dont exists create new table below loop

                    for( $i = 0; $i < count( $myData ); $i++ ){
                        
                        for( $j = 0; $j < count( $weeks_arg ); $j++ ){

                            $new_row = TRUE;

                            if( $myData[$i]['data'] == $weeks_arg[$j] ){

                                $new_row = FALSE;

                                $bool = TRUE;
                               for( $k = 0; $k < count( $myData[$i]['product'] ); $k++ ){

                                    if ( $myData[$i]['product'][$k]['id'] == $mk_product_id ){
                                    
                                        $myData[$i]['product'][$k]['count'] = $myData[$i]['product'][$k]['count'] + 1;  
                                    
                                        $bool = FALSE;

                                    }

                                    $sum_price = $sum_price + ( $myData[$i]['product'][$k]['price'] * $myData[$i]['product'][$k]['count'] );
                                   
                               }
                                    if( $bool ){

                                        $keys = array_keys($myData[$i]['product']);
                                        $last = end($keys);

                                        $myData[$i]['product'][$last + 1]['id'] = $mk_product_id;
                                        $myData[$i]['product'][$last + 1]['name'] = $mk_product_name; 
                                        $myData[$i]['product'][$last + 1]['price'] =  $mk_product_price;
                                        $myData[$i]['product'][$last + 1]['count'] = 1;
                                        $myData[$i]['product'][$last + 1]['product_url'] = $_GET['product_url'];
                                        $sum_price_new = $mk_product_price;

                                    }
                            
                                $myData[$i]['sum'] = $sum_price_new + $sum_price;
                                
                            }

                        } // End Loop weeks
                        $sum_price = 0;  

                        $days_myData[$i] = $myData[$i]['data'];        
                    } // End Loop myData
                    
                    $array_weeks = $myData; 
                } 

            if( count($days_myData) > 0 ){
                $wynik = array();
                $response['wynik'] = $days_myData;
                $j = 0;
                for( $i = 0; $i < count( $weeks_arg ); $i++ ){
                    if( in_array( $weeks_arg[$i], $days_myData ) ){

                    }else{
                        $wynik[$j] = $weeks_arg[$i];
                        $j++;
                    }
                }
                $result_help = $wynik;

                $response['array_unique'] = $result_help;
            }
                if( count($result_help) > 0 ){

                    for( $k = 0; $k < count($result_help); $k++ ){
                        $keys = array_keys($myData);
                        $last = end($keys);  
                        $myData[$last + 1] = array(
                            'data' => $result_help[$k],
                            'product' => array(
                                    0 => array(
                                        'id' => $mk_product_id,
                                        'name' => $mk_product_name,
                                        'price' => $mk_product_price,
                                        'count' => 1,
                                        'product_url' => $_GET['product_url'],
                                    ),
                                ),
                            'sum' => $mk_product_price,
                        ); 
                    }
                    $array_weeks = $myData;
                    
                }
                /** / End Insert Value to Session Weeks **/
                    
                    Mage::getSingleton('core/session')->setWeeksCalendar($array_weeks);

                    
                    $response['product_url'] = $_GET['product_url'];
                    $response['toplink'] = $toplink;
                    $response['sidebar'] = $sidebar;
                    $response['product_date'] = $array_weeks;
                    $response['product_date_count'] = count( $array_weeks );
                }
            } catch (Mage_Core_Exception $e) {
                $msg = "";
                if ($this->_getSession()->getUseNotice(true)) {
                    $msg = $e->getMessage();
                } else {
                    $messages = array_unique(explode("\n", $e->getMessage()));
                    foreach ($messages as $message) {
                        $msg .= $message.'<br/>';
                    }
                }
 


                $response['status'] = 'ERROR';
                $response['message'] = $msg;
                
            } catch (Exception $e) {
                $response['status'] = 'ERROR';
                $response['message'] = $this->__('Cannot add the item to shopping cart.');
                Mage::logException($e);
            }
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
            return;
        } // End Check if time 7 p.m
        }else{
            return parent::addAction();
        }
    }

    /**
     * Add products in group to shopping cart action
     */
    public function addgroupAction()
    {
        $orderItemIds = $this->getRequest()->getParam('order_items', array());

        if (!is_array($orderItemIds) || !$this->_validateFormKey()) {
            $this->_goBack();
            return;
        }

        $itemsCollection = Mage::getModel('sales/order_item')
            ->getCollection()
            ->addIdFilter($orderItemIds)
            ->load();
        /* @var $itemsCollection Mage_Sales_Model_Mysql4_Order_Item_Collection */
        $cart = $this->_getCart();
        foreach ($itemsCollection as $item) {
            try {
                $cart->addOrderItem($item, 1);
            } catch (Mage_Core_Exception $e) {
                if ($this->_getSession()->getUseNotice(true)) {
                    $this->_getSession()->addNotice($e->getMessage());
                } else {
                    $this->_getSession()->addError($e->getMessage());
                }
            } catch (Exception $e) {
                $this->_getSession()->addException($e, $this->__('Cannot add the item to shopping cart.'));
                Mage::logException($e);
                $this->_goBack();
            }
        }
        $cart->save();
        $this->_getSession()->setCartWasUpdated(true);
        $this->_goBack();
    }

    /**
     * Action to reconfigure cart item
     */
    public function configureAction()
    {
        // Extract item and product to configure
        $id = (int) $this->getRequest()->getParam('id');
        $quoteItem = null;
        $cart = $this->_getCart();
        if ($id) {
            $quoteItem = $cart->getQuote()->getItemById($id);
        }

        if (!$quoteItem) {
            $this->_getSession()->addError($this->__('Quote item is not found.'));
            $this->_redirect('checkout/cart');
            return;
        }

        try {
            $params = new Varien_Object();
            $params->setCategoryId(false);
            $params->setConfigureMode(true);
            $params->setBuyRequest($quoteItem->getBuyRequest());

            Mage::helper('catalog/product_view')->prepareAndRender($quoteItem->getProduct()->getId(), $this, $params);
        } catch (Exception $e) {
            $this->_getSession()->addError($this->__('Cannot configure product.'));
            Mage::logException($e);
            $this->_goBack();
            return;
        }
    }

    /**
     * Update product configuration for a cart item
     */
    public function updateItemOptionsAction()
    {
        $cart   = $this->_getCart();
        $id = (int) $this->getRequest()->getParam('id');
        $params = $this->getRequest()->getParams();

        if (!isset($params['options'])) {
            $params['options'] = array();
        }
        try {
            if (isset($params['qty'])) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                $params['qty'] = $filter->filter($params['qty']);
            }

            $quoteItem = $cart->getQuote()->getItemById($id);
            if (!$quoteItem) {
                Mage::throwException($this->__('Quote item is not found.'));
            }

            $item = $cart->updateItem($id, new Varien_Object($params));
            if (is_string($item)) {
                Mage::throwException($item);
            }
            if ($item->getHasError()) {
                Mage::throwException($item->getMessage());
            }

            $related = $this->getRequest()->getParam('related_product');
            if (!empty($related)) {
                $cart->addProductsByIds(explode(',', $related));
            }

            $cart->save();

            $this->_getSession()->setCartWasUpdated(true);

            Mage::dispatchEvent('checkout_cart_update_item_complete',
                array('item' => $item, 'request' => $this->getRequest(), 'response' => $this->getResponse())
            );
            if (!$this->_getSession()->getNoCartRedirect(true)) {
                if (!$cart->getQuote()->getHasError()) {
                    $message = $this->__('%s was updated in your shopping cart.', Mage::helper('core')->escapeHtml($item->getProduct()->getName()));
                    $this->_getSession()->addSuccess($message);
                }
                $this->_goBack();
            }
        } catch (Mage_Core_Exception $e) {
            if ($this->_getSession()->getUseNotice(true)) {
                $this->_getSession()->addNotice($e->getMessage());
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->_getSession()->addError($message);
                }
            }

            $url = $this->_getSession()->getRedirectUrl(true);
            if ($url) {
                $this->getResponse()->setRedirect($url);
            } else {
                $this->_redirectReferer(Mage::helper('checkout/cart')->getCartUrl());
            }
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('Cannot update the item.'));
            Mage::logException($e);
            $this->_goBack();
        }
        $this->_redirect('*/*');
    }

    /**
     * Update shopping cart data action
     */
    public function updatePostAction()
    {
        if (!$this->_validateFormKey()) {
            $this->_redirect('*/*/');
            return;
        }

        $updateAction = (string)$this->getRequest()->getParam('update_cart_action');

        switch ($updateAction) {
            case 'empty_cart':
                $this->_emptyShoppingCart();
                break;
            case 'update_qty':
                $this->_updateShoppingCart();
                break;
            default:
                $this->_updateShoppingCart();
        }

        $this->_goBack();
    }

    /**
     * Update customer's shopping cart
     */
    protected function _updateShoppingCart()
    {
        try {
            $cartData = $this->getRequest()->getParam('cart');
            if (is_array($cartData)) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                foreach ($cartData as $index => $data) {
                    if (isset($data['qty'])) {
                        $cartData[$index]['qty'] = $filter->filter(trim($data['qty']));
                    }
                }
                $cart = $this->_getCart();
                if (! $cart->getCustomerSession()->getCustomer()->getId() && $cart->getQuote()->getCustomerId()) {
                    $cart->getQuote()->setCustomerId(null);
                }

                $cartData = $cart->suggestItemsQty($cartData);
                $cart->updateItems($cartData)
                    ->save();
            }
            $this->_getSession()->setCartWasUpdated(true);
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError(Mage::helper('core')->escapeHtml($e->getMessage()));
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('Cannot update shopping cart.'));
            Mage::logException($e);
        }
    }

    /**
     * Empty customer's shopping cart
     */
    protected function _emptyShoppingCart()
    {
        try {
            $this->_getCart()->truncate()->save();
            $this->_getSession()->setCartWasUpdated(true);
        } catch (Mage_Core_Exception $exception) {
            $this->_getSession()->addError($exception->getMessage());
        } catch (Exception $exception) {
            $this->_getSession()->addException($exception, $this->__('Cannot update shopping cart.'));
        }
    }

    /**
     * Delete shoping cart item action
     */
    public function deleteAction()
    {
        $id = (int) $this->getRequest()->getParam('id');
        if ($id) {
            try {
                $this->_getCart()->removeItem($id)
                  ->save();
            } catch (Exception $e) {
                $this->_getSession()->addError($this->__('Cannot remove the item.'));
                Mage::logException($e);
            }
        }
        $this->_redirectReferer(Mage::getUrl('*/*'));
    }

    /**
     * Initialize shipping information
     */
    public function estimatePostAction()
    {
        $country    = (string) $this->getRequest()->getParam('country_id');
        $postcode   = (string) $this->getRequest()->getParam('estimate_postcode');
        $city       = (string) $this->getRequest()->getParam('estimate_city');
        $regionId   = (string) $this->getRequest()->getParam('region_id');
        $region     = (string) $this->getRequest()->getParam('region');

        $this->_getQuote()->getShippingAddress()
            ->setCountryId($country)
            ->setCity($city)
            ->setPostcode($postcode)
            ->setRegionId($regionId)
            ->setRegion($region)
            ->setCollectShippingRates(true);
        $this->_getQuote()->save();
        $this->_goBack();
    }

    /**
     * Estimate update action
     *
     * @return null
     */
    public function estimateUpdatePostAction()
    {
        $code = (string) $this->getRequest()->getParam('estimate_method');
        if (!empty($code)) {
            $this->_getQuote()->getShippingAddress()->setShippingMethod($code)/*->collectTotals()*/->save();
        }
        $this->_goBack();
    }

    /**
     * Initialize coupon
     */
    public function couponPostAction()
    {
        /**
         * No reason continue with empty shopping cart
         */
        if (!$this->_getCart()->getQuote()->getItemsCount()) {
            $this->_goBack();
            return;
        }

        $couponCode = (string) $this->getRequest()->getParam('coupon_code');
        if ($this->getRequest()->getParam('remove') == 1) {
            $couponCode = '';
        }
        $oldCouponCode = $this->_getQuote()->getCouponCode();

        if (!strlen($couponCode) && !strlen($oldCouponCode)) {
            $this->_goBack();
            return;
        }

        try {
            $codeLength = strlen($couponCode);
            $isCodeLengthValid = $codeLength && $codeLength <= Mage_Checkout_Helper_Cart::COUPON_CODE_MAX_LENGTH;

            $this->_getQuote()->getShippingAddress()->setCollectShippingRates(true);
            $this->_getQuote()->setCouponCode($isCodeLengthValid ? $couponCode : '')
                ->collectTotals()
                ->save();

            if ($codeLength) {
                if ($isCodeLengthValid && $couponCode == $this->_getQuote()->getCouponCode()) {
                    $this->_getSession()->addSuccess(
                        $this->__('Coupon code "%s" was applied.', Mage::helper('core')->escapeHtml($couponCode))
                    );
                } else {
                    $this->_getSession()->addError(
                        $this->__('Coupon code "%s" is not valid.', Mage::helper('core')->escapeHtml($couponCode))
                    );
                }
            } else {
                $this->_getSession()->addSuccess($this->__('Coupon code was canceled.'));
            }

        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addError($this->__('Cannot apply the coupon code.'));
            Mage::logException($e);
        }

        $this->_goBack();
    }
}
