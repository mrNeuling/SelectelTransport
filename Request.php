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
    const REQUEST_METHOD_PURGE = 'PURGE';
    const REQUEST_METHOD_DELETE = 'DELETE';

    /**
     * Массив допустимых методов запросов
     * @var array
     */
    private static $allowedMethods = [
        self::REQUEST_METHOD_GET,
        self::REQUEST_METHOD_POST,
        self::REQUEST_METHOD_PUT,
        self::REQUEST_METHOD_HEAD,
        self::REQUEST_METHOD_PURGE,
        self::REQUEST_METHOD_DELETE,
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
     * Список файлов для отправки
     * @var string
     */
    private $file = null;

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
        $fileHandler = null;

        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl, CURLOPT_VERBOSE, true);
        curl_setopt($this->curl, CURLOPT_HEADER, true);
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $this->headers);

        if ($this->method === self::REQUEST_METHOD_POST) {

            curl_setopt($this->curl, CURLOPT_POSTFIELDS, $this->content);
            curl_setopt($this->curl, CURLOPT_POST, true);

        } elseif ($this->method === self::REQUEST_METHOD_PUT) {

            curl_setopt($this->curl, CURLOPT_PUT, true);

            if (!is_null($this->file)) {
                $fileHandler = fopen($this->file, 'r');
                curl_setopt($this->curl, CURLOPT_INFILE, $fileHandler);
                curl_setopt($this->curl, CURLOPT_INFILESIZE, filesize($this->file));
            }
        } elseif (in_array($this->method, [self::REQUEST_METHOD_HEAD, self::REQUEST_METHOD_PURGE, self::REQUEST_METHOD_DELETE])) {

            curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, $this->method);
            
            if ($this->method === self::REQUEST_METHOD_PURGE) {
                curl_setopt($this->curl, CURLOPT_POSTFIELDS, $this->content);
            }
        }

        $response = curl_exec($this->curl);

        if (isset($fileHandler)) {
            fclose($fileHandler);
        }
        
        if ($response === false) {
            throw new RequestException('Ошибка при выволнении запроса');
        }

        $result = Response::factory(
            (int) curl_getinfo($this->curl, CURLINFO_HTTP_CODE),
            $response,
            (int) curl_getinfo($this->curl, CURLINFO_HEADER_SIZE)
        );

        curl_close($this->curl);

        return $result;
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
     * @return Request
     */
    public function setContent(array $content)
    {
        $this->content = $content;
        
        return $this;
    }

    /**
     * Регистрирует файл для отправки
     * @param string $filePath
     * @return $this
     */
    public function setFile($filePath)
    {
        $this->file = $filePath;

        return $this;
    }
}