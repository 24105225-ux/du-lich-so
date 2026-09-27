@extends('layouts.app')

@section(
    'title',
    $program->title . ' | Du lịch học đường'
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
                {{ $program->title }}
            </span>
        </nav>

        <div class="detail-grid">
            <article class="detail-main">
                <span class="badge">
                    {{ $program->education_level }}
                </span>

                <h1>
                    {{ $program->title }}
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
                                            Ngày {{ $stop->day_no }}
                                            · điểm
                                            {{ $stop->seq_no }}
                                        </strong>

                                        <p>
                                            {{ $stop->place?->name }}
                                        </p>

                                        <small>
                                            {{ $stop->place?->province }}
                                        </small>

                                        @if ($stop->note)
                                            <p>
                                                {{ $stop->note }}
                                            </p>
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

                    <a
                        class="button"
                        href="{{ route(
                            'registrations.create'
                        ) }}"
                    >
                        Đăng ký theo lớp
                    </a>
                </div>
            </aside>
        </div>
    </div>
</section>
@endsection
