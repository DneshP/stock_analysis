<?php

namespace App\System;

/**
 * Class Response
 * @package App\System
 */
class Response
{

    public function setStausCode($code)
    {
        http_response_code($code);
    }
}