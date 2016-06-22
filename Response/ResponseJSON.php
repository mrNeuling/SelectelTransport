<?php

namespace SelectelTransport\Response;


use SelectelTransport\Response;

/**
 * Class ResponseJSON
 * @package SelectelTransport\Response
 */
class ResponseJSON extends Response
{
    /**
     * Заполняет содержимоке ответа в формате JSON
     * @param string $response
     * @param int $headerSize
     */
    protected function fillContent($response, $headerSize)
    {
        $this->content = json_decode(substr($response, $headerSize));
    }
}