<?php

namespace Smoren\Containers\Exceptions;

use Smoren\ExtendedExceptions\BadDataException;

class GraphException extends BadDataException
{
    public const STATUS_ID_EXIST = 1;
    public const STATUS_ID_NOT_EXIST = 2;
    public const STATUS_TYPE_NOT_EXIST = 3;
}
