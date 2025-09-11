<template>
  <div class="reset-password-page">
    <div class="reset-password-container">
      <div class="logo-container">
        <img src="@/assets/posu_logo.png" alt="POSU Logo" class="logo" />
        <h1>POSU Traffic Violation System</h1>
      </div>
      
      <div class="reset-password-form">
        <h2>Create New Password</h2>
        <p>Please create a new password for your account.</p>
        
        <form @submit.prevent="handleSubmit" v-if="!passwordReset">
          <div class="form-group">
            <label for="password">New Password</label>
            <div class="password-input">
              <input
                :type="showPassword ? 'text' : 'password'"
                id="password"
                v-model="password"
                required
                placeholder="Enter your new password"
                :class="{ 'error': errors.password }"
                @input="validatePassword"
              />
              <button type="button" class="toggle-password" @click="togglePasswordVisibility">
                <span v-if="showPassword">🙈</span>
                <span v-else>👁️</span>
              </button>
            </div>
            <p class="error-message" v-if="errors.password">{{ errors.password }}</p>

            <!-- Strength Bar -->
            <div v-if="password.length > 0">
              <div class="password-strength" :class="passwordStrength">
                <div class="strength-bar"></div>
              </div>
              <div class="strength-text" :class="passwordStrength">
                {{ passwordStrength === 'weak' ? 'Weak' : passwordStrength === 'medium' ? 'Medium' : 'Strong' }}
              </div>
            </div>
          </div>
          
          <div class="form-group">
            <label for="confirmPassword">Confirm New Password</label>
            <div class="password-input">
              <input
                :type="showConfirmPassword ? 'text' : 'password'"
                id="confirmPassword"
                v-model="confirmPassword"
                required
                placeholder="Confirm your new password"
                :class="{ 'error': errors.confirmPassword }"
                @input="validateConfirmPassword"
              />
              <button type="button" class="toggle-password" @click="toggleConfirmPasswordVisibility">
                <span v-if="showConfirmPassword">🙈</span>
                <span v-else>👁️</span>
              </button>
            </div>
            <p class="error-message" v-if="errors.confirmPassword">{{ errors.confirmPassword }}</p>
          </div>
          
          <button type="submit" class="btn-primary" :disabled="isLoading">
            <span v-if="!isLoading">Reset Password</span>
            <span v-else>Resetting Password...</span>
          </button>
          
          <div class="back-to-login">
            <router-link to="/login">Back to Login</router-link>
          </div>
        </form>
        
        <div class="success-message" v-else>
          <div class="success-icon">
            <i class="fas fa-check-circle"></i>
          </div>
          <h3>Password Reset Successful</h3>
          <p>Your password has been successfully reset. You can now log in with your new password.</p>
          
          <div class="back-to-login">
            <router-link to="/login" class="btn-primary">Go to Login</router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, getCurrentInstance } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

export default {
  name: 'ResetPassword',
  setup() {
    const authStore = useAuthStore();
    const route = useRoute();
    const { proxy } = getCurrentInstance();
    
    const password = ref('');
    const confirmPassword = ref('');
    const showPassword = ref(false);
    const showConfirmPassword = ref(false);
    const passwordStrength = ref('');
    const errors = ref({
      password: '',
      confirmPassword: ''
    });
    const isLoading = ref(false);
    const passwordReset = ref(false);
    const token = ref('');
    const emailWithType = ref('');

    onMounted(() => {
      token.value = route.query.token || '';
      emailWithType.value = route.query.email || '';
      
      if (!token.value || !emailWithType.value) {
        proxy.$swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Invalid or expired reset link. Please request a new one.',
          confirmButtonColor: '#3b82f6',
        }).then(() => {
          router.push('/forgot-password');
        });
      }
    });

    const validatePassword = () => {
      errors.value.password = '';

      // Rule 1: length
      if (password.value.length < 8) {
        errors.value.password = 'Password must be at least 8 characters long';
        passwordStrength.value = 'weak';
        return false;
      }

      // Rule 2: must contain uppercase
      const hasUpperCase = /[A-Z]/.test(password.value);

      // Rule 3: must contain number
      const hasNumber = /\d/.test(password.value);

      let strength = 0;
      if (hasUpperCase) strength++;
      if (hasNumber) strength++;
      if (password.value.length >= 12) strength++;

      // Strength levels
      if (strength === 0) {
        passwordStrength.value = 'weak';
      } else if (strength === 1) {
        passwordStrength.value = 'medium';
      } else {
        passwordStrength.value = 'strong';
      }

      return true;
    };

    const validateConfirmPassword = () => {
      errors.value.confirmPassword = '';
      
      if (confirmPassword.value !== password.value) {
        errors.value.confirmPassword = 'Passwords do not match';
        return false;
      }
      
      return true;
    };

    const togglePasswordVisibility = () => {
      showPassword.value = !showPassword.value;
    };

    const toggleConfirmPasswordVisibility = () => {
      showConfirmPassword.value = !showConfirmPassword.value;
    };

    const validateForm = () => {
      const isPasswordValid = validatePassword();
      const isConfirmPasswordValid = validateConfirmPassword();
      
      return isPasswordValid && isConfirmPasswordValid;
    };

    const router = useRouter();
    
    const handleSubmit = async () => {
      if (!validateForm()) return;
      
      isLoading.value = true;
      
      try {
        const result = await authStore.resetPassword({
          email: emailWithType.value,
          token: token.value,
          password: password.value,
          password_confirmation: confirmPassword.value
        });
        
        if (result.success) {
          passwordReset.value = true;
          proxy.$swal.fire({
            icon: 'success',
            title: 'Success',
            text: result.message || 'Your password has been reset successfully',
            confirmButtonColor: '#3b82f6',
          }).then(() => {
            router.push('/login');
          });
        } else {
          proxy.$swal.fire({
            icon: 'error',
            title: 'Error',
            text: result.message || 'Failed to reset password. Please try again.',
            confirmButtonColor: '#3b82f6',
          });
        }
      } catch (error) {
        const errorMessage = error.response?.data?.message || 'An unexpected error occurred. Please try again.';
        proxy.$swal.fire({
          icon: 'error',
          title: 'Error',
          text: errorMessage,
          confirmButtonColor: '#3b82f6',
        });
      } finally {
        isLoading.value = false;
      }
    };

    return {
      password,
      confirmPassword,
      showPassword,
      showConfirmPassword,
      passwordStrength,
      errors,
      isLoading,
      passwordReset,
      validatePassword,
      validateConfirmPassword,
      togglePasswordVisibility,
      toggleConfirmPasswordVisibility,
      handleSubmit
    };
  }
};
</script>

