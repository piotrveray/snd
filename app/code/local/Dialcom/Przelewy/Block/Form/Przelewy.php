<?php

class Dialcom_Przelewy_Block_Form_Przelewy extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('dialcom/przelewy/form.phtml');
    }
	
}
