import { createApp } from "vue";
import App from "./App.vue";
import router from "./router";

import "./assets/themes/tokens.css";
import "./assets/layout.css";

createApp(App)
    .use(router)
    .mount("#app");