<?php

/**
 * Class Linus_Common_Helper_Csp
 *
 * CSP helpers allow simple data to be securely passed to JavaScript without
 * the need to include inline JavaScript blocks. This is the first half of
 * the implementation; the second half exists in common.js, which includes its
 * own set of helpers for quickly grabbing this data.
 *
 * @author Dane MacMillan <work@danemacmillan.com>
 */
class Linus_Common_Helper_Csp extends Mage_Core_Helper_Abstract
{
    /**
     * Hold arbitrary data to be encoded for CSP methods on frontend.
     *
     * This is also where frontend translations are stored, using the `__`
     * array key.
     *
     * @var array
     */
    private $cspData = array();

    /**
     * CSS selector class name that JavaScript will target.
     *
     * @var string
     */
    private $cspSelectorClassName = 'csp-data';

    /**
     * Generate hidden input HTML with data that has been set.
     *
     * Typically this should be wrapped by a Magento Block method, then
     * the block should call that.
     *
     * @return string
     */
    public function generateHiddenCspMarkup()
    {
        $cspHiddenInputMarkupTemplate = '<input class="%s" type="hidden" value="%s" />';

        return sprintf(
            $cspHiddenInputMarkupTemplate,
            $this->cspSelectorClassName,
            $this->getEncodedCspJsonContent()
        );
    }

    /**
     * Encode an array that can be used in a hidden HTML input.
     *
     * This is used for circumventing the need to directly include JavaScript
     * blocks on a page, which is no good if creating a Content Security Policy
     * against inline JavaScript.
     *
     * This is the encoded content that can be safely inserted into a hidden
     * HTML input. If using the `setCspData` or `setCspTranslation` methods to
     * set all data, a call to this without a parameter will return all the
     * stored values.
     *
     * @param array $cspData
     *
     * @return string
     */
    public function getEncodedCspJsonContent($cspData = array())
    {
        if (!count($cspData)) {
            $cspData = $this->cspData;
        }

        return rawurlencode(json_encode($cspData));
    }

    /**
     * Set arbitrary CSP data for use by frontend.
     *
     * @param array $cspData
     */
    public function setCspData(array $cspData)
    {
        if (count(array_filter($cspData))) {
            $this->cspData = array_merge(
                $this->cspData,
                $cspData
            );
        }
    }

    /**
     * Provide a translation map.
     *
     * Provide a translation map, which will merge with any others previously
     * set. Pass an array with key being the text string to translate, and
     * the value being the translation, or null to fallback to Magento's
     * built-in translation method and locale files.
     *
     * Handle translation merges here, because array_merge will
     * replace duplicate entries, which is a good default for regular
     * CSP data, but not translations. Every call to setCspTranslation
     * would just replace the content in the `__` key. Meanwhile,
     * array_merge_recursive would not let values get overwritten if there
     * are duplicates, which is how standard CSP data should behave.
     *
     * @param array $translationMap
     */
    public function setCspTranslation(array $translationMap)
    {
        // Special key for mapping translations within CSP frontend content.
        if (!array_key_exists('__', $this->cspData)) {
            $this->cspData['__'] = array();
        }

        array_walk($translationMap, function(&$value, $key) {
            if (is_null($value) || empty($value)) {
                $value = Mage::helper('core')->__($key);
            }
        });

        // Merge current with new translations.
        $mergedTranslations = array_merge(
            $this->cspData['__'],
            $translationMap
        );

        $this->setCspData(array(
            '__' => $mergedTranslations
        ));
    }
}
