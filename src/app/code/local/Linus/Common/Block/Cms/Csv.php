<?php

/**
 * A generic drop-in replacement for cms/block with the Csv trait already added.
 *
 * Instead of defining a separate block for every cms entry that needs the csv
 * trait, you can use "linus_common/cms_csv" anywhere you would have used
 * "cms/block".
 *
 * @author Sam Schmidt <samuel@dersam.net>
 * @since 2015-12-22
 * @company Linus Shops
 */
class Linus_Common_Block_Cms_Csv extends Mage_Cms_Block_Block
{
    use Linus_Common_Trait_Cms;
}
