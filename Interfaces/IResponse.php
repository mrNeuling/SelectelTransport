<?php

namespace SelectelTransport\Interfaces;

/**
 * Interface IResponse
 * @package SelectelTransport\Interfaces
 */
interface IResponse
{
    const RESPONSE_CODE_OK = 200;
    const RESPONSE_CODE_CREATED = 201;
    const RESPONSE_CODE_NO_CONTENT = 204;
    const RESPONSE_CODE_FORBIDDEN = 403;

    const RESPONSE_TYPE_TEXT = 'text';
    const RESPONSE_TYPE_JSON = 'json';
    const RESPONSE_TYPE_XML = 'xml';
}