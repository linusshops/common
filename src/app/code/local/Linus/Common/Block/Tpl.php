<?php

/**
 * Contains frontend templates for use by the JS automatic templating system.
 * Provides methods for generating common JS code prepared for use in TPL. Using
 * these methods will make your life easier, since it avoids directly showing
 * the TPL language that has no hinting or help.
 *
 * @author Sam Schmidt <samuel@dersam.net>
 * @since 2016-01-25
 */
class Linus_Common_Block_Tpl extends Linus_Common_Block_CommonAbstract
{
    /**
     * Depending on the value of render_mode, if we are in 'tpl', echo
     * the first parameter, otherwise echo the second.
     * @param string $tplEchoValue
     * @param string $defaultEchoValue
     * @return string
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
     * @return string
     */
    public function each($collectionVariable='items', $itemName='item')
    {
        return $this->wrap("_.forEach($collectionVariable, function($itemName, index){");
    }

    /**
     * Close an iteration function opened by each.
     */
    public function endeach()
    {
        return $this->wrap("});");
    }

    /**
     * Begin a conditional block
     * @param $condition
     * @return string
     */
    public function displayBlockCondition($condition)
    {
        return $this->wrap("if ($condition) {");
    }

    /**
     * Add an elseif statement to a conditional block
     * @param $condition
     * @return string
     */
    public function displayBlockElseIf($condition)
    {
        return $this->wrap("} else if($condition) {");
    }

    /**
     * Add an else statement to a conditional block.
     * @return string
     */
    public function displayBlockElse()
    {
        return $this->wrap("} else {");
    }

    /**
     * Close a conditional block
     * @return string
     */
    public function endDisplayBlockCondition()
    {
        return $this->wrap("}");
    }

    /**
     * Display some markup if a given condition is met.
     * @param $condition
     * @param $markup
     * @return string
     */
    public function showIf($condition, $markup)
    {
        return $this->displayBlockCondition($condition)
            .$markup
            .$this->endDisplayBlockCondition()
        ;
    }

    /**
     * Wrap some js code in the execution tags
     * @param $code
     * @return string
     */
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

    /**
     * Start a standard JS for loop
     * @param $counterName string name of the counter variable
     * @param $counterStartValue string the starting value of the counter (any valid rvalue)
     * @param $terminationCondition string condition that must be false to end loop
     * @param $iterationAction string action to take on completion of an iteration
     * @return string
     */
    public function iterate($counterName, $counterStartValue, $terminationCondition, $iterationAction)
    {
        return "{{% for (var $counterName=$counterStartValue; $terminationCondition; $iterationAction) { }}";
    }

    /**
     * Close a for loop block
     * @return string
     */
    public function endIterate()
    {
        return "{{% } }}";
    }

    /**
     * Generate the TPL markup to echo a variable.
     * @param $variable
     * @return string
     */
    public function e($variable)
    {
        return "{{".$variable."}}";
    }

    /**
     * Echo the resulting value of a JS expression
     * @param $expression
     * @return string
     */
    public function p($expression)
    {
        return $this->wrap("print({$expression})");
    }

    /**
     * Print a value from the page's CSP dataset.
     * @param $path string the path to print.
     * @return string
     */
    public function csp($path)
    {
        return
            "{{% print(linus.common.getCspData('".$path."')) }}";
    }
}
