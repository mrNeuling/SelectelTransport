<?php

namespace SelectelTransport;

/**
 * Class Response
 * @package SelectelTransport
 */
class Response
{
    const RESPONSE_CODE_OK = 200;
    const RESPONSE_CODE_CREATED = 201;
    const RESPONSE_CODE_NO_CONTENT = 204;
    const RESPONSE_CODE_FORBIDDEN = 403;

    /**
     * Заголовки ответа
     * @var array
     */
    private $headers = [];

    /**
     * Содержимое ответа
     * @var null
     */
    private $content = null;

    /**
     * HTTP-код ответа
     * @var null
     */
    private $httpCode = null;

    private function __construct($httpCode, $response, $headerSize)
    {
        $this->httpCode = $httpCode;
        $this->fillHeaders($response, $headerSize);
        $this->fillContent($response, $headerSize);
    }

    /**
     * @param $httpCode
     * @param $response
     * @param $headerSize
     * @return Response
     */
    public static function factory($httpCode, $response, $headerSize)
    {
        return new static($httpCode, $response, $headerSize);
    }

    /**
     * Заполняет массив заголовков ответа
     * @param string $response
     * @param int $headerSize
     */
    protected function fillHeaders($response, $headerSize)
    {
        $headersContent = substr($response, 0, $headerSize);
        
        $this->headers = self::parseHeaders($headersContent);
    }

    /**
     * Распрашивает текст с заголовками ответа и формирует из них массив
     * @param string $headersContent
     * @return array
     */
    protected static function parseHeaders($headersContent)
    {
        $headerLines = explode("\r\n", $headersContent);
        // Удаляем первую строку ответа (HTTP/1.1 204 No Content)
        unset($headerLines[0]);
        
        $headers = [];
        foreach (array_filter($headerLines) as $headerLine) {
            $header = explode(': ', $headerLine);
            $headers[$header[0]] = $header[1];
        }
        
        return $headers;
    }

    /**
     * Заполняет содержимоке ответа
     * @param string $response
     * @param int $headerSize
     */
    protected function fillContent($response, $headerSize)
    {
        $this->content = substr($response, $headerSize);
    }

    /**
     * Возвращает значение заголовка ответа
     * @param string $name
     * @return mixed|null
     */
    public function getHeader($name)
    {
        return array_key_exists($name, $this->headers) ? $this->headers[$name] : null;
    }

    /**
     * Возвращает массив заголовков ответа
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Возвращает код ответа
     * @return int
     */
    public function getCode()
    {
        return $this->httpCode;
    }

    /**
     * Возвращает содержимое ответа
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }
}