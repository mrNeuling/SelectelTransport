<?php

namespace SelectelTransport;


use SelectelTransport\Exceptions\AuthException;

/**
 * Class Auth
 * @package SelectelTransport
 */
class Auth
{
    /**
     * URL авторизации на Selectel
     */
    const SELECTEL_AUTH_URL = 'https://auth.selcdn.ru/';

    /**
     * Логин для авторизации (номер договора)
     * @var string
     */
    private $login = null;

    /**
     * Пароль для авторизации
     * @var string
     */
    private $password = null;

    /**
     * Токен авторизованного пользователя
     * @var string
     */
    private $token = null;

    /**
     * Время действия токена в секундах
     * @var int
     */
    private $expireToken = null;

    /**
     * Базовый адрес для выполнения операций с хранилищем
     * @var string
     */
    private $storageUrl = null;

    /**
     * Auth constructor.
     * @param string $login
     * @param string $password
     */
    private function __construct($login, $password)
    {
        $this->login = $login;
        $this->password = $password;
    }

    /**
     * @param string $login
     * @param string $password
     * @return Auth
     */
    public static function factory($login, $password)
    {
        return new static($login, $password);
    }

    /**
     * Возвращает токен авторизации
     * @return string
     * @throws AuthException
     */
    public function getToken()
    {
        if (is_null($this->token)) {
            $this->auth();
        }

        return $this->token;
    }

    /**
     * Возвращает время действия токена в секундах
     * @return int
     */
    public function getExpireToken()
    {
        return $this->expireToken;
    }

    /**
     * Возвращает базовый адрес для выполнения операций с хранилищем
     * @return string
     */
    public function getStorageUrl()
    {
        return $this->storageUrl;
    }

    /**
     * Авторизация пользователя Selectel
     * @throws AuthException
     * @throws Exceptions\RequestException
     */
    protected function auth()
    {
        $request = Request::factory(self::SELECTEL_AUTH_URL);
        $response = $request
            ->setHeaders([
                'X-Auth-User' => $this->login,
                'X-Auth-Key' => $this->password,
            ])
            ->send();

        if ($response->getCode() !== Response::RESPONSE_CODE_NO_CONTENT) {
            throw new AuthException('Ошибка авторизации на сервере Selectel');
        }

        $this->token = $response->getHeader('X-Auth-Token');
        $this->expireToken = (int) $response->getHeader('X-Expire-Auth-Token');
        $this->storageUrl = $response->getHeader('X-Storage-Url');
    }
}