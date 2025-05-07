<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReviewPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // TODO gate auth didn't already review subject
    }

    public function rules(): array
    {
        return [
            'content' => ['string', 'max:200'],
            'rating' => ['integer', 'between:1,5'],
        ];
    }
}
