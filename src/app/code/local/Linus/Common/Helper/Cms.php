<?php

/**
 *
 *
 * @author Sam Schmidt <samuel@dersam.net>
 * @since 2015-12-22
 * @company Linus Shops
 */
class Linus_Common_Helper_Cms extends Mage_Core_Helper_Abstract
{
    public function getCsvBlock($blockId)
    {
        return Mage::app()->getLayout()->createBlock('linus_common/cms_csv')
            ->setBlockId($blockId)
            ->setStoreId(Mage::app()->getStore()->getId())
        ;
    }
}
