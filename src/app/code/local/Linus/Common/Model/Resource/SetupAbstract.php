<?php

/**
 * Extend this when creating resource setup scripts.
 *
 * @author Dane MacMillan <work@danemacmillan.com>
 */
abstract class Linus_Common_Model_Resource_SetupAbstract extends Mage_Core_Model_Resource_Setup
{
    /**
     * Basic setup feedback template that appears at top of site on success.
     *
     * This will only appear when a version bump occurs within a module.
     */
    const TPL_FEEDBACK_SETUP_SUCCESS = '<div class="magento-module-setup magento-module-setup-success">%s</div>';

    /**
     * Basic setup feedback template that appears at top of site on fail.
     */
    const TPL_FEEDBACK_SETUP_FAIL = '<div class="magento-module-setup magento-module-setup-fail">%s</div>';

    /**
     * Shortcut for translation method.
     *
     * @param $text
     *
     * @return string
     */
    public function __($text)
    {
        return Mage::helper('core')->__($text);
    }

    /**
     * Get the module name running setup.
     *
     * @return string
     */
    public function getModuleName()
    {
        $moduleName = 'Complete!';

        $resourceSetupName = get_class($this);
        if (strlen($resourceSetupName)) {
            $resourceSetupNameParts = explode('_', $resourceSetupName);
            $resourceSetupNameParts = array_slice($resourceSetupNameParts, 0, 2);
            $moduleName = implode('_', $resourceSetupNameParts);
        }

        return $moduleName;
    }

    /**
     * Output the success setup feedback.
     *
     * @return string
     */
    public function outputSetupFeedback()
    {
        $upgradeText = $this->__('Upgrading module resource') . ': ' . $this->getModuleName();
        echo sprintf(
            self::TPL_FEEDBACK_SETUP_SUCCESS,
            $upgradeText
        );
    }

    /**
     * Output the fail setup feedback and log exception.
     *
     * @param Exception $Exception
     */
    public function outputSetupFeedbackFail($Exception)
    {
        $upgradeText = $this->__('Failure upgrading module resource') . ': ' . $this->getModuleName();
        echo sprintf(
            self::TPL_FEEDBACK_SETUP_FAIL,
            $upgradeText
        );

        Mage::log($upgradeText);
        Mage::log($Exception->getMessage());
        Mage::log($Exception->getTraceAsString());

        if (Mage::getIsDeveloperMode()) {
            var_dump($Exception);
        }

    }
}
