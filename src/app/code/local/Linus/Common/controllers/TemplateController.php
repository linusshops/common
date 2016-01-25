<?php

/**
 * Provide endpoints to manage and retrieve Lodash frontend templates.
 *
 * @author Sam Schmidt <samuel@dersam.net>
 * @since 2016-01-22
 */
class Linus_Common_TemplateController extends Mage_Core_Controller_Front_Action
{
    /**
     * Fetch lodash templates by block name.  These should be defined in
     * layout xml as blocks, and fetched by block name.
     */
    public function indexAction()
    {
        //Get keys to look up from request
        $templateKeys = json_decode($this->getRequest()->getRawBody(), true);
        if (empty($templateKeys)) {
            Mage::helper('linus_common/request')->sendResponseJson(
                array(),
                'No template keys provided'
            );

            return;
        }

        //Load requested blocks by name
        $this->loadLayout();

        Mage::helper('linus_common/request')->sendResponseJson(
            Mage::getModel('linus_common/tpl')
                ->getTplBlocksByName(
                    $this->getLayout(),
                    $templateKeys
                )
        );
    }
}
