[![Build Status](https://api.travis-ci.com/jaxon-php/jaxon-annotations.svg?branch=main)](https://app.travis-ci.com/github/jaxon-php/jaxon-annotations)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/jaxon-php/jaxon-annotations/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/jaxon-php/jaxon-annotations/?branch=main)
[![StyleCI](https://styleci.io/repos/481695775/shield?branch=main)](https://styleci.io/repos/481695775)
[![Coverage Status](https://coveralls.io/repos/github/jaxon-php/jaxon-annotations/badge.svg?branch=main)](https://coveralls.io/github/jaxon-php/jaxon-annotations?branch=main)

[![Latest Stable Version](https://poser.pugx.org/jaxon-php/jaxon-annotations/v/stable)](https://packagist.org/packages/jaxon-php/jaxon-annotations)
[![Total Downloads](https://poser.pugx.org/jaxon-php/jaxon-annotations/downloads)](https://packagist.org/packages/jaxon-php/jaxon-annotations)
[![Latest Unstable Version](https://poser.pugx.org/jaxon-php/jaxon-annotations/v/unstable)](https://packagist.org/packages/jaxon-php/jaxon-annotations)
[![License](https://poser.pugx.org/jaxon-php/jaxon-annotations/license)](https://packagist.org/packages/jaxon-php/jaxon-annotations)

Annotations for the Jaxon library
=================================

This package provides annotation support for the Jaxon library.
The configuration options that are related to Jaxon classes can be set directly in the class files using annotations.

Two different syntax are allowed for annotations: the default array-like syntax, and an alternative docblock-like syntax,
available since version `1.4`.

Installation
------------

Install this package with `composer`.
It requires `jaxon-php/jaxon-core` v4 or higher.

```shell
composer require jaxon-php/jaxon-annotations
```

Set the annotation config option to on.

```php
jaxon()->setOption('core.annotations.on', true);
```

Usage
-----

The following annotations are provided.

### @exclude

It prevents a method or a class from being exported to javascript.
It takes an optional boolean parameter.

```php
/**
 * @exclude(true)
 */
class JaxonExample
{
// This class will not be exported to javascript.
}
```

```php
class JaxonExample
{
    /**
     * @exclude
     */
    public function doNot()
    {
        // This method will not be exported to javascript.
    }
}
```

The docblock like syntax can also be used.

```php
class JaxonExample
{
    /**
     * @exclude false
     */
    public function do()
    {
        // This method will be exported to javascript.
    }

    /**
     * @exclude true
     */
    public function doNot()
    {
        // This method will not be exported to javascript.
    }
}
```

### @upload

It adds file upload to an ajax request.
It takes the id of the HTML field as a mandatory option.
It applies only to methods.

```php
class JaxonExample extends \Jaxon\App\CallableClass
{
    /**
     * @upload('field' => 'div-user-file')
     */
    public function saveFile()
    {
        // Get the uploaded files.
        $files = $this->upload()->files();
    }
}
```

The docblock like syntax can also be used.

```php
class JaxonExample extends \Jaxon\App\CallableClass
{
    /**
     * @upload div-user-file
     */
    public function saveFile()
    {
        // Get the uploaded files.
        $files = $this->upload()->files();
    }
}
```

### @before

It defines a method of the class as a callback to be called before processing the request.
It takes the name of the method as a mandatory parameter, and an array as optional parameters to be passed to the callback.
It applies to methods and classes.

```php
class JaxonExample
{
    protected function funcBefore1()
    {
        // Do something
    }

    protected function funcBefore2($param1, $param2)
    {
        // Do something with parameters
    }

    /**
     * @before('call' => 'funcBefore1')
     * @before('call' => 'funcBefore2', 'with' => ['value1', 'value2'])
     */
    public function action()
    {
    }
}
```

The docblock like syntax can also be used.

```php
class JaxonExample
{
    protected function funcBefore1()
    {
        // Do something
    }

    protected function funcBefore2($param1, $param2)
    {
        // Do something with parameters
    }

    /**
     * @before funcBefore1
     * @before funcBefore2 ["value1", "value2"]
     */
    public function action()
    {
    }
}
```

### @after

It defines a method of the class as a callback to be called after processing the request.
It takes the name of the method as a mandatory parameter, and an array as optional parameters to be passed to the callback.
It applies to methods and classes.

```php
class JaxonExample
{
    protected function funcAfter1()
    {
        // Do something
    }

    protected function funcAfter2($param)
    {
        // Do something with parameter
    }

    /**
     * @after('call' => 'funcAfter1')
     * @after('call' => 'funcAfter2', 'with' => ['value'])
     */
    public function action()
    {
    }
}
```

The docblock like syntax can also be used.

```php
class JaxonExample
{
    protected function funcAfter1()
    {
        // Do something
    }

    protected function funcAfter2($param)
    {
        // Do something with parameter
    }

    /**
     * @after funcAfter1
     * @after funcAfter2 ["value"]
     */
    public function action()
    {
    }
}
```

### @databag

It defines a data bag to be appended to ajax requests to a method.
It takes the name of the data bag as a mandatory parameter.
It applies to methods and classes.

```php
class JaxonExample extends \Jaxon\App\CallableClass
{
    /**
     * @databag('name' => 'user')
     */
    public function action()
    {
        // Update a value in the data bag.
        $count = $this->bag('user')->get('count', 0);
        $this->bag('user')->set('count', $count++);
    }
}
```

The docblock like syntax can also be used.

```php
class JaxonExample extends \Jaxon\App\CallableClass
{
    /**
     * @databag user
     */
    public function action()
    {
        // Update a value in the data bag.
        $count = $this->bag('user')->get('count', 0);
        $this->bag('user')->set('count', $count++);
    }
}
```

### @di

It defines an attribute that will be injected in a class.
When applied on methods and classes, it takes the name and the class of the attribute as mandatory parameters.

```php
class JaxonExample extends \Jaxon\App\CallableClass
{
    /**
     * @var \App\Services\Translator
     */
     protected $translator;

    /**
     * @di('attr' => 'translator', class => '\App\Services\Translator')
     */
    public function translate(string $phrase)
    {
        // The $translator property is set from the DI container when this method is called.
        $phrase = $this->translator->translate($phrase);
    }
}
```

When applied on attributes, it takes the class of the attribute as only parameter, which can be omitted if it is already specified by a `@var` annotation.

```php
class JaxonExample extends \Jaxon\App\CallableClass
{
    /**
     * @di(class => '\App\Services\Translator')
     * @var \App\Services\Translator
     */
     protected $translator;

    public function translate(string $phrase)
    {
        // The $translator property is set from the DI container when this method is called.
        $phrase = $this->translator->translate($phrase);
    }
}
```

```php
class JaxonExample extends \Jaxon\App\CallableClass
{
    /**
     * @di
     * @var \App\Services\Translator
     */
     protected $translator;

    public function translate(string $phrase)
    {
        // The $translator property is set from the DI container when this method is called.
        $phrase = $this->translator->translate($phrase);
    }
}
```

If the class name does not start with a `"\"`, then the corresponding fully qualified name (FQN) will be set using
either the `use` instructions or the `namespace` in its source file.

```php
namespace App\Ajax;

use App\Services\Translator;

class JaxonExample extends \Jaxon\App\CallableClass
{
    /**
     * @var Translator
     */
     protected $translator;

    /**
     * @var Formatter
     */
     protected $formatter;

    /**
     * @di('attr' => 'translator', class => 'Translator')
     * @di('attr' => 'formatter', class => 'Formatter')
     */
    public function translate(string $phrase)
    {
        // The Translator FQN is defined by the use instruction => App\Services\Translator.
        // The Formatter FQN is defined by the current namespace => App\Ajax\Formatter.
        $phrase = $this->formatter->format($this->translator->translate($phrase));
    }
}
```

The docblock like syntax can also be used.

```php
namespace App\Ajax;

use App\Services\Translator;

class JaxonExample extends \Jaxon\App\CallableClass
{
    /**
     * @var Translator
     */
     protected $translator;

    /**
     * @var Formatter
     */
     protected $formatter;

    /**
     * @di $translator   Translator
     * @di $formatter    Formatter
     */
    public function translate(string $phrase)
    {
        // The Translator FQN is defined by the use instruction => App\Services\Translator.
        // The Formatter FQN is defined by the current namespace => App\Ajax\Formatter.
        $phrase = $this->formatter->format($this->translator->translate($phrase));
    }
}
```

```php
namespace App\Ajax;

use App\Services\Translator;

/**
 * @di $translator   Translator
 * @di $formatter    Formatter
 */
class JaxonExample extends \Jaxon\App\CallableClass
{
    /**
     * @var Translator
     */
     protected $translator;

    /**
     * @var Formatter
     */
     protected $formatter;

    public function translate(string $phrase)
    {
        // The Translator FQN is defined by the use instruction => App\Services\Translator.
        // The Formatter FQN is defined by the current namespace => App\Ajax\Formatter.
        $phrase = $this->formatter->format($this->translator->translate($phrase));
    }
}
```

```php
namespace App\Ajax;

use App\Services\Translator;

class JaxonExample extends \Jaxon\App\CallableClass
{
    /**
     * @di  Translator
     * @var Translator
     */
     protected $translator;

    /**
     * @di  Formatter
     * @var Formatter
     */
     protected $formatter;

    public function translate(string $phrase)
    {
        // The Translator FQN is defined by the use instruction => App\Services\Translator.
        // The Formatter FQN is defined by the current namespace => App\Ajax\Formatter.
        $phrase = $this->formatter->format($this->translator->translate($phrase));
    }
}
```
