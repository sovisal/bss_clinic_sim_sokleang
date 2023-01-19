<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class MedicineRequest extends FormRequest
{
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize()
	{
		return true;
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules()
	{
        if ($this->ajax()) {
            return [];
        }
		return [
            'name_kh' => 'required|string|min:2|max:255',
            'name_en' => 'nullable|string|min:2|max:255',
            'cost' => 'nullable|numeric',
            'price' => 'nullable|numeric',
		];
	}
}
