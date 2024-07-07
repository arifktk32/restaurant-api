<?php

namespace App\Http\Requests;

use App\Models\Dish;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDishRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $dishId = $this->route('dish')->id;

        return [
            'name' => 'sometimes|string|max:255|unique:dishes,name,' . $dishId,
            'description' => 'sometimes|string|max:255|unique:dishes,description,' . $dishId,
            'image_url' => 'sometimes|url|max:255',
            'price' => 'sometimes|numeric|min:0',
        ];
    }
}
