<template>
  <SidebarLayout page-title="My Profile">
    <div class="violator-profile">
      <!-- Profile Header -->
      <div class="profile-header">
        <div class="profile-avatar">
          <div class="avatar-circle">
            {{ userInitials }}
          </div>
        </div>
        <div class="profile-info">
          <h2>{{ fullName }}</h2>
          <p class="mobile-number">Mobile: {{ user?.mobile_number || 'N/A' }}</p>
          <p class="member-since">Member since {{ formatDate(user?.created_at) }}</p>
        </div>
        <div class="profile-actions">
          <button @click="editMode = !editMode" class="btn btn-primary">
            {{ editMode ? 'Cancel' : 'Edit Profile' }}
          </button>
        </div>
      </div>

      <!-- Profile Form -->
      <div class="profile-form">
        <div class="form-section">
          <h3>Personal Information</h3>
          <div class="form-grid">
            <div class="form-group">
              <label for="first_name">First Name *</label>
              <input
                id="first_name"
                type="text"
                v-model="formData.first_name"
                :disabled="!editMode"
                class="form-input"
                :class="{ error: errors.first_name }"
              >
              <span v-if="errors.first_name" class="error-message">{{ errors.first_name[0] }}</span>
            </div>

            <div class="form-group">
              <label for="middle_name">Middle Name</label>
              <input
                id="middle_name"
                type="text"
                v-model="formData.middle_name"
                :disabled="!editMode"
                class="form-input"
                :class="{ error: errors.middle_name }"
              >
              <span v-if="errors.middle_name" class="error-message">{{ errors.middle_name[0] }}</span>
            </div>

            <div class="form-group">
              <label for="last_name">Last Name *</label>
              <input
                id="last_name"
                type="text"
                v-model="formData.last_name"
                :disabled="!editMode"
                class="form-input"
                :class="{ error: errors.last_name }"
              >
              <span v-if="errors.last_name" class="error-message">{{ errors.last_name[0] }}</span>
            </div>

            <div class="form-group">
              <label for="mobile_number">Mobile Number *</label>
              <input
                id="mobile_number"
                type="text"
                v-model="formData.mobile_number"
                :disabled="!editMode"
                class="form-input"
                :class="{ error: errors.mobile_number }"
                placeholder="09XXXXXXXXX"
                maxlength="11"
              >
              <span v-if="errors.mobile_number" class="error-message">{{ errors.mobile_number[0] }}</span>
            </div>

          </div>
        </div>

        <div v-if="editMode" class="form-actions">
          <button @click="cancelEdit" class="btn btn-secondary">
            Cancel
          </button>
          <button @click="saveProfile" :disabled="saving" class="btn btn-success">
            <span v-if="saving" class="spinner-sm"></span>
            {{ saving ? 'Saving...' : 'Save Changes' }}
          </button>
        </div>
      </div>

      <!-- Change Password Section -->
      <div class="profile-form">
        <div class="form-section">
          <h3>Change Password</h3>
          <div class="form-grid">
            <div class="form-group">
              <label for="current_password">Current Password *</label>
              <input
                id="current_password"
                type="password"
                v-model="passwordData.current_password"
                class="form-input"
                :class="{ error: passwordErrors.current_password }"
                placeholder="Enter Current Password"
              >
              <span v-if="passwordErrors.current_password" class="error-message">{{ passwordErrors.current_password[0] }}</span>
            </div>

            <div class="form-group">
              <label for="new_password">New Password *</label>
              <input
                id="new_password"
                type="password"
                v-model="passwordData.new_password"
                class="form-input"
                :class="{ error: passwordErrors.new_password }"
                placeholder="Minimum 6 characters"
              >
              <span v-if="passwordErrors.new_password" class="error-message">{{ passwordErrors.new_password[0] }}</span>
            </div>

            <div class="form-group">
              <label for="new_password_confirmation">Confirm New Password *</label>
              <input
                id="new_password_confirmation"
                type="password"
                v-model="passwordData.new_password_confirmation"
                class="form-input"
                :class="{ error: passwordErrors.new_password_confirmation }"
                placeholder="Minimum 6 characters"
              >
              <span v-if="passwordErrors.new_password_confirmation" class="error-message">{{ passwordErrors.new_password_confirmation[0] }}</span>
            </div>
          </div>

          <div class="form-actions">
            <button @click="changePassword" :disabled="changingPassword" class="btn btn-primary">
              <span v-if="changingPassword" class="spinner-sm"></span>
              {{ changingPassword ? 'Changing...' : 'Change Password' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Account Statistics -->
      <div class="account-stats">
        <h3>Account Statistics</h3>
        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-icon">📋</div>
            <div class="stat-content">
              <div class="stat-number">{{ accountStats.total_violations || 0 }}</div>
              <div class="stat-label">Total Violations</div>
            </div>
          </div>
          
          <div class="stat-card">
            <div class="stat-icon">⏳</div>
            <div class="stat-content">
              <div class="stat-number">{{ accountStats.pending_violations || 0 }}</div>
              <div class="stat-label">Pending Payments</div>
            </div>
          </div>
          
          <div class="stat-card">
            <div class="stat-icon">✅</div>
            <div class="stat-content">
              <div class="stat-number">{{ accountStats.paid_violations || 0 }}</div>
              <div class="stat-label">Paid Violations</div>
            </div>
          </div>
          
          <div class="stat-card">
            <div class="stat-icon">💰</div>
            <div class="stat-content">
              <div class="stat-number">₱{{ formatCurrency(accountStats.total_amount || 0) }}</div>
              <div class="stat-label">Total Fines</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Success/Error Messages -->
      <div v-if="message" class="message" :class="messageType">
        {{ message }}
      </div>
    </div>
  </SidebarLayout>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import SidebarLayout from '@/components/SidebarLayout.vue'
import { violatorAPI,authAPI } from '@/services/api'
import { useAuthStore } from '@/stores/auth'
import Swal from 'sweetalert2'

export default {
  name: 'ViolatorProfile',
  components: {
    SidebarLayout
  },
  setup() {
    const { state } = useAuthStore()
    const editMode = ref(false)
    const saving = ref(false)
    const changingPassword = ref(false)
    const accountStats = ref({})
    
    const formData = ref({
      first_name: '',
      middle_name: '',
      last_name: '',
      mobile_number: '',

    })

    const passwordData = ref({
      current_password: '',
      new_password: '',
      new_password_confirmation: ''
    })
    
    const errors = ref({})
    const passwordErrors = ref({})
    
    const notificationSettings = ref({
      sms_notifications: false,
      payment_reminders: true
    })
    
    const user = computed(() => state.user)
    
    const fullName = computed(() => {
      if (!user.value) return ''
      const middle = user.value.middle_name ? ` ${user.value.middle_name}` : ''
      return `${user.value.first_name}${middle} ${user.value.last_name}`
    })
    
    const userInitials = computed(() => {
      if (!user.value) return 'U'
      const first = user.value.first_name?.charAt(0) || ''
      const last = user.value.last_name?.charAt(0) || ''
      return `${first}${last}`.toUpperCase()
    })
    
    const loadProfileData = async () => {
  try {
    const response = await authAPI.profile()
    if (response.data.success) {
      const profileUser = response.data.data

      state.user = {
        ...state.user,
        ...profileUser
      }

      localStorage.setItem("user_data", JSON.stringify(state.user))

      formData.value = {
        first_name: state.user.first_name || '',
        middle_name: state.user.middle_name || '',
        last_name: state.user.last_name || '',
        mobile_number: state.user.mobile_number || ''
      }
    }
  } catch (error) {
    console.error("Failed to load profile:", error)
  }
}


    const saveProfile = async () => {
      try {
        saving.value = true
        errors.value = {}
        
        const formDataToSend = new FormData()
        formDataToSend.append('first_name', formData.value.first_name)
        formDataToSend.append('middle_name', formData.value.middle_name || '')
        formDataToSend.append('last_name', formData.value.last_name)
        formDataToSend.append('mobile_number', formData.value.mobile_number)
        
        const response = await violatorAPI.updateProfile(formDataToSend)
        
        if (response.data.status === 'success') {
          await Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: 'Profile updated successfully!',
            confirmButtonColor: '#10b981'
          })
          
          editMode.value = false
          
          state.user = {
  ...state.user,   
  ...response.data.data.violator
}


localStorage.setItem("user_data", JSON.stringify(state.user))

await loadProfileData()
        }
      } catch (error) {
        console.error('Failed to update profile:', error)
        
        if (error.response?.data?.errors) {
          errors.value = error.response.data.errors
        }
        
        const errorMessage = error.response?.data?.message || 'Failed to update profile. Please try again.'
        
        await Swal.fire({
          icon: 'error',
          title: 'Update Failed',
          text: errorMessage,
          confirmButtonColor: '#ef4444'
        })
      } finally {
        saving.value = false
      }
    }

    const changePassword = async () => {
      try {
        changingPassword.value = true
        passwordErrors.value = {}

        const response = await violatorAPI.changePassword({
          current_password: passwordData.value.current_password,
          new_password: passwordData.value.new_password,
          new_password_confirmation: passwordData.value.new_password_confirmation
        })

        if (response.data.status === 'success') {
          await Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: 'Password changed successfully!',
            confirmButtonColor: '#10b981'
          })
          
          // Clear password form
          passwordData.value = {
            current_password: '',
            new_password: '',
            new_password_confirmation: ''
          }
        }
      } catch (error) {
        console.error('Failed to change password:', error)
        
        if (error.response?.data?.errors) {
          passwordErrors.value = error.response.data.errors
        }
        
        const errorMessage = error.response?.data?.message || 'Failed to change password. Please try again.'
        
        await Swal.fire({
          icon: 'error',
          title: 'Password Change Failed',
          text: errorMessage,
          confirmButtonColor: '#ef4444'
        })
      } finally {
        changingPassword.value = false
      }
    }
    
    const cancelEdit = () => {
      editMode.value = false
      errors.value = {}
      loadProfileData() // Reload original data
    }
    
    const formatCurrency = (amount) => {
      return new Intl.NumberFormat('en-PH').format(amount)
    }
    
    const formatDate = (dateString) => {
      if (!dateString) return 'N/A'
      return new Date(dateString).toLocaleDateString('en-PH', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      })
    }
    
    const formatDateTime = (dateString) => {
      if (!dateString) return 'N/A'
      return new Date(dateString).toLocaleString('en-PH', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
    }
    
    onMounted(() => {
      loadProfileData()
    })
    
    return {
      editMode,
      saving,
      changingPassword,
      formData,
      passwordData,
      errors,
      passwordErrors,
      accountStats,
      notificationSettings,
      user,
      fullName,
      userInitials,
      saveProfile,
      changePassword,
      cancelEdit,
      formatCurrency,
      formatDate,
      formatDateTime
    }
  }
}
</script>

