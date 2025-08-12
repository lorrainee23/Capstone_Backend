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
          <p class="license-number">License: {{ user?.license_number || 'N/A' }}</p>
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
              <span v-if="errors.first_name" class="error-message">{{ errors.first_name }}</span>
            </div>

            <div class="form-group">
              <label for="middle_name">Middle Name</label>
              <input
                id="middle_name"
                type="text"
                v-model="formData.middle_name"
                :disabled="!editMode"
                class="form-input"
              >
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
              <span v-if="errors.last_name" class="error-message">{{ errors.last_name }}</span>
            </div>

            <div class="form-group">
              <label for="license_number">License Number *</label>
              <input
                id="license_number"
                type="text"
                v-model="formData.license_number"
                :disabled="!editMode"
                class="form-input"
                :class="{ error: errors.license_number }"
              >
              <span v-if="errors.license_number" class="error-message">{{ errors.license_number }}</span>
            </div>
          </div>
        </div>

        <div class="form-section">
          <h3>Contact Information</h3>
          <div class="form-grid">
            <div class="form-group">
              <label for="email">Email Address *</label>
              <input
                id="email"
                type="email"
                v-model="formData.email"
                :disabled="!editMode"
                class="form-input"
                :class="{ error: errors.email }"
              >
              <span v-if="errors.email" class="error-message">{{ errors.email }}</span>
            </div>

            <div class="form-group">
              <label for="phone_number">Phone Number</label>
              <input
                id="phone_number"
                type="tel"
                v-model="formData.phone_number"
                :disabled="!editMode"
                class="form-input"
                :class="{ error: errors.phone_number }"
              >
              <span v-if="errors.phone_number" class="error-message">{{ errors.phone_number }}</span>
            </div>

            <div class="form-group full-width">
              <label for="address">Address</label>
              <textarea
                id="address"
                v-model="formData.address"
                :disabled="!editMode"
                class="form-textarea"
                rows="3"
                placeholder="Enter your complete address"
              ></textarea>
            </div>
          </div>
        </div>

        <div v-if="editMode" class="form-section">
          <h3>Change Password</h3>
          <div class="form-grid">
            <div class="form-group">
              <label for="current_password">Current Password</label>
              <input
                id="current_password"
                type="password"
                v-model="formData.current_password"
                class="form-input"
                :class="{ error: errors.current_password }"
              >
              <span v-if="errors.current_password" class="error-message">{{ errors.current_password }}</span>
            </div>

            <div class="form-group">
              <label for="new_password">New Password</label>
              <input
                id="new_password"
                type="password"
                v-model="formData.new_password"
                class="form-input"
                :class="{ error: errors.new_password }"
              >
              <span v-if="errors.new_password" class="error-message">{{ errors.new_password }}</span>
            </div>

            <div class="form-group">
              <label for="confirm_password">Confirm New Password</label>
              <input
                id="confirm_password"
                type="password"
                v-model="formData.confirm_password"
                class="form-input"
                :class="{ error: errors.confirm_password }"
              >
              <span v-if="errors.confirm_password" class="error-message">{{ errors.confirm_password }}</span>
            </div>
          </div>
          <div class="password-note">
            <p>Leave password fields empty if you don't want to change your password.</p>
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

      <!-- Account Actions -->
      <div class="account-actions">
        <h3>Account Actions</h3>
        <div class="actions-grid">
          <div class="action-card">
            <div class="action-icon">🔔</div>
            <div class="action-content">
              <h4>Notification Preferences</h4>
              <p>Manage how you receive notifications about violations and payments.</p>
              <div class="notification-settings">
                <label class="checkbox-label">
                  <input type="checkbox" v-model="notificationSettings.email_notifications">
                  <span class="checkmark"></span>
                  Email Notifications
                </label>
                <label class="checkbox-label">
                  <input type="checkbox" v-model="notificationSettings.sms_notifications">
                  <span class="checkmark"></span>
                  SMS Notifications
                </label>
                <label class="checkbox-label">
                  <input type="checkbox" v-model="notificationSettings.payment_reminders">
                  <span class="checkmark"></span>
                  Payment Reminders
                </label>
              </div>
            </div>
          </div>

          <div class="action-card">
            <div class="action-icon">📱</div>
            <div class="action-content">
              <h4>Download Data</h4>
              <p>Download your violation history and payment records.</p>
              <button @click="downloadData" class="btn btn-primary btn-sm">
                Download Report
              </button>
            </div>
          </div>

          <div class="action-card">
            <div class="action-icon">🔒</div>
            <div class="action-content">
              <h4>Account Security</h4>
              <p>Your account is secured with password authentication.</p>
              <div class="security-info">
                <div class="security-item">
                  <span class="security-label">Last Login:</span>
                  <span class="security-value">{{ formatDateTime(user?.last_login_at) || 'N/A' }}</span>
                </div>
                <div class="security-item">
                  <span class="security-label">Account Created:</span>
                  <span class="security-value">{{ formatDate(user?.created_at) }}</span>
                </div>
              </div>
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
import { violatorAPI } from '@/services/api'
import { useAuthStore } from '@/stores/auth'

