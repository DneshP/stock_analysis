<?php

namespace App\System;

use Error;
use stdClass;

class Controller
{

    /**
     * @param $viewData
     * @param array $params
     * @return string
     */
    public function render($viewData, array $params = []): string
    {
        if ($viewData->body === '') {
            throw new Error('View not specified');
        }
        $header = $viewData->header ? $this->getPath($viewData->header) : false;
        $body = $this->getPath($viewData->body);
        $footer = $viewData->footer ? $this->getPath($viewData->footer) : false;
        $scripts = $viewData->scripts ?? false;
        $viewContent = '';

        if (count($params) > 0) {
            foreach ($params as $key => $value) {
                $$key = $value;
            }
        }

        foreach ([$header, $body, $footer] as $view) {
            if ($view) {
                if (file_exists($view)) {
                    ob_start();
                    require_once $view;
                    $viewContent .= ob_get_clean();
                }
            }
        }
        return $viewContent;
    }

    /**
     * @param $values
     * @return string
     */
    protected function getPath($values): string
    {
       $pathParts = explode('/', $values);
       $levels = count($pathParts);

       if ($levels === 1) {
           return VIEWS_PATH. DIRECTORY_SEPARATOR . $values . '.php';
       }
       $path = VIEWS_PATH;

       for ($i = 0; $i <= $levels - 1; $i++) {
           $path .= DIRECTORY_SEPARATOR . $pathParts[$i];
           if ($i === $levels - 1) {
               $path .= '.php';
           }
       }
       return $path;
    }

    /**
     * View Data Signature
     * @return stdClass
     */
    public function viewData(): stdClass
    {
        $viewData = new stdClass();
        $viewData->header = '';
        $viewData->body = '';
        $viewData->footer = '';
        $viewData->scripts = [];
        return $viewData;
    }
}