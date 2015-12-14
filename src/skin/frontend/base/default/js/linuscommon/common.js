var linus = linus || {};

/**
 * common.js - Linus Common
 *
 * Provide useful set of frontend methods for interacting with Magento.
 *
 * @author Dane MacMillan <work@danemacmillan.com>
 */
linus.common = linus.common || (function($, Dependencies)
{
   'use strict';

    /**
     * Wrapper for accounting.js library.
     *
     * https://github.com/openexchangerates/accounting.js
     */
    var Accounting;

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
     * Implementation of Google's Web Font Loader. These are Common defaults.
     *
     * https://github.com/typekit/webfontloader
     *
     * @type object
     */
    var webFontConfig = {
        classes: false,
        events: false,
        timeout: 2000
    };

    /**
     * Toggle downloading of Web fonts.
     *
     * @type bool
     */
    var webFontsEnabled = true;

    /**
     * Constructor
     *
     * @private
     */
    function __construct()
    {
        Accounting = use('Accounting', Dependencies);
        setAccountingDefaultSettings();

        // Store data immediately.
        getCspData();

        // Let all other ready callbacks fire through codebase, then run this.
        deadLastReady(loadWebFonts);
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
            // original string, or aggressive mode is enabled
            if (aggressive || translation != textString) {
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
     * Find and write all text nodes in a given container to the console.
     *
     * This is a helper for developers- run in the browser console.  It will
     * create each line so that it can be easily copied and pasted for use
     * in the backend setCspTranslation method as an array. Use it so you
     * don't have to look for every text string manually.
     */
    function makeCspArray(selector)
    {
        $(getAllTextNodesIn($(selector)[0])).each(function(){
            console.log("'"+$.trim($(this.parentNode).text())+"'=>null,")
        });
    }

    /**
     * Get the given key from a urlencoded # url fragment.
     * Example: www.example.com#param1=test&param2=example
     * Calling getHashParameter('param1') will return 'test'.
     * Calling getHashParameter('param3') will return false.
     * @param key
     * @returns the value of the key, or false if the key does not exist.
     */
    function getHashParameter(key)
    {
        var query = window.location.hash.substring(1);
        var vars = query.split("&");
        for (var i=0;i<vars.length;i++) {
            var pair = vars[i].split("=");
            if (pair[0] == key) {
                return pair[1];
            }
        }
        return false;
    }

    /**
     * Pass a font object literal and it will be added to the font stack.
     *
     * This is an implementation of Google's Web Font Loader:
     * https://github.com/typekit/webfontloader.
     *
     * - Go to https://www.google.com/fonts#QuickUsePlace:quickUse
     * - Choose styles (1)
     * - Copy JavaScript font object (3).
     *
     * Example usage:
     *  Common.addWebFont({
     *      google: {
     *          families: ['Roboto:400,400italic:latin']
     *      }
     *  });
     *
     * Should the same provider but different fonts be called by multiple
     * modules, the provider's fonts will be merged and all of them will be
     * downloaded in a single payload.
     *
     * Note: this can also be used to load custom fonts. See the documentation
     * for details.
     *
     * @param fontObject Object literal of associated webfontloader font.
     */
    function addWebFont(fontObject)
    {
        $(document).on('Common:loadWebFonts',function(e, WebFontConfig) {
            $.each(WebFontConfig, function(provider, providerPayload) {
                if ($.type(providerPayload) === 'object') {
                    $.each(providerPayload, function(familiesKey, familiesValues) {
                        if ($.type(familiesValues) === 'array'
                            && !$.isEmptyObject(fontObject[provider])
                        ) {
                            $.merge(
                                fontObject[provider][familiesKey],
                                WebFontConfig[provider][familiesKey]
                            );

                        }
                    });
                }
            });

            $.extend(true, WebFontConfig, fontObject);
        });
    }

    /**
     * Prevent Web fonts from downloading.
     */
    function disableWebFonts()
    {
        webFontsEnabled = false;
    }

    /**
     * Load Google Web Fonts, if any added through event.
     *
     * Common calls this internally through the `deadLastReady` method to ensure
     * that all modules can add their own fonts. They do this by binding to
     * the `Common:loadWebFonts` event that is thrown in this method. Any
     * external module can then just call the `addWebFonts` method, and Common
     * will handle the queuing and loading of fonts. If no calls to
     * `addWebFont` are made, the Google webfont.js file will not be downloaded.
     *
     *  @event Common:loadWebFonts
     */
    function loadWebFonts()
    {
        if (webFontsEnabled) {
            window.WebFontConfig = webFontConfig;
            var lastWebFontConfig = JSON.stringify(window.WebFontConfig);

            $(document).trigger('Common:loadWebFonts', [webFontConfig]);

            if (lastWebFontConfig !== JSON.stringify(webFontConfig)) {
                $.extend(true, window.WebFontConfig, webFontConfig);

                var wf = document.createElement('script');
                wf.src = ('https:' == document.location.protocol ? 'https' : 'http') +
                    '://ajax.googleapis.com/ajax/libs/webfont/1.5.18/webfont.js';
                wf.type = 'text/javascript';
                wf.async = 'true';
                var s = document.getElementsByTagName('script')[0];
                s.parentNode.insertBefore(wf, s);
            }
        }
    }

    /**
     * Methods added by this will be the LAST ready callbacks fired.
     *
     * In other words, this hack ensures that despite the number of calls to
     * $.ready() throughout a codebase, the ones added with this method will be
     * run after all of them are run. This works because jQuery fires ready
     * callbacks in order. By scoping a ready callback within another ready
     * callback, this compels it to run after all the first-level callbacks are
     * run.
     *
     * This is for those use cases when `ready` is too early, and `load` is too
     * late. One such use case is when throwing custom events: events need to
     * be bound before the actual event is triggered. Doing this allows all
     * libraries that use Common to bind to any custom events, then the
     * actual event is fired last.
     */
    function deadLastReady(callback)
    {
        $(document).ready(function(e) {
            callback();
        });
    }

    /**
     * Get all data values from a form's input body.
     *
     * TODO: eventually provide a hijax helper for creating hijax requests,
     * which will wrap the standard jQuery ajax method.
     *
     * @param target HTML DOM node or jQuery object.
     *
     * @returns object
     */
    function getFormData(target)
    {
        return $(target).serializeArray().reduce(function(formObject, item) {
            formObject[item.name] = item.value;
            return formObject;
        }, {});
    }

    /**
     * Default settings for the Accounting library wrapper.
     */
    function setAccountingDefaultSettings()
    {
        Accounting.settings = {
            currency: {
                symbol : '$',   // default currency symbol is '$'
                format: {
                    pos : '%s%v',
                    neg : '%s (%v)',
                    zero: '%s0.00'
                }, // controls output: %s = symbol, %v = value/number (can be object: see below)
                decimal : '.',  // decimal point separator
                thousand: ',',  // thousands separator
                precision : 2   // decimal places
            },
            number: {
                precision : 2,  // default precision on numbers is 0
                thousand: ',',
                decimal : '.'
            }
        }
    }

    /**
     * Get the formatted price of a string or int.
     *
     * Provide '4444.98' or 4444.97777 and get '$4,444.98'.
     *
     * @param price String|Int
     *
     * @return string
     */
    function getFormattedPrice(price)
    {
        return Accounting.formatMoney(price);
    }

    /**
     * Get the formatted number of a string or int.
     *
     * Provide '4444.98' or 4444.97777 and get '4,444.98'.
     *
     * @param price String|Int
     *
     * return string
     */
    function getFormattedNumber(price)
    {
        return Accounting.formatNumber(price);
    }

    /**
     * Get the int of a number or int in a format ready for calculating.
     *
     * Provide '4444.98' or 4444.97777 and get 4444.98.
     *
     * @param price String|Int
     *
     * @return int
     */
    function getPriceAsInt(price)
    {
        return parseFloat(Accounting.toFixed(price));
    }

    /**
     * Pass array of subtotals, and get total subtotal.
     *
     * Example usage:
     *  calculateSubtotalFromBasePrices([99.87,100.10000]) returns 199.97
     *
     * @param basePrices Array
     *
     * @return int
     */
    function calculateSubtotalFromBasePrices(basePrices)
    {
        var subtotal = getPriceAsInt(0.00);
        for (var i = 0, l = basePrices.length; i < l; ++i) {
            subtotal =+ getPriceAsInt(basePrices[i]);
        }

        return subtotal;
    }

    /**
     * Pass a string or int in any format, and a quantity, and get the total.
     *
     * getSubtotalWithQuantities('$103.271', 3) returns 309.81
     *
     * @param unitPrice
     * @param unitQuantity
     *
     * @returns int
     */
    function calculateSubtotalFromQuantity(unitPrice, unitQuantity)
    {
        return getPriceAsInt(getPriceAsInt(unitPrice) * parseInt(unitQuantity));
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
        getHashParameter: getHashParameter,
        hide: hide,
        invisible: invisible,
        makeCspArray: makeCspArray,
        show: show,
        showUntil: showUntil,
        translateAllTextIn: translateAllTextIn,
        use: use,
        addWebFont: addWebFont,
        disableWebFonts: disableWebFonts,
        getFormData: getFormData,
        getFormattedNumber: getFormattedNumber,
        getFormattedPrice: getFormattedPrice,
        getPriceAsInt: getPriceAsInt,
        calculateSubtotalFromBasePrices: calculateSubtotalFromBasePrices,
        calculateSubtotalFromQuantity: calculateSubtotalFromQuantity
    };

}(jQuery, {
    Accounting: accounting || {}
}));
