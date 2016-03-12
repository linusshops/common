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
     *
     * @return string
     */
    public function plural($count, $singularWordForm, $pluralWordForm, $nilFormat = false)
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

            $formattedString = "%s $grammaticalWordForm";
        }

        return sprintf(
            Mage::helper('core')->__($formattedString),
            $count
        );
    }

    /**
     * Get the user agent body class name.
     */
    public function getUserAgentBodyClassName()
    {
        $userAgentBodyClassName = '';

        if (preg_match('~MSIE|Internet Explorer~i', $_SERVER['HTTP_USER_AGENT'])
            || (strpos($_SERVER['HTTP_USER_AGENT'], 'Trident/7.0; rv:11.0') !== false)
        ) {
            $userAgentBodyClassName = 'ie';
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

        if ($this->getUserAgentBodyClassName() == 'ie') {
            preg_match('/MSIE (.*?);/', $_SERVER['HTTP_USER_AGENT'], $matches);
            if(count($matches) < 2){
                preg_match('/Trident\/\d{1,2}.\d{1,2}; rv:([0-9]*)/', $_SERVER['HTTP_USER_AGENT'], $matches);
            }

            if (count($matches) > 1){
                $version = explode('.', $matches[1]);
                $version = reset($version);
                $userAgentVersionBodyClassName = 'ie' . $version;
            }
        }

        return $userAgentVersionBodyClassName;
    }
}
