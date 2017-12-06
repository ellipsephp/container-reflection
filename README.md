# Reflection container

This package provides a **[Psr-11 container](http://www.php-fig.org/psr/psr-11/)** decorator enabling **auto-wiring** to any Psr-11 container implementation.

**Require** php >= 7.1

**Installation** `composer require ellipse/container-reflection`

**Run tests** `./vendor/bin/kahlan`

* [Decorating a container](#decorating-a-container)

## Decorating a container

This package provides a `Ellipse\Container\ReflectionContainer` class which can be used to decorate any Psr-11 container, modifying the `->has()` and `->get()` methods behavior:

- The `->has()` method now returns true for aliases contained in the original container but also when the given alias is an existing class name.
- The `->get()` method returns the value contained in the original container when the given alias is defined. Otherwise it will try to return an instance of a class named like the given alias by recursively injecting its constructor dependencies using the container `->get()` method. When called multiple times, the same instance of the class is returned like any Psr-11 container would do.

```php
<?php

namespace App;

interface SomeInterface
{
    //
}
```

```php
<?php

namespace App;

class SomeClass implements SomeInterface
{
    //
}
```

```php
<?php

namespace App;

class SomeOtherClass
{
    public function __construct(SomeInterface $class1, YetSomeOtherClass $class2)
    {
        //
    }
}
```

```php
<?php

namespace App;

class YetSomeOtherClass
{
    //
}
```

```php
<?php

use Some\Psr11Container;

use Ellipse\Container\ReflectionContainer;

use App\SomeInterface;
use App\SomeClass;
use App\SomeOtherClass;
use App\YetSomeOtherClass;

// Get an instance of some Psr-11 container.
$container = new Psr11Container;

// Add some definitions. Method name depends on which Psr-11 container you are using.
$container->set('some.value', function () {

    return 'something';

});

$container->set(SomeInterface::class, function () {

    return new SomeClass;

});

// Decorate the container.
$container = new ReflectionContainer($container);

// Now ->has() returns true for all those aliases:
$container->has('some.value');
$container->has(SomeInterface::class);
$container->has(SomeClass::class);
$container->has(SomeOtherClass::class);
$container->has(YetSomeOtherClass::class);

// ->get() still returns the values contained in the original container:
$container->get('some.value'); // returns 'something'
$container->get(SomeInterface::class); // returns the defined instance of SomeClass

// now ->get() can also build instances of non contained classes. Here an instance of
// SomeOtherClass is build by injecting the contained implementation of SomeInterface and a
// new instance of YetSomeOtherClass.
$container->get(SomeOtherClass::class);

// On multiple call the same instance is returned.
$someotherclass1 = $container->get(SomeOtherClass::class);
$someotherclass2 = $container->get(SomeOtherClass::class);

$someotherclass1 === $someotherclass2; // true
```