<style scoped>
.violator-profile {
  max-width: 1200px;
  margin: 0 auto;
}

.profile-header {
  background: linear-gradient(135deg, #1e3a8a, #3b82f6);
  color: white;
  border-radius: 12px;
  padding: 32px;
  margin-bottom: 32px;
  display: flex;
  align-items: center;
  gap: 24px;
}

.profile-avatar {
  flex-shrink: 0;
}

.avatar-circle {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.2);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 32px;
  font-weight: 700;
  color: white;
}

.profile-info {
  flex: 1;
}

.profile-info h2 {
  font-size: 28px;
  font-weight: 700;
  margin: 0 0 8px 0;
}

.mobile-number {
  font-size: 16px;
  opacity: 0.9;
  margin: 0 0 4px 0;
}

.member-since {
  font-size: 14px;
  opacity: 0.8;
  margin: 0;
}

.profile-actions .btn {
  background: rgba(255, 255, 255, 0.2);
  color: white;
  border: 2px solid rgba(255, 255, 255, 0.3);
}

.profile-actions .btn:hover {
  background: rgba(255, 255, 255, 0.3);
  border-color: rgba(255, 255, 255, 0.5);
}

.profile-form {
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  margin-bottom: 32px;
  overflow: hidden;
}

.form-section {
  padding: 32px;
  border-bottom: 1px solid #e5e7eb;
}

