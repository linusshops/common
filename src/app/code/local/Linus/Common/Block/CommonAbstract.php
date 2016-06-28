<?php

/**
 * Common Abstract block class that all Common block classes extend.
 *
 * @author Dane MacMillan <work@danemacmillan.com>
 */
abstract class Linus_Common_Block_CommonAbstract extends Mage_Core_Block_Template
{
    /**
     * Quick access to linus_common/data helpers.
     *
     * @return Linus_Common_Helper_Data
     */
    public function Common()
    {
        return Mage::helper('linus_common');
    }

    /**
     * Quick access to linus_common/cms helpers.
     *
     * @return Linus_Common_Helper_Cms
     */
    public function CommonCms()
    {
        return Mage::helper('linus_common/cms');
    }

    /**
     * Quick access to linus_common/request helpers.
     *
     * @return Linus_Common_Helper_Request
     */
    public function CommonRequest()
    {
        return Mage::helper('linus_common/request');
    }
}
