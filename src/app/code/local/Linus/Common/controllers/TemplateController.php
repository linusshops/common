<?php

/**
 *
 *
 * @author Sam Schmidt <samuel@dersam.net>
 * @since 2016-01-22
 */
class Linus_Common_TemplateController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        //Get keys to look up from request

        $templateContainer = new Varien_Object(array(
            'templates' => array()
        ));

        Mage::dispatchEvent('common_template_lookup', array(
            'templates' => new Varien_Object(),
            'requested_keys' => array()
        ));

        Mage::helper('linus_common/request')->sendResponseJson(
            $templateContainer->getTemplates()
        );
    }
}
