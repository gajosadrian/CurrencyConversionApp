<?php

namespace App\Http\Controllers;

use App\Http\Requests\CurrencyConvertFromRequest;
use App\Http\Requests\CurrencyConvertToRequest;
use App\Services\NbpService;
use Illuminate\Http\JsonResponse;

class CurrencyConvertionController extends Controller
{
    /**
     * @param CurrencyConvertToRequest $request
     * @param NbpService $nbpService
     * @return JsonResponse
     */
    public function convertTo(CurrencyConvertToRequest $request, NbpService $nbpService)
    {
        $currency = $request->get('currency');
        $amount = $request->get('amount');

        $data = $nbpService->convertAmountTo($currency, $amount);

        return response()->json($data);
    }

    /**
     * @param CurrencyConvertFromRequest $request
     * @param NbpService $nbpService
     * @return JsonResponse
     */
    public function convertFrom(CurrencyConvertFromRequest $request, NbpService $nbpService)
    {
        $currency = $request->get('currency');
        $amount = $request->get('amount');

        $data = $nbpService->convertAmountFrom($currency, $amount);

        return response()->json($data);
    }
}
