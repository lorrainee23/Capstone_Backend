<template>
  <div class="forgot-password-page">
    <div class="forgot-password-container">
      <div class="logo-container">
        <img src="@/assets/posu_logo.png" alt="POSU Logo" class="logo" />
        <h1>POSU Traffic Violation System</h1>
      </div>
      
      <div class="forgot-password-form">
        <h2>Reset Your Password</h2>
        <p>Enter your email address or mobile number and we'll send you a link to reset your password.</p>
        
        <form @submit.prevent="handleSubmit" v-if="!emailSent">
          <div class="form-group">
            <label for="identifier">Email or Mobile Number</label>
            <input
              type="text"
              id="identifier"
              v-model="identifier"
              required
              placeholder="Enter your email or mobile number"
              :class="{ 'error': error }"
            />
            <p class="error-message" v-if="error">{{ error }}</p>
          </div>
          
          <button type="submit" class="btn-primary" :disabled="isLoading">
            <span v-if="!isLoading">Send Reset Link</span>
            <span v-else>Sending...</span>
          </button>
          
          <div class="back-to-login">
            <router-link to="/login">Back to Login</router-link>
          </div>
        </form>
        
        <div class="success-message" v-else>
          <div class="success-icon">
            <i class="fas fa-check-circle"></i>
          </div>
          <h3>Password Reset Link Sent</h3>
          <p>We've sent a password reset link to your email address. Please check your inbox and follow the instructions to reset your password.</p>
          <p>Didn't receive the email? <a href="#" @click.prevent="resetForm">Try again</a></p>
          
          <div class="back-to-login">
            <router-link to="/login">Back to Login</router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, getCurrentInstance } from 'vue';
import { useAuthStore } from '@/stores/auth';

export default {
  name: 'ForgotPassword',
  setup() {
    const authStore = useAuthStore();
    const { proxy } = getCurrentInstance();
    const identifier = ref('');
    const error = ref('');
    const emailSent = ref(false);
    const isLoading = ref(false);

    const resetForm = () => {
      identifier.value = '';
      error.value = '';
      emailSent.value = false;
    };

    const handleSubmit = async () => {
      if (!identifier.value.trim()) {
        error.value = 'Please enter your email or mobile number';
        return;
      }

      isLoading.value = true;
      error.value = '';

      try {
        const result = await authStore.forgotPassword(identifier.value);
        if (result.success) {
          emailSent.value = true;
          proxy.$swal.fire({
            icon: 'success',
            title: 'Success',
            text: result.message || 'Password reset link sent to your email',
            confirmButtonColor: '#3b82f6',
          });
        } else {
          error.value = result.message || 'Failed to send reset link. Please try again.';
          proxy.$swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.value,
            confirmButtonColor: '#3b82f6',
          });
        }
      } catch (err) {
        error.value = err.response?.data?.message || 'An unexpected error occurred. Please try again.';
        proxy.$swal.fire({
          icon: 'error',
          title: 'Error',
          text: error.value,
          confirmButtonColor: '#3b82f6',
        });
      } finally {
        isLoading.value = false;
      }
    };

    return {
      identifier,
      error,
      emailSent,
      isLoading,
      handleSubmit,
      resetForm
    };
  }
};
</script>

<style scoped>
.forgot-password-page {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
  padding: 20px;
}

.forgot-password-container {
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

.forgot-password-form {
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
  margin-bottom: 20px;
}

label {
  display: block;
  margin-bottom: 8px;
  font-weight: 500;
  color: #374151;
}

input {
  width: 100%;
  padding: 12px 15px;
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

.error-message {
  color: #ef4444;
  font-size: 0.875rem;
  margin-top: 5px;
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
  margin-bottom: 10px;
}

.success-message a {
  color: #3b82f6;
  text-decoration: none;
  font-weight: 500;
}

.success-message a:hover {
  text-decoration: underline;
}
</style>
