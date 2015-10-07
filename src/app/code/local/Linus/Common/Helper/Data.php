<?php

/**
 * Class Linus_Common_Helper_Data
 *
 * Provide common verification helpers while in the observed event.
 *
 * @author Dane MacMillan <work@danemacmillan.com>
 */
class Linus_Common_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Hold miscellaneous front data to be encoded for CSP.
     *
     * This is also where frontend translations are stored, using the `__`
     * array key.
     *
     * @var array
     */
    private $cspData = array();

    /**
     * Provide a translation map.
     *
     * Provide a translation map, which will merge with any others previously
     * set. This is typically used in conjunction with encodeCspContent for
     * frontend JavaScript translations provided by Magento.
     *
     * @param array $translationMap
     */
    public function setCspTranslation(array $translationMap)
    {
        // Special key for mapping translations within CSP frontend content.
        $this->setCspData(array(
            '__' => $translationMap
        ));
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
     * Encode an array that can be used in a hidden HTML input.
     *
     * This is used for circumventing the need to directly include JavaScript
     * blocks on a page, which is no good if creating a Content Security Policy
     * against inline JavaScript.
     *
     * This is the encoded content that can be safely inserted into a hidden
     * HTML input. It
     *
     * @param array $cspData
     *
     * @return array
     */
    public function getEncodedCspJsonContent(array $cspData = array())
    {
        return rawurlencode(json_encode());
    }
}
