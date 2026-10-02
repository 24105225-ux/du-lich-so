<script setup>
defineProps({
    program: {
        type: Object,
        required: true,
    },
});

function formatVND(value) {
    return new Intl.NumberFormat(
        "vi-VN"
    ).format(value ?? 0);
}
</script>

<template>
    <article
        class="card the-sp"
    >
        <div class="card__body">
            <span class="badge">
                {{
                    program.education_level
                }}
            </span>

            <h2 class="card__title">
                {{ program.title }}
            </h2>

            <p class="muted">
                Đơn vị tổ chức:
                <strong>
                    {{
                        program.organizer
                            ?.name
                        || "Đang cập nhật"
                    }}
                </strong>
            </p>

            <p>
                Thời lượng:
                <strong>
                    {{
                        program.duration_days
                    }}
                    ngày
                </strong>
            </p>

            <p>
                Chi phí:
                <strong>
                    {{
                        formatVND(
                            program.price_per_student
                        )
                    }}
                    đ/học sinh
                </strong>
            </p>

            <p>
                Sức chứa:
                <strong>
                    {{ program.capacity }}
                    học sinh
                </strong>
            </p>

            <RouterLink
                class="button"
                :to="{
                    name: 'program-detail',
                    params: {
                        id: program.id,
                    },
                }"
            >
                Xem chi tiết
            </RouterLink>
        </div>
    </article>
</template>
