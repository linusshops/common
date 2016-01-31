var linus = linus || {};

/**
 * common.js - Linus Common
 *
 * Provide useful set of frontend methods for interacting with Magento.
 *
 * @author Dane MacMillan <work@danemacmillan.com>
 */
linus.common = linus.common || (function($, _, Dependencies)
{
   'use strict';

    /**
     * Store jQuery noConflict on window as `$j`.
     *
     * The `$j` is a legacy name still being used by `theme.js` in Linus
     * Shops' theme repo. When that is removed, store it as `jQuery`.
     *
     * @type {jQuery}
     */
    window.$j = $;

    /**
     * Store lodash noConflict on window as `lodash`.
     *
     * Other modules can then include it their IIFEs.
     *
     * @type {lodash}
     */
    window.lodash = _;

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
     * Object store for memoized functions using lodash.
     *
     * @type Object
     */
    var mem = {};

    /**
     * Store for compiled templates
     * We store templates by their hash, so that different targets that share
     * the same template benefit from a single memoized function.
     *
     * @type Object
     */
    var compiledTemplateFunctions = {
        mappings: {},
        templates: {}
    };

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
     * Validate emails per the spec.
     *
     * From https://html.spec.whatwg.org/multipage/forms.html#valid-e-mail-address
     *
     * Note that this will evaluate dane@localhost as valid, per the spec.
     *
     * @type {RegExp}
     */
    var emailLoosest = /^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/;

    /**
     * Validate emails with traditional restrictions, such as TLD.
     *
     * See blame from: https://github.com/jzaefferer/jquery-validation/commit/dd162ae360639f73edd2dcf7a256710b2f5a4e64
     *
     * @type {RegExp}
     */
    var emailLoose = /^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))$/i;

    /**
     * This will deny valid, RFC 2822 emails, but most people do not use them.
     *
     * Most users do not use unusual characters in their emails. Even companies
     * like Google set limitations on what is allowed and do not follow the
     * RFC perfectly. Most people would look at a RFC-valid email with unusual
     * characters and think it illegal when in fact it is not.
     *
     * This is best regex to use for most people with emails.
     *
     * From: http://stackoverflow.com/a/46181/2973534
     *
     * @type {RegExp}
     */
    var emailStrict = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/i;

    var regexes = {
        properName: /^[a-z][a-z\s\-\'\.]+$/i,
        canadianPostalCode: /^[abceghjklmnprstvxy]\d[abceghjklmnprstvwxyz]( )?\d[abceghjklmnprstvwxyz]\d$/i,
        cityName: /^[a-z0-9][a-z0-9\s\-\'\.]+$/i,
        companyName: /^.{4,}$/i
    };

    /**
     * Constructor
     *
     * @private
     */
    function __construct()
    {
        // Store data immediately.
        getCspData();

        Accounting = use('Accounting', Dependencies);
        setAccountingDefaultSettings();

        setLodashDefaultSettings();

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
     * Get `baseUrl` set by CSP data.
     *
     * @returns {string}
     */
    function getBaseUrl()
    {
        return getCspData('baseUrl');
    }

    /**
     * Get `jsUrl` set by CSP data.
     *
     * @returns {string}
     */
    function getJsUrl()
    {
        return getCspData('jsUrl');
    }

    /**
     * Get `formKey` set by CSP data.
     *
     * @returns {string}
     */
    function getFormKey()
    {
        return getCspData('formKey');
    }

    /**
     * Get `locale` set by CSP data.
     *
     * @returns {string}
     */
    function getLocale()
    {
        return getCspData('locale');
    }

    /**
     * Get `mediaUrl` set by CSP data.
     *
     * @returns {string}
     */
    function getMediaUrl()
    {
        return getCspData('mediaUrl');
    }

    /**
     * Get `skinUrl` set by CSP data.
     *
     * @returns {string}
     */
    function getSkinUrl()
    {
        return getCspData('skinUrl');
    }

    /**
     * Get `skinUrl` set by CSP data.
     *
     * @returns {string}
     */
    function getStoreUrl()
    {
        return getCspData('storeUrl');
    }

    /**
     * Get `uenc` set by CSP data.
     *
     * @returns {string}
     */
    function getUenc()
    {
        return getCspData('uenc');
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
                $.extend(true, mergedCspData, newCspData);

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
        if (_.isArray(selector)) {
            _.map(selector, hide);
        } else {
            selector = $(selector);

            if (!selector.hasClass('js-hidden')) {
                selector.addClass('js-hidden');
            }
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
        if (_.isArray(selector)) {
            _.map(selector, invisible);
        } else {
            selector = $(selector);

            if (!selector.hasClass('js-invisible')) {
                selector.addClass('js-invisible');
            }
        }
    }

    /**
     * Shows an element that was hidden using the js-hidden or js-invisible classes.
     *
     * @param selector a single selector string, or a jquery object,
     *                 or an array containing a mixture of either
     */
    function show(selector)
    {
        if (_.isArray(selector)) {
            _.map(selector, show);
        } else {
            selector = $(selector);
            selector.removeClass('js-hidden');
            selector.removeClass('js-invisible');
        }
    }

    /**
     * Display an element for X seconds, then hide is
     * @param selector
    * @param seconds
     */
    function showUntil(selector, seconds)
    {
        if (_.isArray(selector)) {
            _.map(selector, showUntil);
        } else {
            show(selector);
            setTimeout(function () {
                hide(selector);
            }, seconds * 1000, selector);
        }
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
        return $(target).closest('form').serializeArray().reduce(function(formObject, item) {
            formObject[item.name] = item.value;
            return formObject;
        }, {});
    }

    /**
     * Default settings for the Accounting library wrapper.
     *
     * This will handle `fr_FR` locale.
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
        };

        if (getLocale() == 'fr_FR') {
            Accounting.settings.currency.format = {
                pos : '%v %s',
                neg : '(%v) %s',
                zero: '0,00 %s'
            };

            Accounting.settings.currency.decimal = ',';
            Accounting.settings.currency.thousand = ' ';
            Accounting.settings.number.decimal = ',';
            Accounting.settings.number.thousand = ' ';
        }
    }

    /**
     * Default settings for Lodash.
     *
     * Template
     * interpolate = {{}}
     * escape = {{-}}
     * evaluate = {{%}}
     *
     * Will convert default template delimiters to mustache-inspired delimiters.
     * The usual templating for lodash uses ERB (embedded ruby) style templating.
     */
    function setLodashDefaultSettings()
    {
        _.templateSettings.interpolate = /{{([\s\S]+?)}}/g; //{{}}
        _.templateSettings.escape = /{{-([\s\S]+?)}}/g; //{{-}}
        _.templateSettings.evaluate = /{{%([\s\S]+?)}}/g; //{{%}}
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
        return parseFloat(Accounting.toFixed(
            // Note that unformat should just use defaults set, but does not.
            // Submit issue regarding this, so decimal does not need to be
            // manually passed.
            Accounting.unformat(price, Accounting.settings.number.decimal)
        ));
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
     * Validate email according to different rules.
     *
     * @param email
     * @param strictness
     *
     */
    function validateEmail(email, strictness)
    {
        return mem.validateEmail(email, strictness);
    }

    mem.validateEmail = _.memoize(function(email, strictness)
    {
        var emailRegex = '';
        switch (strictness) {
            case 'loosest':
                emailRegex = emailLoosest;
                break;
            case 'loose':
                emailRegex = emailLoose;
                break;
            case 'strict':
            default:
                emailRegex = emailStrict;
                break;
        }

        return emailRegex.test(email);
    });

    /**
     * Validate Canadian Postal Codes.
     *
     * This will validate `a1b2c3` and `a1b 2c3`.
     *
     * @param postalCode
     * @returns {boolean}
     */
    function validatePostalCode(postalCode)
    {
        var validPostalCode = false;
        if (_.size(postalCode)) {
            postalCode = _.deburr(_.trim(stripRedundantSpaces(postalCode)));
            if (regexes.canadianPostalCode.test(postalCode)) {
                validPostalCode = postalCode;
            }
        }

        return validPostalCode;
    }

    /**
     * Basic name validation.
     *
     * This will deburr names before testing, as validating against unicode
     * characters from other languages, like accents in French, for example,
     * will invalidate it.
     *
     * @todo consolidate all regex checkers.
     *
     * @param name
     */
    function validateProperName(properName)
    {
        var validProperName = false;
        if (_.size(properName)) {
            properName = _.trim(stripRedundantSpaces(properName));
            if (regexes.properName.test(_.deburr(properName))) {
                validProperName = properName;
            }
        }

        return validProperName;
    }

    function validateCityName(cityName)
    {
        var validCityName = false;
        if (_.size(cityName)) {
            cityName = _.trim(stripRedundantSpaces(cityName));
            if (regexes.cityName.test(_.deburr(cityName))) {
                validCityName = cityName;
            }
        }

        return validCityName;
    }

    function validateCompanyName(companyName)
    {
        var validCompanyName = false;
        if (_.size(companyName)) {
            companyName = _.trim(stripRedundantSpaces(companyName));
            if (regexes.companyName.test(_.deburr(companyName))) {
                validCompanyName = companyName;
            }
        }

        return validCompanyName;
    }

    /**
     * Strip out redundant spaces.
     *
     * This is useful for inputs where a user types a full name, for example,
     * and multiple spaces are included between the      name     parts.
     *
     * @param string
     * @returns {*}
     */
    function stripRedundantSpaces(string)
    {
        return string.replace(/\s+/g, ' ');
    }

    /**
     * AJAX helper that conforms to Linus Shops' payload structure.
     *
     * This is the frontend counterpart to Linus_Common_Helper_Request
     * ->sendResponseJson.
     *
     * Highlights of this method:
     *
     * - The data returned to every callback is always in JSON format. Every
     * callback, except `error`, receives the payload content directly. The
     * `error` callback receives a `jqXHR` object.
     *
     * - All requests are auto-cached based on endpoint and request data, so
     * identical requests will be retrieved from memory.
     *
     * - Target selectors will automatically be injected with HTML content,
     * should corresponding data exist, either in the payload as a CSS selector
     * key name, or the feedback `message`. `Common:afterTargetPayloadInsert`
     * and `Common:afterTargetFeedbackInsert` events are fired after this
     * HTML content has been auto-inserted, which provide access to the
     * live node for further manipulation by other modules.
     *
     * - If a target selector exists, but does not have a corresponding data
     * node in the response data, Common will attempt automatic templating,
     * using the payload as the template data, and the target as the template
     * key. The tpl method is used for this functionality.
     *
     * - `Common:beforeMETHOD` and `Common:afterMETHOD` events are fired
     * before and after the asynchronous call, which can be used by other
     * modules for modifying a request before it is sent out, or after it has
     * completed. The event name is based on the method of the request (for
     * example, a POST will trigger `Common:beforePost` and `Common:afterPost`).
     *
     * - Debug data will automatically output to the console, if
     * provided.
     *
     * @param {string} endpoint - The URL endpoint to send request.
     * @param {string} method - The HTTP method to use.
     * @param {Object} requestData - The object literal to send.
     * @param {Object} callbacks - Callback object for named functions.
     * @param {function} callbacks.limbo - State while waiting for response /
     * show progress. Passes `requestData`.
     * @param {function} callbacks.valid - Response is valid AND reports no
     * `error`. Passes `responseData.payload`.
     * @param {function} callbacks.invalid - Response is valid BUT reports
     * `error`. Passes `responseData.payload`.
     * @param {function} callbacks.cleanup - This will always run after request
     * is complete. Passes `responseData.payload`.
     * @param {function} callbacks.error - This will run when an 4xx/5xx error
     * occurs. Passes `jqXHR`.
     */
    function ajax(endpoint, method, requestData, callbacks)
    {
        method = _.capitalize(method);
        var eventData = {
            endpoint: endpoint,
            requestData: requestData,
            callbacks: _.defaultsDeep(callbacks, {
                limbo: function(){},
                valid: function(){},
                invalid: function(){},
                cleanup: function(){},
                error: function(){}
            })
        };
        $(document).trigger('Common:before'+method, eventData);
        endpoint = eventData.endpoint;
        requestData = eventData.requestData;
        callbacks = eventData.callbacks;

        callbacks.limbo(requestData);

        mem.ajax(endpoint, method, requestData)
            .done(function(responseData) {
                var error = _.get(responseData, 'error');
                var payload = _.get(responseData, 'payload');
                var targetPayloadSelectors = _.get(responseData, 'target.payload', '');
                if (_.isNumber(error) && _.size(payload)) {
                    if (!_.isArray(targetPayloadSelectors)) {
                        targetPayloadSelectors = [targetPayloadSelectors];
                    }

                    _.each(targetPayloadSelectors, function(targetPayloadSelector) {
                        var $target = $(targetPayloadSelector);

                        if (_.isString(payload[targetPayloadSelector])
                            && $target.length
                        ) {
                            $target
                                .addClass('payload-target-container')
                                .html(payload[targetPayloadSelector])
                                .trigger('Common:afterTargetPayloadInsert');
                        } else if (_.isUndefined(payload[targetPayloadSelector])
                                && $target.length
                        ) {
                            tpl(targetPayloadSelector, payload);
                        }
                    });

                    if (error === 0) {
                        callbacks.valid(payload);
                    } else if (parseInt(responseData.error) >= 1) {
                        callbacks.invalid(payload);
                    }
                }
            })
            .fail(function(responseData) {
                mem.ajax.cache.delete(generateHash(endpoint, method, requestData));

                var jqXHR = responseData;
                if (_.has(jqXHR, 'responseJSON.payload')) {
                    callbacks.invalid(_.get(jqXHR, 'responseJSON.payload'));
                }

                callbacks.error(jqXHR);
            })
            .always(function (responseData) {
                var standardResponse = responseData;
                if (_.has(responseData, 'responseJSON.payload')) {
                    standardResponse = _.get(responseData, 'responseJSON');
                }

                var targetFeedbackSelector = _.get(standardResponse, 'target.feedback');
                var feedbackMessage = _.get(standardResponse, 'feedback.message');

                if (_.isString(targetFeedbackSelector)
                    && $(targetFeedbackSelector).length
                    && _.isString(feedbackMessage)
                ) {
                    $(targetFeedbackSelector)
                        .addClass('feedback-target-container feedback-error-' + standardResponse.error)
                        .html(feedbackMessage)
                        .trigger('Common:afterTargetFeedbackInsert');
                }

                callbacks.cleanup(standardResponse.payload);

                $(document).trigger('Common:after'+method, {
                    endpoint: endpoint,
                    requestData: requestData,
                    responseData: responseData
                });

                if (_.size(_.get(standardResponse, 'feedback.debug'))) {
                    console.log('Debug data:');
                    console.log(standardResponse);
                }
            });
    }

    /**
     * Memoized function for jQuery's `ajax` method.
     *
     * This will allow any asynchronous responses to be cached. The promise
     * returned by jQuery's ajax method is actually cached.
     *
     * @return {promise}
     */
    mem.ajax = _.memoize(function(endpoint, method, requestData) {
        return $.ajax(endpoint, {
            method: method,
            data: requestData
        });
    }, function(endpoint, method, requestData) {
        return generateHash(endpoint, method, requestData);
    });

    /**
     * Alias for Common.ajax with the GET http method
     * @param endpoint
     * @param callbacks
     */
    function get(endpoint, callbacks)
    {
        ajax(endpoint, 'GET', null, callbacks);
    }

    /**
     * Alias for Common.ajax with the POST http method
     * @param endpoint
     * @param requestData
     * @param callbacks
     */
    function post(endpoint, requestData, callbacks)
    {
        ajax(endpoint, 'POST', requestData, callbacks);
    }

    /**
     * Handles the compile and rendering of templates via lodash templating.
     *
     * Templates will be cached to localstorage, and the template function
     * will be cached on the page in the compiledTemplateFunctions store.
     *
     * The template key should be the css selector that indicates the target
     * for the template content, as provided in the JSON response `target.payload`.
     *
     * @param {array|string} templateKeys
     * @param {Object} data - The data to apply to the template. If false, tpl
     * will not render any data, but will download and parse the template if it
     * is not already available from the cache.
     */
    function tpl(templateKeys, data)
    {
        //Normalize inputs
        if (_.isUndefined(data)) {
            data = {};
        }

        if (!_.isArray(templateKeys)) {
            templateKeys = [templateKeys];
        }

        //Divide into cached and uncached templates. We will dispatch the fetch
        //of uncached templates, and then render from the cache list.
        var groupedTemplates = _.reduce(templateKeys, function(container, templateKey){
            //We want to get the local compiled template immediately, instead
            //of just noting the key, since there is the slim chance that if
            //local storage were cleared the template would not be available.
            var localTemplate = getLocalTpl(templateKey);

            if (localTemplate === false) {
                container.fetch.push(templateKey);
            } else {
                container.local.push({selector: templateKey, template: localTemplate});
            }

            return container;
        }, {local:[], fetch:[]});

        var localTemplates = _.get(groupedTemplates, 'local', []);
        var fetchTemplateKeys = _.get(groupedTemplates, 'fetch', []);

        fetchTemplateKeys = _.uniq(fetchTemplateKeys);

        tplFetch(fetchTemplateKeys, data);

        //If we're just prefetching, don't render.
        if (data !== false) {
            _.forEach(localTemplates, function (localTemplate) {
                tplRender(
                    data,
                    _.get(localTemplate, 'template'),
                    _.get(localTemplate, 'selector')
                );
            });
        }
    }

    /**
     * Event handler for templates being retrieved successfully from the server.
     * Compiles and renders the received templates, and caches as necessary.
     * @param {Object} data
     * @param {array} templates
     */
    function onValidTplFetch(data, templates) {
        _.forEach(templates, function(template, key){
            var content = _.get(template, 'content');
            var checksum =_.get(template, 'checksum');

            var compiled = tplCompile(
                key,
                content,
                checksum
            );

            storeLocalTpl(key, checksum, content);

            //If we're just prefetching, don't render.
            if (data !== false) {
                tplRender(
                    data,
                    compiled,
                    key
                );
            }
        });
    }

    /**
     * Retrieve frontend templates from the server.
     *
     * @param {array} templateKeys
     * @param {object} data
     */
    function tplFetch(templateKeys, data)
    {
        if (!_.isEmpty(templateKeys)) {
            post(getStoreUrl() + 'common/template', JSON.stringify(templateKeys), {
                valid: _.partial(onValidTplFetch, data)
            });
        }
    }

    /**
     * Retrieves the compiled tpl if it is stored locally, or false if it is
     * not available. Checks memory and localStorage, in that order.
     * @param {string} templateKey
     * @returns {function|boolean}
     */
    function getLocalTpl(templateKey)
    {
        //Check if it exists in memory- don't bother with localstorage if it
        //does, as this only persists on one request.
        //We store compiled templates by checksum, as they are memoized once
        //compiled. This way, multiple templates pointing to the same hash
        //all benefit from sharing the memoized fn.
        var checksum = _.get(compiledTemplateFunctions, 'mappings.'+templateKey, false);
        if (checksum) {
            var local = _.get(compiledTemplateFunctions, 'templates.' + checksum, false);
            if (local !== false) {
                return local;
            }
        }

        //Check local storage (if available), then check hashes
        //Invalidate and delete as necessary
        //If template exists and is not invalid, load it into memory
        if (isLocalStorageAvailable()) {
            checksum = window.localStorage.getItem('common-tpl-mapping:'+templateKey);
            var isValid = isLocalTplValid(templateKey, checksum);
            if (!_.isNull(checksum) && isValid) {
                var rawTemplate = window.localStorage.getItem('common-tpl-hash:'+checksum);
                if (!_.isNull(rawTemplate)) {
                    var compiled = tplCompile(templateKey, rawTemplate, checksum);
                    storeMemoryTpl(templateKey, checksum, compiled);
                    return compiled;
                }
            } else if (!isValid) {
                window.localStorage.removeItem('common-tpl-mapping:'+templateKey);
                window.localStorage.removeItem('common-tpl-hash:'+checksum);
            }
        }

        return false;
    }

    /**
     * Checks the retrieved template against the current checksum list, verifying
     * if the locally cached template is usable.
     * @param {string} templateKey
     * @param {string} checksum
     * @returns {boolean}
     */
    function isLocalTplValid(templateKey, checksum)
    {
        var checksums = getCspData('commonTplChecksums');
        var blockName = getTplBlockNameFromTemplateKey(templateKey);

        var cspChecksum = _.get(
            checksums,
            blockName
        );

        return checksum === cspChecksum;
    }

    /**
     * Transforms the block name for the template key to match the block name
     * after transformation of the selector. This key then matches the csp
     * data key for the template checksum.
     * @param {string} templateKey
     * @returns {string}
     */
    function getTplBlockNameFromTemplateKey(templateKey)
    {
        return templateKey
            .replace(/^([\.\#])/, '')
            .replace(/([-])/g, '_')
        ;
    }

    /**
     * Attempts to store the uncompiled template in localstorage.
     * @param {string} templateKey
     * @param {string} checksum
     * @param {string} templateContent
     */
    function storeLocalTpl(templateKey, checksum, templateContent)
    {
        if (isLocalStorageAvailable()) {
            try {
                window.localStorage.setItem('common-tpl-mapping:' + templateKey, checksum);
                window.localStorage.setItem('common-tpl-hash:' + checksum, templateContent);
            } catch (e) {
                console.log(e);
            }
        }
    }

    /**
     * Stores the compiled and memoized template function in memory.
     * @param {string} templateKey
     * @param {string} checksum
     * @param {string} compiledTemplate
     */
    function storeMemoryTpl(templateKey, checksum, compiledTemplate)
    {
        compiledTemplateFunctions.mappings[templateKey] = checksum;

        //The compiled function doesn't need to be updated if it already
        //exists in memory.
        if (!_.get(compiledTemplateFunctions, 'templates.'+checksum, false)) {
            compiledTemplateFunctions.templates[checksum] = _.memoize(
                compiledTemplate,
                _.partial(generateHash, templateKey)
            );
        }
    }

    /**
     * Test if local storage is available on the current browser.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/API/Web_Storage_API/Using_the_Web_Storage_API#Testing_for_support_vs_availability
     * @returns {boolean}
     */
    function isLocalStorageAvailable()
    {
        try {
            var storage = window['localStorage'],
                x = '__storage_test__';
            storage.setItem(x, x);
            storage.removeItem(x);
            return true;
        }
        catch(e) {
            return false;
        }
    }

    /**
     * Compiles the template content to a function, then stores it in memory
     * as a memoized function. Will also attempt to save the template
     * in localStorage, depending on availability.
     *
     * @param {string} templateKey
     * @param {string} templateContent
     * @param {string} checksum
     * @returns {function}
     */
    function tplCompile(templateKey, templateContent, checksum)
    {
        var compiled = _.template(templateContent);

        storeMemoryTpl(templateKey, checksum, compiled);

        return compiled;
    }

    /**
     * Render a template to the target locations on the page
     * @param {Object} data
     * @param {Function} compiledTemplate - a compiled template function
     * @param {string|jQuery} selector - the target selector location for the rendered template
     */
    function tplRender(data, compiledTemplate, selector)
    {
        var $target = $(selector);

        //Target doesn't exist, skip render.
        if (!$target.length) {
            return;
        }

        $target.html(
            compiledTemplate(data)
        );
    }

    /**
     * Focus on the first empty, visible, unfocused input within a node.
     *
     * The first child input that is found within a parent that is empty,
     * visible, and unfocused will be focused upon.
     *
     * @param {HTMLElement|string} node - Pass a selector string, or node, of
     * either the specific input to focus on, or the parent that contains
     * several of them.
     *
     * @param {Object} userOptions - User options available.
     * @param {HTMLElement|string} userOptions.node - Node or container node to
     * target. If only one relevant match found, it will be targeted directly,
     * regardless of its contents. If multiple are found, the one that is least
     * complete will be targeted. This is optional; if nothing is passed, the
     * entire DOM will be searched for the most relevant input. If an input
     * node with only a single match is found, it will take focus regardless of
     * its value or type, and the focused cursor will be placed at the end of
     * the text, instead of the beginning, as is the default.
     * @param {number} userOptions.delay - Time in milliseconds to wait until
     * focus is placed on an input. Default: 300.
     * @param {bool} userOptions.allowButtons - Decide whether buttons should
     * also take focus if they are most relevant. By default this is off
     * because users can mistakenly submit forms by pressing certain keys.
     * @param {bool} userOptions.allowRadios - Radios typically require a
     * selection from a pool, so by default this is off, because this method
     * does not make any assumption about the most relevant radio selection.
     */
    function focusMostRelevantInput(userOptions)
    {
        var options = _.defaultsDeep(userOptions||{}, {
            node: null,
            delay: 300,
            allowButtons: false,
            allowRadios: false
        });

        setTimeout(function() {
            var $currentFocus = $(document.activeElement);
            var focusSelectors = 'input, textarea, button';

            var selector = (options.node)
                ? options.node
                : focusSelectors;

            if (selector || !$currentFocus.is(focusSelectors)) {
                if (!$(selector).is(focusSelectors)) {
                    selector = $(selector).find(focusSelectors);
                }

                var originalMatches = $(selector).length;
                if (originalMatches === 1) {
                    options.allowButtons = true;
                    options.allowRadios = true;
                }

                var $selector = $(selector).filter(function(index, filteredNode) {
                    if ((!options.allowButtons && $(filteredNode).is('input[type=submit], button'))
                        || (!options.allowRadios && $(filteredNode).is('input[type=radio]'))
                    ) {
                        return false
                    }

                    var hasHiddenClass = false;
                    var hasDisplay = true;
                    var hasVisibility = true;
                    var hasOpacity = true;

                    $(filteredNode).parents().addBack().each(function(i, el) {
                        if ($(el).is('[class*=hidden]')) {
                            hasHiddenClass = true;
                        }

                        if ($(el).css('display') == 'none') {
                            hasDisplay = false;
                        }

                        if ($(el).css('visibility') == 'hidden') {
                            hasVisibility = false;
                        }

                        if ($(el).css('opacity') < 1) {
                            hasOpacity = false;
                        }
                    });

                    if ((!hasHiddenClass || hasOpacity)
                        && hasDisplay
                        && hasVisibility
                        && $(filteredNode).is(':visible')
                    ) {
                        return true;
                    }
                });

                var workingMatches = $selector.length;

                $selector.each(function(i, el) {
                    var $node = $(el);
                    var $submit = $node.is('input[type=submit], button');
                    var $radio = $node.is('input[type=radio]');

                    // Select only empty inputs or buttons, or if a
                    if (($submit || $radio || !$node.val())
                        && !$node.is(':focus')
                    ) {
                        if (!$submit && !$radio) {
                            $node[0].selectionStart = $node[0].selectionEnd = $node.val().length;
                        }

                        if ($radio) {
                            $node.prop('checked', true);
                        }

                        $node.focus();
                        return false;
                    }

                    // Last ditch effort, to select input even with text.
                    if (workingMatches == i+1
                        && !$submit
                        && !$radio
                    ) {
                        $node[0].selectionStart = $node[0].selectionEnd = $node.val().length;
                        $node.focus();
                        return false;
                    }
                });
            }
        }, options.delay);
    }

    /**
     * Generate string hash from arbitrary number of arguments.
     *
     * @return String
     */
    function generateHash()
    {
        return JSON.stringify(arguments);
    }

    /**
     * Lazy load a resource from the given url. If no type is provided, will
     * attempt to detect if the resource is a stylesheet or script, and handle
     * it appropriately.
     *
     * @param {string} url
     * @param {string} type (optional) the data type of the resource, if any
     * @return {Promise} A promise, to which functions can be attached to take
     * actions once the load is completed.
     */
    function lazy(url, type)
    {
        var promise;

        if (_.isUndefined(type)) {
            if (_.includes(url, '.js')) {
                type = 'script';
            } else if (_.includes(url, '.css')) {
                type = 'css';
            }
        }

        if (type == 'css') {
            promise = $.when(injectStylesheet(url));
        } else {
            promise = lazyLoad(url, type);
        }

        return promise;
    }

    /**
     * Dynamically load a resource lazily.
     * @param {string} url
     * @param {string} type - optional, if specified will be set as the dataType of the ajax request.
     * @returns {jqXHR}
     */
    function lazyLoad(url, type) {
        var options = {
            url: url,
            cache: true,
            crossDomain: true
        };

        if (!_.isUndefined(type)) {
            options.dataType = type;
        }

        return $.ajax(options);
    }

    /**
     * Dynamically load a stylesheet by injecting a new link element.
     * @param url
     */
    function injectStylesheet(url)
    {
        $('head').append('<link rel="stylesheet" type="text/css" href="'+url+'"/>');
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
        getBaseUrl: getBaseUrl,
        getJsUrl: getJsUrl,
        getFormKey: getFormKey,
        getMediaUrl: getMediaUrl,
        getLocale: getLocale,
        getSkinUrl: getSkinUrl,
        getStoreUrl: getStoreUrl,
        getUenc: getUenc,
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
        calculateSubtotalFromQuantity: calculateSubtotalFromQuantity,
        validateEmail: validateEmail,
        validatePostalCode: validatePostalCode,
        ajax: ajax,
        get: get,
        post: post,
        focusMostRelevantInput: focusMostRelevantInput,
        tpl: tpl,
        lazy: lazy,
        validateProperName: validateProperName,
        validateCityName: validateCityName,
        validateCompanyName: validateCompanyName,
        stripRedundantSpaces: stripRedundantSpaces
    };
}(jQuery.noConflict() || {}, _.noConflict() || {}, {
    Accounting: accounting || {}
}));
