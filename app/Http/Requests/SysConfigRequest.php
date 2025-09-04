<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SysConfigRequest extends FormRequest {
    public function authorize(): bool {
        return true;
    }

    public function rules(): array {
        return [
            'val' => ['required', 'string', 'in:A,B,C,D']
        ];
    }
}
