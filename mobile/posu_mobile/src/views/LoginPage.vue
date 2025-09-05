<template>
  <ion-page>
    <ion-content fullscreen class="login-page">
      <div class="login-wrapper">
        <ion-card class="login-card">
          <ion-card-header>
            <h1 class="title">Welcome Back</h1>
            <p class="subtitle">Sign in to your account</p>
          </ion-card-header>

          <ion-card-content>
            <form @submit.prevent="login">
              <ion-item fill="outline">
                <ion-input
                label="Email" 
                label-placement="floating"
                  type="text"
                  v-model="identifier"
                  required
                ></ion-input>
              </ion-item>

              <ion-item fill="outline">
                <ion-input
                label="Password" 
                label-placement="floating"
                  type="password"
                  v-model="password"
                  required
                ></ion-input>
              </ion-item>

              <ion-button
                expand="block"
                type="submit"
                :disabled="loading"
                class="ion-margin-top login-btn"
              >
                <ion-spinner v-if="loading" name="crescent"></ion-spinner>
                <span v-else>Login</span>
              </ion-button>

              <p v-if="error" class="error-msg ion-margin-top">
                {{ error }}
              </p>
            </form>
          </ion-card-content>
        </ion-card>
      </div>
    </ion-content>
  </ion-page>
</template>

<script>
import { ref } from "vue";
import { useRouter } from "vue-router";
import {
  IonPage,
  IonContent,
  IonCard,
  IonCardHeader,
  IonCardContent,
  IonItem,
  IonInput,
  IonButton,
  IonSpinner,
} from "@ionic/vue";
import { authAPI } from "@/services/api";

export default {
  name: "LoginPage",
  components: {
    IonPage,
    IonContent,
    IonCard,
    IonCardHeader,
    IonCardContent,
    IonItem,
    IonInput,
    IonButton,
    IonSpinner,
  },
  setup() {
    const router = useRouter();
    const identifier = ref("");
    const password = ref("");
    const loading = ref(false);
    const error = ref("");

    const login = async () => {
  if (!identifier.value || !password.value) return;
  loading.value = true;
  error.value = "";

  try {
    const response = await authAPI.login({
      identifier: identifier.value,
      password: password.value,
    });

    // 👇 Correct destructuring
    const { token, user } = response.data.data;

    if (token) {
      localStorage.setItem("auth_token", token);
      localStorage.setItem("user_data", JSON.stringify(user));
      router.push("/tabs/tab1");
    } else {
      error.value = "Invalid credentials";
    }
  } catch (err) {
    if (err.response?.data?.message) {
      error.value = err.response.data.message;
    } else {
      error.value = "Login failed. Try again.";
    }
  } finally {
    loading.value = false;
  }
};


    return { identifier, password, login, loading, error };
  },
};
</script>

<style scoped>
/* Background gradient */
.login-page {
  --background: none;
  background: linear-gradient(135deg, #1e3a8a, #3b82f6);
}

/* Center wrapper */
.login-wrapper {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100%;
  padding: 16px;
}

/* Card styling */
.login-card {
  width: 100%;
  max-width: 380px;
  border-radius: 20px;
  padding: 20px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
  background: #fff;
}

/* Typography */
.title {
  font-size: 28px;
  font-weight: 700;
  color: var(--ion-color-dark);
  margin-bottom: 8px;
  text-align: center;
}

.subtitle {
  font-size: 14px;
  color: var(--ion-color-medium);
  margin: 0;
  text-align: center;
}

/* Button */
.login-btn {
  --border-radius: 12px;
  font-weight: 600;
  letter-spacing: 0.5px;
}

/* Error */
.error-msg {
  color: var(--ion-color-danger);
  font-size: 14px;
  text-align: center;
}
</style>
