<?php

/**
 * Class Linus_Common_Model_Observer
 *
 * @author Dane MacMillan <work@danemacmillan.com>
 */
class Linus_Common_Model_Observer
{
    const STATIC_DATA_BLOCK_CACHE_KEY = "static_data_block_%s";

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

        if ($controllerName == 'category'
            && !is_null($category = Mage::registry('current_category'))
        ) {
            /** @var Mage_Catalog_Model_Category $category */
            $categoryId = $category->getId();
            $categoryParentId = $category->getParentId();

            $layoutUpdate->removeHandle('CATEGORY_' . $categoryId);
            $layoutUpdate->addHandle('CATEGORY_PARENT_' . $categoryParentId);
            $layoutUpdate->addHandle('CATEGORY_' . $categoryId);
        }

        if ($controllerName == 'product'
            && !is_null($category = Mage::registry('current_product'))
        ) {
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

    /**
     * Add some Block manipulations, and dispatch custom events.
     *
     * This method enhances regular CMS static blocks so they can also contain
     * raw CSV data. That CSV data is formatted exactly like Magento's locale
     * handling for translation files. Paste content like that into a CMS
     * static block, and any block that has been defined to have CSV data
     * in the layout XML will be able to access all those properties
     * using standard `$this->getYourCsvKey()` syntax, common to all templates
     * that have blocks defined. It also dispatches an event to manipulate this
     * behaviour further.
     *
     * Additionally, it dispatches an event for the `Mage_Page_Block_Html_Head`
     * class so that the head assets created in `getCssJsHtml` can be reordered.
     * For example, if jQuery should load before Magento's prototype.js library,
     * this can now be done.
     *
     * Events created:
     *
     *  - linus_common_block_before_head_getCssJsHtml:
     *  This event allows the head assets to be reordered. Linus_Common uses
     *  this event to place its JavaScript assets before everything else; this
     *  is due to the fact that Linus_Common loads essential libraries that
     *  many other modules depend upon.
     *
     *  - common_cms_csv_block_load_before:
     *  Linus_Common allows CMS static blocks to contain raw CSV data instead
     *  of just HTML. If a layout block has been defined to have CSV data,
     *  the corresponding CMS static block will be parsed for CSV content.
     *
     * @param Varien_Event_Observer $observer
     */
    public function onCoreBlockAbstractToHtmlBefore(Varien_Event_Observer $observer)
    {
        /** @var Mage_Cms_Block_Block $block */
        $block = $observer->getBlock();

        if ($block instanceof Mage_Page_Block_Html_Head) {
            Mage::dispatchEvent('linus_common_block_before_head_getCssJsHtml', array('block' => $block));
        }

        if ($block->hasCsvData() && !$block->getFiredToHtmlBefore()) {
            //Since we need to call toHtml, detect if this block has already
            //fired the event so we don't end up in an infinite loop.
            $block->setFiredToHtmlBefore(true);

            $blockIdentifier = $block->getNameInLayout();

            $eventContainer = new Varien_Object(array(
                'cms_static_block_identifier' => $blockIdentifier,
                'cms_static_block_id' => null,
                'layout_block_object' => $block
            ));

            Mage::dispatchEvent('common_cms_csv_block_load_before', array(
                'render_data' => $eventContainer,
            ));

            $cmsBlockIdOrIdentifier = $eventContainer->getCmsStaticBlockId() != null
                ? $eventContainer->getCmsStaticBlockId()
                : $eventContainer->getCmsStaticBlockIdentifier();

            $key = sprintf(self::STATIC_DATA_BLOCK_CACHE_KEY, $cmsBlockIdOrIdentifier);

            //Check if the cache key exists. If it does, skip the parsing
            //as the html is cached and pre-rendered.  There is a possible, though
            //unlikely, race condition if the cache is somehow cleared between
            //this check and the html lookup.

            $block->setCacheKey($key);
            $block->setCacheLifetime(302400);

            if(Mage::app()->getCacheInstance()->load($key) !== false) {
                return;
            }

            /** @var Linus_Common_Helper_Cms $cmsHelper */
            $cmsHelper = Mage::helper('linus_common/cms');
            $csvBlock = $cmsHelper->getCsvBlock($cmsBlockIdOrIdentifier);

            $cmsHelper->loadCsvDataFromCmsBlock(
                $csvBlock,
                $block
            );

            if ($block->getCsvData() == 'nested') {
                $block->prepare();
            }
        }
    }

    /**
     * Use this custom event for re-ordering JS/CSS assets.
     *
     * This will load all `js` `linuscommon` assets before everything else.
     *
     * Note that it will continue to respect Magento's default load order for
     * asset types. For example, `js` assets will always load before `skin_js`
     * assets. Common places its JavaScript files in the root `/js` directory,
     * because they are library-level code, used universally, so they take
     * precedence over regular module assets in `skin_js`.
     *
     * @param Varien_Event_Observer $observer
     */
    public function onLinusCommonBlockBeforeHeadGetCssJsHtml(Varien_Event_Observer $observer)
    {
        /** @var Mage_Page_Block_Html_Head $block */
        $block = $observer->getBlock();
        $assets = $block->getItems();

        $commonAssets = array();
        foreach ($assets as $assetKey => $assetValue) {
            if (strpos($assetKey, '/linuscommon/') !== false) {
                $commonAssets[$assetKey] = $assetValue;
                unset($assets[$assetKey]);
            }
        }

        $block->setData('items', $commonAssets + $assets);
    }

    public function onAdminModelSaveAfter(Varien_Event_Observer $observer)
    {
        /** @var Mage_Cms_Model_Block $block */
        $block = $observer->getObject();
        if ($block->getResourceName() == 'cms/block') {
            $key = sprintf(self::STATIC_DATA_BLOCK_CACHE_KEY, $block->getIdentifier());
            Mage::app()->getCacheInstance()->remove($key);
        }
    }
}
