var linus = linus || {};

/**
 * common.js - Linus Common
 *
 * Provide useful set of frontend methods for interacting with Magento.
 *
 * @author Dane MacMillan <work@danemacmillan.com>
 */

linus.common = linus.common || (function($)
{
    'use strict';

    /**
     * Constructor
     *
     * @private
     */
    function __construct()
    {

    }

    /**
     * Initialize class. Register for DOM ready.
     */
    (function __init() {
        $(document).ready(function(e) {
            __construct();
        });
    }());

    /**
     * Return empty object. Reveal methods if necessary.
     */
    return {};

}(jQuery));