export default {
  name: 'ViolatorProfile',
  components: {
    SidebarLayout
  },
  setup() {
    const { state } = useAuthStore()
    const editMode = ref(false)
    const saving = ref(false)
    const message = ref('')
    const messageType = ref('success')
    const accountStats = ref({})
    
    const formData = ref({
      first_name: '',
      middle_name: '',
      last_name: '',
      license_number: '',
      email: '',
      phone_number: '',
      address: '',
      current_password: '',
      new_password: '',
      confirm_password: ''
    })
    
    const errors = ref({})
    
    const notificationSettings = ref({
      email_notifications: true,
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
        const response = await violatorAPI.profile()
        
        if (response.data.status === 'success') {
          const data = response.data.data
          
          // Update form data with user info
          formData.value = {
            first_name: data.user?.first_name || '',
            middle_name: data.user?.middle_name || '',
            last_name: data.user?.last_name || '',
            license_number: data.user?.license_number || '',
            email: data.user?.email || '',
            phone_number: data.user?.phone_number || '',
            address: data.user?.address || '',
            current_password: '',
            new_password: '',
            confirm_password: ''
          }
          
          accountStats.value = data.stats || {}
          
          if (data.notification_settings) {
            notificationSettings.value = data.notification_settings
          }
        }
      } catch (error) {
        console.error('Failed to load profile data:', error)
        
        // Use current user data as fallback
        if (user.value) {
          formData.value = {
            first_name: user.value.first_name || '',
            middle_name: user.value.middle_name || '',
            last_name: user.value.last_name || '',
            license_number: user.value.license_number || '',
            email: user.value.email || '',
            phone_number: user.value.phone_number || '',
            address: user.value.address || '',
            current_password: '',
            new_password: '',
            confirm_password: ''
          }
        }
        
        // Mock stats for demo
        accountStats.value = {
          total_violations: 3,
          pending_violations: 1,
          paid_violations: 2,
          total_amount: 2500
        }
      }
    }
    
    const validateForm = () => {
      errors.value = {}
      
      if (!formData.value.first_name.trim()) {
        errors.value.first_name = 'First name is required'
      }
      
      if (!formData.value.last_name.trim()) {
        errors.value.last_name = 'Last name is required'
      }
      
      if (!formData.value.license_number.trim()) {
        errors.value.license_number = 'License number is required'
      }
      
      if (!formData.value.email.trim()) {
        errors.value.email = 'Email is required'
      } else if (!/\S+@\S+\.\S+/.test(formData.value.email)) {
        errors.value.email = 'Please enter a valid email address'
      }
      
      if (formData.value.phone_number && !/^[\d\s\-+()]+$/.test(formData.value.phone_number)) {
        errors.value.phone_number = 'Please enter a valid phone number'
      }
      
      // Password validation
      if (formData.value.new_password) {
        if (!formData.value.current_password) {
          errors.value.current_password = 'Current password is required to change password'
        }
        
        if (formData.value.new_password.length < 8) {
          errors.value.new_password = 'New password must be at least 8 characters long'
        }
        
        if (formData.value.new_password !== formData.value.confirm_password) {
          errors.value.confirm_password = 'Password confirmation does not match'
        }
      }
      
      return Object.keys(errors.value).length === 0
    }
    
    const saveProfile = async () => {
      if (!validateForm()) {
        showMessage('Please fix the errors before saving.', 'error')
        return
      }
      
      try {
        saving.value = true
        
        const profileData = {
          first_name: formData.value.first_name,
          middle_name: formData.value.middle_name,
          last_name: formData.value.last_name,
          license_number: formData.value.license_number,
          email: formData.value.email,
          phone_number: formData.value.phone_number,
          address: formData.value.address
        }
        
        // Add password fields if changing password
        if (formData.value.new_password) {
          profileData.current_password = formData.value.current_password
          profileData.new_password = formData.value.new_password
          profileData.new_password_confirmation = formData.value.confirm_password
        }
        
        const response = await violatorAPI.updateProfile(profileData)
        
        if (response.data.status === 'success') {
          showMessage('Profile updated successfully!', 'success')
          editMode.value = false
          
          // Clear password fields
          formData.value.current_password = ''
          formData.value.new_password = ''
          formData.value.confirm_password = ''
          
          // Reload profile data
          await loadProfileData()
        }
      } catch (error) {
        console.error('Failed to update profile:', error)
        
        if (error.response?.data?.errors) {
          errors.value = error.response.data.errors
        }
        
        showMessage('Failed to update profile. Please try again.', 'error')
      } finally {
        saving.value = false
      }
    }
    
    const cancelEdit = () => {
      editMode.value = false
      errors.value = {}
      loadProfileData() // Reload original data
    }
    
    const downloadData = () => {
      // In a real app, this would generate and download a report
      alert('Data download feature would be implemented here. This would generate a PDF or CSV file with your violation history and payment records.')
    }
    
    const showMessage = (text, type = 'success') => {
      message.value = text
      messageType.value = type
      setTimeout(() => {
        message.value = ''
      }, 5000)
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
      message,
      messageType,
      formData,
      errors,
      accountStats,
      notificationSettings,
      user,
      fullName,
      userInitials,
      saveProfile,
      cancelEdit,
      downloadData,
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

.license-number {
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

.password-note {
  margin-top: 16px;
  padding: 12px;
  background: #f3f4f6;
  border-radius: 8px;
}

.password-note p {
  font-size: 14px;
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
