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
}
