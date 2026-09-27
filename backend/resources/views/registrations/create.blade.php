@extends('layouts.app')

@section('title', 'Đăng ký chương trình theo lớp')

@section('content')
<section class="section">
    <div class="container container--narrow">
        <span class="eyebrow">
            NHÀ TRƯỜNG
        </span>

        <h1>
            Đăng ký chương trình theo lớp
        </h1>

        <p class="lead">
            Chọn lớp, lịch trình và số lượng học sinh
            tham gia chương trình trải nghiệm.
        </p>

        <form
            method="POST"
            action="{{ route(
                'registrations.store'
            ) }}"
            class="form-card"
        >
            @csrf

            <div class="form-group">
                <label for="class_id">
                    Lớp
                </label>

                <select
                    id="class_id"
                    name="class_id"
                    required
                >
                    <option value="">
                        -- Chọn lớp --
                    </option>

                    @foreach ($classes as $class)
                        <option
                            value="{{ $class->id }}"
                            @selected(
                                old('class_id')
                                == $class->id
                            )
                        >
                            {{ $class->school?->name }}
                            -
                            {{ $class->class_name }}
                            -
                            {{ $class->academic_year }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="program_schedule_id">
                    Lịch trình
                </label>

                <select
                    id="program_schedule_id"
                    name="program_schedule_id"
                    required
                >
                    <option value="">
                        -- Chọn lịch trình --
                    </option>

                    @foreach ($schedules as $schedule)
                        <option
                            value="{{ $schedule->id }}"
                            @selected(
                                old(
                                    'program_schedule_id'
                                )
                                == $schedule->id
                            )
                        >
                            {{ $schedule->program?->name }}
                            -
                            {{ $schedule->trip_date->format(
                                'd/m/Y'
                            ) }}
                            -
                            {{ $schedule->capacity }}
                            chỗ
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="student_count">
                    Số học sinh
                </label>

                <input
                    id="student_count"
                    type="number"
                    name="student_count"
                    min="1"
                    max="60"
                    value="{{ old(
                        'student_count'
                    ) }}"
                    required
                >
            </div>

            <div class="form-group">
                <label for="note">
                    Ghi chú
                </label>

                <textarea
                    id="note"
                    name="note"
                    rows="5"
                    maxlength="500"
                >{{ old('note') }}</textarea>
            </div>

            <button
                type="submit"
                class="button"
            >
                Gửi đăng ký
            </button>
        </form>
    </div>
</section>
@endsection