.form-section:last-child {
  border-bottom: none;
}

.form-section h3 {
  font-size: 20px;
  font-weight: 600;
  color: #1f2937;
  margin: 0 0 24px 0;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.form-group.full-width {
  grid-column: 1 / -1;
}

.form-group label {
  font-size: 14px;
  font-weight: 500;
  color: #374151;
}

.form-input,
.form-textarea {
  padding: 12px 16px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 14px;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.form-input[type="file"] {
  padding: 8px 12px;
}

.form-input:focus,
.form-textarea:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-input:disabled,
.form-textarea:disabled {
  background: #f9fafb;
  color: #6b7280;
  cursor: not-allowed;
}

.form-input.error,
.form-textarea.error {
  border-color: #ef4444;
}

.error-message {
  font-size: 12px;
  color: #ef4444;
}

.file-note {
  margin-top: 8px;
  padding: 8px 12px;
  background: #f3f4f6;
  border-radius: 6px;
}

.file-note p {
  font-size: 12px;
  color: #6b7280;
  margin: 0;
}

.form-actions {
  padding: 24px 32px;
  background: #f9fafb;
  display: flex;
  justify-content: flex-end;
  gap: 12px;
}

.account-stats {
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  padding: 32px;
  margin-bottom: 32px;
}

.account-stats h3 {
  font-size: 20px;
  font-weight: 600;
  color: #1f2937;
  margin: 0 0 24px 0;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
}

.stat-card {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 20px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 15px -3px rgba(0, 0, 0, 0.1);
}

.stat-icon {
  font-size: 24px;
}

.stat-number {
  font-size: 24px;
  font-weight: 700;
  color: #1f2937;
  line-height: 1;
}

.stat-label {
  font-size: 14px;
  color: #6b7280;
  margin-top: 4px;
}

.account-actions {
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  padding: 32px;
}

.account-actions h3 {
  font-size: 20px;
  font-weight: 600;
  color: #1f2937;
  margin: 0 0 24px 0;
}

.actions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 24px;
}

.action-card {
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  padding: 24px;
  display: flex;
  gap: 16px;
}

.action-icon {
  font-size: 24px;
  flex-shrink: 0;
}

.action-content {
  flex: 1;
}

.action-content h4 {
  font-size: 16px;
  font-weight: 600;
  color: #1f2937;
  margin: 0 0 8px 0;
}

.action-content p {
  font-size: 14px;
  color: #6b7280;
  margin: 0 0 16px 0;
  line-height: 1.5;
}

.notification-settings {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  color: #374151;
  cursor: pointer;
}

.checkbox-label input[type="checkbox"] {
  margin: 0;
}

.security-info {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.security-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 14px;
}

.security-label {
  color: #6b7280;
}

.security-value {
  color: #1f2937;
  font-weight: 500;
}

.message {
  position: fixed;
  top: 20px;
  right: 20px;
  padding: 12px 20px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
  z-index: 1000;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.message.success {
  background: #d1fae5;
  color: #065f46;
  border: 1px solid #a7f3d0;
}

.message.error {
  background: #fee2e2;
  color: #991b1b;
  border: 1px solid #fca5a5;
}

.spinner-sm {
  display: inline-block;
  width: 16px;
  height: 16px;
  border: 2px solid transparent;
  border-top: 2px solid currentColor;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-right: 8px;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

@media (max-width: 768px) {
  .profile-header {
    flex-direction: column;
    text-align: center;
    gap: 20px;
  }
  
  .form-grid {
    grid-template-columns: 1fr;
  }
  
  .stats-grid {
    grid-template-columns: 1fr;
  }
  
  .actions-grid {
    grid-template-columns: 1fr;
  }
  
  .form-actions {
    flex-direction: column;
  }
}
</style>