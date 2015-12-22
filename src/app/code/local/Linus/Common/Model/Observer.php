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
     * Add new layout XML handles.
     *
     * CATEGORY_PARENT_{ID}:
     *
     *  This provides the ability to modify all of a category's subcategories
     *  from the layout XML without having to manually target each one
     *  specifically with CATEGORY_{ID}. Ultimately, this provides layout XML
     *  inheritance to subcategories from a parent category, which can normally
     *  only be achieved through the database by specifying XML in the parent
     *  category, and enabling "Use Parent Category Settings" in each
     *  subcategory. This is better.
     *
     *  The CATEGORY_{ID} handle is removed, then added after the
     *  CATEGORY_PARENT_{ID} handle. Order is important. This ensures that,
     *  should an entire parent category be targeted, a subcategory's specific
     *  layout XML will still take precedence over the general one set with
     *  CATEGORY_PARENT_{ID}.
     *
     * PRODUCT_CATEGORY_{ID}:
     *
     *  Note that it does not use $product->getCategoryIds() to get a product's
     *  category IDs, because that method will return a numerically sorted
     *  array of categories, which does not reflect the actual descendant
     *  relationship from category to subcategory, which then leads to the
     *  product. Same as the other handle, the order of handles is carefully
     *  built, so that a more specific handle will always trump a parent or
     *  less specific handle, which is what Magento expects.
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
            $categoryId = $category->getId();
            $categoryParentId = $category->getParentId();

            $layoutUpdate->removeHandle('CATEGORY_' . $categoryId);
            $layoutUpdate->addHandle('CATEGORY_PARENT_' . $categoryParentId);
            $layoutUpdate->addHandle('CATEGORY_' . $categoryId);
        }

        if ($controllerName == 'product') {
            /** @var Mage_Catalog_Model_Product $category */
            $product = $category = Mage::registry('current_product');
            $productId = $product->getId();

            $breadcrumbs = Mage::helper('catalog')->getBreadcrumbPath();
            if (count($breadcrumbs)) {
                $productCategoryIds = array_keys($breadcrumbs);
                array_walk($productCategoryIds, function(&$productCategoryId) {
                    $productCategoryId = (int) str_replace('category', '', $productCategoryId);
                });
                $productCategoryIds = array_filter($productCategoryIds);

                if (count($productCategoryIds)) {
                    $layoutUpdate->removeHandle('PRODUCT_' . $productId);
                    foreach ($productCategoryIds as $productCategoryId) {
                        $layoutUpdate->addHandle('PRODUCT_CATEGORY_' . $productCategoryId);
                    }
                    $layoutUpdate->addHandle('PRODUCT_' . $productId);
                }
            }
        }
    }

    public function onCoreBlockAbstractToHtmlBefore(Varien_Event_Observer $observer)
    {
        /** @var Mage_Cms_Block_Block $block */
        $block = $observer->getBlock();

        if ($block->hasCsvData() && !$block->getFiredToHtmlBefore()) {
            //Since we need to call toHtml, detect if this block has already
            //fired the event so we don't end up in an infinite loop.
            $block->setFiredToHtmlBefore(true);

            $blockName = $block->getNameInLayout();

            $eventContainer = new Varien_Object(array(
                'cms_block_name' => $blockName,
                'layout_block_object' => $block
            ));

            Mage::dispatchEvent('common_cms_csv_block_load_before', array(
                'render_data' => $eventContainer,
            ));

            $blockName = $eventContainer->getCmsBlockName();

            $csvBlock = Mage::helper('linus_common/cms')->getCsvBlock($blockName);

            Mage::helper('linus_common/cms')->loadCsvDataFromCmsBlock(
                $csvBlock,
                $block
            );
        }
    }

    public function testEvent($observer)
    {
        $observer->getRenderData()->setCmsBlockName('hello');
    }
}
