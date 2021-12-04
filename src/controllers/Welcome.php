<?php

namespace App\Controllers;

use App\System\Application;
use App\System\Controller;
use App\System\Request;
use App\Models\Welcome as WelcomeModel;
use Exception;

/**
 * Class Welcome
 * @package App\Controllers\Welcome
 */
class Welcome extends Controller
{
    /** @var string  */
    private $header = 'layouts/header';

    /** @var WelcomeModel */
    private $model;

    /**
     * @inheritDoc
     */
    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->model = new WelcomeModel();
    }

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

    /**
     * Uploads the received chunk
     * @return false|string|void
     */
    public function streamStockData()
    {
        $stream = json_decode(file_get_contents('php://input'));
        if (isset($stream->fresh)) {
            $this->model->fresh();
        }
        if ($stream->status === 'on') {
            $data = explode( ';base64,', $stream->chunk);
            if (isset($data[1])) {
                $base64Decoded = base64_decode($data[1]);
                $rawData = explode(PHP_EOL, $base64Decoded);
                if ($stream->hasTags) {
                    $tags = explode(',', $rawData[0]);
                    array_shift($tags);
                    $diff = array_diff($tags, $this->model->columns());
                    if(!empty($diff)) {
                        return json_encode(['status' => false, 'data' => 'Invalid data provided, 
                        Please check column headings', 'debug' => json_encode($diff)]);
                    }
                    array_shift($rawData);
                }
                $data = [];

                foreach ($rawData as &$r) {
                        $raw = explode(',', $r);
                            array_shift($raw);
                        if (!empty($raw)) {
                            if (empty(array_diff([0,1,2], array_keys($raw)))) {
                                $raw[0] = $this->formatDate($raw[0]);
                                $raw[1] = strtolower($raw[1]);
                                for ($i = 0; $i < count($this->model->columns()); $i++ ) {
                                    $value = $raw[$i] ?? null;
                                    $raw[$i] = "'$value'";
                                }
                                $stock = implode(',', $raw);
                                $data [] = "(".$stock.")";
                            } else {
                                Application::log("Missing" . json_encode($raw), 'missing.txt');
                            }
                         }
                }
                 return $this->model->insertStockData(implode(',', $data));
            } else {
               return Application::jsend_success();
            }
        }
        if ($stream->status === 'done') {
               return $this->model->getStockNames();
        }
    }

    /**
     * @return string
     * @throws Exception
     */
    public function analyseStockData(): string
    {
        $data = $this->request->getBody();
        if (isset($data->stock)) {
            $stock  = $data->stock;
            $startDate = $this->formatDate($data->startDate);
            $endDate = $this->formatDate($data->endDate);
            $dateRange = ['start' => $startDate, 'end' => $endDate];
            return $this->model->getStockDataForADateRange($stock, $dateRange);
        }
        return Application::jsend_error('Data Missing');
    }

    /**
     * Converts date to YYYY-mm-dd
     * if date matches dd-mm-yyyy and mm-dd-yyyy
     * dd-mm-yyyy takes precedence
     * any other format are replaced to 0000-00-00
     * supported date formats
     * YYYY-mm-dd
     * dd-mm-YYYY
     * mm-dd-YYYY
     * @param $dateString
     * @return false|string|null
     */
    private function formatDate($dateString)
    {
        $dateString = str_replace('/', '-', $dateString);
        $monthRegex = '(0[1-9]|1[0-2])';
        $yearRegex = '\d{4}';
        $dayRegex = '(0[1-9]|[12][0-9]|3[01])';
        $yearMonthDay = "/^$yearRegex-$monthRegex-$dayRegex$/";
        $dayMonthYear = "/^$dayRegex-$monthRegex-$yearRegex$/";
        $monthDayYear = "/^$monthRegex-$dayRegex-$yearRegex$/";
        $formats = [$dayMonthYear, $monthDayYear, $yearMonthDay];
        $formattedDate = '0000-00-00';

        foreach ($formats as $format) {
            $result = [];
            preg_match($format, $dateString, $result);
            if (!empty($result)) {
                $date = date_create($dateString);
                if ($date) {
                    $formattedDate = date_format($date, "Y-m-d");
                    break;
                }
            }
        }
        return $formattedDate;
    }
}