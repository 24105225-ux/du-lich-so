<script setup>
import { computed, reactive, ref, watch } from "vue";
import { useRoute } from "vue-router";
import LoadingState from "../components/LoadingState.vue";
import ProgramRecommendations from "../components/ProgramRecommendations.vue";
import { APP_CONFIG } from "../config";
import api from "../lib/api";
import heroImage from "../assets/hero.png";

const route = useRoute();
const program = ref(null);
const loading = ref(true);
const error = ref("");
const reviewEligible = ref(false);
const reviewSubmitted = ref(false);
const reviewMessage = ref("");
const reviewError = ref("");
const reviewForm = reactive({ rating: 5, comment: "" });
const programId = computed(() => route.params.id);

function formatVND(value) {
    return new Intl.NumberFormat("vi-VN").format(value ?? 0);
}

function formatDate(value) {
    if (!value) return "";
    return new Intl.DateTimeFormat("vi-VN").format(new Date(`${value}T00:00:00`));
}

async function loadReviewEligibility() {
    reviewEligible.value = false;
    reviewSubmitted.value = false;
    try {
        const response = await api.get(`/api/v1/programs/${programId.value}/review-eligibility`);
        reviewEligible.value = Boolean(response.data?.eligible);
        reviewSubmitted.value = Boolean(response.data?.reviewed);
    } catch {
        // Người dùng chưa đăng nhập hoặc không đủ quyền.
    }
}

async function loadProgram() {
    loading.value = true;
    error.value = "";
    program.value = null;
    try {
        const response = await api.get(`/api/v1/programs/${programId.value}`);
        program.value = response.data.data;
        await loadReviewEligibility();
    } catch (err) {
        error.value = err.response?.status === 404
            ? "Không tìm thấy chương trình."
            : "Không thể tải thông tin chương trình.";
    } finally {
        loading.value = false;
    }
}

async function submitReview() {
    reviewMessage.value = "";
    reviewError.value = "";
    try {
        await api.post(`/api/v1/programs/${programId.value}/reviews`, reviewForm);
        reviewSubmitted.value = true;
        reviewMessage.value = "Đánh giá đã được ghi nhận.";
    } catch (err) {
        reviewError.value = err.response?.data?.message || "Không thể gửi đánh giá.";
    }
}

watch(programId, loadProgram, { immediate: true });
</script>

<template>
    <section class="section">
        <div class="container">
            <LoadingState v-if="loading" />
            <div v-else-if="error" class="error-state" role="alert">{{ error }}</div>

            <template v-else-if="program">
                <div class="page-heading">
                    <span class="eyebrow">CHI TIẾT CHƯƠNG TRÌNH</span>
                    <h1>{{ program.title }}</h1>
                    <p class="lead">{{ program.description }}</p>
                </div>

                <div class="program-hero-media the-sp">
                    <img :src="heroImage" alt="Minh họa chương trình trải nghiệm giáo dục" />
                </div>

                <div class="detail-grid">
                    <div>
                        <div class="stats-grid">
                            <div class="stat-card"><span>Cấp học</span><strong>{{ program.education_level }}</strong></div>
                            <div class="stat-card"><span>Thời lượng</span><strong>{{ program.duration_days }} ngày</strong></div>
                            <div class="stat-card"><span>Sức chứa</span><strong>{{ program.capacity }} học sinh</strong></div>
                        </div>

                        <section>
                            <h2>Lịch trình mở đăng ký</h2>
                            <div class="schedule-list">
                                <article v-for="schedule in program.schedules" :key="schedule.id" class="schedule-card">
                                    <strong>{{ formatDate(schedule.trip_date) }}</strong>
                                    <p>{{ schedule.start_time }} – {{ schedule.end_time }}</p>
                                    <p>Sức chứa: <strong>{{ schedule.capacity }}</strong></p>

                                    <div v-if="schedule.stops?.length" class="timeline">
                                        <div v-for="stop in schedule.stops" :key="`${schedule.id}-${stop.sequence}`" class="timeline__item">
                                            <strong>Điểm {{ stop.sequence }}</strong>
                                            <p>{{ stop.place }}</p>
                                            <small class="muted">{{ stop.province }}</small>
                                            <p>{{ stop.activity }}</p>
                                            <small v-if="stop.duration_minutes" class="muted">{{ stop.duration_minutes }} phút</small>
                                        </div>
                                    </div>
                                </article>
                            </div>
                        </section>
                    </div>

                    <aside>
                        <div class="the-sp">
                            <h2>Thông tin đăng ký</h2>
                            <p>Chi phí dự kiến: <strong>{{ formatVND(program.price_per_student) }} đ/học sinh</strong></p>
                            <p>Đơn vị tổ chức: <strong>{{ program.organizer?.name || "Đang cập nhật" }}</strong></p>

                            <div class="cancellation-policy">
                                <strong>Chính sách hủy</strong>
                                <p>Việc hủy hoặc thay đổi đăng ký thực hiện theo thời hạn chốt danh sách của nhà trường và đơn vị tổ chức.</p>
                            </div>
                            <a class="button" :href="`${APP_CONFIG.backendUrl}/nha-truong/dang-ky-theo-lop`">Đăng ký theo lớp</a>
                        </div>

                        <section class="detail-extra">
                            <h2>Đánh giá</h2>
                            <p v-if="reviewSubmitted">Bạn đã đánh giá chương trình này.</p>
                            <form v-else-if="reviewEligible" @submit.prevent="submitReview">
                                <label>Số sao<select v-model.number="reviewForm.rating"><option v-for="n in 5" :key="n" :value="n">{{ n }}</option></select></label>
                                <label>Nhận xét<textarea v-model="reviewForm.comment" rows="4" maxlength="2000"></textarea></label>
                                <button type="submit" class="button">Gửi đánh giá</button>
                                <p v-if="reviewMessage" role="status">{{ reviewMessage }}</p>
                                <p v-if="reviewError" class="error-state" role="alert">{{ reviewError }}</p>
                            </form>
                            <p v-else>Chỉ người đã thực sự tham gia chuyến đi mới được đánh giá.</p>
                        </section>
                    </aside>
                </div>

                <ProgramRecommendations :program-id="Number(program.id)" />
            </template>
        </div>
    </section>
</template>

<style scoped>
.program-hero-media { overflow: hidden; padding: 0; }
.program-hero-media img { display: block; width: 100%; aspect-ratio: 16 / 6; object-fit: cover; }
.cancellation-policy { margin: 16px 0; padding: 12px 14px; border: 1px solid var(--mau-vien); border-radius: var(--bo-goc); background: var(--mau-nen-phu); }
.cancellation-policy p { margin: 6px 0 0; }
.detail-extra { margin-top: var(--kc-3); padding: var(--kc-3); border: 1px solid var(--mau-vien); border-radius: var(--bo-goc); background: var(--mau-nen-phu); }
.detail-extra label { display: grid; gap: 6px; margin-bottom: 12px; font-weight: 700; }
.detail-extra select, .detail-extra textarea { width: 100%; padding: 9px 10px; border: 1px solid var(--mau-vien); border-radius: var(--bo-goc); background: var(--mau-nen); color: var(--mau-chu); font: inherit; }
</style>
