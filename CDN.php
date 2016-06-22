<?php

namespace SelectelTransport;


use SelectelTransport\Exceptions\RequestException;
use SelectelTransport\Interfaces\IRequest;
use SelectelTransport\Interfaces\IResponse;
use SelectelTransport\Request\Request;

/**
 * Class CDN
 * @package SelectelTransport
 */
class CDN
{
    /**
     * @var Auth
     */
    private $auth = null;

    /**
     * CDN constructor.
     * @param string $login
     * @param string $password
     */
    private function __construct($login, $password)
    {
        $this->auth = Auth::factory($login, $password);
    }

    /**
     * @param string $login
     * @param string $password
     * @return CDN
     */
    public static function factory($login, $password)
    {
        return new static($login, $password);
    }

    /**
     * Загружает архив с данными на сервер
     * @param string $filePath
     * @return \stdClass[]
     * @throws Exceptions\UndefinedRequestMethodException
     * @throws RequestException
     */
    public function loadArchive($filePath)
    {
        $token = $this->auth->getToken();
        $request = Request::factory(
            $this->auth->getStorageUrl() . 'neuling_test/',
            [
                'extract-archive' => 'tar'
            ]
        );

        $response = $request
            ->setHeaders([
                'X-Auth-Token' => $token,
                'Accept' => 'application/json',
            ])
            ->setFile($filePath)
            ->setMethod(IRequest::REQUEST_METHOD_PUT)
            ->send();

        if ($response->getCode() !== IResponse::RESPONSE_CODE_CREATED) {
            throw new RequestException($response->getContent());
        }

        return json_decode($response->getContent());
    }

    /**
     * Возвращает информацию о хранилище
     * @return array
     * @throws Exceptions\UndefinedRequestMethodException
     * @throws RequestException
     */
    public function getStorageInfo()
    {
        $token = $this->auth->getToken();
        $request = Request::factory($this->auth->getStorageUrl());

        $response = $request
            ->setHeaders([
                'X-Auth-Token' => $token,
            ])
            ->setMethod(IRequest::REQUEST_METHOD_HEAD)
            ->send();

        return [
            'containersCount' => $response->getHeader('X-Account-Container-Count'),
            'objectsCount' => $response->getHeader('X-Account-Object-Count'),
            'bytesUsed' => $response->getHeader('X-Account-Bytes-Used'),
        ];
    }

    /**
     * Возвращает список контейнеров
     * @return \stdClass[]
     * @throws RequestException
     */
    public function getContainersList()
    {
        $token = $this->auth->getToken();
        $request = Request::factory($this->auth->getStorageUrl(), [], IResponse::RESPONSE_TYPE_JSON);

        $response = $request
            ->setHeaders([
                'X-Auth-Token' => $token,
            ])
            ->send();

        return $response->getContent();
    }

    /**
     * Возвращает информацию о контейнере
     * @param string $containerName
     * @return array
     * @throws Exceptions\UndefinedRequestMethodException
     * @throws RequestException
     */
    public function getContainerInfo($containerName)
    {
        $token = $this->auth->getToken();
        $request = Request::factory($this->auth->getStorageUrl() . '/' . $containerName);

        $response = $request
            ->setHeaders([
                'X-Auth-Token' => $token,
            ])
            ->setMethod(IRequest::REQUEST_METHOD_HEAD)
            ->send();

        return [
            'objectsCount' => $response->getHeader('X-Container-Object-Count'),
            'bytesUsed' => $response->getHeader('X-Container-Bytes-Used'),
            'type' => $response->getHeader('X-Container-Meta-Type'),
            'domains' => $response->getHeader('X-Container-Domains'),
        ];
    }

    /**
     * Возвращает список файлов в контейнере
     * @param string $containerName
     * @param string|null $folder
     * @return \stdClass[]
     * @throws RequestException
     */
    public function getFilesList($containerName, $folder = null)
    {
        $token = $this->auth->getToken();
        
        $request = Request::factory(
            $this->auth->getStorageUrl() . $containerName  . '/',
            [
                'path' => $folder
            ],
            IResponse::RESPONSE_TYPE_JSON
        );

        $response = $request
            ->setHeaders([
                'X-Auth-Token' => $token,
            ])
            ->send();

        return $response->getContent();
    }

    /**
     * Удаляет файл из контейнера
     * @param string $containerName
     * @param string $filePath
     * @throws Exceptions\UndefinedRequestMethodException
     * @throws RequestException
     */
    public function deleteFile($containerName, $filePath)
    {
        $token = $this->auth->getToken();
        $request = Request::factory($this->auth->getStorageUrl() . $containerName  . '/' . $filePath);

        $response = $request
            ->setHeaders([
                'X-Auth-Token' => $token,
            ])
            ->setMethod(IRequest::REQUEST_METHOD_DELETE)
            ->send();

        if ($response->getCode() !== IResponse::RESPONSE_CODE_NO_CONTENT) {
            throw new RequestException('Ошибка удаления файла');
        }
    }

    /**
     * Удаляет виртуальную папку
     * Если папка не пуста, сначала удаляет все вложенные файлы и папки,
     * а затем удаляет саму папку.
     * @param string $containerName
     * @param string $folderName
     * @throws RequestException
     */
    public function deleteFolder($containerName, $folderName)
    {
        foreach ($this->getFilesList($containerName, $folderName) as $file) {
            if (self::isDirectory($file)) {
                $this->deleteFolder($containerName, $file->name);
            } else {
                $this->deleteFile($containerName, $file->name);
            }
        }
        
        $this->deleteFile($containerName, $folderName);
    }

    /**
     * Проверяет существование файла на сервере
     * @param string $containerName
     * @param string $filePath
     * @return bool
     * @throws Exceptions\UndefinedRequestMethodException
     * @throws RequestException
     */
    public function isExistFile($containerName, $filePath)
    {
        $token = $this->auth->getToken();
        $request = Request::factory($this->auth->getStorageUrl() . $containerName  . '/' . $filePath);

        $response = $request
            ->setHeaders([
                'X-Auth-Token' => $token,
            ])
            ->setMethod(IRequest::REQUEST_METHOD_HEAD)
            ->send();

        return $response->getCode() === IResponse::RESPONSE_CODE_OK;
    }

    /**
     * Проверяет, является ли файл директорией
     * @param \stdClass $file
     * @return bool
     */
    protected static function isDirectory(\stdClass $file)
    {
        return $file->content_type == 'application/directory';
    }
}