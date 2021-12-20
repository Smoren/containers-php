<?php


namespace Smoren\Structs\exceptions;


use Smoren\ExtendedExceptions\BadDataException;

/**
 * Class LinkedListException
 * @package Smoren\Structs\exceptions
 */
class LinkedListException extends BadDataException
{
    const STATUS_EMPTY = 1;
    const STATUS_INTEGRITY_VIOLATION = 100;
}