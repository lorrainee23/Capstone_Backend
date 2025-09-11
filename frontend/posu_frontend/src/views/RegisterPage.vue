<template>
  <div class="register-page">
    <div class="register-container">
      <div class="register-card">
        <div class="register-header">
          <div class="logo">
            <span class="logo-text">POSU Echague</span>
          </div>
          <h1>Create Account</h1>
          <p>Register using your Email or Mobile Number</p>
        </div>

        <form @submit.prevent="handleRegister" class="register-form">
          <div v-if="error" class="alert alert-error">
            {{ error }}
          </div>

          <div v-if="success" class="alert alert-success">
            {{ success }}
          </div>

          <!-- Identifier (Email or Mobile) -->
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

          <!-- Password -->
          <div class="form-group">
            <label for="password" class="form-label">Password</label>
            <div class="input-wrapper">
              <input
                id="password"
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                class="form-input"
                placeholder="Create a secure password"
                required
                @input="validatePassword"
              />
              <span class="toggle-eye" @click="showPassword = !showPassword">
                {{ showPassword ? '🙈' : '👁️' }}
              </span>
            </div>

            <!-- Strength bar -->
            <div v-if="form.password.length > 0">
              <div class="password-strength" :class="passwordStrength">
                <div class="strength-bar"></div>
              </div>
              <div class="strength-text" :class="passwordStrength">
                {{ passwordStrength === 'weak' ? 'Weak' : passwordStrength === 'medium' ? 'Medium' : 'Strong' }}
              </div>
            </div>
          </div>

          <!-- Confirm Password -->
          <div class="form-group">
            <label for="password_confirmation" class="form-label">Confirm Password</label>
            <div class="input-wrapper">
              <input
                id="password_confirmation"
                v-model="form.password_confirmation"
                :type="showConfirmPassword ? 'text' : 'password'"
                class="form-input"
                placeholder="Confirm your password"
                required
              />
              <span class="toggle-eye" @click="showConfirmPassword = !showConfirmPassword">
                {{ showConfirmPassword ? '🙈' : '👁️' }}
              </span>
            </div>
          </div>

          <button type="submit" class="btn btn-primary btn-full" :disabled="loading">
            <span v-if="loading" class="spinner-small"></span>
            {{ loading ? 'Creating Account...' : 'Create Account' }}
          </button>
        </form>

        <div class="register-footer">
          <p>Already have an account? 
            <router-link to="/login" class="link">Sign in here</router-link>
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
  name: 'RegisterPage',
  setup() {
    const router = useRouter()
    const { register } = useAuthStore()

    const form = ref({
      identifier: '',
      password: '',
      password_confirmation: ''
    })

    const passwordStrength = ref('')
    const error = ref('')
    const success = ref('')
    const loading = ref(false)
    const showPassword = ref(false)
    const showConfirmPassword = ref(false)

    const validatePassword = () => {
      if (form.value.password.length < 8) {
        passwordStrength.value = 'weak'
        return
      }

      const hasUpperCase = /[A-Z]/.test(form.value.password)
      const hasNumber = /\d/.test(form.value.password)

      let strength = 0
      if (hasUpperCase) strength++
      if (hasNumber) strength++
      if (form.value.password.length >= 12) strength++

      if (strength === 0) {
        passwordStrength.value = 'weak'
      } else if (strength === 1) {
        passwordStrength.value = 'medium'
      } else {
        passwordStrength.value = 'strong'
      }
    }

    const handleRegister = async () => {
      try {
        loading.value = true
        error.value = ''
        success.value = ''

        // Enforce minimum length rule
        if (form.value.password.length < 8) {
          error.value = 'Password must be at least 8 characters long'
          return
        }

        if (form.value.password !== form.value.password_confirmation) {
          error.value = 'Passwords do not match'
          return
        }

        const result = await register(form.value)

        if (result.success) {
          success.value = 'Account created successfully! You can now sign in.'
          form.value = { identifier: '', password: '', password_confirmation: '' }
          setTimeout(() => router.push('/login'), 2000)
        } else {
          error.value = result.message
        }
      } catch (err) {
        error.value = 'An unexpected error occurred. Please try again.'
        console.error('Registration error:', err)
      } finally {
        loading.value = false
      }
    }

    return {
      form,
      passwordStrength,
      error,
      success,
      loading,
      handleRegister,
      validatePassword,
      showPassword,
      showConfirmPassword
    }
  }
}
</script>

<style scoped>
.register-page {
  min-height: 100vh;
  background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  position: relative;
}

.register-page::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
  opacity: 0.1;
}

.register-container {
  width: 100%;
  max-width: 500px;
  position: relative;
  z-index: 1;
}

.register-card {
  background: white;
  border-radius: 16px;
  padding: 40px;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
  backdrop-filter: blur(10px);
}

.register-header {
  text-align: center;
  margin-bottom: 32px;
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

.register-header h1 {
  font-size: 24px;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 8px;
}

.register-header p {
  color: #6b7280;
  font-size: 16px;
}

.register-form {
  margin-bottom: 24px;
}
.password-strength {
  margin-top: 10px;
  height: 6px;
  border-radius: 3px;
  overflow: hidden;
  background-color: #e5e7eb;
}

.strength-bar {
  height: 100%;
  width: 0;
  transition: width 0.3s, background-color 0.3s;
}

.strength-text {
  margin-top: 6px;
  font-size: 0.75rem;
  font-weight: 500;
  text-align: right;
}

.weak .strength-bar { width: 33%; background-color: #ef4444; }
.weak.strength-text { color: #ef4444; }

.medium .strength-bar { width: 66%; background-color: #f59e0b; }
.medium.strength-text { color: #f59e0b; }

.strong .strength-bar { width: 100%; background-color: #10b981; }
.strong.strength-text { color: #10b981; }
.input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.input-wrapper .form-input {
  width: 100%;
  padding-right: 40px; 
}

.toggle-eye {
  position: absolute;
  right: 12px;
  cursor: pointer;
  user-select: none;
  font-size: 1.1rem;
  color: #6b7280;
  transition: color 0.2s;
}

.toggle-eye:hover {
  color: #111827;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
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

.register-footer {
  text-align: center;
  padding-top: 24px;
  border-top: 1px solid #e5e7eb;
}

.register-footer p {
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

@media (max-width: 600px) {
  .register-card {
    padding: 24px;
  }
  
  .form-row {
    grid-template-columns: 1fr;
  }
  
  .register-header h1 {
    font-size: 20px;
  }
  
  .logo-text {
    font-size: 24px;
  }
}
</style>