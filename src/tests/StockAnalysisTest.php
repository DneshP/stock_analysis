<?php

namespace App\Tests;

use App\Database\StockDataSeed;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use PHPUnit\Framework\TestCase;
use stdClass;

class StockAnalysisTest extends TestCase
{
    /** @var Client  */
    private static $client;

    /** @var string Base URL */
    private static $uri;

    /**
     * @var StockDataSeed
     */
    private static $stockDataSeeder;

    public static function setUpBeforeClass(): void
    {
        /** To fetch the base url */
        require_once __DIR__ . DIRECTORY_SEPARATOR . '../config/Config.php';
        self::$uri = BASE_URL;
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
        $response = self::$client->get(self::$uri . 'welcome');
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * If not the requested data range for a stock is not found
     * @throws GuzzleException
     */
    public function testHandleInsufficientStockData()
    {
        self::$stockDataSeeder->seedStockData();

        $post = self::$client->post(self::$uri . 'analyseStockData',
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

    /**
     * If stock price is zero fetches the previous stock price
     * @throws GuzzleException
     */
    public function testIfZeroFetchesThePreviousPrice()
    {
        self::$stockDataSeeder->seedStockData(self::$stockDataSeeder->zeroStockPrice);
        $expected = new stdClass();
        $expected->buyDate = '11-02-2020';
        $expected->sellDate = '16-02-2020';
        $expected->profit = 130;
        $expected->meanStockPrice = 1477.286;
        $expected->standardDeviation = 55.874;

        $post = self::$client->post(self::$uri . 'analyseStockData',
            [
                'form_params' => [
                    'stock'     => 'googl',
                    'startDate' => '2020-02-01',
                    'endDate' => '2021-02-02'
                ]
            ]);
        $response = json_decode($post->getBody());
        $this->assertEquals(true, $response->status);
        $data = $response->data;
        $this->assertValues($expected, $data);
    }

    /**
     * Analyse minimal loss option
     * @throws GuzzleException
     */
    public function testMinimiseLoss()
    {
        self::$stockDataSeeder->seedStockData(self::$stockDataSeeder->minimiseLossData);
        $expected = new stdClass();
        $expected->buyDate = '11-02-2020';
        $expected->sellDate = '12-02-2020';
        $expected->profit = -1;
        $expected->meanStockPrice = 1455;
        $expected->standardDeviation = 198.879;

        $post = self::$client->post(self::$uri . 'analyseStockData',
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
        $this->assertValues($expected, $data);
    }

    /**
     * Analyses Stock Data
     * @throws GuzzleException
     */
    public function testMaximiseProfit()
    {
        self::$stockDataSeeder->seedStockData();
        $expected = new stdClass();
        $expected->buyDate = '14-02-2020';
        $expected->sellDate = '16-02-2020';
        $expected->profit = 10;
        $expected->meanStockPrice = 1509.857;
        $expected->standardDeviation = 18.65;

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
        $this->assertValues($expected, $data);
    }

    /**
     * Assert expected and actual
     * @param $expected
     * @param $actual
     */
    private function assertValues($expected, $actual)
    {
        $this->assertEquals($expected->buyDate, $actual->buySellDates->buy->date);
        $this->assertEquals($expected->sellDate, $actual->buySellDates->sell->date);
        $this->assertEquals($expected->profit, $actual->buySellDates->profit);
        $this->assertEquals($expected->meanStockPrice, $actual->meanStockPrice);
        $this->assertEquals($expected->standardDeviation, $actual->standardDeviation);

    }
}