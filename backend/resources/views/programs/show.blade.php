@extends('layouts.app')

@section(
    'title',
    $program->name . ' | Du lịch học đường'
)

@section('content')
<section class="section">
    <div class="container">
        <nav
            class="breadcrumb"
            aria-label="Đường dẫn"
        >
            <a href="{{ route('home') }}">
                Trang chủ
            </a>

            <span>/</span>

            <a href="{{ route('programs.index') }}">
                Chương trình
            </a>

            <span>/</span>

            <span aria-current="page">
                {{ $program->name }}
            </span>
        </nav>

        <div class="detail-grid">
            <article class="detail-main">
                <span class="badge">
                    {{ $program->education_level }}
                </span>

                <h1>
                    {{ $program->name }}
                </h1>

                <p class="lead">
                    {{ $program->description }}
                </p>

                <div class="stats-grid">
                    <div class="stat-card">
                        <span>
                            Thời lượng
                        </span>

                        <strong>
                            {{ $program->duration_days }}
                            ngày
                        </strong>
                    </div>

                    <div class="stat-card">
                        <span>
                            Sức chứa
                        </span>

                        <strong>
                            {{ $program->capacity }}
                            học sinh
                        </strong>
                    </div>

                    <div class="stat-card">
                        <span>
                            Chi phí
                        </span>

                        <strong>
                            {{ number_format(
                                (float)
                                $program->base_cost_per_student,
                                0,
                                ',',
                                '.'
                            ) }}
                            đ
                        </strong>
                    </div>
                </div>

                <section class="content-section">
                    <h2>
                        Các lịch khởi hành
                    </h2>

                    @forelse (
                        $program->schedules
                        as $schedule
                    )
                        <article class="schedule-card">
                            <div>
                                <strong>
                                    {{ $schedule->trip_date->format(
                                        'd/m/Y'
                                    ) }}
                                </strong>

                                <p>
                                    {{ $schedule->start_time }}
                                    –
                                    {{ $schedule->end_time }}
                                </p>
                            </div>

                            <div>
                                <strong>
                                    {{ $schedule->capacity }}
                                    học sinh
                                </strong>
                            </div>
                        </article>

                        @if ($schedule->stops->isNotEmpty())
                            <div class="timeline">
                                @foreach (
                                    $schedule->stops
                                    as $stop
                                )
                                    <div
                                        class="timeline__item"
                                    >
                                        <strong>
                                            Điểm
                                            {{ $stop->seq_no }}
                                        </strong>

                                        <p>
                                            {{ $stop->place?->name }}
                                        </p>

                                        <small>
                                            {{ $stop->place?->province }}
                                        </small>

                                        <p>{{ $stop->activity }}</p>
                                        @if ($stop->duration_minutes)
                                            <small>{{ $stop->duration_minutes }} phút</small>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    @empty
                        <div class="empty-state">
                            Chưa có lịch mở đăng ký.
                        </div>
                    @endforelse
                </section>
            </article>

            <aside class="detail-side">
                <div class="sticky-card">
                    <h2>
                        Đơn vị tổ chức
                    </h2>

                    <p>
                        {{ $program->organizer?->name }}
                    </p>

                    <div class="cancellation-policy" style="margin:16px 0; padding:14px; border:1px solid var(--border); border-radius:var(--radius);">
                        <strong>Chính sách hủy</strong>
                        <p>Việc hủy hoặc thay đổi đăng ký thực hiện theo thời hạn chốt danh sách của nhà trường và đơn vị tổ chức.</p>
                    </div>

                    <a
                        class="button"
                        href="{{ route(
                            'registrations.create'
                        ) }}"
                    >
                        Đăng ký theo lớp
                    </a>

                    <div style="margin-top:16px; padding:14px; border:1px solid var(--border); border-radius:var(--radius);">
                        <strong>Đánh giá</strong>
                        <p>Chỉ người đã thực sự tham gia chuyến đi mới được đánh giá. Việc kiểm tra điều kiện và gửi đánh giá được thực hiện ở giao diện Vue/API của hệ thống.</p>
                    </div>
                </div>
            </aside>
        </div>

        @if (!empty($recommendations['items']))
            <section class="content-section">
                <h2>Chương trình tương tự</h2>
                <div class="card-grid">
                    @foreach ($recommendations['items'] as $item)
                        <article class="card">
                            <div class="card__body">
                                <span class="badge">{{ $item['education_level'] ?? 'Chương trình' }}</span>
                                <h3 class="card__title">{{ $item['title'] ?? 'Chương trình gợi ý' }}</h3>
                                <p>Chi phí: <strong>{{ number_format((float)($item['price_per_student'] ?? 0), 0, ',', '.') }} đ/học sinh</strong></p>
                                @if (isset($item['similarity']))
                                    <p class="muted">Độ tương đồng: {{ number_format((float)$item['similarity'] * 100, 1) }}%</p>
                                @endif
                                <a class="button" href="{{ route('programs.show', $item['program_id']) }}">Xem chương trình</a>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</section>
@endsection
