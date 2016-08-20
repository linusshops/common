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
    public function addClassNameToBodyClass($className)
    {
        /** @var Mage_Page_Block_Html $root */
        $root = $this->getLayout()->getBlock('root');

        if ($root) {
            $root->addBodyClass($className);
        }
    }

    /**
     * This adds the locale as a class name on the body tag.
     */
    public function addLocaleClassNameToBodyClass()
    {
        $localeCode = Mage::app()->getLocale()->getLocaleCode();
        $this->addClassNameToBodyClass($localeCode);
    }

    /**
     * Detect the locale and set the correct text direction code.
     *
     * For example, English is `ltr` direction, while Arabic is `rtl` direction.
     * This is useful for targeting design changes.
     */
    public function addTextDirectionClassNameToBodyClass()
    {
        $textDirection = array(
            'ar_SA' => 'rtl',
            'en_US' => 'ltr',
            'fr_FR' => 'ltr'
        );

        $localeCode = Mage::app()->getLocale()->getLocaleCode();

        if ($localeCode
            && array_key_exists($localeCode, $textDirection)
        ) {
            $this->addClassNameToBodyClass($textDirection[$localeCode]);
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
    public function addUserAgentToBodyClass()
    {
        $userAgentBodyClassName = $this->Common()->getUserAgentBodyClassName();
        $this->addClassNameToBodyClass($userAgentBodyClassName);

        $userAgentVersionBodyClassName = $this->Common()->getUserAgentVersionBodyClassName();
        $this->addClassNameToBodyClass($userAgentVersionBodyClassName);
    }

    /**
     * Add top most, primary category ID to body class.
     *
     * This is the cheapest way possible to retrieve the primary category
     * ID from the path provided by current_category. Levels 0 and 1 are
     * base Magento designations and never actually seen on the frontend as
     * categories. All primary categories start at level 2.
     *
     * Note that getting the parent ID of a category does not guarantee it is
     * a primary, or top most, category.
     */
    public function addCategoryIdToBodyClass()
    {
        $primaryCategoryLevel = 2;
        $currentCategory = Mage::registry('current_category');
        if ($currentCategory) {
            $categoryPath = $currentCategory->getPathIds();
            if (is_array($categoryPath)
                && array_key_exists($primaryCategoryLevel, $categoryPath)
            ) {
                $categoryIdBodyClassName = 'primary-category-id-' . $categoryPath[$primaryCategoryLevel];
                $this->addClassNameToBodyClass($categoryIdBodyClassName);
            }
        }
    }
}
