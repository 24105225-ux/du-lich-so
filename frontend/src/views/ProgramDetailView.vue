<script setup>
import { computed, reactive, ref, watch } from "vue";

import {
    useRoute,
} from "vue-router";

import LoadingState from "../components/LoadingState.vue";
import api from "../lib/api";

const route = useRoute();

const program = ref(null);
const loading = ref(true);
const error = ref("");
const recommendations = ref([]);
const reviewEligible = ref(false);
const reviewSubmitted = ref(false);
const reviewMessage = ref("");
const reviewError = ref("");

const reviewForm = reactive({
    rating: 5,
    comment: "",
});

const programId = computed(
    () => route.params.id
);

function formatVND(value) {
    return new Intl.NumberFormat(
        "vi-VN"
    ).format(value ?? 0);
}

function formatDate(value) {
    if (!value) {
        return "";
    }

    return new Intl.DateTimeFormat(
        "vi-VN"
    ).format(
        new Date(
            `${value}T00:00:00`
        )
    );
}

async function loadProgram() {
    loading.value = true;
    error.value = "";
    program.value = null;

    try {
        const response =
            await api.get(
                `/api/v1/programs/${programId.value}`
            );

        program.value =
            response.data.data;
    } catch (err) {
        if (
            err.response?.status === 404
        ) {
            error.value =
                "Không tìm thấy chương trình.";
        } else {
            error.value =
                "Không thể tải thông tin chương trình.";
        }
    } finally {
        loading.value = false;
    }
}

watch(
    programId,
    loadProgram,
    {
        immediate: true,
    }
);

async function loadRecommendations() {
    recommendations.value = [];

    try {
        const response = await api.get(
            `/api/v1/programs/${programId.value}/recommendations`
        );

        recommendations.value =
            response.data.data ?? [];
    } catch {
        recommendations.value = [];
    }
}

async function loadReviewEligibility() {
    reviewEligible.value = false;
    reviewSubmitted.value = false;
    reviewMessage.value = "";
    reviewError.value = "";

    try {
        const response = await api.get(
            `/api/v1/programs/${programId.value}/review-eligibility`
        );

        reviewEligible.value =
            Boolean(response.data.eligible);

        reviewSubmitted.value =
            Boolean(response.data.reviewed);
    } catch {
        reviewEligible.value = false;
    }
}

async function submitReview() {
    reviewMessage.value = "";
    reviewError.value = "";

    try {
        await api.post(
            `/api/v1/programs/${programId.value}/reviews`,
            reviewForm
        );

        reviewSubmitted.value = true;
        reviewMessage.value =
            "Đánh giá đã được ghi nhận.";
    } catch (err) {
        reviewError.value =
            err.response?.data?.message ||
            "Không thể gửi đánh giá.";
    }
}
</script>

<template>
    <section class="section">
        <div class="container">
            <LoadingState
                v-if="loading"
            />

            <div
                v-else-if="error"
                class="error-state"
                role="alert"
            >
                {{ error }}
            </div>

            <template
                v-else-if="program"
            >
                <div class="page-heading">
                    <span class="eyebrow">
                        CHI TIẾT CHƯƠNG TRÌNH
                    </span>

                    <h1>
                        {{ program.title }}
                    </h1>

                    <p class="lead">
                        {{ program.description }}
                    </p>
                </div>

                <!-- <BỔ SUNG ẢNH> -->
                <div
                    class="the-sp"
                    style="margin-bottom: 24px;"
                >
                    Hình ảnh chương trình
                    trải nghiệm
                </div>

                <div class="detail-grid">
                    <div>
                        <div class="stats-grid">
                            <div
                                class="stat-card"
                            >
                                <span>
                                    Cấp học
                                </span>

                                <strong>
                                    {{
                                        program.education_level
                                    }}
                                </strong>
                            </div>

                            <div
                                class="stat-card"
                            >
                                <span>
                                    Thời lượng
                                </span>

                                <strong>
                                    {{
                                        program.duration_days
                                    }}
                                    ngày
                                </strong>
                            </div>

                            <div
                                class="stat-card"
                            >
                                <span>
                                    Sức chứa
                                </span>

                                <strong>
                                    {{
                                        program.capacity
                                    }}
                                    học sinh
                                </strong>
                            </div>
                        </div>

                        <section>
                            <h2>
                                Lịch trình mở đăng ký
                            </h2>

                            <div
                                class="schedule-list"
                            >
                                <article
                                    v-for="
                                        schedule
                                        in program.schedules
                                    "
                                    :key="
                                        schedule.id
                                    "
                                    class="schedule-card"
                                >
                                    <strong>
                                        {{
                                            formatDate(
                                                schedule.trip_date
                                            )
                                        }}
                                    </strong>

                                    <p>
                                        {{
                                            schedule.start_time
                                        }}
                                        –
                                        {{
                                            schedule.end_time
                                        }}
                                    </p>

                                    <p>
                                        Sức chứa:
                                        <strong>
                                            {{
                                                schedule.capacity
                                            }}
                                        </strong>
                                    </p>

                                    <div
                                        v-if="
                                            schedule.stops?.length
                                        "
                                        class="timeline"
                                    >
                                        <div
                                            v-for="
                                                stop in
                                                schedule.stops
                                            "
                                            :key="
                                                `${schedule.id}-${stop.sequence}`
                                            "
                                            class="timeline__item"
                                        >
                                            <strong>
                                                Ngày
                                                {{
                                                    stop.day
                                                }}
                                                · điểm
                                                {{
                                                    stop.sequence
                                                }}
                                            </strong>

                                            <p>
                                                {{
                                                    stop.place
                                                }}
                                            </p>

                                            <small
                                                class="muted"
                                            >
                                                {{
                                                    stop.province
                                                }}
                                            </small>

                                            <p
                                                v-if="
                                                    stop.note
                                                "
                                            >
                                                {{
                                                    stop.note
                                                }}
                                            </p>
                                        </div>
                                    </div>
                                </article>
                            </div>
                        </section>

                        <section
                            class="the-sp"
                            style="margin-top: 24px;"
                        >
                            <h2>
                                Hồ sơ an toàn
                            </h2>

                            <p>
                                Thông tin an toàn
                                của chuyến đi được
                                quản lý theo hồ sơ
                                an toàn của lịch trình
                                tại phía hệ thống.
                            </p>
                        </section>
                    </div>

                    <aside>
                        <div class="the-sp">
                            <h2>
                                Thông tin đăng ký
                            </h2>

                            <p>
                                Chi phí dự kiến:
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
                                Đơn vị tổ chức:
                                <strong>
                                    {{
                                        program.organizer?.name
                                        ||
                                        "Đang cập nhật"
                                    }}
                                </strong>
                            </p>

