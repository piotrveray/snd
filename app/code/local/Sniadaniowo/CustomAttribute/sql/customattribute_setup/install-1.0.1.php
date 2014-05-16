<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
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
 * @category    Atwix
 * @package     Atwix_CustomAttribute
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

/* @var $installer Mage_Sales_Model_Resource_Setup */
$installer = new Mage_Sales_Model_Resource_Setup('core_setup');

$installer->addAttribute('order_item', 'day_week', array(
    'type'              => 'text',
    'label'                => 'Day week',
    'global'            => 1,
    'visible'           => 1,
    'required'          => 0,
    'user_defined'      => 1,
    'searchable'        => 0,
    'filterable'        => 0,
    'comparable'        => 0,
    'visible_on_front'  => 0,
    'visible_in_advanced_search' => 0,
    'unique'            => 0,
    'is_configurable'   => 0,
    'position'          => 1000,
));    

$installer->addAttribute('quote_item', 'day_week', array(
    'type'              => 'text',
    'label'                => 'Day week',
    'global'            => 1,
    'visible'           => 1,
    'required'          => 0,
    'user_defined'      => 1,
    'searchable'        => 0,
    'filterable'        => 0,
    'comparable'        => 0,
    'visible_on_front'  => 0,
    'visible_in_advanced_search' => 0,
    'unique'            => 0,
    'is_configurable'   => 0,
    'position'          => 1000,
));  

$installer->addAttribute('order_item', 'product_url', array(
    'type'              => 'text',
    'label'                => 'Product url',
    'global'            => 1,
    'visible'           => 1,
    'required'          => 0,
    'user_defined'      => 1,
    'searchable'        => 0,
    'filterable'        => 0,
    'comparable'        => 0,
    'visible_on_front'  => 0,
    'visible_in_advanced_search' => 0,
    'unique'            => 0,
    'is_configurable'   => 0,
    'position'          => 1010,
)); 

$installer->addAttribute('quote_item', 'day_week', array(
    'type'              => Varien_Db_Ddl_Table::TYPE_VARCHAR,
    'backend'           => '',
    'frontend'          => '',
    'label'             => 'Day Week',
    'input'             => 'text',
    'class'             => '',
    'source'            => '',
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'visible'           => true,
    'required'          => false,
    'user_defined'      => true,
    'default'           => '',
    'searchable'        => true,
    'filterable'        => true,
    'comparable'        => false,
    'visible_on_front'  => false,
    'unique'            => false,
    'is_configurable'   => false
)); 

//$installer->addAttribute('catalog_product', 'day_week', array(
//    'group'             => 'General',
//    'type'              => Varien_Db_Ddl_Table::TYPE_VARCHAR,
//    'backend'           => '',
//    'frontend'          => '',
//    'label'             => 'Day Week',
//    'input'             => 'text',
//    'class'             => '',
//    'source'            => '',
//    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
//    'visible'           => true,
//    'required'          => false,
//    'user_defined'      => true,
//    'default'           => '',
//    'searchable'        => true,
//    'filterable'        => true,
//    'comparable'        => false,
//    'visible_on_front'  => false,
//    'unique'            => false,
//    'apply_to'          => 'simple,configurable,virtual',
//    'is_configurable'   => false
//));

/**
 * Add 'custom_attribute' attribute for entities
 */
$entities = array(
    'quote',
//    'quote_address',
    'quote_item',
//    'quote_address_item',
    'order',
    'order_item'
);
$options = array(
    'type'     => Varien_Db_Ddl_Table::TYPE_VARCHAR,
    'visible'  => true,
    'required' => false
);
foreach ($entities as $entity) {
    $installer->addAttribute($entity, 'day_week', $options);
    $installer->addAttribute($entity, 'product_url', $options);
}
$installer->endSetup();