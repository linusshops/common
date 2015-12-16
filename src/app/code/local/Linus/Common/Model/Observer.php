<?php

/**
 * Class Linus_Common_Model_Observer
 *
 * @author Dane MacMillan <work@danemacmillan.com>
 */
class Linus_Common_Model_Observer
{
    /**
     * Observe adding item to cart event.
     *
     * Hijax the event so it responds to asynchronous requests.
     *
     * @TODO Dane - Look at oemparts integration.
     *
     * @param Varien_Event_Observer $observer
     */
    public function onControllerActionPredispatchCheckoutCartAdd(Varien_Event_Observer $observer)
    {

    }

    /**
     * Add new layout XML handle: CATEGORY_PARENT_{ID}
     *
     * This provides the ability to modify all of a category's subcategories
     * from the layout XML without having to manually target each one
     * specifically with CATEGORY_{ID}. Ultimately, this provides layout XML
     * inheritance to subcategories from a parent category, which can normally
     * only be achieved through the database by specifying XML in the parent
     * category, and enabling "Use Parent Category Settings" in each
     * subcategory. This is better.
     *
     * This can be used to create handles of any kind, but the most immediate
     * use case that compelled this into existence.
     *
     * @param Varien_Event_Observer $observer
     */
    public function onControllerActionLayoutLoadBefore(Varien_Event_Observer $observer)
    {
        /** @var Varien_Event $event */
        $event = $observer->getEvent();
        /** @var Mage_Core_Controller_Front_Action $action */
        $action = $event->getAction();
        /** @var Mage_Core_Model_Layout_Update $layoutUpdate */
        $layoutUpdate = $event->getLayout()->getUpdate();

        $controllerName = $action->getRequest()->getControllerName();
        if ($controllerName == 'category') {
            /** @var Mage_Catalog_Model_Category $category */
            $category = Mage::registry('current_category');
            $layoutUpdate->addHandle('CATEGORY_PARENT_' . $category->getParentId());
        }
    }
}
