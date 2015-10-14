<?php

/**
 * Class Linus_Common_Helper_Request
 *
 * Provide additional request helpers.
 *
 * @author Dane MacMillan <work@danemacmillan.com>
 */
class Linus_Common_Helper_Request extends Mage_Core_Helper_Abstract
{
    /**
     * Check whether user agent making request is a bot crawler.
     *
     * High overhead processes that do not affect general scrape content, but
     * severely impact server performance can be turned off for bot crawlers.
     * This was created in response to WEBPERF-52.
     *
     * Testing:
     *
     *  // Mozilla/5.0 (compatible; Yahoo! Slurp; http://help.yahoo.com/help/us/ysearch/slurp)
     *  // Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)
     *  // Mozilla/5.0 (Macintosh; Intel Mac OS X 10.11; rv:43.0) Gecko/20100101 Firefox/43.0
     *
     *  curl --insecure --user-agent "Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)" https://develop.vagrant.dev/street-bike/parts.html?XDEBUG_SESSION_START | grep "amshopby-slider-price"
     *
     * @param string $userAgent
     *
     * @return bool
     *
     * @todo Curate more specific list of bot crawlers.
     */
    public function isBot($userAgent = '')
    {
        if (!$userAgent) {
            $userAgent = Mage::helper('core/http')->getHttpUserAgent();
        }

        $botCrawlers = array(
            'archive',
            'baidu',
            'bing',
            'BOT',
            'crawler',
            'facebook',
            'google',
            'httrack',
            'msn',
            'slurp',
            'spider',
            'yahoo',
            'yandex'
        );

        array_walk($botCrawlers, function(&$value) {
            $value = preg_quote(strtolower($value));
        });

        $regexBotCrawlers = '(' . implode('|', $botCrawlers) . ')';

        return (bool) preg_match('/' . $regexBotCrawlers . '/', $userAgent);
    }

    /**
     * Create shell structure for JSON responses.
     *
     * @param array|bool $payload The main data payload.
     * @param int $error The error code. A non-zero/not null value indicates an error
     * @param string $feedbackMessage The feedback message.
     * @param array $feedbackDebug The debug dump
     * @return array
     */
    public function buildJsonPayload($payload = array(), $feedbackMessage = '', $error = null, $feedbackDebug = array())
    {
        if ($error == null) {
            $error = ($payload || count($payload))
                ? 0
                : 1;
        }

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
     * Cancel dispatch to underlying controller when responses are handled locally.
     *
     * @param $controller
     */
    public function cancelDispatch($controller)
    {
        $controller->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
    }

    /**
     * Send JSON response body to client.
     *
     * @param array $payload
     * @param int $error The error code. A non-zero/not null value indicates an error
     * @param string $feedbackMessage
     * @param array $feedbackDebug
     * @param int $httpCode
     * @param int $cacheTimeSeconds
     *
     * @throws Zend_Controller_Response_Exception
     */
    public function sendResponseJson($payload = array(), $feedbackMessage = '', $error = null, $feedbackDebug = array(), $httpCode = 200, $cacheTimeSeconds = 0)
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
            ->setBody($this->buildJsonPayload($payload, $feedbackMessage, $error, $feedbackDebug))
            ->setHttpResponseCode($httpCode);
    }
}
