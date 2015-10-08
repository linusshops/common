# Common (AKA Magento Plus Plus)

[This README is a draft: Oct 6 2015]

This module provides a modern base of Magento helpers, Magento endpoints,
and frontend goodness to Magento, which is sorely lacking if
modern development is a thing--and in particular, *your* thing. This module
aims to solve very general problems. Its ultimate purpose is to simply exist
for the benefit of other modules, as a *Magento++*&trade; of sorts, which other
modules can build on. Think of it as a superset to Magento's own functionality.

## Why?

Magento presents a number of challenges. One of them is having to solve the same
problems all of the time, especially when dealing with a massive framework.
Large Magento sites tend to be composed of many different modules. Some of them
are third-party, but if there is an in-house team, that team will be
responsible for a number of custom ones as well. In all likelihood, there are
solutions to similar problems peppered through all of them. This module
serves as a single point of dependency. Doing this will
improve development time and decrease redundancies.

### Impetus

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

## Usage

Create a new module. Ensure it depends on `Common` by defining it in `app/etc/modules/*.xml`:

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

The snippet above is from an internal `Adapter` module that Linus Shops uses
for adapting third party modules without actually touching their source. Be
sure to use your own module name.

## API (ish)

This is not really a true API, but these are all of `Common`'s helpers and
endpoints that will become available to Magento and thus other modules that
depend on it.

Calling the `Common` helpers are just like any other Magento module:

```
$Common = Mage::helper('linus_common');
```

### `Csp.php` helpers

The `CSP` helpers are designed to prevent adding JavaScript blocks into a
document's markup. The reason that should be avoided is so that a site can
tighten up security by defining a Content Security Policy, in which
a policy to prevent inline JavaScript from executing can be defined. That is
a very important concern when money is passing through an online store.

This requires knowledge of the backend and frontend `CSP` helpers. The general
workflow is outlined below. The examples are rudimentary.

Read the source for `Helper/Csp.php` for more information about the methods
available to the backend. Read the source for `linus/common.js` for more
information about the methods available to the frontend.

#### Backend

It is recommended that these methods are used in a Magento `Block`, and then
called from that block's corresponding template.

**The block:**

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

    return $CommonCsp->generateHiddenCspMarkup();
}
```

**The template (phtml):**
```
<div class="linus-section">
    <h1 class="linus-head"><i class="fa fa-cube"></i> <?php echo $this->__('Linus Title'); ?></h1>
    <?php echo $this->insertHiddenCspMarkup(); ?>
</div>
```

#### Frontend

Check to see if `linus.common` is available. Once it is available, use the
corresponding `CSP` methods for retrieving the data passed to the frontend.

```
jQuery(document).ready(function(e) {

    if (!$.isEmptyObject(linus.common)) {
        var Common = linus.common;
        
        console.log(Common.__('Add to Cart'));
        console.log(Common.getCspData('formKey'));
    }
});

```

### `Data.php` helpers

Fill in.

### `Request.php` helpers

Fill in.

## Authors

- [Dane MacMillan](https://github.com/danemacmillan)
- [Samuel Schmidt](https://github.com/dersam)

## Contributing

[TODO] Submit PRs off of `develop`.

## License

This module was created by Linus Shops and enthusiastically licensed to the
Magento community under the [MIT License](http://opensource.org/licenses/MIT).
