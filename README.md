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

```xml
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

`Common` links all of its frontend assets to `base/default`. It is
properly namespaced, so it will never
interfere with other code. This is deliberate. `Common` does not attempt to
assume where themes are stored and what are loaded; this is so that anyone can
install `Common` and start writing code without having to modify this module.
Instead, `Common` takes advantage of Magento's built-in fallback mechanism,
which eventually loads files from `base/default`. JavaScript is  included from
the root `js` directory, as this code is library-level and should be loaded
before `skin_js`. Assets will be automatically loaded.

## External dependencies

`Common` includes the following external dependencies:
 - `jQuery 1.11.3`
 - `lodash 3.10.1` custom build: `lodash strict include=noConflict,has,get,size,memoize,defaultsDeep`
 
Additionally, `Common` has modified Magento in such a way that it can reorder
the loading of frontend assets. This is described below. Subsequently, this
means that all of `Common`s JavaScript is loaded before anything else&mdash;even
Magento's own assets, like `prototype.js`.

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

Common provides a block and template to simplify usage of CSP. The general process
to utilize CSP in a module is to create a new block that extends `Linus_Common_Block_CspAbstract`.
Implement `defineCspData` to declare the data to be included on the page.
Once the block has been created, it must then be added to the layout. Using the
`linuscommon/csp.phtml` template will take care of including the data in the page.
See Linus_Common_Block_Csp for an example implementation.

Multidimensional arrays are valid, and recommended to use to namespace your
module data to avoid unwanted conflicts and overwrites on the frontend.

```php
class Linus_Common_Block_Csp extends Linus_Common_Block_CspAbstract
{
    public function defineCspData()
    {
        $this->setTranslationString([
            'Checkout' => null
        ]);

        $this->setCspData([
            'baseUrl' => $this->getBaseUrl(),
            'formKey' => Mage::getSingleton('core/session')->getFormKey(),
            'jsUrl' => $this->getJsUrl(),
            'storeCode' => Mage::app()->getStore()->getCode(),
            'locale' => Mage::app()->getLocale()->getLocaleCode(),
            'mediaUrl' => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA),
            'skinUrl' => $this->getSkinUrl(),
            'storeUrl' => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB),
            'uenc' => Mage::helper('core')->urlEncode(rtrim($this->getBaseUrl(), '/') . $this->getRequest()->getRequestString()),
            'isLoggedIn' => Mage::getSingleton('customer/session')->isLoggedIn(),
            'isDeveloperMode' => Mage::getIsDeveloperMode(),
            'customerId' => Mage::getSingleton('customer/session')->getCustomerId(),
            'commonTplChecksums' => $commonTplChecksums
            'sample' => [
                'data' => 'here'
            ]
        ]);
    }
}
```

Add the block to your module's layout.xml. The type should be the type of the
block that you created.
```xml
<block type="linus_sample/csp" name="csp_sample" template="linuscommon/csp.phtml"/>
```

###### Frontend

Check to see if `linus.common` is available. Once it is available, use the
corresponding `CSP` methods for retrieving the data passed to the frontend.

The node containing the CSP data will be removed, as it should only be
consumed using Common helpers.

If a key is replicated in several CSP blocks, the last one to be included on
the page will be the winner. This can be leveraged to allow a module to overwrite
an individual key of another module.  If this behavior is not desired, namespace
your data in nested objects, and retrieve it with dot notation (see example below)

```javascript
var example = example || (function($, Common)
{
    function __construct()
    {
        if ($.isEmptyObject(Common)) {
            throw new Error('`Common` dependency not available.');
        }
        
        // Print out value of formKey.
        console.log(Common.getCspData('formKey'));

        // Print out nested values
        console.log(Common.getCspData('sample.data')); //will print 'here'
        
        // Print out translated version of Add to Cart. This uses the
        // wrapper defined below.
        console.log(__('Add to Cart'));
        
        // Translate every text node found within provided DOM reference.
        Common.translateAllTextIn($('.dropdown-primary'));
        
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

Read the source for `linus/common.js` for
more information about the methods available to the frontend.

*No more messy script tags in templates with PHP embedded within inline
JavaScript.*

### Load custom fonts from module, page, etc

Check to see if `linus.common` is available.

No matter how many times a call to `addWebFont` is made, the stack of fonts
will all be queued and a single request will be made.

```javascript
var example = example || (function($, Common)
{
    function __construct()
    {
        if ($.isEmptyObject(Common)) {
            throw new Error('`Common` dependency not available.');
        }
        
        // Add a Web font from Google.
        Common.addWebFont({
            google: {
                families: ['Roboto:400,400italic:latin']
            }
        });
        
        // Add another Web font from Google and change the timeout, because
        // there are a lot of fonts to download. The previous font will be
        // queued alongside this one before actually requesting the fonts.
        Common.addWebFont({
            google: {
                families: ['Open+Sans:400,400italic,700,700italic:latin']
            },
            timeout: 5500
        });
    }

    (function __init() {
        $(document).ready(function(e) {
            __construct();
        });
    }());

    return {};

}(jQuery, linus.common || {}));
```

### Bot protection

On very large Magento stores, scraping of its content can become a source of
backend overhead. The databases get hit, possibly requiring more than
trivial computations. `Common` provides a rudimentary method for detecting
bots, which can be used for removing parts of a document that typically
require burdensome overhead, but does not detract from the SEO, for example.

```php
// If no user agent string passed, will use current request's user agent.
// isBot() returns bool.
if (!Mage::helper('linus_common/request')->isBot()) {
    // - Some massive database operation that should be cached anyway.
}
```

### New layout XML handles

###### `CATEGORY_PARENT_{ID}`

This provides the ability to modify all of a category's subcategories
from the layout XML without having to manually target each one
specifically with `CATEGORY_{ID}`. Ultimately, this provides layout XML
inheritance to subcategories from a parent category, which can normally
only be achieved through the database by specifying XML in the parent
category, and enabling "Use Parent Category Settings" in each
subcategory. This is better.

Note that should a subcategory of the parent category be targeted with its own
layout XML (e.g., `<CATEGORY_1201>`) in addition to the parent category, it
will take precedence. This ensures that Magento's standard behaviour is not
changed, as the order of handles is important.

```xml
<?xml version="1.0"?>
<layout version="0.1.0">
    <CATEGORY_PARENT_823>
        <reference name="root">
            <action method="setTemplate">
                <template>page/2columns-left-special-category.phtml</template>
            </action>
        </reference>
    </CATEGORY_PARENT_823>
</layout>
```

###### `PRODUCT_CATEGORY_{ID}`

This allows products that belong to a category to be targeted with custom
layout XML. Products can exist in multiple categories, so every product will
have all those category handles available, sorted from least specific category
to most specific category. For example, should a motorcycle helmet be in a
category such as `Street (ID:159) > Helmets (ID:39) > Full Face (ID:409)`, a
layout handle of `PRODUCT_CATEGORY_409` will trump the layout handle for
`PRODUCT_CATEGORY_39`, because it is more specific; this allows all helmets to
inherit the same layout XML, with an exception for full face helmets, which
have their even more specific layout XML. Similarly, should a product that
belongs to category handle `PRODUCT_CATEGORY_409` have its own layout handle
specified, like `PRODUCT_674238`, it will trump that more general one.

The order of handles is carefully built, so that a more specific handle will
always trump a parent or less specific handle, which is what Magento expects.

###### `cms_identifier_{identifier}`

This allows specific cms pages to be targeted with layout xml. For example, to
target the home page, the layout handle would be `cms_identifier_home`, if your
home page uses the 'home' identifier.

### New layout XML action methods

A new `linus_common` block is created. Within it are methods that should be
called from the layout XML as layout XML action methods.

###### Add browser user agent string to body class

This is an example usage of adding a browser user agent string to the body
class from the onepage checkout. For the meantime, only IE-related classes
are created.

The example also adds an arbitrary string, to demonstrate the ease of adding
new body class strings to the body class attribute.

```xml
<checkout_onepage_index>
    <reference name="linus_common">
        <action method="addUserAgentToBodyClass"/>
        <action method="addClassNameToBodyClass">
            <classname>hiyooo</classname>
        </action>
    </reference>
</checkout_onepage_index>
```

### Hijax: hijacking existing form endpoints for asynchronous (Ajax) responses

[TODO]


### Generic CMS Templating as CSV

It is common to have many static blocks in Magento. Many of these static blocks
may share similar html structure, only differing in the actual content. This can
result in difficulty when the structure needs updating, as EVERY static block
would have to be modified.

Common's generic templating solves this by allowing static blocks to be treated
as data containers holding CSV formatted data as key-value pairs. The linus_common/cms
helper provides methods for parsing this data.  Once a cms block is created that
contains csv data, that data can be parsed to an array.

Common also provides a way to do this via layout XML, treating the csv block as
a data source to be injected into a core/template block with a generic template.

* Create your static data block in CMS. In our example, our block's name is `common_example`.
Our static block content would be as follows:
```
"foo","bar"
"fizz","buzz"
```
You will notice that this is the same convention as the translation files. This
is deliberate, as it allows these static blocks to potentially be used in translations.

* Define your generic template phtml file. In our example, the path is "page/html/example.phtml".
```php
<h1>My foo is <?php echo $this->getFoo() ?></h1>
<p>My fizz is <?php echo $this->getFizz() ?></p>
```

* In your layout xml, define a `core/template block` with the SAME NAME as your
cms static block identifier, and the template path of your generic template. Place a call to setData
as shown. This will flag this block to have the static cms block data injected.
The data injection is done on the `core_block_abstract_to_html_before` event.
```xml
<reference name="header">
    <block type="core/template" name="common_example" template="page/html/example.phtml">
        <action method="setData">
            <name>csv_data</name>
            <value>simple</value>
        </action>
    </block>
</reference>
```

* Finally, in the parent template, if applicable, call getChildHtml as you would normally.
```php
<?php echo $this->getChildHtml('common_example'); ?>
```

Your generic template will be filled with the data from the data template.
```html
<h1>My foo is bar</h1>
<p>My fizz is buzz</p>
```

Should you have dynamic content that would be prohibitive to specify specific
handles for, the `common_cms_csv_block_load_before` event can be listened to,
which allows modification of the cms block id to use. By default, it is the name
 of the block in the layout xml. A helper is provided to eliminate the need
 for boilerplate code in the event.
 
```php
//In an observer
public function onCommonCmsCsvBlockLoadBefore($observer)
{
    Mage::helper('linus_common/cms')->transformIdentifier(
        $observer,
        //The regex to identify block names that should have the identifier transformed
        "/^current_block_name$/",
        //The transformation to apply.
        function ($block){
            return "new_block_name"
        }
    );
}
```

#### Nested CSV data
The major limitation of using CSV as a data format is that it is flat. There is
no native way to create nested data structures in CSV. Common provides a way to
do this.

The `Linus_Common_Trait_Csv_Parser` can be applied to any class that has had
CSV data injected.  By setting `csv_data` to `nested` instead of `simple` in the
layout.xml, Common will automatically parse the static block csv into a
multidimensional array.

```xml
<reference name="header">
    <block type="linus_common/csv" name="common_example" template="page/html/example.phtml">
        <action method="setData">
            <name>csv_data</name>
            <value>nested</value>
        </action>
    </block>
</reference>
```

The expected format for a csv row is an arbitrary-length underscore-delimited
path that ends in a key name, with the second column being the value of that key.

There are no special names, with the exception that is it not recommended to
start your key names with `get`, `set`, `uns`, or `has`, as these may conflict
with the standard Varien_Object magic methods.

Given the following CSV:
```
"column_1_section_helmets_title","Motorcycle Helmets"
"column_1_section_helmets_icon","motorcycle-helmets"
```

Will yield:
```
'column' => [
    1 => [
        'section' => [
            'helmets' => [
                'title' => "Motorcycle Helmets",
                'icon'  => "motorcycle-helmets"
            ]
        ]
    ]
]]
```

This data can be accessed via magic methods.
```php
$this->column(1, 'section', 'helmets', 'title'); //"Motorcycle Helmets"
$this->column(1) //yields the column 1 array.
```

Linus_Common_Block_Csv can be used if you do not already have a block, and are
composing your blocks via layout xml.

### Reorder head assets like `CSS` and `JS`

`Linus_Common` dispatches a new `linus_common_block_before_head_getCssJsHtml`
event with an instance of the `Mage_Page_Block_Html_Head` block passed. This
allows other modules to reorder the assets loaded in the head of a document.
`Linus_Common` itself uses this method so that its assets can be loaded even
before Magento's own assets: for example, this is used for loading `jQuery`,
`lodash` and other `Linus_Common` assets. [todo more]

### Async Resource loader
`common.js` provides a `lazy()` method, which allows asynchronous loading of
resources such as stylesheets and scripts.  It returns a promise, which can be
used to only continue once the resource is loaded.

The type can be explicitly specified as 'css' or 'script'.  If not specified,
an attempt will be made to autodetect the resource type, based on file
extension (js for script, css for css).
 
Stylesheets will be auto-injected into the page by creating a new <link> element
in the page head.

### `linus.common.post` method

Asynchronous POST helper that conforms to Linus Shops' payload structure. This
is the frontend counterpart to `Linus_Common_Helper_Request->sendResponseJson`.
If using that PHP method, then this should be used, as it handles all the 
boilerplate logic for errors.

Highlights of this method:

- The data returned to every callback is always in JSON format. Every
callback, except `error`, receives the payload content directly. The
`error` callback receives a `jqXHR` object.
- All requests are auto-cached based on endpoint and request data, so identical
requests will be retrieved from memory. 
- Target selectors will automatically be injected with HTML content,
should corresponding data exist, either in the payload as a CSS selector
key name, or the feedback `message`. `Common:afterTargetPayloadInsert`
and `Common:afterTargetFeedbackInsert` events are fired after this
HTML content has been auto-inserted, which provide access to the
live node for further manipulation by other modules.
- `Common:beforePost` and `Common:afterPost` events are fired
before and after the asynchronous POST, which can be used by other
modules for modifying a request before it is sent out, or after it has
completed.
- Debug data will automatically output to the console, if
provided.

###### Using the method

Note that `error` and `cleanup` are empty but included. They do not need to
be included at all if there is no necessity to define them. The `post` method
will handle callbacks that are not defined. Read the source of the method
to understand when each callback gets executed.

```JavaScript
Common.post(endpoint, requestData, {
    limbo: function(requestData) {
        setFormFieldState($fieldset, 'limbo');
    },
    valid: function(payload) {
        if (payload.deliverable) {
            setFormFieldState($fieldset, 'valid');
            revealNextDependentFieldset($fieldset);
        } else {
            setFormFieldState($fieldset, 'invalid');
        }
    },
    invalid: function(payload) {
        setFormFieldState($fieldset, 'invalid');
    },
    cleanup: function(payload) {
    },
    error: function(jqXHR) {
    }
});
```

###### Binding to a custom event fired by the `post` method
This demonstrates how to bind to the `Common:beforePost` event to change the
entire request and behaviour before it is sent.

```JavaScript
$(document).on('Common:beforePost', function(e, eventData) {
    // Change endpoint.
    eventData.endpoint = '/new-url/endpoint';
    // Add new request data.
    eventData.requestData['user[name]'] = 'foobar';
    // Save parent limbo method.
    var parentLimboMethod = eventData.callbacks.limbo;
    // Create new limbo method, and call parent.
    eventData.callbacks.limbo = function(requestData) {
        parentLimboMethod(eventData.requestData);
        console.log('limbo!', requestData);
    };
});
```

#### Automatic templating
Common provides a magical frontend automatic templater.
 
* If the json payload contains a key beginning with `.` or `#`, and the selector exists
on the page, the contents of that property will be written to that DOM target
* If `tpl` is specified in the response (top level, like `error`), Common
will attempt to look up a matching template for use on the frontend. If it
finds a template, it will automatically insert the response payload data and
render to the target selector.
* If a selector exists in both `tpl` and the body of the response, Common will
throw an error for that selector. This error does not block other valid templates.

Common will request the template with a block name matching that of the selector
with any leading `.` or `#` stripped, and `-` converted to `_`. To define a
template, simply define a Magento block under `default` as follows.

In this example, the template target selector is `#demo_lodash_template`.

```xml
<default>
    <block type="linus_common/tpl" name="demo_lodash_template" as="demo_lodash_template" template="tpl/demo_lodash_template.phtml"/>
</default>
```

To benefit from caching, frontend templates must be of the type linus_common/tpl,
or a child of that class.

Common will automatically cache received templates in local storage, and automatically
handle cache invalidation via CSP injected md5 hashes. All caching and fetching
is black-boxed within the single tpl() method.  Should you want to use a template
without making an ajax request first (just passing some arbitrary data), the
`Common.tpl()` method can be called directly, passing only the targets for
your template, and the data to inject. If you pass false for your data, this
will just precache the templates, but not attempt rendering.

Manual call to tpl()
```javascript
linus.common.tpl(['#demo-lodash-template','#demo-lodash-template2'], {name:"linus"})
```

Templates should be defined like regular Magento templates. There are three
tag types available:

* `{{}}` - interpolate, will attempt to insert the data from the path of the json
object provided as data to the template. `data.` must prefix every property. `data`
represents the object provided to the template
```
{{data.property1}}
```
* `{{- }}` - escape, will automatically encode special characters as html
* `{{% }}` - evaluate, will execute javascript contained within. The `data` object
is available, as well as Common and Lodash (as `_`).

##### Inline templates
Common can inline templates in the page. This removes the need to fetch the template
asynchronously, but increases the initial page payload size.  Use only for critical
content that cannot afford delays.

To inline a template, add the tpl block to `<default>` as normal.
```
<block type="linus_common/tpl" name="example_template" template="path/to/template.phtml"/>
```

Next, using the `linus_common_tpl_inline` reference under `<default>`, add the block by name.
```
<reference name="linus_common_tpl_inline">
    <action method="addInlineBlock">
        <blockName>example_template</blockName>
    </action>
</reference>
```

Common will now seamlessly use the inlined template instead of fetching. If you
remove it from the inlined set, it will fetch it from the backend. No additional
configuration is necessary.

Inlined templates will not be added to LocalStorage.

Template lookup priority:

1. Compiled templates currently in memory
2. Inlined templates
3. Templates in LocalStorage
4. Async request for template

#### Messaging
Common provides a way to display status messages in the page, similar to the 
Magento admin message block. In Magento, the messageblock id will vary depending
on the page.  By binding to the `LinusCommonMessages:init` event and updating
the `eventContainer`, the location of the messages can be modified on a per-page basis.

Templating is done through tpl(), but is hidden from the consumer.

```
$('body').on('LinusCommonMessages:init', function(event, data){
    data.messageContainer = '#messages_product_view';
});

linus.common.messages.success("This is a success styled message");

//Message box with multiple classes, and an icon class.
linus.common.messages.display(messageText, [
            'alert',
            'alert-warning'
        ], 'fa-exclamation-triangle');
```

#### Override Hardcoded Blocks
Magento templates should always be set via layout xml. This prevents issues when
a developer wants to use a different template in the same block. However, many
modules and blocks hardcode the template in the block class, making it impossible
to modify without extending the block itself. This then opens the door to more
rewrite conflicts, more code to maintain, and more yaks to shave.

Common provides a way to painlessly remap these blocks to the desired template path.
Simply listen for the `common_block_template_update` event, and provide it your
path mappings.

```php
public function onCommonBlockTemplateUpdate(Varien_Event_Observer $observer)
    {
        /** @var Linus_Common_Model_PathMapper $mapper */
        $mapper = $observer->getPathMapper();

        $mapper->addPaths([
            'catalog/layer/filter.phtml' => 'linusadapter/catalog/layer/my-new-filter-template.phtml'
        ]);
    }
```

## Authors

- [Dane MacMillan](https://github.com/danemacmillan)
- [Samuel Schmidt](https://github.com/dersam)

## Contributing

[TODO] Submit PRs off of `develop`.

## License

This module was created by Linus Shops and enthusiastically licensed to the
Magento community under the [MIT License](http://opensource.org/licenses/MIT).
