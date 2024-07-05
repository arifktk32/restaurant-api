<?php

namespace App\Http\Requests;

use App\Models\Dish;
use Illuminate\Foundation\Http\FormRequest;

class StoreDishRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Dish::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:dishes,name',
            'description' => 'required|string|max:255|unique:dishes,description',
            'image_url' => 'required|url|max:255',
            'price' => 'required|numeric|min:0'
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'The name field is required.',
            'name.unique' => 'A dish with this name already exists.',
            'description.required' => 'The description field is required.',
            'description.unique' => 'A dish with this description already exists.',
            'image_url.required' => 'The image URL field is required.',
            'image_url.url' => 'The image URL must be a valid URL.',
            'price.required' => 'The price field is required.',
            'price.numeric' => 'The price must be a number.',
            'price.min' => 'The price must be at least 0.',
        ];
    }
}
