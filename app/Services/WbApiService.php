<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;

class WbApiService
{
    private string $baseUrl = 'http://109.73.206.144:6969/api';
    private string $apiKey = 'E6kUTYrYwZq2tN4QEtyzsbEBk3ie';

    public function stocks($params = [])
    {
        return Http::get($this->baseUrl . '/stocks', array_merge([
            'key' => $this->apiKey,
            'dateFrom' => now()->toDateString(),
            'page' => $params['page'],
            'limit' => 100,
        ], $params))->json();
    }

    public function incomes($params = [])
    {
        return Http::get($this->baseUrl . '/incomes', array_merge([
            'key' => $this->apiKey,
            'dateFrom' => '2024-01-01',
            'dateTo' => '2027-01-01',
            'page' => $params['page'],
            'limit' => 100,
        ], $params))->json();
    }

    public function sales($params = [])
    {
        $response = Http::retry(5, 2000) // 5 попыток, 2 сек пауза
        ->timeout(30)
            ->get($this->baseUrl . '/sales', array_merge([
                'key' => $this->apiKey,
                'dateFrom' => '2024-01-01',
                'dateTo' => '2027-01-01',
                'limit' => 500,
            ], $params));

        if ($response->status() == 429) {
            sleep(5);
            return $this->sales($params); // повтор
        }

        return $response->json();
    }

    public function orders($params = [])
    {
        $response = Http::retry(5, 2000) // 5 попыток, 2 сек пауза
        ->timeout(30)
            ->get($this->baseUrl . '/orders', array_merge([
                'key' => $this->apiKey,
                'dateFrom' => '2024-01-01',
                'dateTo' => '2027-01-01',
                'limit' => 500,
            ], $params));

        if ($response->status() == 429) {
            sleep(5);
            return $this->sales($params); // повтор
        }

        return $response->json();
    }

}
