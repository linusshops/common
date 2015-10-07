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
     * Clear asynchronous feedback message after n milliseconds.
     *
     * @type {number}
     */
    var clearFeedbackMessage = 5000;

    /**
     * Store CSP content once it is retrieved and parsed.
     *
     * @type {{}}
     */
    var cspContent = {};

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
        var translation = getCspContent()['__'][textString];
        return (translation)
            ? translation
            : textString;
    }

    /**
     * Wrapper to get baseUrl from CSP.
     *
     * @returns string
     */
    function getBaseUrl()
    {
        var baseUrl = getCspContent()['baseUrl'];
        return (baseUrl)
            ? baseUrl
            : '/';
    }

    /**
     * Helper for retrieving inline CSP content.
     *
     * @param nodeIdSelector
     *
     * @returns {*}
     */
    function getCspContent()
    {
        var cspContent = cspContent;
        if (!cspContent) {
            cspContent = JSON.parse(decodeURIComponent($('.csp-inline').val()));
        }

        return cspContent;
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
