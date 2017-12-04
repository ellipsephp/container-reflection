<?php declare(strict_types=1);

namespace Ellipse\Container\Executions\Exceptions;

use RuntimeException;
use ReflectionParameter;

use Psr\Container\ContainerExceptionInterface;

class UnresolvedValueException extends RuntimeException implements ContainerExceptionInterface
{
    public function __construct()
    {
        $msg = "Unable to infer a value (no class type hint, no placeholders available, no default value).";

        parent::__construct($msg);
    }
}
