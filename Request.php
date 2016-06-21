<?php

namespace SelectelTransport;


use SelectelTransport\Exceptions\InitException;
use SelectelTransport\Exceptions\RequestException;
use SelectelTransport\Exceptions\UndefinedRequestMethodException;

/**
 * Class Request
 * @package SelectelTransport
 */
class Request
{
    const REQUEST_METHOD_GET = 'GET';
    const REQUEST_METHOD_POST = 'POST';
    const REQUEST_METHOD_PUT = 'PUT';
    const REQUEST_METHOD_HEAD = 'HEAD';

    /**
     * Массив допустимых методов запросов
     * @var array
     */
    private static $allowedMethods = [
        self::REQUEST_METHOD_GET,
        self::REQUEST_METHOD_POST,
        self::REQUEST_METHOD_PUT,
        self::REQUEST_METHOD_HEAD,
    ];

    /**
     * Ссылка на объект cUrl
     * @var null|resource
     */
    private $curl = null;

    /**
     * Массив заголовков запроса
     * @var array
     */
    private $headers = [];

    /**
     * Метод (тип) запроса
     * @var string
     */
    private $method = null;

    /**
     * Контент запроса (для POST и PUT)
     * @var array
     */
    private $content = null;

    /**
     * Request constructor.
     * @param string $url
     * @throws InitException
     */
    private function __construct($url)
    {
        $this->curl = curl_init($url);
        
        if (!$this->curl) {
            throw new InitException('Не удалось инициализировать сеанс cURL');
        }
    }

    /**
     * @param string $url URL, на который подаются запросы
     * @return Request
     */
    public static function factory($url)
    {
        return new static($url);
    }

    /**
     * Метод выполнения cURL-запросов
     * @return Response
     * @throws RequestException
     */
    public function send()
    {
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl, CURLOPT_VERBOSE, true);
        curl_setopt($this->curl, CURLOPT_HEADER, true);
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $this->headers);

        if (in_array($this->method, [self::REQUEST_METHOD_POST, self::REQUEST_METHOD_PUT])) {
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, $this->content);

            if ($this->method === self::REQUEST_METHOD_POST) {
                curl_setopt($this->curl, CURLOPT_POST, true);
            } else {
                curl_setopt($this->curl, CURLOPT_PUT, true);
            }
        }

        $response = curl_exec($this->curl);

        if ($response === false) {
            throw new RequestException('Ошибка при выволнении запроса');
        }
        
        return Response::factory(
            (int) curl_getinfo($this->curl, CURLINFO_HTTP_CODE),
            $response,
            (int) curl_getinfo($this->curl, CURLINFO_HEADER_SIZE)
        );
    }

    /**
     * Метод устанавливает заголовки запроса
     * @param array $headers
     * @return Request
     */
    public function setHeaders(array $headers)
    {
        foreach ($headers as $name => $value) {
            $this->setHeader($name, $value);
        }

        return $this;
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    protected function setHeader($name, $value)
    {
        $this->headers[] = $name . ': ' . $value;
    }

    /**
     * Устанавливает метод запроса
     * @param string $method
     * @return Request
     * @throws UndefinedRequestMethodException
     */
    public function setMethod($method)
    {
        if (!in_array($method, self::$allowedMethods)) {
            throw new UndefinedRequestMethodException('Метод ' . $method . ' не поддерживается');
        }

        $this->method = $method;

        return $this;
    }

    /**
     * Устанавливает данные для POST и PUT запросов
     * @param array $content
     */
    public function setContent(array $content)
    {
        $this->content = $content;
        
        return $this;
    }
}