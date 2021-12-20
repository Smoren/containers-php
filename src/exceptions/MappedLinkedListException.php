<?php


namespace Smoren\Structs\exceptions;


use Smoren\ExtendedExceptions\BadDataException;

class MappedLinkedListException extends BadDataException
{
    const STATUS_ID_EXIST = 1;
    const STATUS_ID_NOT_EXIST = 2;
    const STATUS_EMPTY = 3;
}