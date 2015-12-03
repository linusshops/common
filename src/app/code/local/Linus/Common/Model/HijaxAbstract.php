<?php

/**
 * Base Hijax model for hijacking controller actions through observers.
 *
 * Observers that make exclusive use of Magento's pre- and post-dispatch
 * controller events should extend this HijaxAbstract class. By doing so,
 * every observer method will have native access to the controller by simply
 * using `$this`, which eliminates the need to write all boilerplate
 * from within every observer method: this essentially renders the observer
 * class an instance of the currently running controller. For example, there is
 * no need to do `$currentController = $observer->getControllerAction()` and
 * then access a limited version of the controller from `$currentController`; by
 * limited, that means that none of the protected or private methods are
 * available. Extending this class ensures that all methods are available
 * to the observer, regardless of the foreign class' method visibility.
 *
 * Hijax is a technique for enhancing standard HTTP form POSTs that typically
 * expect synchronous behaviour, so that instead of the response being a header
 * redirect, the response can be some other arbitrary value, like JSON. This is
 * useful because no form POST action URLs need to be changed. The same endpoint
 * will work in a standard form POST, but also in an asynchronous (Ajax) POST.
 *
 * This makes extending the behaviour of controllers simple: no longer does the
 * standard Magento concern of rewriting core classes arise, because the
 * controller is not being rewritten. That means multiple modules will not be
 * conflicting, trying to establish rewrite supremacy. Rewriting Magento
 * classes in general are just inferior to the event system.
 *
 * The most straightforward way of implementing a Hijax method is to simply
 * name the method the entire name of the event, and then copy the contents
 * of the original method into this new one and make the changes. It is
 * ultimately no different than redefining a parent method in a child class.
 *
 * For example:
 *  - Method name: `onControllerActionPredispatchCheckoutCartEstimatePost`
 *  - Copy contents of: Mage_Checkout_CartController->estimatePostAction
 *
 * Event breakdown:
 *  - Prefix: onControllerActionPredispatch
 *  - Module: Checkout
 *  - Controller: Cart
 *  - Action: EstimatePost
 *
 * Using the example above, the module that makes use of this, will define a
 * file with the following path, extending this abstract class:
 *  - `Namespace/Modulename/Model/Hijax/CartControllerObserver.php`
 *
 * @author Dane MacMillan <work@danemacmillan.com>
 */
abstract class Linus_Common_Model_HijaxAbstract
{
    /**
     * The actual controller instance.
     *
     * It will morph depending on route. The autocompleted controller suggested
     * here is just the base one, which all controllers extend.
     *
     * @var $hijaxController Mage_Core_Controller_Front_Action
     */
    public $hijaxController;

    /**
     * Get the current instance controller in memory.
     *
     * The actual controller instance will be different.
     */
    public function __construct()
    {
        $this->hijaxController = Mage::app()->getFrontController()->getAction();
    }

    /**
     * Get local method access to current controller instance.
     *
     * The majority of the actual methods cannot be verified because they, too,
     * are magical, so just go ahead and pipe it through.
     *
     * Reflection is necessary so that protected and private methods can still
     * be called.
     *
     * @param string $name
     * @param array $arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        $arguments = implode(',', $arguments);

        $reflectedMethod = new ReflectionMethod($this->hijaxController, $name);
        if ($reflectedMethod->isProtected()
            || $reflectedMethod->isPrivate()
        ) {
            $reflectedMethod->setAccessible(true);

            return $reflectedMethod->invoke($this->hijaxController, $arguments);
        }

        return $this->hijaxController->$name($arguments);
    }

    /**
     * Get local property access to current controller instance.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        return $this->hijaxController->$name;
    }

    /**
     * Get local property access to current controller instance.
     *
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        $this->hijaxController->$name = $value;
    }

    /**
     * Cancel dispatch to underlying controller.
     *
     * This must be called to prevent the underlying, base controller from
     * also executing. The Hijax technique described here essentially moves the
     * control flow higher up and performs all logic there, so passing back
     * to the original controller would be harmful, and likely result in a
     * header redirect.
     */
    public function cancelDispatch()
    {
        $this->hijaxController->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
    }
}
