Common
======

This module provides a modern base of Magento helpers, Magento endpoints,
and frontend goodness to Magento, which is sorely lacking. This module
aims to solve very general problems. Its ultimate purpose is to simply exist
for the benefit of other modules, as a *Magento++*&trade; of sorts, which other
modules can build on. Think of it as a superset to Magento's own functionality.

> :zap: Note that as of October 9, 2015 this module is still undergoing a lot
of change. A number of features from Linus Shops' internal codebase are still
being ported over.

## Why?

Magento presents a number of challenges. One of them is having to solve the same
problems all of the time, especially when dealing with a massive framework.
Large Magento sites tend to be composed of many different modules. Some of them
are third-party, but if there is an in-house team, that team will be
responsible for a number of custom ones as well. In all likelihood, there are
solutions to similar problems peppered through all of them. This module
serves as a single point of dependency. Doing this will
improve development time and decrease redundancies.

###### Impetus

As the Linus Shops' codebase grew, resulting in a variety of modules to perform
different tasks, a pattern of common solutions became apparent. One module
required a simple methodology for getting translations to work in JavaScript.
Several months later, another module wanted to benefit from this, but because
there was no common module, the code would need to be copied over. That sucks.
Another module created a Hijax-like endpoint for posting data to a cart, 
JavaScript helpers and all; a while later another module wanted to use that
endpoint for posting its own data to a cart. Instead of having to depend on
a module made for a specific purpose, this endpoint and the related helpers
were ported into a common module, which both of them could depend on.
This is why the common module exists. Internally, Linus Shops defines a hard
dependency on `Common`, because it provides a tonne of useful functionality to
improve daily workflows.

## Who?

This is for professional Magento developers who follow modern development
practices. This has been created to work with the magento-composer-installer
and modman [cite URLs].

If `FTP` and drag and drop is still being used to deploy Magento code, this is
not going to work out.

## Installation

