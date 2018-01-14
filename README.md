# Reflection container

This package provides a **[Psr-11 container](http://www.php-fig.org/psr/psr-11/)** decorator enabling **auto-wiring** to any Psr-11 container implementation.

**Require** php >= 7.1

**Installation** `composer require ellipse/container-reflection`

**Run tests** `./vendor/bin/kahlan`

* [Getting started](#getting-started)
* [Restricted auto wiring](#restricted-auto-wiring)

## Getting started

This package provides an `Ellipse\Container\ReflectionContainer` class which can be used to decorate any Psr-11 container. By default it enables auto wiring for all the existing classes, modifying the behaviors of the original container `->has()` and `->get()` methods:

The `->has()` method now returns true for aliases contained in the original container but also when the given alias is an existing class name.

The `->get()` method returns the value contained in the original container when the given alias is defined. Otherwise when the alias is an existing class name then auto wiring is used to return an new instance of this class. It means php reflection feature is used to extract the class constructor parameters and the reflection container `->get()` method is called to retrieve a value for all parameters type hinted as a class name. For the other parameters their default values are used. Once a value is retrieved for all the class constructor parameters, a new instance of the class is built using those values. When called multiple times, the same instance of the class is returned like any Psr-11 container would do.

This auto wiring feature [can be restricted](#restricted-auto-wiring) to classes implementing a specified list of interfaces.

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

// Add some definitions into the original container.
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

// now ->get() can also build instances of non contained classes.
// Here an instance of SomeOtherClass is build by injecting the contained implementation of SomeInterface and a new instance of YetSomeOtherClass.
$container->get(SomeOtherClass::class);

// On multiple call the same instance is returned.
$someotherclass1 = $container->get(SomeOtherClass::class);
$someotherclass2 = $container->get(SomeOtherClass::class);

$someotherclass1 === $someotherclass2; // true
```

## Restricted auto wiring

The `ReflectionContainer` class takes an optional array of interface names as second parameter. When this array is not empty, auto wiring is enabled only for classes implementing at least one of those interfaces.

For exemple when an application has a lot of controllers it can be an exaustive task to register all of them into the container. To allow flexibility without loosing control on how the application services are created, the `ReflectionContainer` can be set up to allow auto wiring only for classes implementing a `ControllerInterface`.

```php
<?php

namespace App\Controllers;

interface ControllerInterface
{
    //
}
```

```php
<?php

namespace App\Controllers;

use App\SomeDependency;

class SomeController implements ControllerInterface
{
    private $dependency;

    public function __construct(SomeDependency $dependency)
    {
        $this->dependency = $dependency;
    }

    public function index()
    {
        //
    }
}
```

```php
<?php

namespace App\Controllers;

use App\SomeOtherDependency;

class SomeOtherController implements ControllerInterface
{
    private $dependency;

    public function __construct(SomeOtherDependency $dependency)
    {
        $this->dependency = $dependency;
    }

    public function index()
    {
        //
    }
}
```

```php
<?php

use Some\Psr11Container;

use Ellipse\Container\ReflectionContainer;

use App\Controllers\ControllerInterface;
use App\Controllers\SomeController;
use App\Controllers\SomeOtherController;
use App\SomeDependency;
use App\SomeOtherDependency;

// Get an instance of some Psr-11 container.
$container = new Psr11Container;

// Register 'App\SomeDependency' into the original container.
$container->set(SomeDependency::class, function () {

    return new SomeDependency;

});

// Allow auto wiring only for classes implementing ContainerInterface.
$container = new ReflectionContainer($container, [ControllerInterface::class]);

// This returns an new instance of SomeController with the defined instance of SomeDependency injected.
$controller = $container->get(SomeController::class);

// This fails because as SomeOtherDependency is not defined in the original container.
$controller = $container->get(SomeOtherController::class);
```
