<?php

namespace App\System;

class Router
{
    /** @var Request */
    protected $request;

    /** @var Response  */
    protected $response;

    /** @var array */
    protected $routes;

    /**
     * @param Request $request
     * @param Response $response
     */
    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    public function get($path, $callback)
    {
        $this->routes['get'][$path] = $callback;
    }

    public function post($path, $callback)
    {
        $this->routes['post'][$path] = $callback;
    }

    public function resolve()
    {
        $path = $this->request->getPath();
        $method = $this->request->method();
        $callback = $this->routes[$method][$path] ?? false;
        if ($callback === false) {
            $this->response->setStausCode(404);
            return  'Not found';
        }
        $callback[0] = new $callback[0]($this->request);

        return call_user_func($callback);
    }
}