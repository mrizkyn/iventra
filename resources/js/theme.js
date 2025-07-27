// resources/js/theme.js
import { ref, onMounted } from "vue";

const theme = ref("light");

const loadTheme = () => {
    if (
        localStorage.theme === "dark" ||
        (!("theme" in localStorage) &&
            window.matchMedia("(prefers-color-scheme: dark)").matches)
    ) {
        theme.value = "dark";
        document.documentElement.classList.add("dark");
    } else {
        theme.value = "light";
        document.documentElement.classList.remove("dark");
    }
};

const toggle = () => {
    theme.value = theme.value === "dark" ? "light" : "dark";
    localStorage.theme = theme.value;

    if (theme.value === "dark") {
        document.documentElement.classList.add("dark");
    } else {
        document.documentElement.classList.remove("dark");
    }
};

onMounted(() => {
    loadTheme();
});

export { theme, toggle };
