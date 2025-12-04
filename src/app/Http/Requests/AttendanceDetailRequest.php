<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceDetailRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $user = auth()->user();
        $isAdmin = (bool) ($user->is_admin ?? false);

        // 管理者の直接修正 → 出勤・退勤・備考は必須
        if ($isAdmin && $this->isMethod('post')) {
            return [
                'clock_in'               => ['required', 'date_format:H:i'],
                'clock_out'              => ['required', 'date_format:H:i'],
                'break_times.*.break_in' => ['nullable', 'date_format:H:i'],
                'break_times.*.break_out'=> ['nullable', 'date_format:H:i'],
                'reason'                 => ['required', 'string', 'max:255'],
            ];
        }

        // 一般ユーザーの修正申請 → 出勤・退勤・備考は必須
        if (!$isAdmin && $this->isMethod('post')) {
            return [
                'clock_in'               => ['required', 'date_format:H:i'],
                'clock_out'              => ['required', 'date_format:H:i'],
                'break_times.*.break_in' => ['nullable', 'date_format:H:i'],
                'break_times.*.break_out'=> ['nullable', 'date_format:H:i'],
                'reason'                 => ['required', 'string', 'max:255'],
            ];
        }

        // GET（詳細表示のみ） → バリデーション不要
        return [];
    }

    public function messages()
    {
        return [
            'clock_in.required'        => '出勤時間を入力してください',
            'clock_out.required'       => '退勤時間を入力してください',
            'reason.required'          => '備考を記入してください',
            'clock_in.date_format'     => '出勤時間は00:00形式で入力してください',
            'clock_out.date_format'    => '退勤時間は00:00形式で入力してください',
            'break_times.*.break_in.date_format'  => '休憩開始時間は00:00形式で入力してください',
            'break_times.*.break_out.date_format' => '休憩終了時間は00:00形式で入力してください',
            'reason.max'              => '備考は255文字以内で入力してください',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $clockIn  = $this->input('clock_in');
            $clockOut = $this->input('clock_out');
            $breaks   = $this->input('break_times', []);

            // 「出勤時間」が「退勤時間」より後、またはその逆
            if ($clockIn && $clockOut && $clockIn > $clockOut) {
                $validator->errors()->add('clock_in',  '出勤時間もしくは退勤時間が不適切な値です');
                $validator->errors()->add('clock_out', '出勤時間もしくは退勤時間が不適切な値です');
            }

            foreach ($breaks as $index => $break) {
                $breakIn  = $break['break_in'] ?? null;
                $breakOut = $break['break_out'] ?? null;

                // 「休憩開始時間」が「出勤時間」より前
                if ($breakIn && $clockIn && $breakIn < $clockIn) {
                    $validator->errors()->add("break_times.$index.break_in", '休憩時間が不適切な値です');
                }

                // 「休憩開始時間」が「退勤時間」後
                if ($breakIn && $clockOut && $breakIn > $clockOut) {
                    $validator->errors()->add("break_times.$index.break_in", '休憩時間が不適切な値です');
                }

                // 「休憩終了時間」が「退勤時間」より後
                if ($breakOut && $clockOut && $breakOut > $clockOut) {
                    $validator->errors()->add("break_times.$index.break_out", '休憩時間もしくは退勤時間が不適切な値です');
                }

                // 休憩の片方だけ入力はエラーにする
                if (($breakIn && !$breakOut) || (!$breakIn && $breakOut)) {
                    $validator->errors()->add("break_times.$index.break_in", '休憩の開始と終了は両方入力してください');
                    $validator->errors()->add("break_times.$index.break_out", '休憩の開始と終了は両方入力してください');
                }
            }
        });
    }
}