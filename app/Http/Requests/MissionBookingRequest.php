<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MissionBookingRequest extends FormRequest {
    public function authorize(): bool {
        return true;
    }

    public function rules(): array {
        return [
            'name' => ['required', 'string', 'max:255'],
            'ap'   => [
                'required',
                'string',
                'max:255'
            ],
            'ip'   => ['required', 'ip'], // 或使用 custom rule for INET 格式
        ];
    }
}
