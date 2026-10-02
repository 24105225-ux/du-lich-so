<script setup>
import {
    onMounted,
    ref,
} from "vue";

import ProgramCard from "../components/ProgramCard.vue";
import LoadingState from "../components/LoadingState.vue";
import api from "../lib/api";

const programs = ref([]);
const loading = ref(true);
const error = ref("");

async function loadPrograms() {
    loading.value = true;
    error.value = "";

    try {
        const response =
            await api.get(
                "/api/v1/programs",
                {
                    params: {
                        per_page: 6,
                    },
                }
            );

        programs.value =
            response.data.data;
    } catch (err) {
        error.value =
            "Không thể tải chương trình.";
    } finally {
        loading.value = false;
    }
}

onMounted(loadPrograms);
</script>

<template>
    <section class="hero">
        <div class="container hero__grid">
            <div>
                <span class="eyebrow">
                    ĐT-17 · CSE703073
                </span>

                <h1>
                    Nền tảng du lịch học đường
                    và chương trình trải nghiệm giáo dục
                </h1>

                <p class="lead">
                    Kết nối nhà trường,
                    phụ huynh và đơn vị tổ chức
                    trong quá trình lựa chọn,
                    đăng ký và quản lý
                    chương trình trải nghiệm.
                </p>

                <div class="form-actions">
                    <RouterLink
                        class="button"
                        :to="{
                            name: 'program-list',
                        }"
                    >
                        Xem chương trình
                    </RouterLink>

                    <RouterLink
                        class="button button--secondary"
                        :to="{
                            name: 'registration',
                        }"
                    >
                        Đăng ký theo lớp
                    </RouterLink>
                </div>
            </div>

            <!-- <BỔ SUNG ẢNH> -->
            <div class="the-sp hero-visual">
                <div
                    class="hero-visual__placeholder"
                >
                    Hình ảnh trải nghiệm
                    giáo dục
                </div>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="page-heading">
                <span class="eyebrow">
                    CHƯƠNG TRÌNH
                </span>

                <h2>
                    Chương trình trải nghiệm
                    nổi bật
                </h2>
            </div>

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

            <div
                v-else-if="
                    programs.length > 0
                "
                class="card-grid"
            >
                <ProgramCard
                    v-for="program in programs"
                    :key="program.id"
                    :program="program"
                />
            </div>

            <div
                v-else
                class="empty-state"
            >
                Chưa có chương trình
                được công khai.
            </div>
        </div>
    </section>
</template>

<style scoped>
.eyebrow {
    color: var(--mau-chinh);
    font-weight: 800;
    letter-spacing: 0.08em;
}

.hero-visual {
    min-height: 320px;

    display: grid;
    place-items: center;

    overflow: hidden;
}

.hero-visual__placeholder {
    color:
        var(--mau-chu-nhat);

    text-align: center;
}
</style>
