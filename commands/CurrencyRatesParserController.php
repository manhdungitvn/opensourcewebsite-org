<?php

namespace app\commands;

use app\commands\traits\ControllerLogTrait;
use app\helpers\Number;
use app\models\Currency;
use app\models\CurrencyRate;
use yii\base\Exception;
use yii\httpclient\Client;

class CurrencyRatesParserController extends Controller implements CronChainedInterface
{
    use ControllerLogTrait;

    const UPDATE_INTERVAL = 1 * 60 * 60; // seconds

    public function actionIndex()
    {
        $this->parser();
    }

    protected function parser()
    {
        $updatesCount = 0;
        $baseURL = 'https://api.exchangeratesapi.io/';
        $endpoint = 'latest';
        $currencyBase = Currency::find()->where('code=:code', [
            ':code' => 'USD'
        ])->one();
        $currencyRates = CurrencyRate::find()->where([
            'or',
            ['updated_at' => null],
            ['<', 'updated_at', time() - self::UPDATE_INTERVAL],
        ])->all();
        $flag = count(Currency::find()->all()) > 0 && count($currencyRates) > 0;
        if ($flag) {
            $client = new Client(['baseUrl' => $baseURL . $endpoint]);
            $response = $client->createRequest()
                ->addHeaders(['content-type' => 'application/json'])
                ->setData([
                    'base' => 'USD',
                ])->send();
            try {
                $data = $response->getData();
                if (count($data['rates']) > 0) {
                    $exchangeRates = $data['rates'];
                    foreach (array_keys($exchangeRates) as $key) {
                        $currency = Currency::find()->where('code=:code', [
                            ':code' => $key
                        ])->one();
                        if (isset($currency)) {
                            $currencyRate = CurrencyRate::find()->where([
                                'or',
                                ['updated_at' => null],
                                ['<', 'updated_at', time() - self::UPDATE_INTERVAL],
                            ])->andWhere([
                                'from_currency_id' => $currencyBase->id,
                                'to_currency_id' => $currency->id
                            ])->one();
                            if (!isset($currencyRate)) {
                                $currencyRate = new CurrencyRate();
                                $currencyRate->from_currency_id = $currencyBase->id;
                                $currencyRate->to_currency_id = $currency->id;
                            }
                            $currencyRate->rate = $exchangeRates[$key];
                            $currencyRate->updated_at = time();
                            $currencyRate->save();
                            $updatesCount++;
                        }
                    }
                }
                if ($updatesCount) {
                    $this->output('Currencies parsed: ' . $updatesCount);
                }
            } catch (Exception $e) {
                echo 'ERROR: parsing result from ' . $baseURL . ': ' . $e->getMessage() . "\n";
            }
        }
    }
}
