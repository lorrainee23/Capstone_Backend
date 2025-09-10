<template>
  <ion-page>
    <ion-content fullscreen class="login-page">
      <div class="login-wrapper">
        <ion-card class="login-card">
          <ion-card-header>
            <div class="logo-container">
              <div class="logo-circle">
                <ion-icon :icon="shieldCheckmarkOutline" class="logo-icon"></ion-icon>
              </div>
            </div>
            <h1 class="title">Traffic Enforcer</h1>
            <p class="subtitle">Sign in to continue</p>
          </ion-card-header>

          <ion-card-content>
            <form @submit.prevent="login">
              <ion-item fill="outline" class="input-item">
                <ion-input
                  label="Email" 
                  label-placement="floating"
                  type="text"
                  v-model="identifier"
                  required
                ></ion-input>
              </ion-item>

              <ion-item fill="outline" class="input-item">
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
                class="login-btn"
              >
                <ion-spinner v-if="loading" name="crescent"></ion-spinner>
                <span v-else>Sign In</span>
              </ion-button>

              <p v-if="error" class="error-msg">
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
import { ref, nextTick } from "vue";
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
  IonIcon,
} from "@ionic/vue";
import { shieldCheckmarkOutline } from 'ionicons/icons';
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
    IonIcon,
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

        const token = response.data.token 
          || response.data.access_token 
          || response.data.data?.token;

        const user = response.data.user 
          || response.data.data?.user;
        
        if (token) {
          localStorage.setItem("auth_token", token);
          localStorage.setItem("user_data", JSON.stringify(user));
          await nextTick();
          router.push({name: 'Tab1'});
        } else {
          error.value = "Invalid credentials";
        }
      } catch (err) {
        console.error("Login error:", err.response?.data || err.message);
        if (err.response?.data?.message) {
          error.value = err.response.data.message;
        } else {
          error.value = "Login failed. Try again.";
        }
      } finally {
        loading.value = false;
      }
    };

    return { identifier, password, login, loading, error, shieldCheckmarkOutline };
  },
};
</script>

<style scoped>
/* Background */
.login-page {
  --background: linear-gradient(135deg, #1e40af 0%, #3b82f6 50%, #60a5fa 100%);
}

/* Center wrapper */
.login-wrapper {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100%;
  padding: 20px;
}

/* Card styling */
.login-card {
  width: 100%;
  max-width: 400px;
  border-radius: 24px;
  padding: 32px 24px;
  box-shadow: 0 20px 60px rgba(30, 64, 175, 0.15);
  background: #ffffff;
  border: none;
}

/* Logo */
.logo-container {
  display: flex;
  justify-content: center;
  margin-bottom: 24px;
}

.logo-circle {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: linear-gradient(135deg, #1e40af, #3b82f6);
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 8px 32px rgba(30, 64, 175, 0.3);
}

.logo-icon {
  font-size: 40px;
  color: white;
}

/* Typography */
.title {
  font-size: 32px;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 8px 0;
  text-align: center;
}

.subtitle {
  font-size: 16px;
  color: #64748b;
  margin: 0 0 32px 0;
  text-align: center;
}

/* Input styling */
.input-item {
  margin-bottom: 20px;
  --border-radius: 16px;
  --border-width: 2px;
  --border-color: #e2e8f0;
  --highlight-color-focused: #3b82f6;
  --color: #1e293b;
}

.input-item:hover {
  --border-color: #3b82f6;
}

/* Button */
.login-btn {
  --border-radius: 16px;
  --background: linear-gradient(135deg, #1e40af, #3b82f6);
  --background-activated: linear-gradient(135deg, #1d4ed8, #2563eb);
  --color: white;
  height: 56px;
  margin: 24px 0 16px 0;
  font-weight: 600;
  font-size: 16px;
  letter-spacing: 0.5px;
  box-shadow: 0 4px 16px rgba(59, 130, 246, 0.3);
  transition: all 0.3s ease;
}

.login-btn:hover:not([disabled]) {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(59, 130, 246, 0.4);
}

.login-btn[disabled] {
  opacity: 0.7;
  --background: #94a3b8;
}

.login-btn ion-spinner {
  --color: white;
  margin-right: 12px;
}

/* Error */
.error-msg {
  color: #dc2626;
  font-size: 14px;
  text-align: center;
  margin: 8px 0 0 0;
  padding: 12px;
  background: #fee2e2;
  border-radius: 12px;
  border-left: 4px solid #dc2626;
}

/* Responsive */
@media (max-width: 480px) {
  .login-wrapper {
    padding: 16px;
  }
  
  .login-card {
    padding: 24px 20px;
    border-radius: 20px;
  }
  
  .title {
    font-size: 28px;
  }
  
  .logo-circle {
    width: 70px;
    height: 70px;
  }
  
  .logo-icon {
    font-size: 35px;
  }
}
</style>