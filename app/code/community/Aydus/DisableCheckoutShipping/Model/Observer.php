<?php

/**
 * DisableCheckoutShipping observer
 *
 * @category   Aydus
 * @package    Aydus_DisableCheckoutShipping
 * @author     Aydus <davidt@aydus.com>
 */

class Aydus_DisableCheckoutShipping_Model_Observer 
{
    /**
     * Add disable_checkout_shipping field to all carrier groups
     *
     * @param Varien_Event_Observer $observer
     * @return Varien_Event_Observer
     */
    public function addDisableCheckoutShippingFields($observer)
    {
        $config = $observer->getConfig();
    
        $carrierGroups = $config->getNode('sections/carriers/groups');
        
        $usualCarrierGroups = array(
                'flatrate' => 2,
                'freeshipping' => 2,
                'tablerate' => 2,
                'dhl' => 11,
                'fedex' => 11,
                'ups' => 11,
                'usps' => 11,
                'dhlint' => 11,
        );
        
        $carriers = array_keys((array)$carrierGroups);
        
        foreach ($usualCarrierGroups as $carrier => $position){
            
            if (!in_array($carrier, $carriers)){
                $observer->setAddedDisableCheckoutShippingFields(false);
                return $observer;
            }
        }        
        
        $usualCarrierGroupsKeys = array_keys($usualCarrierGroups);
    
        foreach ($carrierGroups->children() as $group => $element){
            	
            $fields = &$element->fields;
            
            if (!$fields->disable_checkout_shipping){
                
                $disableCheckoutShipping = $fields->addChild('disable_checkout_shipping');
                
                $disableCheckoutShipping->addAttribute('translate', 'label');
                $disableCheckoutShipping->addAttribute('module', 'aydus_disablecheckoutshipping');
                
                $disableCheckoutShipping->addChild('label', 'Disable Checkout Shipping');
                $disableCheckoutShipping->addChild('frontend_type', 'select');
                $disableCheckoutShipping->addChild('source_model', 'adminhtml/system_config_source_yesno');
                $sortOrder = (in_array($group, $usualCarrierGroupsKeys)) ? $usualCarrierGroups[$group] : 11;
                $disableCheckoutShipping->addChild('sort_order', $sortOrder);
                $disableCheckoutShipping->addChild('show_in_default', 1);
                $disableCheckoutShipping->addChild('show_in_website', 1);
                $disableCheckoutShipping->addChild('show_in_store', 0);
                $disableCheckoutShipping->addChild('comment', 'Disable from front end checkout shipping methods.');
                
            }
            	
        }
    
        $observer->setAddedDisableCheckoutShippingFields(true);
            
        return $observer;
    }
        
    /**
     * 
     * Disable carrier if frontend shipping method is disabled
     * 
     * @see sales_quote_address_collect_totals_before
     * @param Varien_Event_Observer $observer
     * @return Varien_Event_Observer
     */
    public function disableCheckoutShipping($observer)
    {
        $quoteAddress = $observer->getQuoteAddress();
        
        if ($quoteAddress->getAddressType()=='shipping'){
            
            $shippingAddress = $quoteAddress;
            
            $quote = $shippingAddress->getQuote();
            $quoteId = $quote->getId();
            
            $store = Mage::app()->getStore($quote->getStoreId());
            $storeId = $store->getId();
            $carriers = $store->getConfig('carriers');
            
            foreach ($carriers as $code => $configAr){
                
                if ($configAr['active'] && $configAr['disable_checkout_shipping']){
                                        
                    $store->setConfig('carriers/'.$code.'/active', 0);
                                        
                }
                
            }
                        
        }
        
        return $observer;
    }
   
}