<style scoped>
.reset-password-page {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
  padding: 20px;
}

.reset-password-container {
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
  width: 100%;
  max-width: 480px;
  overflow: hidden;
}

.logo-container {
  background: linear-gradient(135deg, #1e40af, #3b82f6);
  color: white;
  padding: 30px;
  text-align: center;
}

.logo {
  width: 80px;
  height: 80px;
  margin-bottom: 15px;
  border-radius: 50%;
  background: #0d55c8;
  border: 3px solid white;
  padding: 5px;
  box-shadow: 0 4px 16px rgba(255, 255, 255, 0.2);
}

h1 {
  margin: 0;
  font-size: 1.5rem;
  font-weight: 600;
}

.reset-password-form {
  padding: 30px;
}

h2 {
  color: #1f2937;
  margin-top: 0;
  margin-bottom: 10px;
  font-size: 1.5rem;
}

p {
  color: #6b7280;
  margin-top: 0;
  margin-bottom: 25px;
  line-height: 1.5;
}

.form-group {
  margin-bottom: 25px;
}

label {
  display: block;
  margin-bottom: 8px;
  font-weight: 500;
  color: #374151;
}

.password-input {
  position: relative;
  display: flex;
  align-items: center;
}

input {
  width: 100%;
  padding: 12px 45px 12px 15px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 1rem;
  transition: border-color 0.3s, box-shadow 0.3s;
}

input:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

input.error {
  border-color: #ef4444;
}

.toggle-password {
  position: absolute;
  right: 12px;
  background: none;
  border: none;
  color: #9ca3af;
  cursor: pointer;
  padding: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: color 0.3s;
}

.toggle-password:hover {
  color: #4b5563;
}

.error-message {
  color: #ef4444;
  font-size: 0.875rem;
  margin-top: 5px;
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

.medium .strength-bar {
  width: 66%;
  background-color: #f59e0b;
}

.medium .strength-text {
  color: #f59e0b;
}

.strong .strength-bar {
  width: 100%;
  background-color: #10b981;
}

.strong .strength-text {
  color: #10b981;
}

.btn-primary {
  width: 100%;
  padding: 12px;
  background-color: #3b82f6;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.3s;
  text-decoration: none;
  display: block;
  text-align: center;
}

.btn-primary:hover {
  background-color: #2563eb;
}

.btn-primary:disabled {
  background-color: #93c5fd;
  cursor: not-allowed;
}

.back-to-login {
  text-align: center;
  margin-top: 20px;
}

.back-to-login a {
  color: #3b82f6;
  text-decoration: none;
  font-weight: 500;
  transition: color 0.3s;
}

.back-to-login a:hover {
  color: #2563eb;
  text-decoration: underline;
}

.success-message {
  text-align: center;
  padding: 20px 0;
}

.success-icon {
  font-size: 3.5rem;
  color: #10b981;
  margin-bottom: 15px;
}

.success-message h3 {
  color: #10b981;
  margin-bottom: 15px;
}

.success-message p {
  color: #6b7280;
  margin-bottom: 20px;
}

.success-message .btn-primary {
  margin-top: 15px;
}
</style>
