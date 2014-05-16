<?php

class Dialcom_Przelewy_Model_Payment_Przelewy extends Mage_Payment_Model_Method_Abstract
{
	protected $_code  = 'dialcom_przelewy';
	protected $_formBlockType = 'przelewy/form_przelewy';
	protected $_infoBlockType = 'przelewy/info_przelewy';

    protected $_isGateway               = false;
    protected $_canAuthorize            = false;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = false;
    protected $_canRefund               = false;
    protected $_canVoid                 = false;
    protected $_canUseInternal          = true;
    protected $_canUseCheckout          = true;
    protected $_canUseForMultishipping  = false;
	protected $_canSaveCc               = false;
	
	
	public function getText()
	{
		return $this->getConfigData("text");
	}
	
	public function getOrderPlaceRedirectUrl() {
        return Mage::getUrl('przelewy/przelewy/redirect');
    }
	
	public function getCheckout() {
        return Mage::getSingleton('checkout/session');
    }
    
	public function getRedirectionFormData(){
		$billing  = $this->getCheckout()->getQuote()->getBillingAddress();
		$order_id = $this->getCheckout()->getLastRealOrderId();

		$order=Mage::getModel('sales/order');
		$sa_billing=$order->loadByIncrementId($order_id)->getPayment()->getOrder()->_data;
		if($this->getTestMode() == '0' || $this->getTestMode() == '1'){
		//	$desc = 'TEST_OK';
			$desc = $order_id;
		}elseif($this->getTestMode() == '2'){			
			$desc =	'TEST_ERR102';
		}
		
		$p24_klient = $order->getBillingAddress()->getData('firstname').' '.$order->getBillingAddress()->getData('lastname');
		$p24_adres  = $order->getBillingAddress()->getData('street');
		$p24_miasto = $order->getBillingAddress()->getData('city');		
		$mode = $this->getPaymentURI();
		if($mode=='https://sandbox.przelewy24.pl/'){
			$p24_id_sprzedawcy = '13224';
		}else{
			$p24_id_sprzedawcy = $this->getShopNumber();
		}
		
		$redirectionFormData = array(
			"p24_session_id"	=>	$order_id.'|'.time(),//*session_id(),*//*Mage::getSingleton('customer/session'),*/
			"p24_id_sprzedawcy" => $p24_id_sprzedawcy,
			"p24_kwota" => $sa_billing['grand_total']*100,//$sa_data['grand_total']*100,//$this->getTotalPrice()*100,
			"url" => Mage::getUrl('przelewy/przelewy/return'),
			"p24_klient"	=>  iconv("UTF-8","ISO-8859-2",$p24_klient),
			"p24_adres"		=>	iconv("UTF-8","ISO-8859-2",$p24_adres),
			"p24_kod" 		=>  $order->getBillingAddress()->getData('postcode'),
			"p24_miasto"	=>	iconv("UTF-8","ISO-8859-2",$p24_miasto),
			"p24_kraj"		=>	'PL',
			"p24_email"		=>  $sa_billing['customer_email'],
								
			"p24_return_url_ok"	=>	Mage::getUrl('przelewy/przelewy/success'),
			"p24_return_url_error"	=>	Mage::getUrl('przelewy/przelewy/failure'),
			//"p24_opis" => $order_id,
			"p24_opis" => $desc,
			"p24_language" => 'pl'
		);
		return (array)@$redirectionFormData;
	}
	
	
	public function getShopNumber(){	
			return Mage::getStoreConfig('payment/dialcom_przelewy/shopno',$this->getStoreId());			
	}
	
    public function getTestMode(){
		return Mage::getStoreConfig('payment/dialcom_przelewy/mode');
	}
	
	
	public function getTotalPrice(){
        return number_format($this->getCheckout()->getQuote()->getBaseGrandTotal(),2,'.','');
	}
	
	
	public function getPaymentURI(){
		return Mage::getStoreConfig('payment/dialcom_przelewy/uri');
	}
	
	
	public function getCountriesToOptionArray(){
		$new =array();
		foreach($this->_sa_countries as $key=>$option)
		{
			$new[] = array( 'value' => $key, 'label' => $option);
		}
		
    return $new;
	}
	

