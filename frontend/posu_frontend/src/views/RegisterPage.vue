<template>
  <div class="register-page">
    <div class="register-container">
      <div class="register-card">
        <div class="register-header">
          <div class="logo">
            <span class="logo-text">POSU Echague</span>
          </div>
          <h1>Create Account</h1>
          <p>Register as a violator to access your violation records</p>
        </div>

        <form @submit.prevent="handleRegister" class="register-form">
          <div v-if="error" class="alert alert-error">
            {{ error }}
          </div>

          <div v-if="success" class="alert alert-success">
            {{ success }}
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="first_name" class="form-label">First Name</label>
              <input
                id="first_name"
                v-model="form.first_name"
                type="text"
                class="form-input"
                placeholder="Enter your first name"
                required
              />
            </div>
            <div class="form-group">
              <label for="last_name" class="form-label">Last Name</label>
              <input
                id="last_name"
                v-model="form.last_name"
                type="text"
                class="form-input"
                placeholder="Enter your last name"
                required
              />
            </div>
          </div>

          <div class="form-group">
            <label for="middle_name" class="form-label">Middle Name (Optional)</label>
            <input
              id="middle_name"
              v-model="form.middle_name"
              type="text"
              class="form-input"
              placeholder="Enter your middle name"
            />
          </div>

          <div class="form-group">
            <label for="license_number" class="form-label">License Number</label>
            <input
              id="license_number"
              v-model="form.license_number"
              type="text"
              class="form-input"
              placeholder="Enter your driver's license number"
              required
            />
          </div>

          <div class="form-group">
            <label for="email" class="form-label">Email Address</label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              class="form-input"
              placeholder="Enter your email address"
              required
            />
          </div>

          <div class="form-group">
            <label for="phone" class="form-label">Phone Number</label>
            <input
              id="phone"
              v-model="form.phone"
              type="tel"
              class="form-input"
              placeholder="Enter your phone number"
              required
            />
          </div>

          <div class="form-group">
            <label for="address" class="form-label">Address</label>
            <textarea
              id="address"
              v-model="form.address"
              class="form-input"
              rows="3"
              placeholder="Enter your complete address"
              required
            ></textarea>
          </div>

          <div class="form-group">
            <label for="password" class="form-label">Password</label>
            <input
              id="password"
              v-model="form.password"
              type="password"
              class="form-input"
              placeholder="Create a secure password"
              required
            />
          </div>

          <div class="form-group">
            <label for="password_confirmation" class="form-label">Confirm Password</label>
            <input
              id="password_confirmation"
              v-model="form.password_confirmation"
              type="password"
              class="form-input"
              placeholder="Confirm your password"
              required
            />
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
      first_name: '',
      middle_name: '',
      last_name: '',
      license_number: '',
      email: '',
      phone: '',
      address: '',
      password: '',
      password_confirmation: ''
    })
    
    const error = ref('')
    const success = ref('')
    const loading = ref(false)
    
    const handleRegister = async () => {
      try {
        loading.value = true
        error.value = ''
        success.value = ''
        
        // Validate password confirmation
        if (form.value.password !== form.value.password_confirmation) {
          error.value = 'Passwords do not match'
          return
        }
        
        const result = await register(form.value)
        
        if (result.success) {
          success.value = 'Account created successfully! You can now sign in.'
          // Reset form
          form.value = {
            first_name: '',
            middle_name: '',
            last_name: '',
            license_number: '',
            email: '',
            phone: '',
            address: '',
            password: '',
            password_confirmation: ''
          }
          
          // Redirect to login after 2 seconds
          setTimeout(() => {
            router.push('/login')
          }, 2000)
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
      error,
      success,
      loading,
      handleRegister
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
