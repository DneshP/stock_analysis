<?php

namespace App\Controllers;

use App\System\Controller;

/**
 * Class Welcome
 * @package App\Controllers\Welcome
 */
class Welcome extends Controller
{
    private $header = 'layouts/header';

    /**
     * @return string
     */
    public function index(): string
    {
        $viewData = $this->viewData();
        $viewData->header = $this->header;
        $viewData->body = 'welcome/index';
        $viewData->footer = 'layouts/footer';

        return $this->render($viewData);
    }

    /**
     * @return string
     */
    public function welcome(): string
    {
        $viewData = $this->viewData();
        $viewData->header = $this->header;
        $viewData->body = 'welcome/form';
        $viewData->footer = 'layouts/welcome/footer';

        return $this->render($viewData);
    }
}