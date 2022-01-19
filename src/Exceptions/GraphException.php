<?php


namespace Smoren\Containers\Exceptions;


use Smoren\ExtendedExceptions\BadDataException;

class GraphException extends BadDataException
{
    const STATUS_ID_EXIST = 1;
    const STATUS_ID_NOT_EXIST = 2;
    const STATUS_TYPE_NOT_EXIST = 3;
}