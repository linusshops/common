<?php

/**
 * Contains frontend templates for use by the JS automatic templating system.
 *
 * @author Sam Schmidt <samuel@dersam.net>
 * @since 2016-01-25
 */
class Linus_Common_Block_Tpl extends Mage_Core_Block_Template
{
    public function ifTpl($tplEchoValue, $defaultEchoValue)
    {
        if ($this->isTplMode()) {
            echo $tplEchoValue;
        } else {
            echo $defaultEchoValue;
        }
    }

    public function each($collectionVariable='items', $itemName='item')
    {
        echo "{{% _.forEach($collectionVariable, function($itemName){ }}";
    }

    public function endeach()
    {
        echo "{{% }); }}";
    }

    public function isTplMode()
    {
        return $this->getRenderMode() == 'tpl';
    }
}
