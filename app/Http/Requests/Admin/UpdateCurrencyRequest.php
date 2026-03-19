<?php

namespace App\Http\Requests\Admin;

use App\Models\Currency;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCurrencyRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Currency $currency */
        $currency = $this->route('currency');

        return [
            'code' => ['required', 'string', 'max:10', Rule::unique('currencies', 'code')->ignore($currency->id)],
            'name' => ['required', 'string', 'max:255'],
            'symbol' => ['required', 'string', 'max:10'],
            'decimal_places' => ['required', 'integer', 'between:0,4'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
