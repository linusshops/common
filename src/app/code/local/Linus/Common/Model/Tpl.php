<?php

/**
 * Provide methods for finding and manipulating Common tpl templates
 * before returning them to the frontend.
 *
 * @author Sam Schmidt <samuel@dersam.net>
 * @since 2016-01-25
 */
class Linus_Common_Model_Tpl extends Mage_Core_Block_Template
{
    /**
     * Fetch an array of the templates matching the provided block names.
     * @param array $blockNames
     * @return array
     */
    public function getTplBlocksByName(Mage_Core_Model_Layout $layout, array $blockNames)
    {
        $lodashTemplates = array();

        foreach ($blockNames as $templateKey) {
            //Strip id and class indicators from the key to get the block identifier.
            $identifier = preg_replace('/^([\.\#])/', '', $templateKey);
            if ($block = $layout->getBlock($identifier)) {
                $lodashTemplates[$templateKey] = $block->toHtml();
            }
        }

        return $lodashTemplates;
    }
}
