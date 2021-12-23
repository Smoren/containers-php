<?php


namespace Smoren\Structs\Exceptions;


use Smoren\ExtendedExceptions\BadDataException;

/**
 * Class MappedCollectionException
 */
class MappedCollectionException extends BadDataException
{
    const STATUS_ID_EXIST = 1;
    const STATUS_ID_NOT_EXIST = 2;
}