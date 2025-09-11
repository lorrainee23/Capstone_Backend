import { reactive } from 'vue'
import { authAPI } from '@/services/api'

const state = reactive({
  user: null,
  token: null,
  isAuthenticated: false,
  loading: false
})

export const useAuthStore = () => {
  const login = async (credentials) => {
      try {
          state.loading = true;
          const response = await authAPI.login(credentials);

          if (response.data.success) {
              const data = response.data.data;
              const user = data.user || data.violator || null;
              if (user && !user.role && data.user_type) {
                  user.role = data.user_type;
              }

              state.user = user;
              state.token = data.token;
              state.isAuthenticated = true;

              localStorage.setItem("auth_token", data.token);
              localStorage.setItem("user_data", JSON.stringify(user));

              return { success: true, user };
          } else {
      return {
        success: false,
        message: response.data.message || "Login failed",
      };
    }
      } catch (error) {
          console.error("Login error:", error);
          return {
              success: false,
              message: error.response?.data?.message || "Login failed",
          };
      } finally {
          state.loading = false;
      }
  };

  const logout = async () => {
    try {
      await authAPI.logout()
    } catch (error) {
      console.error('Logout error:', error)
    } finally {
      state.user = null
      state.token = null
      state.isAuthenticated = false
      localStorage.removeItem('auth_token')
      localStorage.removeItem('user_data')
    }
  }

  const initAuth = () => {
    const token = localStorage.getItem('auth_token')
    const userData = localStorage.getItem('user_data')
    
    if (token && userData) {
      state.token = token
      state.user = JSON.parse(userData)
      state.isAuthenticated = true
    }
  }

  const register = async (data) => {
    try {
      state.loading = true
      const response = await authAPI.register(data)
      return { success: true, data: response.data }
    } catch (error) {
      return { 
        success: false, 
        message: error.response?.data?.message || 'Registration failed' 
      }
    } finally {
      state.loading = false
    }
  }

  const forgotPassword = async (identifier) => {
    try {
      state.loading = true;
      const response = await authAPI.forgotPassword({ identifier });
      return { 
        success: true, 
        message: response.data?.message || 'A password reset link has been sent to your email',
        ...response.data 
      };
    } catch (error) {
      console.error('Forgot password error:', error);
      return { 
        success: false, 
        message: error.response?.data?.message || 'Failed to send reset link. Please try again.' 
      };
    } finally {
      state.loading = false;
    }
  };

  const resetPassword = async ({ email, token, password, password_confirmation }) => {
    try {
      state.loading = true;
      const response = await authAPI.resetPassword({
        email,
        token,
        password,
        password_confirmation
      });
      return { 
        success: true, 
        message: response.data?.message || 'Your password has been reset successfully',
        ...response.data 
      };
    } catch (error) {
      console.error('Reset password error:', error);
      return { 
        success: false, 
        message: error.response?.data?.message || 'Failed to reset password. Please try again.' 
      };
    } finally {
      state.loading = false;
    }
  };

  return {
    state,
    login,
    logout,
    register,
    initAuth,
    forgotPassword,
    resetPassword
  }
}
