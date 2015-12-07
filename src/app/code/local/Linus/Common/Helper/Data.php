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
}
