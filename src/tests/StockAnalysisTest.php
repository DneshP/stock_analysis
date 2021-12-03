<?php

namespace App\Tests;

use App\Database\StockDataSeed;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use PHPUnit\Framework\TestCase;

class StockAnalysisTest extends TestCase
{
    /** @var Client  */
    private static $client;

    /** @var string Base URL */
    private static $uri = 'http://localhost/test/stock_analysis';

    /**
     * @var StockDataSeed
     */
    private static $stockDataSeeder;

    public static function setUpBeforeClass(): void
    {
        self::$client = new Client([
            'base_uri' => self::$uri
        ]);
        self::$stockDataSeeder = new StockDataSeed();
    }

    public static function tearDownAfterClass(): void
    {
        self::$client = null;
    }

    /**
     * Access homepage
     * @throws GuzzleException
     */
    public function testAccessHome()
    {
        $response = self::$client->get('/');
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Access welcome page
     * @throws GuzzleException
     */
    public function testAccessWelcome()
    {
        $response = self::$client->get(self::$uri . '/welcome');
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * If not the requested data range for a stock is not found
     * @throws GuzzleException
     */
    public function testHandleInsufficientStockData()
    {
        self::$stockDataSeeder->seedStockData();

        $post = self::$client->post(self::$uri . '/analyseStockData',
            [
                'form_params' => [
                    'stock'     => 'appl',
                    'startDate' => '2020-02-02',
                    'endDate' => '2021-02-02'
                ]
            ]);
        $response = json_decode($post->getBody());
        $this->assertEquals(false, $response->status);
        $this->assertEquals('Not enough data to analyse, try changing the date range or pick a different stock.', $response->data);
    }

    public function testMinimiseLoss()
    {
        self::$stockDataSeeder->seedStockData(self::$stockDataSeeder->minimiseLossData);
        $buyDate = '11-02-2020';
        $sellDate = '12-02-2020';
        $profit = -1;
        $meanStockPrice = 1455;
        $standardDeviation = 198.879;

        $post = self::$client->post(self::$uri . '/analyseStockData',
            [
                'form_params' => [
                    'stock'     => 'googl',
                    'startDate' => '2020-02-02',
                    'endDate' => '2021-02-02'
                ]
            ]);
        $response = json_decode($post->getBody());
        $this->assertEquals(true, $response->status);
        $data = $response->data;
        $this->assertEquals($buyDate, $data->buySellDates->buy->date);
        $this->assertEquals($sellDate, $data->buySellDates->sell->date);
        $this->assertEquals($profit, $data->buySellDates->profit);
        $this->assertEquals($meanStockPrice, $data->meanStockPrice);
        $this->assertEquals($standardDeviation, $data->standardDeviation);
    }

    /**
     * Analyses Stock Data
     * @throws GuzzleException
     */
    public function testMaximiseProfit()
    {
        self::$stockDataSeeder->seedStockData();
        $buyDate = '14-02-2020';
        $sellDate = '16-02-2020';
        $profit = 10;
        $meanStockPrice = 1509.857;
        $standardDeviation = 18.65;

        $post = self::$client->post(self::$uri . '/analyseStockData',
            [
                'form_params' => [
                    'stock'     => 'googl',
                    'startDate' => '2020-02-02',
                    'endDate' => '2021-02-02'
                ]
        ]);
        $response = json_decode($post->getBody());
        $this->assertEquals(true, $response->status);
        $data = $response->data;
        $this->assertEquals($buyDate, $data->buySellDates->buy->date);
        $this->assertEquals($sellDate, $data->buySellDates->sell->date);
        $this->assertEquals($profit, $data->buySellDates->profit);
        $this->assertEquals($meanStockPrice, $data->meanStockPrice);
        $this->assertEquals($standardDeviation, $data->standardDeviation);
    }
}