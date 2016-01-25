<?php

/**
 *
 *
 * @author Sam Schmidt <samuel@dersam.net>
 * @since 2016-01-22
 */
class Linus_Common_Helper_Template extends Mage_Core_Helper_Abstract
{
    public function addTemplate(Varien_Event_Observer $templateEvent, $templateKey, $templateBody)
    {
        $templateEvent->getTemplates()->setData($templateKey, $templateBody);
    }
}
