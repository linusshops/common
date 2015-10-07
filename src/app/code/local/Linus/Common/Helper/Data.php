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

    /**
     * Create shell structure for JSON responses.
     *
     * @param array|bool $payload The main data payload.
     * @param string $feedbackMessage The feedback message.
     * @param array $feedbackDebug The debug dump
     *
     * @return array
     */
    public function buildJsonPayload($payload = array(), $feedbackMessage = '', $feedbackDebug = array())
    {
        $error = ($payload || count($payload))
            ? 0
            : 1;

        if (!strlen($feedbackMessage)) {
            $feedbackMessage = (count($payload))
                ? 'Data retrieved successfully!'
                : 'Data could not be retrieved.';
        }

        // If developer mode is off, clear any revealing debug traces.
        if (!Mage::getIsDeveloperMode()) {
            $feedbackDebug = array();
        }

        return json_encode(array(
            'error' => $error,
            'feedback' => array(
                'message' => $feedbackMessage,
                'debug' => $feedbackDebug
            ),
            'payload' => $payload,
        ));
    }

    /**
     * Send JSON response body to client.
     *
     * @param array $payload
     * @param string $feedbackMessage
     * @param array $feedbackDebug
     * @param int $httpCode
     * @param int $cacheTimeSeconds
     *
     * @throws Zend_Controller_Response_Exception
     */
    public function sendResponseJson($payload = array(), $feedbackMessage = '', $feedbackDebug = array(), $httpCode = 200, $cacheTimeSeconds = 0)
    {
        $cacheControlDirectives = (!$cacheTimeSeconds)
            ? "private, no-cache, no-store, no-transform, max-age=0, s-maxage=0"
            : "public, no-transform, max-age=$cacheTimeSeconds, s-maxage=$cacheTimeSeconds";

        // For determining Expires. Magento already defines it, so just work
        // with the deprecated header, even though Cache-Control is superior.
        $expireTimestamp = time() + $cacheTimeSeconds;
        $expiresHeader = gmdate('D, d M Y H:i:s', $expireTimestamp) . ' GMT';

        // This should not be defined at all.
        $pragmaHeader = '';

        Mage::app()->getResponse()
            ->clearAllHeaders()
            ->setHeader('Content-type', 'application/json', true)
            ->setHeader('Cache-Control', $cacheControlDirectives, true)
            ->setHeader('Expires', $expiresHeader, true)
            ->setHeader('Pragma', $pragmaHeader, true)
            ->setBody($this->buildJsonPayload($payload, $feedbackMessage, $feedbackDebug))
            ->setHttpResponseCode($httpCode);
    }
}
