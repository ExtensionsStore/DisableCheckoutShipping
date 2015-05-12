<?php

/**
 * Disable checkout observer test
 *
 * @category   Aydus
 * @package    Aydus_DisableCheckoutShipping
 * @author     Aydus <davidt@aydus.com>
 */

class Aydus_DisableCheckoutShipping_Test_Model_Observer extends EcomDev_PHPUnit_Test_Case_Config
{    
    /**
     * Test adding fields to configuration
     *
     * @test
     */
    public function addDisableCheckoutShippingFieldsTest()
    {
        $this->assertEventObserverDefined(
         'adminhtml', 'adminhtml_init_system_config', 'aydus_disablecheckoutshipping/observer', 'addDisableCheckoutShippingFields'
        );

        $config = Mage::getConfig()->loadModulesConfiguration('system.xml')
            ->applyExtends();
        $observer = new Varien_Event_Observer();
        $observer->setConfig($config);

        $model = Mage::getModel('aydus_disablecheckoutshipping/observer');
                        
        $observer = $model->addDisableCheckoutShippingFields($observer);

        $this->assertTrue($observer->getAddedDisableCheckoutShippingFields());            

    }
        
    /**
     * Test disabling shipping carrier
     *
     * @test
     * @loadFixture
     */
    public function disableCheckoutShippingTest()
    {
        $this->assertEventObserverDefined(
         'frontend', 'sales_quote_address_collect_totals_before', 'aydus_disablecheckoutshipping/observer', 'disableCheckoutShipping'
        );
               
        $observer = new Varien_Event_Observer();
        $code = 'flatrate';
        $quote = Mage::getSingleton('sales/quote');
        $storeId = $this->app()->getAnyStoreView()->getId();
        $quote->setStoreId($storeId);
        $address = Mage::getModel('sales/quote_address');
        $address->setAddressType('shipping');
        $address->setQuote($quote);
        $quote->setShippingAddress($address);
        
        $observer->setQuoteAddress($address);
        $model = Mage::getModel('aydus_disablecheckoutshipping/observer');
                        
        $model->disableCheckoutShipping($observer);
        
        $flatrate = $this->app()->getStore($storeId)->getConfig('carriers/'.$code.'/active');
        $active = (!$flatrate) ? false : true;

        $this->assertFalse($active);    
    }
   
}