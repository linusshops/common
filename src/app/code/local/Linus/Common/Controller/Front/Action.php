<?php

/**
 * All custom front controllers should extend this, not Mage.
 *
 * This will allow all front controllers to automatically have access to the
 * basic helpers and models provided by Linus_Common.
 */
abstract class Linus_Common_Controller_Front_Action extends Mage_Core_Controller_Front_Action
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
     * Quick access to linus_common/request helpers.
     *
     * @return Linus_Common_Helper_Request
     */
    public function CommonRequest()
    {
        return Mage::helper('linus_common/request');
    }
}
