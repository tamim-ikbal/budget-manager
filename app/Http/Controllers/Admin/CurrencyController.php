<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Currency\DestroyCurrencyAction;
use App\Actions\Currency\SetDefaultCurrencyAction;
use App\Actions\Currency\StoreCurrencyAction;
use App\Actions\Currency\UpdateCurrencyAction;
use App\DTOs\Currency\SetDefaultCurrencyData;
use App\DTOs\Currency\StoreCurrencyData;
use App\DTOs\Currency\UpdateCurrencyData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DestroyCurrencyRequest;
use App\Http\Requests\Admin\SetDefaultCurrencyRequest;
use App\Http\Requests\Admin\StoreCurrencyRequest;
use App\Http\Requests\Admin\UpdateCurrencyRequest;
use App\Http\Resources\Admin\CurrencyResource;
use App\Models\Currency;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CurrencyController extends Controller
{
    public function index(Request $request): Response
    {
        $currencies = Currency::query()
            ->orderByDesc('is_default')
            ->orderBy('code')
            ->get();

        return Inertia::render('admin/currencies/Index', [
            'currencies' => CurrencyResource::collection($currencies)->resolve(),
            'status' => $request->session()->get('status'),
        ]);
    }

    public function store(StoreCurrencyRequest $request, StoreCurrencyAction $storeCurrency): RedirectResponse
    {
        $validated = $request->validated();

        $storeCurrency(new StoreCurrencyData(
            code: strtoupper($validated['code']),
            name: $validated['name'],
            symbol: $validated['symbol'],
            decimalPlaces: (int) $validated['decimal_places'],
            isActive: (bool) $validated['is_active'],
        ));

        return back()->with('status', 'Currency created successfully.');
    }

    public function update(UpdateCurrencyRequest $request, Currency $currency, UpdateCurrencyAction $updateCurrency): RedirectResponse
    {
        $validated = $request->validated();

        $updateCurrency($currency, new UpdateCurrencyData(
            code: strtoupper($validated['code']),
            name: $validated['name'],
            symbol: $validated['symbol'],
            decimalPlaces: (int) $validated['decimal_places'],
            isActive: (bool) $validated['is_active'],
        ));

        return back()->with('status', 'Currency updated successfully.');
    }

    public function setDefault(SetDefaultCurrencyRequest $request, Currency $currency, SetDefaultCurrencyAction $setDefaultCurrency): RedirectResponse
    {
        $setDefaultCurrency(new SetDefaultCurrencyData($currency));

        return back()->with('status', 'Default currency updated successfully.');
    }

    public function destroy(DestroyCurrencyRequest $request, Currency $currency, DestroyCurrencyAction $destroyCurrency): RedirectResponse
    {
        $destroyCurrency($currency);

        return back()->with('status', 'Currency deleted successfully.');
    }
}
