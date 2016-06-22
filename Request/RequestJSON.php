<?php

namespace SelectelTransport\Request;


use SelectelTransport\Request;

class RequestJSON extends Request
{
    protected function __construct($url, array $queryParams = [])
    {
        $this->queryParams = [
            'format' => 'json'
        ];
        
        parent::__construct($url, $queryParams);
    }
}