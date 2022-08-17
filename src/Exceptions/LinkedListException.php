<?php

namespace Smoren\Containers\Exceptions;

use Smoren\ExtendedExceptions\BadDataException;

/**
 * Class LinkedListException
 * @package Smoren\Containers\Exceptions
 */
class LinkedListException extends BadDataException
{
    public const STATUS_EMPTY = 1;
    public const STATUS_INTEGRITY_VIOLATION = 100;
}
