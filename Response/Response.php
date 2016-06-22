<?php

namespace SelectelTransport;
use SelectelTransport\Interfaces\IResponse;
use SelectelTransport\Response\ResponseJSON;

/**
 * Class Response
 * @package SelectelTransport
 */
class Response implements IResponse
{
    /**
     * Заголовки ответа
     * @var array
     */
    protected $headers = [];

    /**
     * Содержимое ответа
     * @var null
     */
    protected $content = null;

    /**
     * HTTP-код ответа
     * @var null
     */
    protected $httpCode = null;

    protected function __construct($httpCode, $response, $headerSize)
    {
        $this->httpCode = $httpCode;
        $this->fillHeaders($response, $headerSize);
        $this->fillContent($response, $headerSize);
    }

    /**
     * @param $httpCode
     * @param $response
     * @param $headerSize
     * @param string $type
     * @return Response
     */
    public static function factory($httpCode, $response, $headerSize, $type = self::RESPONSE_TYPE_TEXT)
    {
        switch ($type) {
            case IResponse::RESPONSE_TYPE_JSON:
                return new ResponseJSON($httpCode, $response, $headerSize);
            default:
                return new self($httpCode, $response, $headerSize);
        }
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