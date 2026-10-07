<script setup>
import ThemeToggle from "./ThemeToggle.vue";
import { APP_CONFIG } from "../config";
</script>

<template>
    <header class="site-header">
        <div class="container nav-wrap">
            <RouterLink
                class="brand"
                :to="{ name: 'home' }"
                aria-label="Du lịch học đường - Trang chủ"
            >
                <span class="brand__title">
                    Du lịch học đường
                </span>

                <span class="brand__subtitle">
                    CSE703073 · ĐT-17
                </span>
            </RouterLink>

            <nav
                class="main-nav"
                aria-label="Điều hướng chính"
            >
                <RouterLink
                    :to="{ name: 'home' }"
                >
                    Trang chủ
                </RouterLink>

                <RouterLink
                    :to="{ name: 'program-list' }"
                >
                    Chương trình
                </RouterLink>

                <a
                    :href="`${APP_CONFIG.backendUrl}/nha-truong/dang-ky-theo-lop`"
                >
                    Đăng ký theo lớp
                </a>
            </nav>

            <div class="header-actions">
                <ThemeToggle />

                <a
                    class="button button--secondary"
                    :href="`${APP_CONFIG.backendUrl}/dang-nhap`"
                >
                    Đăng nhập
                </a>
            </div>
        </div>
    </header>
</template>
