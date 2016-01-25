<?php

/**
 *
 *
 * @author Sam Schmidt <samuel@dersam.net>
 * @since 2016-01-25
 */
class Linus_Common_Model_Template_Lodash extends Mage_Core_Block_Template
{
    /**
     * @param array $blockNames
     * @return array
     */
    public function getAllLodashBlocksByName(Mage_Core_Model_Layout $layout, array $blockNames)
    {
        $lodashTemplates = array();

        foreach ($blockNames as $name) {
            if ($block = $layout->getBlock($name)) {
                $lodashTemplates[$name] = $block->toHtml();
            }
        }

        return $lodashTemplates;
    }
}
