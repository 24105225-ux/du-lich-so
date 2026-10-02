<script setup>
import AppHeader from "./components/AppHeader.vue";
import AppFooter from "./components/AppFooter.vue";
</script>

<template>
    <a
        class="skip-link"
        href="#main-content"
    >
        Bỏ qua điều hướng
    </a>

    <AppHeader />

    <main
        id="main-content"
        tabindex="-1"
    >
        <RouterView />
    </main>

    <AppFooter />
</template>
