<?php


namespace Smoren\Containers\Exceptions;


use Smoren\ExtendedExceptions\BadDataException;

/**
 * Class SortedLinkedListException
 */
class SortedLinkedListException extends BadDataException
{
    const STATUS_BAD_LINKED_LIST_TYPE = 1;
}