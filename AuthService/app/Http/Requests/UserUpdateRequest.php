<?php

namespace Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Пример валидации редактирование пользователя.
 */
class UserUpdateRequest extends FormRequest
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
    #[ArrayShape([
        'name' => "string[]",
        'email' => "array",
        'password' => "array"
    ])] public function rules(): array
    {
        return [
            'name' => ['string', 'max:255'],
            'email' => ['email', Rule::unique('users', 'email')->ignore(auth()->user()->id)],
            'password' => ['required', 'string', 'min:6', 'max:255'],
        ];
    }
}
