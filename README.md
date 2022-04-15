[![Build Status](https://api.travis-ci.com/jaxon-php/jaxon-annotations.svg?branch=main)](https://app.travis-ci.com/github/jaxon-php/jaxon-annotations)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/jaxon-php/jaxon-annotations/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/jaxon-php/jaxon-annotations/?branch=main)
[![StyleCI](https://styleci.io/repos/481695775/shield?branch=main)](https://styleci.io/repos/481695775)
[![Coverage Status](https://coveralls.io/repos/github/jaxon-php/jaxon-annotations/badge.svg?branch=main)](https://coveralls.io/github/jaxon-php/jaxon-annotations?branch=main)

[![Latest Stable Version](https://poser.pugx.org/jaxon-php/jaxon-annotations/v/stable)](https://packagist.org/packages/jaxon-php/jaxon-annotations)
[![Total Downloads](https://poser.pugx.org/jaxon-php/jaxon-annotations/downloads)](https://packagist.org/packages/jaxon-php/jaxon-annotations)
[![Latest Unstable Version](https://poser.pugx.org/jaxon-php/jaxon-annotations/v/unstable)](https://packagist.org/packages/jaxon-php/jaxon-annotations)
[![License](https://poser.pugx.org/jaxon-php/jaxon-annotations/license)](https://packagist.org/packages/jaxon-php/jaxon-annotations)

Annotations support for the Jaxon library
=========================================

This package provides annotations support for the Jaxon core library.

Installation
------------

Install this package with `composer`.
It requires `jaxon-php/jaxon-core` v4 or higher.

```shell
composer require jaxon-php/jaxon-annotations
```

Register the annotation reader with Jaxon.

```php
use Jaxon\Annotations\AnnotationReader;

AnnotationReader::register();
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
     * @before('call' => 'funcBefore2', 'with' => ['param1', 'param2'])
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

    protected function funcAfter2($param1)
    {
        // Do something with parameters
    }

    /**
     * @after('call' => 'funcAfter1')
     * @after('call' => 'funcAfter2', 'with' => ['param'])
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
        $count = $this->bags('user')->get('count', 0);
        $this->bags('user')->set('count', $count++);
    }
}
```
