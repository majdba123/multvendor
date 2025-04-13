<?php

namespace App\Http\Requests\Afiliate;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserAndAfiliateRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,'.$this->user()->id,
            'password' => 'sometimes|string|min:8',
            'status' => 'sometimes|string|in:active,pending,pand',
        ];
    }
}
