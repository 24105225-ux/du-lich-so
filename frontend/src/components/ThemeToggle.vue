<script setup>
import { useTheme } from "../composables/useTheme";

const {
    theme,
    toggle,
} = useTheme();
</script>

<template>
    <button
        class="theme-toggle"
        type="button"
        :aria-pressed="
            theme === 'toi'
        "
        :title="
            theme === 'toi'
                ? 'Chuyển sang chủ đề sáng'
                : 'Chuyển sang chủ đề tối'
        "
        @click="toggle"
    >
        <span
            aria-hidden="true"
        >
            {{
                theme === "toi"
                    ? "☾"
                    : "☀"
            }}
        </span>

        <span class="sr-only">
            Đổi chủ đề giao diện
        </span>
    </button>
</template>

<style scoped>
.theme-toggle {
    min-width: 42px;
    min-height: 42px;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    border:
        1px solid
        var(--mau-vien);

    border-radius: 50%;

    background: var(--mau-nen-phu);
    color: var(--mau-chu);

    font-size: 1.1rem;
    cursor: pointer;
}

.theme-toggle:hover {
    border-color: var(--mau-chinh);
}
</style>
