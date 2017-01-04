<?php

/**
 * Generic class to allow easy use of the csv parser trait from layout xml.
 *
 * @author Sam Schmidt <samuel@dersam.net>
 * @since 2016-01-05
 * @company Linus Shops
 */
class Linus_Common_Block_Csv extends Linus_Common_Block_CommonAbstract
{
    use Linus_Common_Trait_Csv_Parser;
    use Linus_Adapter_Trait_Cms_Segment;
}
