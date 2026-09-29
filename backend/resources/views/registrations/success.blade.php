@extends('layouts.app')

@section('title', 'Đăng ký thành công')

@section('content')
<section class="section">
    <div class="container container--narrow">
        <div class="success-card">
            <span class="success-icon">
                ✓
            </span>

            <h1>
                Đăng ký đã được ghi nhận
            </h1>

            <p>
                Lớp:
                <strong>
                    {{ $registration->schoolClass?->class_name }}
                </strong>
            </p>

            <p>
                Chương trình:
                <strong>
                    {{ $registration->schedule?->program?->name }}
                </strong>
            </p>

            <p>
                Số học sinh:
                <strong>
                    {{ $registration->student_count }}
                </strong>
            </p>

            <p>
                Trạng thái:
                <strong>
                    {{ $registration->status }}
                </strong>
            </p>

            <div class="hero__actions">
                <a
                    class="button"
                    href="{{ route(
                        'programs.index'
                    ) }}"
                >
                    Quay lại chương trình
                </a>

                <a
                    class="button button--secondary"
                    href="{{ route(
                        'registrations.create'
                    ) }}"
                >
                    Đăng ký lớp khác
                </a>
            </div>
        </div>
    </div>
</section>
@endsection
