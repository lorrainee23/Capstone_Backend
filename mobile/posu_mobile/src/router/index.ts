import { createRouter, createWebHistory } from '@ionic/vue-router';
import { RouteRecordRaw } from 'vue-router';
import TabsPage from '../views/TabsPage.vue'

const routes: Array<RouteRecordRaw> = [
    {
        path: "/",
        redirect: "/login",
    },
    {
        path: "/login",
        component: () => import("@/views/LoginPage.vue"),
    },
    {
        path: "/tabs/",
        component: TabsPage,
        meta: { requiresAuth: true },
        children: [
            {
                path: "",
                redirect: "/tabs/tab1",
            },

            {
                path: "tab1",
                name: "Tab1",
                component: () => import("@/views/Tab1Page.vue"),
            },
            {
                path: "tab2",
                name: "Tab2",
                component: () => import("@/views/Tab2Page.vue"),
            },
            {
                path: "tab3",
                name: "Tab3",
                component: () => import("@/views/Tab3Page.vue"),
            },
            {
                path: "tab4",
                name: "Tab4",
                component: () => import("@/views/Tab4Page.vue"),
            },
            {
                path: "tab5",
                name: "Tab5",
                component: () => import("@/views/Tab5Page.vue"),
            },
        ],
    },
];

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes
})

// Route guard to check authentication
router.beforeEach((to, from, next) => {
  const token = localStorage.getItem("auth_token");
  
  if (to.meta.requiresAuth) {
    if (token) {
      // User has token, allow access (even if offline)
      next();
    } else {
      // No token, redirect to login
      next("/login");
    }
  } else {
    // Public route, allow access
    next();
  }
});

export default router
