<?php

/**
 *
 *
 * @author Sam Schmidt <samuel@dersam.net>
 * @since 2015-12-21
 * @company Linus Shops
 */
class CmsCacheTestBlock extends Mage_Core_Block_Template
{
    use Linus_Common_Trait_Cms;

    public function __construct(array $args)
    {
        parent::__construct($args);
    }

    public function getBlockId()
    {
        return 1234;
    }

    public function _toHtml()
    {
        return <<<CSV
something,written
CSV;

    }
}
