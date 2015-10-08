<?php

/**
 * Class Linus_Common_Model_Observer
 *
 * @author Dane MacMillan <work@danemacmillan.com>
 */
class Linus_Common_Model_Observer
{
    /**
     * Observe adding item to cart event.
     *
     * Hijax the event so it responds to asynchronous requests.
     *
     * @TODO Dane - Look at oemparts integration.
     *
     * @param Varien_Event_Observer $observer
     */
    public function onControllerActionPredispatchCheckoutCartAdd(Varien_Event_Observer $observer)
    {

    }
}
