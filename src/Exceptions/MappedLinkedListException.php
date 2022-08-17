<?php

namespace Smoren\Containers\Exceptions;

use Smoren\ExtendedExceptions\BadDataException;

/**
 * Class MappedLinkedListException
 */
class MappedLinkedListException extends BadDataException
{
    public const STATUS_ID_EXIST = 1;
    public const STATUS_ID_NOT_EXIST = 2;
    public const STATUS_EMPTY = 3;
}
