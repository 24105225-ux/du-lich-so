<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClassRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'class_id' => [
                'required',
                'integer',
                'exists:school_classes,id',
            ],

            'program_schedule_id' => [
                'required',
                'integer',
                'exists:program_schedules,id',
            ],

            'student_count' => [
                'required',
                'integer',
                'min:1',
                'max:25',
            ],

            'note' => [
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'class_id.required' =>
                'Vui lòng chọn lớp.',

            'class_id.exists' =>
                'Lớp được chọn không tồn tại.',

            'program_schedule_id.required' =>
                'Vui lòng chọn lịch trình.',

            'program_schedule_id.exists' =>
                'Lịch trình không tồn tại.',

            'student_count.min' =>
                'Số học sinh phải từ 1 trở lên.',

            'student_count.max' =>
                'Số học sinh đăng ký tối đa mỗi lần là 25.',

            'note.max' =>
                'Ghi chú không được vượt quá 500 ký tự.',
        ];
    }
}
