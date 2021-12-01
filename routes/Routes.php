<?php

namespace App\Routes;

use App\Controllers\Welcome;
use App\System\Application;

class Routes
{
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function init()
    {
        $this->app->router->get('/', [Welcome::class, 'index']);
        $this->app->router->get('/welcome', [Welcome::class, 'welcome']);
        $this->app->router->post('/streamStockData', [Welcome::class, 'streamStockData']);
        $this->app->router->post('/analyseStockData', [Welcome::class, 'analyseStockData']);
    }

}