<?php

/**
 * CSP block for handling CSP data blocks and setting their type.
 *
 * @author Dane MacMillan <work@danemacmillan.com>
 */
class Linus_Common_Block_Csp extends Linus_Common_Block_CommonAbstract
{
    /**
     * Insert base CSP data at reference `after_body_start`.
     *
     * Modules that define their own CSP data can override the defaults set
     * here.
     *
     * @return string
     */
    public function outputBaseCspData()
    {
        /** @var Linus_Common_Helper_Data $Common */
        $CommonCsp = Mage::helper('linus_common/csp');

        $CommonCsp->setCspData(array(
            'baseUrl' => $this->getBaseUrl(),
            'formKey' => Mage::getSingleton('core/session')->getFormKey(),
            'jsUrl' => $this->getJsUrl(),
            'locale' => Mage::app()->getLocale()->getLocaleCode(),
            'mediaUrl' => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA),
            'skinUrl' => $this->getSkinUrl(),
            'storeUrl' => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB),
            'uenc' => Mage::helper('core')->urlEncode(rtrim($this->getBaseUrl(), '/') . $this->getRequest()->getRequestString()),
            'isLoggedIn' => Mage::getSingleton('customer/session')->isLoggedIn(),
            'isDeveloperMode' => Mage::getIsDeveloperMode()
        ));

        return $CommonCsp->generateHiddenCspMarkup();
    }
}
