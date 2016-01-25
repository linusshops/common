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
        $templateKeys = json_decode($this->getRequest()->getRawBody(), true);
        if (empty($templateKeys)) {
            return Mage::helper('linus_common/request')->sendResponseJson(
                array(),
                'No template keys provided'
            );
        }

        //Strip id and class indicators from the key to get the block identifier.
        array_walk($templateKeys, function(&$value, $index) {
            $value = preg_replace('/[\.\#](.+)/', '', $value);
        });

        //Load requested blocks by name
        $this->loadLayout();

        Mage::helper('linus_common/request')->sendResponseJson(
            Mage::getModel('linus_common/template_lodash')
                ->getAllLodashBlocksByName(
                    $this->getLayout(),
                    $templateKeys
                )
        );
    }
}
