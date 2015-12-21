<?php

/**
 *
 *
 * @author Sam Schmidt <samuel@dersam.net>
 * @since 2015-12-21
 * @company Linus Shops
 */
class LinusCommonTraitCmsTest extends PHPUnit_Framework_TestCase
{
    public function testCsvParse()
    {
        $block = new CmsTestBlock(array());

        $data = $block->something();
        $this->assertEquals('written', $data);
        $this->assertEquals('', $block->nonexistent());
    }

    public function testCsvMultilineParse()
    {
        $block = new CmsMultilineTestBlock(array());

        $this->assertEquals('written', $block->something());
        $this->assertEquals('the answer is thus', $block->data());
        $this->assertEquals('too much money', $block->cost());
        $this->assertEquals('', $block->nonexistent());
    }

    public function testCsvNonAlphanumericParse()
    {
        $block = new CmsNonAlphaTestBlock(array());

        $this->assertEquals('written', $block->something123());
        $this->assertEquals('too much money', $block->cost());
        $this->assertEquals('', $block->nonexistent());
    }
}
