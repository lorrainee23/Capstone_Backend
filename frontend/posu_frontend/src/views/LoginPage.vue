<template>
  <div class="login-page">
    <div class="login-container">
      <div class="login-card">
        <div class="login-header">
          <div class="logo">
            <span class="logo-text">POSU Echague</span>
          </div>
          <h1>Welcome Back</h1>
          <p>Sign in to your account to continue</p>
        </div>

        <form @submit.prevent="handleLogin" class="login-form">
          <div v-if="error" class="alert alert-error">
            {{ error }}
          </div>

          <div class="form-group">
            <label for="identifier" class="form-label">Email or Mobile Number</label>
            <input
              id="identifier"
              v-model="form.identifier"
              type="text"
              class="form-input"
              placeholder="Enter your email or mobile number"
              required
            />
          </div>

          <div class="form-group">
            <div class="password-header">
              <label for="password" class="form-label">Password</label>
              <router-link to="/forgot-password" class="forgot-password">Forgot Password?</router-link>
            </div>
            <input
              id="password"
              v-model="form.password"
              type="password"
              class="form-input"
              placeholder="Enter your password"
              required
            />
          </div>

          <button type="submit" class="btn btn-primary btn-full" :disabled="loading">
            <span v-if="loading" class="spinner-small"></span>
            {{ loading ? 'Signing in...' : 'Sign In' }}
          </button>
        </form>

        <div class="login-footer">
          <p>Don't have an account? 
            <router-link to="/register" class="link">Sign up here</router-link>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

export default {
  name: 'LoginPage',
  setup() {
    const router = useRouter()
    const { login } = useAuthStore()

    const form = ref({
      identifier: '',
      password: ''
    })

    const error = ref('')
    const loading = ref(false)

    const handleLogin = async () => {
      try {
        loading.value = true
        error.value = ''

        const result = await login(form.value)

        if (result.success) {
        const user = result.user;

        if (!user.role && user.user_type) {
          user.role = user.user_type;
        }

        result.user.role = user.role;

        const roleRoutes = {
          Head: "/admin/dashboard",
          Deputy: "/admin/dashboard",
          Admin: "/admin/dashboard",
          Enforcer: "/enforcer/dashboard",
          Violator: "/violator/dashboard"
        }

        const redirectTo = roleRoutes[user.role] || "/";
        router.push(redirectTo);
      } else {
          error.value = result.message
        }
      } catch (err) {
        error.value = 'An unexpected error occurred. Please try again.'
        console.error('Login error:', err)
      } finally {
        loading.value = false
      }
    }

    return {
      form,
      error,
      loading,
      handleLogin
    }
  }
}
</script>


<style scoped>
.login-page {
  min-height: 100vh;
  background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  position: relative;
}

.login-page::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
  opacity: 0.1;
}

.login-container {
  width: 100%;
  max-width: 400px;
  position: relative;
  z-index: 1;
}

.login-card {
  background: white;
  border-radius: 16px;
  padding: 40px;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
  backdrop-filter: blur(10px);
}

.login-header {
  text-align: center;
  margin-bottom: 32px;
}

.form-group {
  margin-bottom: 1.5rem;
}

.password-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.5rem;
}

.forgot-password {
  font-size: 0.875rem;
  color: #3b82f6;
  text-decoration: none;
  font-weight: 500;
  transition: color 0.2s;
}

.forgot-password:hover {
  color: #2563eb;
  text-decoration: underline;
}

.logo {
  margin-bottom: 24px;
}

.logo-text {
  font-size: 28px;
  font-weight: 700;
  background: linear-gradient(135deg, #1e3a8a, #3b82f6);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.login-header h1 {
  font-size: 24px;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 8px;
}

.login-header p {
  color: #6b7280;
  font-size: 16px;
}

.login-form {
  margin-bottom: 24px;
}

.btn-full {
  width: 100%;
  justify-content: center;
  display: flex;
  align-items: center;
  gap: 8px;
}

.spinner-small {
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top: 2px solid white;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

.login-footer {
  text-align: center;
  padding-top: 24px;
  border-top: 1px solid #e5e7eb;
}

.login-footer p {
  color: #6b7280;
  margin: 0;
}

.link {
  color: #3b82f6;
  text-decoration: none;
  font-weight: 500;
}

.link:hover {
  color: #1e40af;
  text-decoration: underline;
}

@media (max-width: 480px) {
  .login-card {
    padding: 24px;
  }
  
  .login-header h1 {
    font-size: 20px;
  }
  
  .logo-text {
    font-size: 24px;
  }
}
</style>
