<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for creating or updating a layer.
 *
 * @property int   $layer_order
 * @property float $thickness
 * @property float $width
 * @property float $angle
 */
class StoreLayerRequest extends FormRequest
{
    /**
     * Determine if the user is authorised to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'layer_order' => 'required|integer|min:1',
            'thickness'   => 'required|numeric|min:0',
            'width'       => 'required|numeric|min:0',
            'angle'       => 'required|numeric',
        ];
    }
}