<div class="cancellation-policy">
    <strong>Chính sách hủy</strong>
    <p>
        Việc hủy hoặc thay đổi đăng ký thực hiện theo thời hạn
        chốt danh sách của nhà trường và đơn vị tổ chức.
    </p>
</div>
                            <a
                                class="button"
                                href="http://127.0.0.1:8000/nha-truong/dang-ky-theo-lop"
                            >
                                Đăng ký theo lớp
                            </a>
                        </div>
<div class="part-vii-extra">

    <section class="detail-extra">
        <h2>Đánh giá</h2>

        <p v-if="reviewSubmitted">
            Bạn đã đánh giá chương trình này.
        </p>

        <form
            v-else-if="reviewEligible"
            @submit.prevent="submitReview"
        >
            <label>
                Số sao
                <select v-model.number="reviewForm.rating">
                    <option :value="5">5</option>
                    <option :value="4">4</option>
                    <option :value="3">3</option>
                    <option :value="2">2</option>
                    <option :value="1">1</option>
                </select>
            </label>

            <label>
                Nhận xét
                <textarea
                    v-model="reviewForm.comment"
                    rows="4"
                    maxlength="2000"
                ></textarea>
            </label>

            <button type="submit" class="button">
                Gửi đánh giá
            </button>
        </form>

        <p v-else>
            Chỉ người đã thực sự tham gia chuyến đi mới được đánh giá.
        </p>
    </section>

    <section
        v-if="recommendations.length"
        class="detail-extra"
    >
        <h2>Gợi ý chương trình tương tự</h2>

        <div class="recommendation-list">
            <RouterLink
                v-for="item in recommendations"
                :key="item.id"
                class="recommendation-card"
                :to="{
                    name: 'program-detail',
                    params: { id: item.id }
                }"
            >
                <strong>{{ item.title }}</strong>
                <span>{{ item.education_level }}</span>
            </RouterLink>
        </div>
    </section>

</div>
                    </aside>
                </div>
            </template>
        </div>
    </section>
</template>

<style scoped>
.cancellation-policy {
    margin: 16px 0;
    padding: 12px 14px;
    border: 1px solid var(--mau-vien);
    border-radius: var(--bo-goc);
    background: var(--mau-nen-phu);
}

.cancellation-policy p {
    margin: 6px 0 0;
}

.part-vii-extra {
    display: grid;
    gap: var(--kc-3);
    margin-top: var(--kc-3);
}

.detail-extra {
    padding: var(--kc-3);
    border: 1px solid var(--mau-vien);
    border-radius: var(--bo-goc);
    background: var(--mau-nen-phu);
}

.detail-extra label {
    display: grid;
    gap: 6px;
    margin-bottom: 12px;
    font-weight: 700;
}

.detail-extra select,
.detail-extra textarea {
    width: 100%;
    padding: 9px 10px;
    border: 1px solid var(--mau-vien);
    border-radius: var(--bo-goc);
    background: var(--mau-nen);
    color: var(--mau-chu);
    font: inherit;
}

.recommendation-list {
    display: grid;
    gap: 10px;
}

.recommendation-card {
    display: grid;
    gap: 4px;
    padding: 12px;
    border: 1px solid var(--mau-vien);
    border-radius: var(--bo-goc);
    background: var(--mau-nen);
    text-decoration: none;
}

.recommendation-card span {
    color: var(--mau-chu-nhat);
}
</style>