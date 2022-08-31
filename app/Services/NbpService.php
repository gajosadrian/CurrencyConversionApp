<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class NbpService
{
    private const BASE_URL = 'https://api.nbp.pl/api/';
    private const CACHE_KEY = 'nbp.exchangerates';

    public function getExchangeRates(): ?Collection
    {
        $data = Cache::get(static::CACHE_KEY);

        if (!$data) {
            return null;
        }

        return collect($data['rates']);
    }

    /**
     * @param string $currency
     * @param float $amount
     * @return array|null
     */
    public function convertAmountTo(string $currency, float $amount): ?array
    {
        $rates = $this->getExchangeRates();
        $rate = $rates->firstWhere('code', Str::upper($currency));

        if (!$rate) {
            return null;
        }

        $bidAmount = round($amount / $rate['bid'], 4);
        $askAmount = round($amount / $rate['ask'], 4);

        return [
            'rates' => $rate,
            'bidAmount' => $bidAmount,
            'askAmount' => $askAmount,
        ];
    }

    /**
     * @param string $currency
     * @param float $amount
     * @return array|null
     */
    public function convertAmountFrom(string $currency, float $amount): ?array
    {
        $rates = $this->getExchangeRates();
        $rate = $rates->firstWhere('code', Str::upper($currency));

        if (!$rate) {
            return null;
        }

        $bidAmount = round($amount * $rate['bid'], 4);
        $askAmount = round($amount * $rate['ask'], 4);

        return [
            'rates' => $rate,
            'bidAmount' => $bidAmount,
            'askAmount' => $askAmount,
        ];
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function fetchExchangeRatesTable(): void
    {
        $res = Http::get(static::BASE_URL . '/exchangerates/tables/c/today', [
            'format' => 'json',
        ]);

        if ($res->failed()) {
            throw new \Exception('Failed to connect to the ' . static::BASE_URL);
        }

        if ($res->status() == '404') {
            Cache::forget(static::CACHE_KEY);
        }

        Cache::put(static::CACHE_KEY, $res->collect()->first(), now()->endOfDay());
    }
}
