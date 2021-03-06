<?php

namespace App\System;

class Request
{
    /**
     * @return false|string
     */
    public function getPath()
    {
        $requestPathParts = explode('/',$_SERVER['REQUEST_URI']);
        $basePath = parse_url(BASE_URL)['path'];
        $path = '/' . implode(',', array_diff($requestPathParts, explode('/',$basePath)));
        $position = strpos($path, '?');
        if ($position === false) {
            return $path;
        }
        return substr($path, 0, $position);
    }

    /**
     * @return string
     */
    public function method(): string
    {
        return strtolower($_SERVER['REQUEST_METHOD']);
    }

    /**
     * @return bool
     */
    public function isGet(): bool
    {
        return $this->method() === 'get';
    }

    /**
     * @return bool
     */
    public function isPost(): bool
    {
        return $this->method() === 'post';
    }

    /**
     * @return object
     */
    public function getBody(): object
    {
        $body = [];

        if ($this->isGet()) {
            foreach ($_GET as $key => $value) {
                $body[$key] = filter_input(INPUT_GET, $key, FILTER_SANITIZE_SPECIAL_CHARS);
            }
        }
        if ($this->isPost()) {
            if (is_array($_POST)) {
                foreach ($_POST as $key => $value) {
                    $body[$key] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS);
                }
            }
        }
        return (object)$body;
    }

}