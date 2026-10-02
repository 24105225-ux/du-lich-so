import {
    createRouter,
    createWebHistory,
} from "vue-router";

const routes = [
    {
        path: "/",
        name: "home",
        component: () =>
            import("../views/HomeView.vue"),
    },

    {
        path: "/chuong-trinh",
        name: "program-list",
        component: () =>
            import("../views/ProgramListView.vue"),
    },

    {
        path: "/chuong-trinh/:id",
        name: "program-detail",
        component: () =>
            import("../views/ProgramDetailView.vue"),
        props: true,
    },

    {
        path: "/dang-ky",
        name: "registration",
        component: () =>
            import("../views/RegistrationView.vue"),
        meta: {
            requiresAuth: true,
        },
    },

    {
        path: "/:pathMatch(.*)*",
        name: "not-found",
        component: () =>
            import("../views/NotFoundView.vue"),
    },
];

const router = createRouter({
    history: createWebHistory(),

    routes,

    scrollBehavior() {
        return {
            top: 0,
        };
    },
});

router.beforeEach(
    async (to) => {
        if (!to.meta.requiresAuth) {
            return true;
        }

        try {
            const response =
                await fetch(
                    "/api/v1/me",
                    {
                        credentials: "same-origin",
                        headers: {
                            Accept:
                                "application/json",
                        },
                    }
                );

            if (response.ok) {
                return true;
            }
        } catch {
            // Người dùng chưa đăng nhập.
        }

        return {
            name: "home",
            query: {
                canh_bao:
                    "can_dang_nhap",
            },
        };
    }
);

export default router;
