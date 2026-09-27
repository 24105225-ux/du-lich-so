<article class="card">
    <div class="card__body">
        <span class="badge">
            {{ $program->education_level }}
        </span>

        <h2 class="card__title">
            {{ $program->title }}
        </h2>

        <p>
            Đơn vị tổ chức:
            <strong>
                {{ $program->organizer?->name ?? 'Đang cập nhật' }}
            </strong>
        </p>

        <p>
            Thời lượng:
            {{ $program->duration_days }} ngày
        </p>

        <p>
            Chi phí:
            <strong>
                {{ number_format(
                    (float) $program->base_cost_per_student,
                    0,
                    ',',
                    '.'
                ) }}
                đ/học sinh
            </strong>
        </p>

        <a
            class="button"
            href="{{ route(
                'programs.show',
                $program
            ) }}"
        >
            Xem chi tiết
        </a>
    </div>
</article>
