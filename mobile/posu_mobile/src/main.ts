// src/main.ts
import { createApp } from "vue";
import App from "./App.vue";
import router from "./router";

import { IonicVue } from "@ionic/vue";
import { StatusBar, Style } from "@capacitor/status-bar";


/* Core CSS required for Ionic components to work properly */
import "@ionic/vue/css/core.css";

/* Basic CSS for apps built with Ionic */
import "@ionic/vue/css/normalize.css";
import "@ionic/vue/css/structure.css";
import "@ionic/vue/css/typography.css";

/* Optional CSS utils */
import "@ionic/vue/css/padding.css";
import "@ionic/vue/css/float-elements.css";
import "@ionic/vue/css/text-alignment.css";
import "@ionic/vue/css/text-transformation.css";
import "@ionic/vue/css/flex-utils.css";
import "@ionic/vue/css/display.css";

/* Theme variables */
import "./theme/variables.css";

const app = createApp(App).use(IonicVue).use(router);

router.isReady().then(() => {
    app.mount("#app");

    // --- StatusBar / Fullscreen setup ---
    try {
        // Make webview overlay the status bar
        StatusBar.setOverlaysWebView({ overlay: true });

        // Optional: Dark text/icons on status bar
        StatusBar.setStyle({ style: Style.Dark });

        // Hide status bar entirely
        StatusBar.hide();
    } catch (e) {
        console.warn("StatusBar plugin not available or failed:", e);
    }
});
