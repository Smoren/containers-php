<?php


namespace Smoren\Structs\Exceptions;


use Smoren\ExtendedExceptions\BadDataException;

/**
 * Class LinkedListException
 * @package Smoren\Structs\Exceptions
 */
class LinkedListException extends BadDataException
{
    const STATUS_EMPTY = 1;
    const STATUS_INTEGRITY_VIOLATION = 100;
}