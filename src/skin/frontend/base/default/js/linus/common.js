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
        // Store data immediately.
        getCspData();
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
     * Use injected dependencies.
     *
     * If a dependencies object has been defined, but is empty, halt execution
     * of script. Likewise, if a named dependency is empty, halt as well.
     *
     * In order to inject dependencies, define them in a literal object passed
     * to IIFE.
     *
     * Instead of directly calling dependencies, it is recommended to first
     * check if the dependency can be used. This is a safeguard against running
     * code that might not be available, but is not obligatory. If the
     * dependency is available, the dependency will be returned.
     *
     * Example usage:
     *  var MyModule = Common.use('MyModule', dependencies);
     *
     * @param dependencyName The name of the dependency.
     * @param dependencies Object of dependencies that use can access.
     */
    function use(dependencyName, dependencies)
    {
        if (!dependencyName || $.isEmptyObject(dependencies)) {
            throw new Error('There are no dependencies defined. Ensure they have been injected.');
        }

        // Ensure the dependencies themselves are defined.
        if(dependencies.hasOwnProperty(dependencyName)
            && !$.isEmptyObject(dependencies[dependencyName])
        ) {
            return dependencies[dependencyName];
        }

        throw new Error('Dependency `' + dependencyName + '` has not been defined. Script execution halting.');
    }

    /**
     * Wrapper to get CSP data.
     *
     * Pass the key name to retrieve exact value, or pass nothing to get the
     * entire data set.
     *
     * @param string|optional cspDataKey Get key value or entire data set.
     * @param string cspSelectorName Optional node target to get data.
     *
     * @return string | object
     */
    function getCspData(cspDataKey, cspSelectorName)
    {
        cspSelectorName = (typeof cspSelectorName !== 'undefined')
            ? cspSelectorName
            : '.csp-data';

        // Populate on first execution, then remove.
        if ($.isEmptyObject(cspData)) {

            var mergedCspData = {};

            $(cspSelectorName).each(function() {
                var newCspData = JSON.parse(decodeURIComponent($(this).val()));
                // Deep merge all values together.
                jQuery.extend(true, mergedCspData, newCspData);

                // Remove CSP node from DOM, so origin of data seems mythical.
                $(this).remove();
            });

            cspData = mergedCspData;
        }

        var cspDataValue = (cspData[cspDataKey])
            ? cspData[cspDataKey]
            : '';

        // Passing nothing will return the whole CSP data set.
        if (typeof cspDataKey === 'undefined'){
            cspDataValue = cspData;
        }

        return cspDataValue;
    }

    /**
     * Iterate over nodes in DOM subtree section to find all text nodes.
     *
     * This is multitudes faster than regular DOM traversal techniques. See
     * https://jsperf.com/createnodeiterator-vs-createtreewalker-vs-getelementsby
     * for additional benchmarking. There is no jQuery dependency in this
     * function, but it will only work in IE9+.
     *
     * This function skips all empty text nodes, and text nodes who's parents
     * are script|noscript|iframe elements.
     *
     * @param element Section to scan for text nodes
     *
     * @return array Array of live nodes to manipulate.
     *
     * @todo Search through input nodes as well and get the value of each.
     */
    function getAllTextNodesIn(element)
    {
        // See https://developer.mozilla.org/en-US/docs/Web/API/NodeIterator
        var nodeIterator = document.createNodeIterator(element, NodeFilter.SHOW_TEXT, function(node)
        {
            // If only whitespace, don't use.
            var textString = node.textContent.trim();
            // Do not translate text nodes from these parents, as it's code.
            var hasBadParent = /(.*?script|iframe)/gi.test(node.parentNode.nodeName);

            if (textString
                && !hasBadParent
            ) {
                return NodeFilter.FILTER_ACCEPT;
            } else {
                return NodeFilter.FILTER_SKIP;
            }
        }, false);

        var textNodes = [], currentNode;
        while (currentNode = nodeIterator.nextNode()) {
            textNodes.push(currentNode);
        }

        return textNodes;
    }

    /**
     * Translate all text nodes found within provided DOM node.
     *
     * This will use the found text node values and auto-translate them
     * against the translations provided by the CSP methods.
     *
     * Any string that you want to translate must be wrapped in a parent node.
     * Otherwise, the risk is run of destroying tags, in the case of a node
     * that contains another node and unwrapped text.
     *
     * An option is provided to disable this aggressive behavior, but
     * can result in text not being translated.
     *
     * @param element
     * @param aggressive
     */
    function translateAllTextIn(element, aggressive)
    {
        if (typeof aggressive == 'undefined' || aggressive !== false) {
            aggressive = true;
        }

        // nodeIterator does not recognize jQuery objects, so ensure to get
        // the original DOM reference.
        if (element instanceof jQuery) {
            element = $(element).get(0);
        }

        var textNodes = getAllTextNodesIn(element);
        $(textNodes).each(function () {
            var textString = $.trim($(this.parentNode).text());
            // Translate any text found from CSP translations.
            var translation = __(textString);


            //Only modify a node if the translation is different from the
            // original string, and aggressive mode is disabled
            if (!aggressive && translation != textString) {
                $(this.parentNode).text(translation);
            }
        });
    }

    /**
     * Hides the selected object, removing it from the page flow.
     *
     * This function applies the js-hidden class from common.css. This is an
     * improvement over the standard jQuery show/hide methods as it does not
     * create any inline css.
     *
     * @param selector
     */
    function hide(selector)
    {
        selector = $(selector);

        if (!selector.hasClass('js-hidden')) {
            selector.addClass('js-hidden');
        }
    }

    /**
     * Hides the selected object.
     *
     * This function applies the js-invisible class from common.css. This is an
     * improvement over the standard jQuery show/hide methods as it does not
     * create any inline css.
     *
     * @param selector
     */
    function invisible(selector)
    {
        selector = $(selector);

        if (!selector.hasClass('js-invisible')) {
            selector.addClass('js-invisible');
        }
    }

    /**
     * Shows an element that was hidden using the js-hidden or js-invisible classes.
     *
     * @param selector
     */
    function show(selector)
    {
        selector = $(selector);

        selector.removeClass('js-hidden');
        selector.removeClass('js-invisible');
    }

    /**
     * Display an element for X seconds, then hide is
     * @param selector
    * @param seconds
     */
    function showUntil(selector, seconds)
    {
        show(selector);
        setTimeout(function(){
            hide(selector);
        }, seconds * 1000, selector);
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
     * Public methods.
     */
    return {
        __: __,
        getCspData: getCspData,
        hide: hide,
        invisible: invisible,
        show: show,
        showUntil: showUntil,
        translateAllTextIn: translateAllTextIn,
        use: use
    };

}(jQuery));
