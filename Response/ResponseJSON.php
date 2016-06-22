<?php

namespace SelectelTransport\Response;


/**
 * Class ResponseJSON
 * @package SelectelTransport\Response
 */
class ResponseJSON extends ResponseText
{
    /**
     * Заполняет содержимоке ответа в формате JSON
     * @param string $response
     * @param int $headerSize
     */
    protected function fillContent($response, $headerSize)
    {
        parent::fillContent($response, $headerSize);
        $this->content = json_decode($this->content);
    }
}