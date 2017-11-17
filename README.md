# Reflection container

This package provides a **[Psr-11 container](https://github.com/container-interop/fig-standards/blob/master/proposed/container.md) decorator**.

It allows to add extra features like **auto-wiring** and **callable dependency injection** to any Psr-11 container implementation.

**Require** php >= 7.1

**Installation** `composer require ellipse/container-reflection`

**Run tests** `./vendor/bin/kahlan`

* [Decorating a container](#decorating-a-container)
* [Auto-wiring](#auto-wiring)
* [Callable dependency injection](#callable-dependency-injection)
* [Injecting at runtime](#injecting-at-runtime)

## Decorating a container

This package provides a `Ellipse\Container\ReflectionContainer` class which can be used to decorate any Psr-11 container, giving it new useful features.

First of all, nothing to worry about the `->get()` and `->has()` methods of the decorated container. They are just proxied by the `ReflectionContainer` instance.

```php
<?php

use Some\Container;
use Ellipse\Container\ReflectionContainer;
use App\SomeInterface;
use App\SomeImplementation;

// Get a new instance of some Psr-11 container.
$container = new Container();

// Add some definitions. Depends on which Psr-11 container you are using.
$container->set(SomeInterface::class, function () {

    return new SomeImplementation;

});

// Decorate the container.
$decorated = ReflectionContainer::decorate($container);

// The get and has methods of the decorated container are proxied.
$decorated->has(SomeInterface::class); // returns true.
$decorated->get(SomeInterface::class); // returns the SomeImplementation instance provided by the container.
```

But now the container has two new useful method, the `->make(string $class)` method (see [Auto-wiring](#auto-wiring)) and the `->call(callable $callable)` method (see [Callable dependency injection](#callable-dependency-injection)).

## Auto-wiring

The `->make(string $class)` method of the `ReflectionContainer` class allows to construct instances of the given class by using auto-wiring. It first checks if the container contains the given class name, in which case it just returns the instance provided by the container. Otherwise it constructs an instance of the class by recursively calling the `->make()` method on all its type hinted constructor parameters. This way it is no longer needed to define the whole tree of dependencies required to construct an instance of a class, yet it still relies on the instances provided by the container when special construction logic is needed.

When a parameter is type hinted as `Psr\Container\ContainerInterface`, the decorated container will be injected.

When the given class does not exists, the `ReflectionContainer` instance will throw a `Ellipse\Container\Exceptions\ClassDoesNotExistException`.

Please note a new instance of the class is created on every `->make()` call. If a singleton is needed, it has to be defined in the container.

```php
<?php

namespace App;

class SomeService
{
    public function __construct(SomeInterface $a, SomeOtherClass $b)
    {
        // ...
    }
}
```

```php
<?php

namespace App;

class SomeOtherClass
{
    public function __construct(YetSomeOtherClass $c)
    {
        // ...
    }
}
```

```php
<?php

use Some\Container;
use Ellipse\Container\ReflectionContainer;
use App\SomeService;
use App\SomeInterface;
use App\SomeImplementation;

// Get a new instance of some Psr-11 container.
$container = new Container();

// Add some definitions. Depends on which Psr-11 container you are using.
$container->set(SomeInterface::class, function () {

    // Special construction logic ...

    // ...

    return new SomeImplementation;

});

// Decorate the container.
$decorated = ReflectionContainer::decorate($container);

// The container does not contains SomeService.
$decorated->has(SomeService::class); // returns false.

// Yet an instance of SomeService can be constructed.
// The SomeImplementation instance provided by the container gets injected.
// SomeOtherClass and YetSomeOtherClass gets constructed by recursively using ->make().
$decorated->make(SomeService::class);
```

## Callable dependency injection

The `->call(callable $callable)` method of the `ReflectionContainer` class allows to execute the given callable by using the `->make()` method for injecting its type hinted parameters. Any callable notation is supported.

```php
<?php

namespace App;

class SomeClass
{
    public function __construct(SomeOtherClass $c)
    {
        // ...
    }
}
```

```php
<?php

use Some\Container;
use Ellipse\Container\ReflectionContainer;
use App\SomeInterface;
use App\SomeImplementation;
use App\SomeClass;

// Get a new instance of some Psr-11 container.
$container = new Container();

// Add some definitions. Depends on which Psr-11 container you are using.
$container->set(SomeInterface::class, function () {

    // Special construction logic ...

    // ...

    return new SomeImplementation;

});

// Decorate the container.
$decorated = ReflectionContainer::decorate($container);

// Get some callable.
$some_callable = function (SomeInterface $a, SomeClass $b) {

    // ...

    return 'some_result';

};

// The SomeImplementation instance provided by the container gets injected.
// SomeClass and SomeOtherClass gets constructed by recursively using ->make().
$decorated->call($some_callable); // returns 'some_results'.
```

## Injecting at runtime

Both `->make()` and `->call()` methods can take two arrays as optional parameters.

The first one is a list of class name => instance pairs. It allows to inject a particular instance of a class at runtime. For example it can be useful for injecting the current instance of a request processed by a list of middleware.

The second one just contains any values which will be injected when a parameter has no class type hint. They are injected in the order they are listed and they are **not** propagated to the `->make()` calls used to construct type hinted parameters. A typical use case would be to inject the attributes extracted from an url pattern to a callable action.

When no value can be resolved for one parameter, its default value will be used. If no default value is specified, a `Ellipse\Container\Exceptions\ParameterValueCantBeResolvedException` is thrown.

```php
<?php

use Psr\Http\Message\ServerRequestInterface;

use Some\Container;
use Ellipse\Container\ReflectionContainer;
use App\SomeInterface;
use App\SomeImplementation;

// Get a new instance of some Psr-11 container.
$container = new Container();

// Add some definitions. Depends on which Psr-11 container you are using.
$container->set(SomeInterface::class, function () {

    // Special construction logic ...

    // ...

    return new SomeImplementation;

});

// Decorate the container.
$decorated = ReflectionContainer::decorate($container);

// Get a specific instance of ServerRequestInterface.
$request = get_request_from_somewhere();

$some_callable = function (ServerRequestInterface $request, SomeInterface $a, $b, $c, $d = 'd') {

    // $request is the instance returned by get_request_from_somewhere().
    // $a is the SomeImplementation instance provided by the container.
    // $b value is 'b'.
    // $c value is 'c'.
    // $d value is 'd'.

};

// Call $some_callable with the given injected values.
$decorated->call($some_callable, [ServerRequestInterface::class => $request], ['b', 'c']);
```
