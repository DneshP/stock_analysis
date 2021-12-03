<?php

namespace App\Models;

use App\System\Application;
use App\System\Model;
use DateTime;
use Exception;

/**
 * Class Welcome
 * @package App\Models\Welcome
 */
class Welcome extends Model
{

    /**
     * @var array|false
     */
    private $stockData;

    /** @var string  */
    public static $TABLE_NAME = 'stock_data';

    /** @var string[]  */
    private static $COLUMNS = ['date', 'stock_name', 'price'];

    /** @var int  */
    private static $SAMPLE = 1;

    /** @var int  */
    private static $POPULATED = 2;

    /** @var string */
    private $startDate;

    /** @var string */
    private $endDate;

    /** @var int share bought */
    public static $SHARES = 200;

    /**
     * Table Name
     * @return string
     */
    public function tableName(): string
    {
        return self::$TABLE_NAME;
    }

    /**
     * Fillable columns
     * @return string[]
     */
    public function columns(): array
    {
        return self::$COLUMNS;
    }

    /**
     * @param $data
     * @return string
     */
    public function insertStockData($data): string
    {
        $tableName = $this->tableName();
        $attributes = implode(',', $this->columns());
        $SQL = "INSERT INTO $tableName ($attributes) VALUES " . $data;

        try {
           $this->persist($SQL);
           return Application::jsend_success('Data Received');
        } catch (Exception $exception) {
            $this->log($exception);
            return Application::jsend_error('Internal server error');
        }
    }

    /**
     * Fetches unique stock names
     * @return string
     */
    public function getStockNames(): string
    {
        $params = $this->filterParams();
        $params->select[] = "DISTINCT(stock_name)";
        $stockNames = $this->fetch($params, \PDO::FETCH_COLUMN);
        if (!$stockNames) {
            return Application::jsend_error('Failed to fetch stock name. Please contact support.');
        }
        return Application::jsend_success($stockNames);
    }

    /**
     * @param $stockName
     * @return string|void
     */
    protected function getSockData($stockName): string
    {
        $get = $this->filterParams();
        $get->select = self::$COLUMNS;
        $get->where = ['stock_name' => [$stockName, '=']];
        $this->stockData = $this->fetch($get);
        if ($this->stockData && count($this->stockData) === 1) {
            return Application::$app->jsend_error('Not much data to analyse');
        }
    }

    /**
     * @param $stock
     * @param array $dateRange
     * @return string
     * @throws Exception
     */
    public function getStockDataForADateRange($stock, array $dateRange): string
    {
        $get = $this->filterParams();
        $this->startDate = $dateRange['start'];
        $this->endDate = $dateRange['end'];
        $get->select = ["date, price"];
        $get->where = [
            ['stock_name' => ['=', $stock]],
            ['date' => ['>=', $dateRange['start']]],
            ['date' => ['<=', $dateRange['end']]]
        ];
        $get->orderBy = ['ORDER BY date ASC'];
        $this->stockData = $this->fetch($get);
        if (empty($this->stockData) || count($this->stockData) === 1) {
            return Application::$app->jsend_error('Not enough data to analyse, try changing the date range or pick a different stock.');
        }

        $buySellDates = $this->getBuySellDates();
        $meanStockPrice = $this->meanStockPrice();
        $standardDeviation = $this->calculateStandardDeviation();
        if (!$meanStockPrice || !$standardDeviation || !$buySellDates) {
            return Application::$app->jsend_error('Error calculating numbers, Internal server error');
        }
        return Application::jsend_success([
            'buySellDates' => $buySellDates,
            'meanStockPrice' => $meanStockPrice,
            'standardDeviation' => $standardDeviation
        ]);
    }


    /**
     * Mean stock price
     * @return false|float
     */
    protected function meanStockPrice()
    {
        if (!$this->stockData) {
            return false;
        }
        $prices = array_map(function ($stock) {
            return (float)$stock->price;
        },$this->stockData);
        $total = array_sum($prices);
        $records = count($prices);
        if ($records === 0) {
            $this->log(json_encode(['data' => $this->stockData, 'message' => 'computed zero for count']));
            return false;
        }
        return round(($total / $records),3);
    }

    /**
     * Computes standard deviation
     */
    protected function calculateStandardDeviation($mode = 1)
    {
        if (!$this->stockData) {
            return false;
        }
        $meanStockPrice = $this->meanStockPrice();
        $priceDiffFromMean = array_map(function ($stock) use ($meanStockPrice){
            $minusMeanPrice = $stock->price - $meanStockPrice;
            return $minusMeanPrice * $minusMeanPrice;
            }, $this->stockData);
        $records = count($priceDiffFromMean);
        if ($mode === self::$SAMPLE) {
            $records -= 1;
        }
        if ($records === 0) {
            $this->log(json_encode(['data' => $this->stockData, 'message' => 'computed zero for count']));
            return false;
        }
        $standardDeviation = round(sqrt(array_sum($priceDiffFromMean) / $records),3);
        if (is_nan($standardDeviation)) {
            return false;
        }
        return $standardDeviation;
    }

