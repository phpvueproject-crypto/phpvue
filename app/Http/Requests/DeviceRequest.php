<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeviceRequest extends FormRequest {
    public function authorize(): bool {
        return true;
    }

    public function rules(): array {
        return [
            'name'                           => ['required', 'string', 'max:255'],
            'ap'                             => [
                'nullable',
                'string',
                'max:255'
            ],
            'ip'                             => ['nullable', 'ip'], // 或使用 custom rule for INET 格式,
            'mir_status.initial_petri_count' => [
                'nullable',
                'integer'
            ]
        ];
    }

    public function withValidator($validator): void {
        $validator->after(function($validator) {
            $ap = $this->input('ap');
            $ip = $this->input('ip');

            if(empty($ap) && empty($ip)) {
                $validator->errors()->add('ap', 'AP 或 IP 必須至少填寫一個');
                $validator->errors()->add('ip', 'AP 或 IP 必須至少填寫一個');
            }
        });
    }
}
