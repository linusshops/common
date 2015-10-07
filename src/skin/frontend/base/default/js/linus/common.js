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
     * Publicly expose methods built off this.
     */
    var common;

    /**
     * Clear asynchronous feedback message after n milliseconds.
     *
     * @var int
     */
    var clearFeedbackMessage = 5000;

    /**
     * Store CSP data once it is retrieved and parsed.
     *
     * @var object
     */
    var cspData = {};

    /**
     * Constructor
     *
     * @private
     */
    function __construct()
    {

    }

    /**
     * Translation method, which relies on CSP.
     *
     * @private
     */
    function __(textString)
    {
        var translation = getCspData('__')[textString];
        return (translation)
            ? translation
            : textString;
    }

    /**
     * Wrapper to get CSP data.
     *
     * @param string|null cspDataKey Get key value or entire data set.
     *
     * @return string | object
     */
    function getCspData(cspDataKey)
    {
        // Populate on the fly.
        if (!cspData) {
            cspData = JSON.parse(decodeURIComponent($('.csp-data').val()));
        }

        var cspDataValue = (cspData[cspDataKey])
            ? cspData[cspDataKey]
            : '';

        // Passing null will return the whole CSP data set.
        if (cspDataKey === null
            && typeof cspDataKey === 'object'
        ){
            cspDataValue = cspData;
        }

        return cspDataValue;
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