	private $_sa_countries=array
                                  (
                                    #$K[] = "Afghanistan',
                                    'AL'=>'Albania',
                                    //$K[] = "Algeria',
                                    //$K[] = "American Samoa',
                                    //$K[] = "Andorra',
                                    //$K[] = "Angola',
                                    //$K[] = "Anguilla',
                                    //$K[] = "Antarctica',
                                    //$K[] = "Antigua and Barbuda',
                                    //$pK[] = "Argentina',
                                    //$K[] = "Armenia',
                                    //$K[] = "Aruba',
                                    'AUS'=>'Australia',
                                    'A'=>'Austria',
                                    //$K[] = "Azerbaijan',
                                    //$K[] = "Bahamas',
                                    //$K[] = "Bahrain',
                                    //$K[] = "Bangladesh',
                                    //$K[] = "Barbados',
                                    'BY'=>'Belarus',
                                    'B'=>'Belgium',
                                    //$K[] = "Belize',
                                    //$K[] = "Benin',
                                    //$K[] = "Bermuda',
                                    //$K[] = "Bhutan',
                                    //$K[] = "Bolivia',
                                    'BIH'=>'Bosnia and Herzegowina',
                                    //$K[] = "Botswana',
                                    //$K[] = "Bouvet Island',
                                    'BR'=>'Brazil',
                                    //$K[] = "British Indian Ocean Territory',
                                    //$K[] = "Brunei Darussalam',
                                    'BG'=>'Bulgaria',
                                    //$K[] = "Burkina Faso',
                                    //$K[] = "Burundi',
                                    //$K[] = "Cambodia',
                                    //$K[] = "Cameroon',
                                    'CDN'=>'Canada',
                                    //$K[] = "Cape Verde',
                                    //$K[] = "Cayman Islands',
                                    //$K[] = "Central African Republic',
                                    //$K[] = "Chad',
                                    //$K[] = "Chile',
                                    //$K[] = "China',
                                    //$K[] = "Christmas Island',
                                    //$K[] = "Cocos (Keeling) Islands',
                                    //$K[] = "Colombia',
                                    //$K[] = "Comoros',
                                    //$K[] = "Congo',
                                    //$K[] = "Cook Islands',
                                    //$K[] = "Costa Rica',
                                    'HR'=>'Croatia',
                                    //$K[] = "Cuba',
                                    'CY'=>'Cyprus',
                                    'CZ'=>'Czech Republic',
                                    'DK'=>'Denmark',
                                    //$K[] = "Djibouti',
                                    //$K[] = "Dominica',
                                    //$K[] = "Dominican Republic',
                                    //$K[] = "East Timor',
                                    //$K[] = "Ecuador',
                                    'ET'=>'Egypt',
                                    //$K[] = "El Salvador',
                                    //$K[] = "Equatorial Guinea',
                                    //$K[] = "Eritrea',
                                    'EST'=>'Estonia',
                                    //$K[] = "Ethiopia',
                                    //$K[] = "Falkland Islands (Malvinas)',
                                    //$K[] = "Faroe Islands',
                                    //$K[] = "Fiji',
                                    'FIN'=>'Finland',
                                    'F'=>'France',
                                    //$K[] = "France, Metropolitan',
                                    //$K[] = "French Guiana',
                                    //$K[] = "French Polynesia',
                                    //$K[] = "French Southern Territories',
                                    //$K[] = "Gabon',
                                    //$K[] = "Gambia',
                                    //$K[] = "Georgia',
                                    'DE'=>'Germany',
                                    //$K[] = "Ghana',
                                    //$K[] = "Gibraltar',
                                    'GR'=>'Greece',
                                    //$K[] = "Greenland',
                                    //$K[] = "Grenada',
                                    //$K[] = "Guadeloupe',
                                    //$K[] = "Guam',
                                    //$K[] = "Guatemala',
                                    //$K[] = "Guinea',
                                    //$K[] = "Guinea-bissau',
                                    //$K[] = "Guyana',
                                    //$K[] = "Haiti',
                                    //$K[] = "Heard and Mc Donald Islands',
                                    //$K[] = "Honduras',
                                    //$K[] = "Hong Kong',
                                    'H'=>'Hungary',
                                    'IS'=>'Iceland',
                                    'IND'=>'India',
                                    //$K[] = "Indonesia',
                                    //$K[] = "Iran (Islamic Republic of)',
                                    //$K[] = "Iraq',
                                    'IRL'=>'Ireland',
                                    //$K[] = "Israel',
                                    'I'=>'Italy',
                                    //$K[] = "Jamaica',
                                    'J'=>'Japan',
                                    //$K[] = "Jordan',
                                    //$K[] = "Kazakhstan',
                                    //$K[] = "Kenya',
                                    //$K[] = "Kiribati',
                                    //$K[] = "Korea, Democratic People's Republic of',
                                    //$K[] = "Korea, Republic of',
                                    //$K[] = "Kuwait',
                                    //$K[] = "Kyrgyzstan',
                                    //$K[] = "Lao People's Democratic Republic',
                                    'LV'=>'Latvia',
                                    //$K[] = "Lebanon',
                                    //$K[] = "Lesotho',
                                    //$K[] = "Liberia',
                                    //$K[] = "Libyan Arab Jamahiriya',
                                    'FL'=>'Liechtenstein',
                                    'LT'=>'Lithuania',
                                    'L'=>'Luxembourg',
                                    //$K[] = "Macau',
                                    //$K[] = "Macedonia, The Former Yugoslav Republic of',
                                    //$K[] = "Madagascar',
                                    //$K[] = "Malawi',
                                    //$K[] = "Malaysia',
                                    //$K[] = "Maldives',
                                    //$K[] = "Mali',
                                    //$K[] = "Malta',
                                    //$K[] = "Marshall Islands',
                                    //$K[] = "Martinique',
                                    //$K[] = "Mauritania',
                                    //$K[] = "Mauritius',
                                    //$K[] = "Mayotte',
                                    //$K[] = "Mexico',
                                    //$K[] = "Micronesia, Federated States of',
                                    //$K[] = "Moldova, Republic of',
                                    //$K[] = "Monaco',
                                    //$K[] = "Mongolia',
                                    //$K[] = "Montserrat',
                                    //$K[] = "Morocco',
                                    //$K[] = "Mozambique',
                                    //$K[] = "Myanmar',
                                    //$K[] = "Namibia',
                                    //$K[] = "Nauru',
                                    //$K[] = "Nepal',
                                    'NL'=>'Netherlands',
                                    //$K[] = "Netherlands Antilles',
                                    //$K[] = "New Caledonia',
                                    //$K[] = "New Zealand',
                                    //$K[] = "Nicaragua',
                                    //$K[] = "Niger',
                                    //$K[] = "Nigeria',
                                    //$K[] = "Niue',
                                    //$K[] = "Norfolk Island',
                                    //$K[] = "Northern Mariana Islands',
                                    'N'=>'Norway',
                                    //$K[] = "Oman',
                                    //$K[] = "Pakistan',
                                    //$K[] = "Palau',
                                    //$K[] = "Panama',
                                    //$K[] = "Papua New Guinea',
                                    //$K[] = "Paraguay',
                                    //$K[] = "Peru',
                                    //$K[] = "Philippines',
                                    //$K[] = "Pitcairn',
                                    'PL'=>'Polska',
                                    'P'=>'Portugal',
                                    //$K[] = "Puerto Rico',
                                    //$K[] = "Qatar',
                                    //$K[] = "Reunion',
                                    'RO'=>'Romania',
                                    'RUS'=>'Russian Federation',
                                    //$K[] = "Rwanda',
                                    //$K[] = "Saint Kitts and Nevis',
                                    //$K[] = "Saint Lucia',
                                    //$K[] = "Saint Vincent and the Grenadines',
                                    //$K[] = "Samoa',
                                    //$K[] = "San Marino',
                                    //$K[] = "Sao Tome and Principe',
                                    //$K[] = "Saudi Arabia',
                                    //$K[] = "Senegal',
                                    //$K[] = "Seychelles',
                                    //$K[] = "Sierra Leone',
                                    //$K[] = "Singapore',
                                    'SK'=>'Slovakia (Slovak Republic)',
                                    'SLO'=>'Slovenia',
                                    //$K[] = "Solomon Islands',
                                    //$K[] = "Somalia',
                                    //$K[] = "South Africa',
                                    //$K[] = "South Georgia and the South Sandwich Islands',
                                    'E'=>'Spain',
                                    //$K[] = "Sri Lanka',
                                    //$K[] = "St. Helena',
                                    //$K[] = "St. Pierre and Miquelon',
                                    //$K[] = "Sudan',
                                    //$K[] = "Suriname',
                                    //$K[] = "Svalbard and Jan Mayen Islands',
                                    //$K[] = "Swaziland',
                                    'S'=>'Sweden',
                                    'CH'=>'Switzerland',
                                    //$K[] = "Syrian Arab Republic',
                                    //$K[] = "Taiwan',
                                    //$K[] = "Tajikistan',
                                    //$K[] = "Tanzania, United Republic of',
                                    //$K[] = "Thailand',
                                    //$K[] = "Togo',
                                    //$K[] = "Tokelau',
                                    //$K[] = "Tonga',
                                    //$K[] = "Trinidad and Tobago',
                                    //$K[] = "Tunisia',
                                    'TR'=>'Turkey',
                                    //$K[] = "Turkmenistan',
                                    //$K[] = "Turks and Caicos Islands',
                                    //$K[] = "Tuvalu',
                                    //$K[] = "Uganda',
                                    'UA'=>'Ukraine',
                                    //$K[] = "United Arab Emirates',
                                    'UK'=>'United Kingdom',
                                    'USA'=>'United States',
                                    //$K[] = "United States Minor Outlying Islands',
                                    //$K[] = "Uruguay',
                                    //$K[] = "Uzbekistan',
                                    //$K[] = "Vanuatu',
                                    //$K[] = "Vatican City State (Holy See)',
                                    //$K[] = "Venezuela',
                                    //$K[] = "Viet Nam',
                                    //$K[] = "Virgin Islands (British)',
                                    //$K[] = "Virgin Islands (U.S.)',
                                    //$K[] = "Wallis and Futuna Islands',
                                    //$K[] = "Western Sahara',
                                    //$K[] = "Yemen',
                                    //$K[] = "Yugoslavia',
                                    //$K[] = "Zaire',
                                    //$K[] = "Zambia',
                                    //$K[] = "Zimbabwe'
                                 );
}