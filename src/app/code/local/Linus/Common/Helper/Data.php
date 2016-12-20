<?php

/**
 * Class Linus_Common_Helper_Data
 *
 * A very general set of helpers for Common.
 *
 * @author Dane MacMillan <work@danemacmillan.com>
 */
class Linus_Common_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Prefix for user agent body class name.
     */
    const USER_AGENT_BODY_CLASS_NAME_PREFIX = 'ua';

    /**
     * Body class name used in body class for Internet Explorer.
     */
    const USER_AGENT_BODY_CLASS_NAME_INTERNET_EXPLORER = 'ie';

    public function mergeDefaultOptions($options, $defaultOptions)
    {
        return array_intersect_key(array_filter($options) + $defaultOptions, $defaultOptions);
    }

    /**
     * Get block directly from layout XML handle.
     *
     * Magento itself manually calls all of these methods to generate a block
     * from the layout XML handle every time they need to get a block. This
     * method wraps all that, and returns the block. This is useful for
     * returning asynchronous content, when JSON parsing has not been made
     * available. Magento almost exclusively requests HTML asynchronously, and
     * it uses a variation of the method calls below to generate them.
     *
     * Once the block is returned, any of the usual methods will work against
     * it. For example, `setData` to add data to the block, and `toHtml` to
     * generate the HTML of block. If blockName is null, the whole layout
     * representation for that handle will be returned; the blocks can then
     * be selected individually, if more than one is required.
     *
     * @param string $layoutHandle The layout XML handle.
     * @param string|null $blockName The child block name of the layout XML handle.
     *
     * @return Mage_Core_Block_Abstract
     */
    public function getBlockFromLayoutHandle($layoutHandle, $blockName = null)
    {
        $layout = Mage::app()->getLayout();
        $update = $layout->getUpdate();
        $update->load($layoutHandle);
        $layout->generateXml();
        $layout->generateBlocks();

        return (null !== $blockName)
            ? $layout->getBlock($blockName)
            : $layout;
    }

    /**
     * Similar to getBlockFromLayoutHandle, but return the output from handle.
     *
     * @param string $layoutHandle
     *
     * @return string
     * @throws Mage_Core_Exception
     */
    function getBlockOutputFromLayoutHandle($layoutHandle)
    {
        $layout = Mage::app()->getLayout();
        $update = $layout->getUpdate();
        $update->load($layoutHandle);
        $layout->generateXml();
        $layout->generateBlocks();

        return $layout->getOutput();
    }


    /**
     * Grammatically represent proper word form based on count of objects.
     *
     * Example usages:
     *  plural(0, 'item', 'items') == '0 items'
     *  plural(1, 'product', 'products') == '1 product'
     *  plural(0, 'item', 'items', 'Empty cart') == 'Empty cart'
     *
     * @param int $count The number of objects.
     * @param string $singularWordForm The singular word form of that object.
     * @param string $pluralWordForm The plural word form of that object.
     * @param bool|string $nilFormat Optional: If nil, allow alternate text.
     * @param bool $wordOnly Optional: if true, do not concatenate the word with the total.
     *
     * @return string
     */
    public function plural($count, $singularWordForm, $pluralWordForm, $nilFormat = false, $wordOnly = false)
    {
        $formattedString = "%s $pluralWordForm";

        if ($count === 0
            && is_string($nilFormat)
        ) {
            $formattedString = $nilFormat;
        } else {
            $grammaticalWordForm = ($count >= 2 || $count === 0)
                ? Mage::helper('core')->__($pluralWordForm)
                : Mage::helper('core')->__($singularWordForm);

            if ($wordOnly) {
                $formattedString = $grammaticalWordForm;
            } else {
                $formattedString = "%s $grammaticalWordForm";
            }
        }

        return sprintf(
            Mage::helper('core')->__($formattedString),
            $count
        );
    }

    /**
     * Build user agent string used in body class attribute.
     *
     * @param $userAgentBodyClassName
     *
     * @return string
     */
    private function buildUserAgentBodyClassName($userAgentBodyClassName)
    {
        return self::USER_AGENT_BODY_CLASS_NAME_PREFIX . '-' . $userAgentBodyClassName;
    }

    /**
     * Get the user agent body class name.
     */
    public function getUserAgentBodyClassName()
    {
        $userAgentBodyClassName = '';
        $userAgent = Mage::helper('core/http')->getHttpUserAgent();

        if (preg_match('~MSIE|Internet Explorer~i', $userAgent)
            || (strpos($userAgent, 'Trident/7.0; rv:11.0') !== false)
        ) {
            $userAgentBodyClassName = $this->buildUserAgentBodyClassName(
                self::USER_AGENT_BODY_CLASS_NAME_INTERNET_EXPLORER
            );
        }

        return $userAgentBodyClassName;
    }

    /**
     * Get the user agent body class name.
     *
     * Version detection based on http://stackoverflow.com/a/11741586.
     */
    public function getUserAgentVersionBodyClassName()
    {
        $userAgentVersionBodyClassName = '';
        $userAgent = Mage::helper('core/http')->getHttpUserAgent();

        if ($this->getUserAgentBodyClassName() == $this->buildUserAgentBodyClassName(self::USER_AGENT_BODY_CLASS_NAME_INTERNET_EXPLORER)) {
            preg_match('/MSIE (.*?);/', $userAgent, $matches);
            if(count($matches) < 2){
                preg_match('/Trident\/\d{1,2}.\d{1,2}; rv:([0-9]*)/', $userAgent, $matches);
            }

            if (count($matches) > 1){
                $version = explode('.', $matches[1]);
                $version = reset($version);
                $userAgentVersionBodyClassName = $this->buildUserAgentBodyClassName(
                    self::USER_AGENT_BODY_CLASS_NAME_INTERNET_EXPLORER . $version
                );
            }
        }

        return $userAgentVersionBodyClassName;
    }

    /**
     * Return all the other store IDs, relative to the current one.
     *
     * @param int|null $currentStoreId
     *
     * @return array
     */
    public function getAllOtherStoreIds($currentStoreId = null)
    {
        $allOtherStoreIds = array();

        if (!$currentStoreId) {
            $currentStoreId = Mage::app()->getStore()->getStoreId();
        }

        $stores = Mage::app()->getStores();
        if (!empty($stores[$currentStoreId])) {
            unset($stores[$currentStoreId]);
        }

        if (count($stores)) {
            $allOtherStoreIds = array_keys($stores);
        }

        return $allOtherStoreIds;
    }

    /**
     * Check if the specified layout handle exists in the current context.
     * @param string $handleName
     * @return boolean
     */
    public function hasHandle($handleName)
    {
        return in_array(
            $handleName,
            Mage::app()->getLayout()->getUpdate()->getHandles()
        );
    }

    public function getTextDirection()
    {
        $rtlLocales = array(
            'ar_SA',
        );

        $localeCode = Mage::app()->getLocale()->getLocaleCode();

        return in_array($localeCode, $rtlLocales) ? 'rtl' : 'ltr';
    }
}
