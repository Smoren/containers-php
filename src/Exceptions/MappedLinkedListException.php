<?php


namespace Smoren\Containers\Exceptions;


use Smoren\ExtendedExceptions\BadDataException;

/**
 * Class MappedLinkedListException
 */
class MappedLinkedListException extends BadDataException
{
    const STATUS_ID_EXIST = 1;
    const STATUS_ID_NOT_EXIST = 2;
    const STATUS_EMPTY = 3;
}