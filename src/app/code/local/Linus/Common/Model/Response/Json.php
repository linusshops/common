<?php

/**
 * Provides a fluent interface for creating standard JSON format responses.
 *
 * @author Sam Schmidt <samuel@dersam.net>
 * @since 2016-03-10
 */
class Linus_Common_Model_Response_Json
{
    protected $payload = array();
    protected $feedbackMessage = '';
    protected $options = array();

    /**
     * @return array
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * @param array $payload
     * @return Linus_Common_Model_Response_Json
     */
    public function setPayload($payload)
    {
        $this->payload = $payload;
        return $this;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param array $options
     * @return Linus_Common_Model_Response_Json
     */
    public function setOptions($options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @return string
     */
    public function getFeedbackMessage()
    {
        return $this->feedbackMessage;
    }

    /**
     * @param string $feedbackMessage
     * @return Linus_Common_Model_Response_Json
     */
    public function setFeedbackMessage($feedbackMessage)
    {
        $this->feedbackMessage = $feedbackMessage;
        return $this;
    }

    /**
     * @param $templateKey
     * @return $this
     */
    public function addTpl($templateKey)
    {
        $this->appendOption('tpl', $templateKey);

        return $this;
    }

    /**
     * @param $templateKey
     * @return $this
     */
    public function removeTpl($templateKey)
    {
        $this->dropOption('tpl', $templateKey);
        return $this;
    }

    /**
     * @return $this
     */
    public function reset()
    {
        $this->payload = array();
        $this->feedbackMessage = array();
        $this->options = array();

        return $this;
    }

    /**
     * @param $entry
     * @return $this
     */
    public function addDebugFeedback($entry)
    {
        $this->appendOption('feedbackDebug', $entry);
        return $this;
    }

    /**
     * @param $code
     * @return $this
     */
    public function setErrorCode($code)
    {
        $this->addOption('error', $code);
        return $this;
    }

    /**
     * @param $statusCode
     * @return $this
     */
    public function setHttpCode($statusCode)
    {
        $this->addOption('httpCode', $statusCode);
        return $this;
    }

    /**
     * @param $cacheTimeSeconds
     * @return $this
     */
    public function setCacheTime($cacheTimeSeconds)
    {
        $this->addOption('cacheTimeSeconds', $cacheTimeSeconds);
        return $this;
    }

    /**
     * @param $key
     * @param $value
     */
    protected function addOption($key, $value)
    {
        $this->options[$key] = $value;
    }

    /**
     * @param $key
     */
    protected function removeOption($key)
    {
        if (isset($this->options[$key])){
            unset($this->options[$key]);
        }
    }

    /**
     * Add an item to a list
     * @param $key
     * @param $value
     */
    protected function appendOption($key, $value)
    {
        if (!isset($this->options[$key])) {
            $this->options[$key] = array();
        }

        $this->options[$key][] = $value;
    }

    /**
     * Delete an item from a list
     * @param $key
     * @param $value
     */
    protected function dropOption($key, $value)
    {
        if (!isset($this->options[$key])) {
            $this->options[$key] = array();
        }

        if ( ($valueKey = array_search($value, $this->options[$key])) !== false) {
            unset($this->options[$key][$valueKey]);
        }
    }

    /**
     * @return $this
     */
    public function send()
    {
        Mage::helper('linus_common/request')->sendResponseJson(
            $this->payload,
            $this->feedbackMessage,
            $this->options
        );
        return $this;
    }
}