This should be installed using Composer. A magento build should also include the
[Magento Composer Installer](https://github.com/Cotya/magento-composer-installer).
This module follows the module structure guidelines provided by
[Firegento](https://github.com/firegento/coding-guidelines/tree/master/sample-module),
which will also make it very easy to submit to the
[Firegento Composer Repository](https://github.com/magento-hackathon/composer-repository).

###### Add dependency to `Common`

Create a new module. Ensure it depends on `Common` by defining it
in `app/etc/modules/*.xml`:

```
<?xml version="1.0"?>
<config>
    <modules>
        <Linus_Adapter>
            <active>true</active>
            <codePool>local</codePool>
            <depends>
                <Linus_Iddqd />
                <Linus_Common />
            </depends>
        </Linus_Adapter>
    </modules>
</config>
```

> :zap: Note that this is optional, but highly recommended.

The snippet above is from an internal `Adapter` module that Linus Shops uses
for adapting third party modules without actually touching their source. *Do not
just copy and paste the above.*

###### Add frontend assets

`Common` links all of its frontend assets to `base/default`. It is properly
namespaced, so it will never interfere with other code. However, as most Magento
stores should have their own theme, none of the frontend code, such as
JavaScript or CSS, will load, because Magento will not look for it there. This
is deliberate. `Common` does not attempt to assume where themes are stored and
what are loaded; this is so that anyone can install `Common` and start writing
code without having to modify this module. Instead, `Common` takes advantage
of Magento's built-in fallback mechanism, so all that is required is an
addition to the current theme's, or other module's, `layout/local.xml` file to
include the location, which then triggers Magento to search for it
in `base/default`.

> :zap: Recommendation: define this in an adapter-like module. An adapter
module is one that is used for modifying third-party vendor code without
actually modifying that vendor's source directly.

```
<!-- Load Common module assets. Magento's fallback system will load them
from base/default. -->
<action method="addCss">
    <stylesheet>css/linus/common.css</stylesheet>
</action>
<action method="addItem">
    <type>skin_js</type>
    <name>js/linus/common.js</name>
</action>
```

## Features by use case

### Translations on the frontend

Third-party widgets are sometimes needed on a Web page. In the Magento world
it is common to use a provider for handling customer feedback, Q&As, and the
like. These widgets are typically loaded asynchronously and not usually very
open to modification. In addition, they are usually bad, and offered in one
language. `Common` allows developers to use Magento's built-in translation
system on the frontend. This feature is part of the `CSP` helpers provided by
`Common`. This is all done without the need to include inline JavaScript blocks
in the markup.

> :zap: In addition to providing translations, the `CSP` helpers let developers
pass other arbitrary data to the frontend. Essentially, any data JavaScript
may need from the backend, becomes available to the frontend without messy
inline JavaScript.

The `CSP` helpers are designed to prevent adding JavaScript blocks into a
document's markup. The reason that should be avoided is so that a site can
tighten up security by defining a Content Security Policy, in which
a policy to prevent inline JavaScript from executing can be defined. That is
a very important concern when money is passing through an online store.

This requires knowledge of the backend and frontend `CSP` helpers. The general
workflow is outlined below.

###### Backend

It is recommended that these methods are used in a Magento `Block`, and then
called from that block's corresponding template.

```
/**
 * Set and generate the CSP data to be used by frontend.
 *
 * This depends on Common.
 *
 * @return string
 * @throws Mage_Core_Exception
 */
public function insertHiddenCspMarkup()
{
    /** @var Linus_Common_Helper_Data $Common */
    $CommonCsp = Mage::helper('linus_common/csp');

    // Set general CSP data.
    $CommonCsp->setCspData(array(
        'baseUrl' => $this->getBaseUrl(),
        'cartUrlTemplate' => $this->cartUrlTemplateAddItem,
        'uenc' => Mage::helper('core')->urlEncode(Mage::app()->getStore()->getBaseUrl()),
        'formKey' => Mage::getSingleton('core/session')->getFormKey()
    ));
    
    // Set more data. This merges with previously set data.
    $CommonCsp->setCspData(array(
        'baseUrl' => '//example.com/, // Overwrites
        'newData' => 'abcdefg' // Merges new
    ));

    // Set translation data. Passing a null value will use the key as value
    // and then pass to Magento's internal translation helper.
    $CommonCsp->setCspTranslation(array(
        'Add to Cart' => null,
        'searching' => Mage::helper('core')->__('Looking'),
        'Adding item' => null,
        'Item added' => null,
        'Error adding' => null,
        'Out of stock' => null
    ));
    
    // Redefine a translation string later on.
    $CommonCsp->setCspTranslation(array(
        'Out of stock' => Mage::helper('core')->__('Product unavailable')
    ));

    return $CommonCsp->generateHiddenCspMarkup();
}
```

```
<div class="linus-section">
    <h1 class="linus-head"><i class="fa fa-cube"></i> <?php echo $this->__('Linus Title'); ?></h1>
    <?php echo $this->insertHiddenCspMarkup(); ?>
</div>
```

###### Frontend

Check to see if `linus.common` is available. Once it is available, use the
corresponding `CSP` methods for retrieving the data passed to the frontend.

```
var example = example || (function($, Common)
{
    function __construct()
    {
        if ($.isEmptyObject(Common)) {
            throw new Error('`Common` dependency not available.');
        }
        
        // Print out value of formKey.
        console.log(Common.getCspData('formKey'));
        
        // Print out translated version of Add to Cart. This uses the
        // wrapper defined below.
        console.log(__('Add to Cart'));
        
        // Translate every text node found within provided DOM reference.
        Common.translateAllTextIn($('.dropdown-primary');
        
        // Or translate every text node on the whole page.
        Common.translateAllTextIn(document.body);
    }

    /**
     * Simple wrapper for translating strings.
     */
    function __(textString)
    {
        return Common.__(textString);
    }

    (function __init() {
        $(document).ready(function(e) {
            __construct();
        });
    }());

    return {};

}(jQuery, linus.common || {}));
```

Read the source for `Helper/Csp.php` for more information about the
methods available to the backend. Read the source for `linus/common.js`g for
more information about the methods available to the frontend.

### Bot protection

On very large Magento stores, scraping of its content can become a source of
backend overhead. The databases get hit, possibly requiring more than
trivial computations. `Common` provides a rudimentary method for detecting
bots, which can be used for removing parts of a document that typically
require burdensome overhead, but does not detract from the SEO, for example.

```
// If no user agent string passed, will use current request's user agent.
// gisBot() returns bool.
if (!Mage::helper('linus_common/request')->isBot()) {
    // - Some massive database operation that should be cached anyway.
}
```

### Asynchronously add items to cart (Ajax)

The Magento endpoint for adding an item to the cart is synchronous. In other
words, the browser will redirect the user at least once, and typically to the
cart preview, upon successfully adding an item. Often, a store just wants
clients to quickly add an item to the cart without redirecting them all over
the place; it can be disorienting. `Common` uses a special `hijax` technique
for sending asynchronous request to the existing Magento endpoint without
having to define a new one, or break the regular synchronous one. That means
that all existing forms on the site will continue to work, but if `Common`
detects that the request to add an item to the cart is asynchronous, it will
respond with a `JSON` payload instead of redirect the browser.

[TODO]

## Authors

- [Dane MacMillan](https://github.com/danemacmillan)
- [Samuel Schmidt](https://github.com/dersam)

## Contributing

[TODO] Submit PRs off of `develop`.

## License

This module was created by Linus Shops and enthusiastically licensed to the
Magento community under the [MIT License](http://opensource.org/licenses/MIT).
