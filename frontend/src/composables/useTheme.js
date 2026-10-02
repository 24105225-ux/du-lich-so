import {
    ref,
    watchEffect,
} from "vue";

const KEY =
    "cse703073-theme";

function matchDefault() {
    return window.matchMedia(
        "(prefers-color-scheme: dark)"
    ).matches
        ? "toi"
        : "sang";
}

const theme = ref(
    localStorage.getItem(KEY)
    ?? matchDefault()
);

function saveCookie(value) {
    document.cookie =
        `theme=${encodeURIComponent(value)}; ` +
        `Path=/; ` +
        `Max-Age=31536000; ` +
        `SameSite=Lax`;
}

export function useTheme() {
    watchEffect(() => {
        const value =
            theme.value === "toi"
                ? "toi"
                : "sang";

        document.documentElement.dataset.theme =
            value;

        localStorage.setItem(
            KEY,
            value
        );

        saveCookie(value);
    });

    const toggle = () => {
        theme.value =
            theme.value === "sang"
                ? "toi"
                : "sang";
    };

    return {
        theme,
        toggle,
    };
}
