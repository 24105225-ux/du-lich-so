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

        @if ($errors->any())
            <div class="form-error" role="alert">
                <strong>
                    Không thể gửi đăng ký.
                </strong>

                <ul>
                    @foreach ($errors->all() as $error)
                        <li>
                            {{ $error }}
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form
            method="POST"
            action="{{ route('registrations.store') }}"
            class="form-card"
            id="registration-form"
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
                                old('class_id') == $class->id
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

                <p
                    id="class-info"
                    class="form-hint"
                    aria-live="polite"
                ></p>
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
                            data-capacity="{{ $schedule->capacity }}"
                            data-base-label="{{ $schedule->program?->name }} - {{ $schedule->trip_date->format('d/m/Y') }}"
                            @selected(
                                old('program_schedule_id')
                                == $schedule->id
                            )
                        >
                            {{ $schedule->program?->name }}
                            -
                            {{ $schedule->trip_date->format('d/m/Y') }}
                            -
                            {{ $schedule->capacity }} chỗ tối đa
                        </option>
                    @endforeach
                </select>

                <p
                    id="schedule-info"
                    class="form-hint"
                    aria-live="polite"
                ></p>
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
                    max="25"
                    value="{{ old('student_count') }}"
                    required
                >

                <p
                    id="remaining-seats"
                    class="form-hint"
                    aria-live="polite"
                ></p>
            </div>

            <div class="form-group">
</div>

            <button
                type="submit"
                class="button"
                id="submit-button"
            >
                Gửi đăng ký
            </button>
        </form>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const classSelect =
        document.getElementById('class_id');

    const scheduleSelect =
        document.getElementById('program_schedule_id');

    const studentInput =
        document.getElementById('student_count');

    const scheduleInfo =
        document.getElementById('schedule-info');

    const remainingInfo =
        document.getElementById('remaining-seats');

    const form =
        document.getElementById('registration-form');

    let remainingSeats = null;

    function getSelectedSchedule() {
        return scheduleSelect.options[
            scheduleSelect.selectedIndex
        ];
    }

    function updateScheduleInfo() {
        const selectedSchedule =
            getSelectedSchedule();

        if (!selectedSchedule) {
            scheduleInfo.textContent = '';
            return;
        }

        const capacity =
            Number(
                selectedSchedule.dataset.capacity || 0
            );

        if (capacity > 0) {
            scheduleInfo.textContent =
                `Sức chứa tối đa của lịch trình: ${capacity} chỗ.`;
        } else {
            scheduleInfo.textContent = '';
        }
    }

    function updateScheduleOptionLabel() {
        const selectedSchedule =
            getSelectedSchedule();

        if (!selectedSchedule) {
            return;
        }

        const baseLabel =
            selectedSchedule.dataset.baseLabel || '';

        const capacity =
            Number(
                selectedSchedule.dataset.capacity || 0
            );

        if (!baseLabel || capacity <= 0) {
            return;
        }

        if (remainingSeats === null) {
            selectedSchedule.textContent =
                `${baseLabel} - ${capacity} chỗ tối đa`;
            return;
        }

        selectedSchedule.textContent =
            `${baseLabel} - Còn ${remainingSeats}/${capacity} chỗ`;
    }

    async function loadRemainingSeats() {
        const classId =
            classSelect.value;

        const scheduleId =
            scheduleSelect.value;

        remainingSeats = null;

        updateScheduleOptionLabel();

        if (!classId || !scheduleId) {
            remainingInfo.textContent = '';
            return;
        }

        remainingInfo.textContent =
            'Đang kiểm tra số chỗ trống...';

        const url =
            new URL(
                "{{ route('registrations.remaining-seats') }}",
                window.location.origin
            );

        url.searchParams.set(
            'class_id',
            classId
        );

        url.searchParams.set(
            'program_schedule_id',
            scheduleId
        );

        try {
            const response =
                await fetch(
                    url.toString(),
                    {
                        headers: {
                            'Accept': 'application/json'
                        }
                    }
                );

            if (!response.ok) {
                throw new Error(
                    'Không thể kiểm tra số chỗ.'
                );
            }

            const data =
                await response.json();

            remainingSeats =
                Number(data.remainingSeats);

            updateScheduleOptionLabel();
            updateRemainingMessage();

        } catch (error) {
            remainingSeats = null;

            updateScheduleOptionLabel();

            remainingInfo.textContent =
                'Không thể kiểm tra số chỗ trống.';
        }
    }

    function updateRemainingMessage() {
        if (remainingSeats === null) {
            return;
        }

        const requested =
            studentInput.value
                ? Number(studentInput.value)
                : 0;

        if (remainingSeats <= 0) {
            remainingInfo.textContent =
                'Hiện không còn chỗ trống.';

            remainingInfo.setAttribute(
                'role',
                'alert'
            );

            return;
        }

        if (requested > remainingSeats) {
            remainingInfo.textContent =
                `Chỉ còn ${remainingSeats} chỗ trống.`;

            remainingInfo.setAttribute(
                'role',
                'alert'
            );

            return;
        }

        remainingInfo.textContent =
            `Còn ${remainingSeats} chỗ trống.`;

        remainingInfo.removeAttribute('role');
    }

    classSelect.addEventListener(
        'change',
        function () {
            loadRemainingSeats();
        }
    );

    scheduleSelect.addEventListener(
        'change',
        function () {
            remainingSeats = null;

            updateScheduleInfo();
            updateScheduleOptionLabel();
            loadRemainingSeats();
        }
    );

    studentInput.addEventListener(
        'input',
        updateRemainingMessage
    );

    form.addEventListener(
        'submit',
        function (event) {
            const requested =
                Number(
                    studentInput.value || 0
                );

            if (
                requested < 1 ||
                requested > 25
            ) {
                event.preventDefault();
                return;
            }

            if (
                remainingSeats !== null &&
                requested > remainingSeats
            ) {
                event.preventDefault();

                remainingInfo.textContent =
                    `Chỉ còn ${remainingSeats} chỗ trống.`;

                remainingInfo.setAttribute(
                    'role',
                    'alert'
                );
            }
        }
    );

    updateScheduleInfo();

    if (
        classSelect.value &&
        scheduleSelect.value
    ) {
        loadRemainingSeats();
    }
});
</script>
@endsection
