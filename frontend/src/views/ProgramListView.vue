<script setup>
import {
    computed,
    onMounted,
    reactive,
    ref,
    watch,
} from "vue";

import {
    useRoute,
    useRouter,
} from "vue-router";

import ProgramCard from "../components/ProgramCard.vue";
import LoadingState from "../components/LoadingState.vue";
import api from "../lib/api";

const route = useRoute();
const router = useRouter();

const programs = ref([]);
const loading = ref(false);
const error = ref("");

const meta = reactive({
    current_page: 1,
    last_page: 1,
    total: 0,
    per_page: 9,
});

const filters = reactive({
    keyword: "",
    education_level: "",
    min_price: "",
    max_price: "",
    sort: "newest",
});

function syncFiltersFromRoute() {
    filters.keyword =
        String(
            route.query.keyword ?? ""
        );

    filters.education_level =
        String(
            route.query.education_level ?? ""
        );

    filters.min_price =
        String(
            route.query.min_price ?? ""
        );

    filters.max_price =
        String(
            route.query.max_price ?? ""
        );

    filters.sort =
        String(
            route.query.sort ?? "newest"
        );
}

function buildQuery(page = 1) {
    const query = {
        page,
    };

    if (filters.keyword) {
        query.keyword =
            filters.keyword;
    }

    if (filters.education_level) {
        query.education_level =
            filters.education_level;
    }

    if (filters.min_price !== "") {
        query.min_price =
            filters.min_price;
    }

    if (filters.max_price !== "") {
        query.max_price =
            filters.max_price;
    }

    if (filters.sort) {
        query.sort =
            filters.sort;
    }

    return query;
}

async function loadPrograms() {
    loading.value = true;
    error.value = "";

    try {
        const page = Number(
            route.query.page ?? 1
        );

        const response =
            await api.get(
                "/api/v1/programs",
                {
                    params: {
                        ...buildQuery(page),
                        per_page: 9,
                    },
                }
            );

        programs.value =
            response.data.data;

        Object.assign(
            meta,
            response.data.meta
        );
    } catch (err) {
        error.value =
            "Không thể tải danh sách chương trình.";
    } finally {
        loading.value = false;
    }
}

function submitFilter() {
    router.push({
        name: "program-list",
        query: buildQuery(1),
    });
}

function clearFilter() {
    router.push({
        name: "program-list",
    });
}

function goToPage(page) {
    if (
        page < 1 ||
        page > meta.last_page
    ) {
        return;
    }

    router.push({
        name: "program-list",
        query: buildQuery(page),
    });
}

const pageNumbers = computed(() => {
    const result = [];

    for (
        let page = 1;
        page <= meta.last_page;
        page++
    ) {
        result.push(page);
    }

    return result;
});

watch(
    () => route.query,
    () => {
        syncFiltersFromRoute();
        loadPrograms();
    },
    {
        immediate: true,
    }
);

onMounted(
    syncFiltersFromRoute
);
</script>

<template>
    <section class="section">
        <div class="container">
            <div class="page-heading">
                <span class="eyebrow">
                    DANH MỤC
                </span>

                <h1>
                    Chương trình trải nghiệm
                    giáo dục
                </h1>

                <p class="lead">
                    Tìm kiếm chương trình theo
                    cấp học, mức giá và từ khóa.
                </p>
            </div>

            <div class="trang-danh-sach">
                <aside
                    class="bo-loc"
                    aria-label="Bộ lọc chương trình"
                >
                    <form
                        class="the-sp filter-form"
                        @submit.prevent="
                            submitFilter
                        "
                    >
                        <div class="form-group">
                            <label for="keyword">
                                Từ khóa
                            </label>

                            <input
                                id="keyword"
                                v-model="
                                    filters.keyword
                                "
                                type="search"
                                maxlength="120"
                                placeholder="Ví dụ: lịch sử, sinh thái"
                            />
                        </div>

                        <div class="form-group">
                            <label for="education_level">
                                Cấp học
                            </label>

                            <select
                                id="education_level"
                                v-model="
                                    filters.education_level
                                "
                            >
                                <option value="">
                                    Tất cả
                                </option>

                                <option value="Tieu hoc">
                                    Tiểu học
                                </option>

                                <option value="THCS">
                                    THCS
                                </option>

                                <option value="THPT">
                                    THPT
                                </option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="min_price">
                                Giá từ
                            </label>

                            <input
                                id="min_price"
                                v-model="
                                    filters.min_price
                                "
                                type="number"
                                min="0"
                            />
                        </div>

                        <div class="form-group">
                            <label for="max_price">
                                Giá đến
                            </label>

                            <input
                                id="max_price"
                                v-model="
                                    filters.max_price
                                "
                                type="number"
                                min="0"
                            />
                        </div>

                        <div class="form-group">
                            <label for="sort">
                                Sắp xếp
                            </label>

                            <select
                                id="sort"
                                v-model="
                                    filters.sort
                                "
                            >
                                <option value="newest">
                                    Mới nhất
                                </option>

                                <option value="price_asc">
                                    Giá tăng dần
                                </option>

                                <option value="price_desc">
                                    Giá giảm dần
                                </option>
                            </select>
                        </div>

                        <div class="form-actions">
                            <button
                                class="button"
                                type="submit"
                            >
                                Lọc
                            </button>

                            <button
                                class="button button--secondary"
                                type="button"
                                @click="clearFilter"
                            >
                                Xóa lọc
                            </button>
                        </div>
                    </form>
                </aside>

                <div>
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
                            programs.length
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
                        Không tìm thấy
                        chương trình phù hợp.
                    </div>

                    <nav
                        v-if="
                            !loading &&
                            meta.last_page > 1
                        "
                        class="pagination"
                        aria-label="Phân trang chương trình"
                    >
                        <button
                            type="button"
                            :disabled="
                                meta.current_page === 1
                            "
                            @click="
                                goToPage(
                                    meta.current_page - 1
                                )
                            "
                        >
                            Trước
                        </button>

                        <button
                            v-for="page in pageNumbers"
                            :key="page"
                            type="button"
                            :class="{
                                'is-active':
                                    page ===
                                    meta.current_page,
                            }"
                            :aria-current="
                                page ===
                                meta.current_page
                                    ? 'page'
                                    : undefined
                            "
                            @click="
                                goToPage(page)
                            "
                        >
                            {{ page }}
                        </button>

                        <button
                            type="button"
                            :disabled="
                                meta.current_page ===
                                meta.last_page
                            "
                            @click="
                                goToPage(
                                    meta.current_page + 1
                                )
                            "
                        >
                            Sau
                        </button>
                    </nav>
                </div>
            </div>
        </div>
    </section>
</template>