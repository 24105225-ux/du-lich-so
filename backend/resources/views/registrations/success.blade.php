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

            @if (->status === 'pending' && ->hold_expires_at)
                <p>
                    Giữ chỗ đến:
                    <strong>
                        {{ ->hold_expires_at->format('d/m/Y H:i:s') }}
                    </strong>
                </p>

                <p class="form-hint">
                    Đây là giữ chỗ tạm thời 15 phút. Sau thời điểm trên,
                    chỗ sẽ được giải phóng để lớp có thể đăng ký lại.
                </p>
            @endif
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
