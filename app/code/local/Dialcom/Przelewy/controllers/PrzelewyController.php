<?php

class Dialcom_Przelewy_PrzelewyController extends Mage_Core_Controller_Front_Action {

	
	private function p24Weryfikuj($p24_id_sprzedawcy, $p24_session_id, $p24_order_id, $p24_kwota="")
	{
		
		
		$P = array(); $RET = array();
		$url = Mage::getStoreConfig('payment/dialcom_przelewy/uri').'transakcja.php';	
	//	$url = "https://secure.przelewy24.pl/transakcja.php"; 
		$P[] = "p24_id_sprzedawcy=".$p24_id_sprzedawcy;
		$P[] = "p24_session_id=".$p24_session_id;
		$P[] = "p24_order_id=".$p24_order_id;
		$P[] = "p24_kwota=".$p24_kwota;
		$user_agent = "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_POST,1);
		if(count($P)) curl_setopt($ch, CURLOPT_POSTFIELDS,join("&",$P));
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		$result=curl_exec ($ch);
		curl_close ($ch);
		$T = explode(chr(13).chr(10),$result);
		$res = false;
		foreach($T as $line)	
		{
			//$line = ereg_replace("[\n\r]","",$line);
			
			$line = str_replace("\n\r","",$line);
			$line = str_replace("\n","",$line);
			$line = str_replace("\r","",$line);

			if($line != "RESULT" and !$res) continue;
			if($res) $RET[] = $line;
			else $res = true;
		}
	return $RET;
	}


	public function redirectAction() {
		$session = Mage::getSingleton('checkout/session');
		
        $session->setPrzelewyQuoteId($session->getQuoteId());

        $this->getResponse()->setBody($this->getLayout()->createBlock('przelewy/payment_przelewy_redirect')->toHtml());
        $session->unsQuoteId();
	}
	
	
	public function successAction() {
		$order_id = Mage::getSingleton('checkout/session')->getLastRealOrderId();
		if($_POST["p24_session_id"]){
			$sa_sid = explode('|',$_POST["p24_session_id"]);
			$order_id = $sa_sid[0];
		}
		$order = Mage::getModel('sales/order');
		$order = Mage::getModel('sales/order')->loadByIncrementId($order_id);
		
		$p24_session_id=$_POST["p24_session_id"];
		
		$p24_order_id=$_POST["p24_order_id"];
		$mode = Mage::getStoreConfig('payment/dialcom_przelewy/uri');
		if($mode=='https://sandbox.przelewy24.pl/'){
			$p24_id_sprzedawcy = '13224';
		}else{
			$p24_id_sprzedawcy = Mage::getStoreConfig('payment/dialcom_przelewy/shopno');//TWÓJ ID_SPRZEDAWCY;
		}
	//	$p24_id_sprzedawcy=Mage::getStoreConfig('payment/dialcom_przelewy/shopno');//TWÓJ ID_SPRZEDAWCY;
		
		$p24_kwota=$order->getTotalDue()*100; //WYNIK POBRANY Z TWOJEJ BAZY (w groszach)
		if($p24_kwota==0){
			$p24_kwota=$order->getGrandTotal()*100;	
		}

		$WYNIK=$this->p24Weryfikuj($p24_id_sprzedawcy,$p24_session_id,$p24_order_id,$p24_kwota);
	
		if($WYNIK[0]=="TRUE")
		{
	
			
			$order->addStatusToHistory(Mage_Sales_Model_Order::STATE_PROCESSING, Mage::helper('przelewy')->__('The payment has been accepted.'));
			$order->sendOrderUpdateEmail();
			$payment = $order->getPayment();
			$payment->setData('transaction_id',$p24_order_id);
			$payment->addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_ORDER);

		//	$order->getPayment()->addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_ORDER);
		//	$order->addPayment(Mage_Sales_Model_Order_Payment::REVIEW_ACTION_ACCEPT, $order->getPayment());
			$order->save();
		
			$session = Mage::getSingleton('checkout/session');
        	$session->setQuoteId($session->getPrzelewyQuoteId(true));
        	Mage::getSingleton('checkout/session')->getQuote()->setIsActive(false)->save();
			$this->_redirect('checkout/onepage/success');
		}
		else
		{
			$this->_redirect('przelewy/przelewy/failure');
		}
	}
	
	public function failureAction() 
	{
		$order_id = Mage::getSingleton('checkout/session')->getLastRealOrderId();
		
		$order = Mage::getModel('sales/order')->loadByIncrementId($order_id);
		
		if(!$order->getId()) { return FALSE; }
		$p24_error = $_POST["p24_error_code"];
		$p24_order_id = $_POST["p24_order_id"];
		$payment = $order->getPayment();
			$payment->setData('transaction_id',$p24_order_id);
			$payment->addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_ORDER);
		$order->addStatusToHistory(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT, Mage::helper('przelewy')->__('Payment error :.'.$p24_error));
		//$order->cancel();
		$order->save();
		
		$session = Mage::getSingleton('checkout/session');
		$session->setQuoteId($session->getPrzelewyQuoteId(true));
		$session->addError("Płatność za pomocą serwisu Przelewy24 została zakończona niepowodzeniem.");
		
		$this->_redirect('checkout/cart');
	}
	
}