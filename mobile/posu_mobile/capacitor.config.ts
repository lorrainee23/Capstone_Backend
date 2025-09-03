import type { CapacitorConfig } from "@capacitor/cli";

const config: CapacitorConfig = {
    appId: "io.ionic.starter",
    appName: "posu_mobile",
    webDir: "dist",
    plugins: {
        Keyboard: {
            resize: "none", 
            resizeOnFullScreen: true,
        },
    },
};

export default config;
