# futape/html-utility

This library offers a set of utilities for working with HTML markup.

Utility functions are implemented as static functions in an abstract class, which is never expected to be instantiated.

Moreover this library offers a `HtmlInliner` and its subclass `SemanticHtmlInliner` (see below).

## Install

```bash
composer require futape/html-utility
```

## Usage

### Html

This utility class offers functions for working with HTML markup.

```php
use Futape\Utility\Html\Html;

echo Html::preformatted("PHP is <3\n Foo   Bar\nBaz\tBam ");
// "PHP is &lt;3<br />\n&nbsp;Foo &nbsp; Bar<br />\nBaz&nbsp;&nbsp;&nbsp;&nbsp;Bam&nbsp;"
```

### HtmlInliner

A class for converting HTML markup to plaintext and inlining any text.

The following example is taken from the unit tests.

**markup.html**

```html
<h1>A markup file</h1>
<p>This is just a file containing HTML markup.</p>
<table>
    <thead>
        <tr>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Age</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>John</td>
            <td>Doe</td>
            <td>42</td>
        </tr>
    </tbody>
</table>
<p>A list of very important stuff:</p>
<ul>
    <li>One</li>
    <li>Two</li>
</ul>
<p>And a list of definitions:</p>
<dl>
    <dt>WWW</dt>
    <dd>World Wide Web</dd>
    
    <dt>HTTP</dt>
    <dd>Hypertext Transfer Protocol</dd>
</dl>
<img src="http://example.com/picture.jpg" alt="An example picture" />
```

**inline.php**

```php
use Futape\Utility\Html\HtmlInliner;

echo (new HtmlInliner(file_get_contents('./markup.html')))->render();
// "A markup file This is just a file containing HTML markup. A list of very important stuff: One, Two, And a list of definitions: WWW: World Wide Web, HTTP: Hypertext Transfer Protocol,"
```

### SemanticHtmlInliner

This is a subclass of the `HtmlInliner` and takes the semantic meaning of HTML elements into account instead of relying
mostly on their display.

## Testing

The library is tested by unit tests using PHP Unit.

To execute the tests, install the composer dependencies (including the dev-dependencies), switch into the `tests`
directory and run the following command:

```bash
../vendor/bin/phpunit
```
