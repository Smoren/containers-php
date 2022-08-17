<?php

namespace Smoren\Containers\Exceptions;

use Smoren\ExtendedExceptions\BadDataException;

/**
 * Class MappedCollectionException
 */
class MappedCollectionException extends BadDataException
{
    public const STATUS_ID_EXIST = 1;
    public const STATUS_ID_NOT_EXIST = 2;
}
