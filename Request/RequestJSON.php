<?php

namespace SelectelTransport\Request;


class RequestJSON extends Request
{
    protected function __construct($url, array $queryParams = [], $type = null)
    {
        $this->queryParams = [
            'format' => 'json'
        ];
        
        parent::__construct($url, $queryParams, $type);
    }
}