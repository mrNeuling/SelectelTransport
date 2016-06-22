<?php

namespace SelectelTransport\Interfaces;

/**
 * Interface IRequest
 * @package SelectelTransport\Interfaces
 */
interface IRequest
{
    const REQUEST_METHOD_GET = 'GET';
    const REQUEST_METHOD_POST = 'POST';
    const REQUEST_METHOD_PUT = 'PUT';
    const REQUEST_METHOD_HEAD = 'HEAD';
    const REQUEST_METHOD_PURGE = 'PURGE';
    const REQUEST_METHOD_DELETE = 'DELETE';
}