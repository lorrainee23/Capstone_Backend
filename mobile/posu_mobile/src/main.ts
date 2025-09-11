// src/main.ts
import { createApp } from "vue";
import App from "./App.vue";
import router from "./router";

import { IonicVue } from "@ionic/vue";
import { StatusBar, Style } from "@capacitor/status-bar";
// @ts-ignore
import { offlineQueue } from "./services/offlineQueue";
// @ts-ignore
import { enforcerAPI } from "./services/api";
// @ts-ignore
import { cacheService } from "./services/cacheService";


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

    // Check authentication on app start
    const token = localStorage.getItem("auth_token");
    if (token && window.location.pathname === "/login") {
        // User is logged in but on login page, redirect to tabs
        router.push("/tabs/tab1");
    } else if (!token && window.location.pathname.startsWith("/tabs/")) {
        // User not logged in but trying to access protected route, redirect to login
        router.push("/login");
    }

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

    const tryFlush = async () => {
        if (!navigator.onLine) return;
        await offlineQueue.flush(async (queued: any) => {
            if (queued.type === 'violation') {
                await enforcerAPI.recordViolation(queued.payload);
            }
        });
    };

    const refreshCacheOnStart = async () => {
        if (navigator.onLine) {
            try {
                const response = await enforcerAPI.getViolationTypes();
                cacheService.setViolationTypes(response.data.data || []);
                console.log("Cache refreshed on app start");
            } catch (err) {
                console.error("Failed to refresh cache on start:", err);
            }
        }
    };

    // Attempt flush and cache refresh on start and when going online
    tryFlush();
    refreshCacheOnStart();
    window.addEventListener('online', () => {
        tryFlush();
        refreshCacheOnStart();
    });
});
