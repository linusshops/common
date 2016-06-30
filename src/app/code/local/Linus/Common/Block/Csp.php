<?php

/**
 * CSP block for handling CSP data blocks and setting their type.
 *
 * @author Sam Schmidt <samuel@dersam.net>
 * @since 2016-06-27
 */
class Linus_Common_Block_Csp extends Linus_Common_Block_CspAbstract
{
    public function defineCspData()
    {
        $blocks = Mage::app()->getLayout()->getAllBlocks();
        $commonTplChecksums = array();

        /**
         * Find all tpl blocks, md5 them, and add this
         * hash list to the page CSP data for use by the common.js tpl cache. These
         * hashes allow the tpl cache in window.localStorage to determine if a
         * locally cached template must be invalidated.
         *
         * @var string $identifier
         * @var Mage_Core_Block_Abstract $block
         */
        foreach ($blocks as $identifier => $block) {
            //If the block class is the Tpl block class, or if it is a child,
            //add the block identifier and hash to template csp.
            if (get_class($block) == 'Linus_Common_Block_Tpl'
                || in_array('Linus_Common_Block_Tpl', class_parents($block))
            ) {
                $block->setRenderMode('tpl');
                $commonTplChecksums[$identifier] = md5($block->toHtml());
                $block->setRenderMode('magento');
            }
        };

        $this->setCspData([
            'baseUrl' => $this->getBaseUrl(),
            'formKey' => Mage::getSingleton('core/session')->getFormKey(),
            'jsUrl' => $this->getJsUrl(),
            'storeCode' => Mage::app()->getStore()->getCode(),
            'locale' => Mage::app()->getLocale()->getLocaleCode(),
            'mediaUrl' => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA),
            'skinUrl' => $this->getSkinUrl(),
            'storeUrl' => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB),
            'uenc' => Mage::helper('core')->urlEncode(rtrim($this->getBaseUrl(), '/') . $this->getRequest()->getRequestString()),
            'isLoggedIn' => Mage::getSingleton('customer/session')->isLoggedIn(),
            'isDeveloperMode' => Mage::getIsDeveloperMode(),
            'customerId' => Mage::getSingleton('customer/session')->getCustomerId(),
            'commonTplChecksums' => $commonTplChecksums,
            'currencyCode' => Mage::app()->getStore()->getCurrentCurrencyCode(),
            'currencySymbol' => Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol()
        ));

        ]);
    }
}
