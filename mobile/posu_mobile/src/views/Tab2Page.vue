<template>
  <ion-page>
    <ion-content class="login-page" fullscreen>
      <div class="login-container">
        <h1>Login</h1>
        <p>Enter your credentials to access your account</p>

        <form @submit.prevent="login">
          <ion-item>
            <ion-label position="floating">Email</ion-label>
            <ion-input type="email" v-model="email" required></ion-input>
          </ion-item>

          <ion-item>
            <ion-label position="floating">Password</ion-label>
            <ion-input type="password" v-model="password" required></ion-input>
          </ion-item>

          <div class="login-actions">
            <ion-button expand="block" type="submit" :disabled="loading">
              <span v-if="loading" class="spinner-small"></span>
              {{ loading ? "Logging in..." : "Login" }}
            </ion-button>
          </div>

          <p v-if="error" class="error-msg">{{ error }}</p>
        </form>
      </div>
    </ion-content>
  </ion-page>
</template>

<script>
import { ref } from "vue";
import { IonPage, IonContent, IonItem, IonLabel, IonInput, IonButton } from "@ionic/vue";

export default {
  name: "LoginPage",
  components: { IonPage, IonContent, IonItem, IonLabel, IonInput, IonButton },
  setup() {
    const email = ref("");
    const password = ref("");
    const loading = ref(false);
    const error = ref("");

    const login = async () => {
      if (!email.value || !password.value) return;
      loading.value = true;
      error.value = "";

      try {
        // Simulate API login
        await new Promise((resolve) => setTimeout(resolve, 1500));

        if (email.value === "test@example.com" && password.value === "123456") {
          alert("Login successful!");
          // redirect to dashboard/tab page
        } else {
          error.value = "Invalid email or password";
        }
      } catch (err) {
        error.value = "Login failed. Try again.";
      } finally {
        loading.value = false;
      }
    };

    return { email, password, login, loading, error };
  },
};
</script>

<style scoped>
.login-page {
  display: flex;
  justify-content: center;
  align-items: center;
  background: linear-gradient(135deg, #1e3a8a, #3b82f6);
  height: 100%;
  color: white;
}

.login-container {
  width: 100%;
  max-width: 400px;
  background: rgba(255, 255, 255, 0.05);
  padding: 32px;
  border-radius: 12px;
  backdrop-filter: blur(10px);
  text-align: center;
}

.login-container h1 {
  margin-bottom: 8px;
  font-size: 28px;
  font-weight: 700;
}

.login-container p {
  margin-bottom: 24px;
  font-size: 14px;
  color: #dbeafe;
}

.login-actions {
  margin-top: 24px;
}

.error-msg {
  color: #f87171;
  margin-top: 16px;
  font-size: 14px;
}

.spinner-small {
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top: 2px solid white;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-right: 8px;
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}
</style>
ws