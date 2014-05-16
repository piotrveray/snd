<?php

class Dialcom_Przelewy_Block_Info_Przelewy extends Mage_Payment_Block_Info
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('dialcom/przelewy/info.phtml');
    }
}
