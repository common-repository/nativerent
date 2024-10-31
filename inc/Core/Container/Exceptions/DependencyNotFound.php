<?php

namespace NativeRent\Core\Container\Exceptions;

use Exception;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Dependency not found.
 */
class DependencyNotFound extends Exception implements NotFoundExceptionInterface {

}
