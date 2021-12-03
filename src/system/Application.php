<?php

namespace App\System;

class Application
{
    /** @var Request  */
    public $request;

    /** @var Router  */
    public $router;

    /** @var Response  */
    public $response;

    /** @var Application  */
    public static $app;

    /** @var Database */
    public $db;

    public function __construct()
    {
        self::$app = $this;
        $this->request = new Request();
        $this->response = new Response();
        $this->router = new Router($this->request, $this->response);
        $this->db = new Database();
    }

    public function run()
    {
        echo $this->router->resolve();
    }

    /**
     * success response
     * @todo move to helpers
     * @param mixed $data
     * @return string
     */
    public static function jsend_success($data = ''): string
    {
        return json_encode(['status' => true, 'data' => $data]);
    }

    /**
     * error response
     * @todo move to helpers
     * @param mixed $data
     * @return string
     */
    public static function jsend_error($data = ''): string
    {
        return json_encode(['status' => false, 'data' => $data]);
    }
}