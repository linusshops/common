<?php

/**
 * Class Linus_Common_Block_Common
 *
 * This should be used for providing action layout handles. These are methods
 * that can be executed from directly within the layout XML. Note that a
 * `linus_common` block is available from the default handle, so any module
 * that wants to call the methods available here can simply reference that
 * block name and call the action method.
 *
 * @author Dane MacMillan <work@danemacmillan.com>
 */
class Linus_Common_Block_Common extends Linus_Common_Block_CommonAbstract
{
    /**
     * This adds an arbitrary class name the body class attribute.
     *
     * Example usage on the `checkout_cart_index` handle:
     *
     *  <checkout_cart_index>
     *      <reference name="linus_common">
     *          <action method="addClassNameToBodyClass">
     *              <classname>hiyooo</classname>
     *          </action>
     *      </reference>
     *  </checkout_cart_index>
     */
    function addClassNameToBodyClass($className)
    {
        /** @var Mage_Page_Block_Html $root */
        $root = $this->getLayout()->getBlock('root');

        if ($root) {
            $root->addBodyClass($className);
        }
    }

    /**
     * This adds a browser's user agent the body class.
     *
     * Internet Explorer should really be the only use case for this, but it can
     * be expanded to house more user agents and their versions.
     *
     * Example usage on the `checkout_cart_index` handle:
     *
     *  <checkout_cart_index>
     *      <reference name="linus_common">
     *          <action method="addUserAgentBodyClass"/>
     *      </reference>
     *  </checkout_cart_index>
     */
    function addUserAgentToBodyClass()
    {
        $this->addClassNameToBodyClass('ua');
    }
}