    /**
     * Fetches the buy sell dates for a stock in
     * the data set.
     * @throws Exception
     */
    private function getBuySellDates(): object
    {
        $minMax = $this->getMinMaxPriceIndex($this->stockData);
        $currentMax = $minMax->max;
        $currentMin = $minMax->min;
        if ($currentMax->index === 0) {
            $stock = array_slice($this->stockData, 1, null,true);
            $currentMax = $this->getMinMaxPriceIndex($stock, false)->max;
        }
        $result = new \stdClass();
        if ($currentMin->index < $currentMax->index) {
            $minStock = $this->stockData[$currentMin->index];
            $buyWorth = self::$SHARES * $minStock->price;
            $maxStock = $this->stockData[$currentMax->index];
            $sellWorth = self::$SHARES * $maxStock->price;
            $result->buy = (object)[
                'date' => $this->formatDate($minStock->date),
                'price' => $minStock->price,
                'shares' => $buyWorth
            ];
            $result->sell = (object)[
                'date' => $this->formatDate($maxStock->date),
                'price' => $maxStock->price,
                'shares' => $sellWorth
            ];
            $result->profit = $result->sell->price - $result->buy->price;
            return $result;
        } else {
            $stock = $this->minimiseLoss();
            $buyWorth = self::$SHARES * $stock->buy->price;
            $sellWorth = self::$SHARES * $stock->sell->price;
            $result->buy = (object)[
                'date' => $this->formatDate($stock->buy->date),
                'price' => $stock->buy->price,
                'shares' => $buyWorth
            ];
            $result->sell = (object)[
                'date' => $this->formatDate($stock->sell->date),
                'price' => $stock->sell->price,
                'shares' => $sellWorth
            ];
            $result->profit = $result->sell->price - $result->buy->price;
        }
        return $result;
    }

    /**
     * @param $data
     * @param bool $computeMin
     * @return object
     */
    private function getMinMaxPriceIndex($data, bool $computeMin = true): object
    {
        $currentMax = (object)['price' => 0, 'index' => 0];
        $currentMin = (object)['price' => 0, 'index' => 0];
        foreach ($data as $key => $stock) {
            if ($stock->price === 0) {
                $stock->price = $stock[$key - 1] ?? 0;
            }
            if ($currentMax->price < (float) $stock->price) {
                $currentMax->price = (float) $stock->price;
                $currentMax->index = $key;
            }
            if ($computeMin) {
                if ($key === 0) {
                    $currentMin->price =  $currentMax->price;
                    $currentMin->index =  $currentMax->index;
                }
                if ($stock->price < $currentMin->price) {
                    $currentMin->index = $key;
                    $currentMin->price = (float) $stock->price;
                }
            }
        }
        return (object)['min' => $computeMin ? $currentMin : null, 'max' => $currentMax];
    }

    /**
     *
     * @return object
     */
     private function minimiseLoss(): object
     {
        $stocks = $this->stockData;
        $count = count($stocks) - 1;
        $minimiseLoss = (object)[
            'amount' => (float)$stocks[0]->price - (float)$stocks[1]->price,
            'buy' => (object)['date' => $stocks[0]->date, 'price' => $stocks[0]->price],
            'sell' => (object)['date' => $stocks[1]->date, 'price' => $stocks[1]->price],
            ];
        foreach ($stocks as $i => $stock) {
           for ($j = $i + 1; $j < $count; $j++) {
               if ($stock->price === 0) {
                   $stock->price = $stock[$i - 1]->price ?? 0;
               }
               if ($stocks[$j]->price === 0) {
                   $stocks[$j]->price = $stocks[$j - 1]->price ?? 0;
               }
               $diff = (float)$stock->price - (float)$stocks[$j]->price;
               if ($diff < $minimiseLoss->amount) {
                   $minimiseLoss->price = $diff;
                   $minimiseLoss->buy->date = $stock->date;
                   $minimiseLoss->buy->price = $stock->price;
                   $minimiseLoss->sell->date = $stocks[$j]->date;
                   $minimiseLoss->sell->price = $stocks[$j]->price;
               }
           }
        }
        return $minimiseLoss;
    }

    /**
     * Format date to d-m-Y
     * @throws Exception
     */
    private function formatDate(string $date): string
    {
        try {
            $date = new DateTime($date);
            return $date->format('d-m-Y');
        } catch (Exception $exception) {
            $this->log("Error formatting date $exception");
            return '0000-00-00';
        }
    }
}