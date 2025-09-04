<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MissionQueueRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array {
        return [
            'state_id' => [
                'nullable',
                'integer'
            ],
            'remark'   => [
                'nullable',
                'string',
                'max:65535'
            ],
        ];
    }
}
