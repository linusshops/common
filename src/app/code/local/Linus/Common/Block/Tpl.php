<?php

/**
 * Contains frontend templates for use by the JS automatic templating system.
 *
 * @author Sam Schmidt <samuel@dersam.net>
 * @since 2016-01-25
 */
class Linus_Common_Block_Tpl extends Mage_Core_Block_Template
{
    /**
     * Depending on the value of render_mode, if we are in 'tpl', echo
     * the first parameter, otherwise echo the second.
     * @param string $tplEchoValue
     * @param string $defaultEchoValue
     */
    public function ifTpl($tplEchoValue, $defaultEchoValue)
    {
        if ($this->isTplMode()) {
            $out = $tplEchoValue;
        } else {
            $out = $defaultEchoValue;
        }

        return $out;
    }

    /**
     * Inject tpl tag to open a JS foreach function for iteration
     * @param string $collectionVariable the name of the collection to iterate
     * from the data passed to the template.
     * @param string $itemName name of the instance from the collection on a
     * given iteration
     */
    public function each($collectionVariable='items', $itemName='item')
    {
        return $this->wrap("_.forEach($collectionVariable, function($itemName){");
    }

    /**
     * Close an iteration function opened by each.
     */
    public function endeach()
    {
        return $this->wrap("});");
    }

    public function displayCondition($condition)
    {
        return $this->wrap("if ($condition) {");
    }

    public function displayElseIf($condition)
    {
        return $this->wrap("} else if($condition) {");
    }

    public function displayElse()
    {
        return $this->wrap("} else {");
    }

    public function endDisplayCondition()
    {
        return $this->wrap("}");
    }

    protected function wrap($code)
    {
        return "{{% $code }}";
    }

    /**
     * Detect if the block is rendering in tpl or magento mode.
     * @return bool
     */
    public function isTplMode()
    {
        return $this->getRenderMode() == 'tpl';
    }
}
