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
          <p class="mobile-number">Mobile: {{ user ? user.mobile_number : 'N/A' }}</p>
          <p class="member-since">Member since {{ user ? formatDate(user.created_at) : 'N/A' }}</p>
        </div>
      </div>

      <!-- Account Information (Read-Only) -->
      <div class="profile-form">
        <div class="form-section">
          <h3>Personal Information</h3>
          <div class="info-grid">
            <div class="info-item">
              <label>First Name</label>
              <span>{{ user?.first_name || 'N/A' }}</span>
            </div>
            <div class="info-item">
              <label>Middle Name</label>
              <span>{{ user?.middle_name || 'N/A' }}</span>
            </div>
            <div class="info-item">
              <label>Last Name</label>
              <span>{{ user?.last_name || 'N/A' }}</span>
            </div>
            <div class="info-item">
              <label>Mobile Number</label>
              <span>{{ user?.mobile_number || 'N/A' }}</span>
            </div>
            <div class="info-item full-width">
              <label>Address</label>
              <span>{{ user?.barangay || 'N/A' }} {{ user?.city || 'N/A' }} {{ user?.province || 'N/A' }}</span>
            </div>
            <div class="info-item">
              <label>Driver's License</label>
              <span>{{ user?.license_number || 'N/A' }}</span>
            </div>
            <div class="info-item">
              <label>Email</label>
              <span>{{ user?.email || 'N/A' }}</span>
            </div>
          </div>
          <div class="info-note">
            <div class="note-icon">ℹ️</div>
            <p>To update your personal information, please visit the POSU office with a valid ID.</p>
          </div>
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
                placeholder="Confirm new password"
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
              <div class="stat-label">Unpaid Violations</div>
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

      <!-- Account Security Info -->
      <div class="profile-form">
        <div class="form-section">
          <h3>Account Security</h3>
          <div class="security-info">
            <div class="security-item">
              <div class="security-label">Last Login</div>
              <div class="security-value">{{ formatDateTime(user?.last_login_at) || 'Never' }}</div>
            </div>
            <div class="security-item">
              <div class="security-label">Account Created</div>
              <div class="security-value">{{ formatDate(user?.created_at) || 'N/A' }}</div>
            </div>
            <div class="security-item">
              <div class="security-label">Password Last Changed</div>
              <div class="security-value">{{ formatDate(user?.password_changed_at) || 'Never' }}</div>
            </div>
          </div>
          <div class="security-note">
            <div class="note-icon">🔒</div>
            <p>For your account security, change your password regularly and never share your login credentials.</p>
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

<script setup>
import { ref, computed, onMounted } from 'vue'
import SidebarLayout from '@/components/SidebarLayout.vue'
import { useAuthStore } from '@/stores/auth'
import { violatorAPI } from '@/services/api/' 

const { state } = useAuthStore()
const user = computed(() => state.user)

const passwordData = ref({
  current_password: '',
  new_password: '',
  new_password_confirmation: ''
})

const passwordErrors = ref({})
const changingPassword = ref(false)

const accountStats = ref({
  total_violations: 0,
  pending_violations: 0,
  paid_violations: 0,
  total_amount: 0
})

const message = ref('')
const messageType = ref('')

const userInitials = computed(() => {
  if (!user.value) return '?'
  const first = user.value.first_name?.charAt(0) || ''
  const last = user.value.last_name?.charAt(0) || ''
  return (first + last).toUpperCase()
})

const fullName = computed(() => {
  if (!user.value) return ''
  const middle = user.value.middle_name ? ` ${user.value.middle_name}` : ''
  return `${user.value.first_name}${middle} ${user.value.last_name}`
})

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const formatDateTime = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const formatCurrency = (amount) => {
  if (!amount) return '0.00'
  return new Intl.NumberFormat('en-PH', {
    style: 'decimal',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(Number(amount))
}

const loadProfileData = async () => {
  try {
    const profileRes = await violatorAPI.getProfile()
    const statsRes = await violatorAPI.getStatistics()

    // Map profile data
    if (profileRes.data?.violator) {
      Object.assign(state.user, profileRes.data.violator)
    }

    // Map statistics data
    if (statsRes.data?.data?.stats) {
  const stats = statsRes.data.data.stats
  accountStats.value = {
    total_violations: stats.total_violations,
    pending_violations: stats.pending_violations,
    paid_violations: stats.paid_violations,
    total_amount: Number(stats.total_fines) || 0
  }
    }

  } catch (err) {
    console.error('Failed to load profile data:', err)
  }
}

const changePassword = async () => {
  try {
    changingPassword.value = true
    passwordErrors.value = {}
    message.value = ''

    await violatorAPI.changePassword(passwordData.value)

    message.value = 'Password changed successfully'
    messageType.value = 'success'

    passwordData.value = {
      current_password: '',
      new_password: '',
      new_password_confirmation: ''
    }
  } catch (err) {
    if (err.response?.status === 422) {
      passwordErrors.value = err.response.data.errors
    } else if (err.response?.data?.message) {
      message.value = err.response.data.message
      messageType.value = 'error'
    } else {
      message.value = 'Failed to change password'
      messageType.value = 'error'
    }
  } finally {
    changingPassword.value = false
  }
}

onMounted(() => {
  loadProfileData()
})
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

.profile-form {
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  margin-bottom: 32px;
  overflow: hidden;
}

.form-section {
  padding: 32px;
}

.form-section h3 {
  font-size: 20px;
  font-weight: 600;
  color: #1f2937;
  margin: 0 0 24px 0;
}

.info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
  margin-bottom: 24px;
}

.info-item {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.info-item.full-width {
  grid-column: 1 / -1;
}

.info-item label {
  font-size: 12px;
  font-weight: 500;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.info-item span {
  font-size: 14px;
  color: #1f2937;
  font-weight: 500;
}

.info-note, .security-note {
  background: #f0f9ff;
  border: 1px solid #0ea5e9;
  border-radius: 8px;
  padding: 16px;
  display: flex;
  align-items: flex-start;
  gap: 12px;
}

.note-icon {
  font-size: 16px;
  flex-shrink: 0;
  margin-top: 2px;
}

.info-note p, .security-note p {
  font-size: 14px;
  color: #0c4a6e;
  margin: 0;
  line-height: 1.4;
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

.form-group label {
  font-size: 14px;
  font-weight: 500;
  color: #374151;
}

.form-input {
  padding: 12px 16px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 14px;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.form-input:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-input.error {
  border-color: #ef4444;
}

.error-message {
  font-size: 12px;
  color: #ef4444;
}

.form-actions {
  padding: 24px 32px;
  background: #f9fafb;
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin: 0 -32px -32px -32px;
}

.notification-settings {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.checkbox-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 14px;
  font-weight: 500;
  color: #374151;
  cursor: pointer;
}

.checkbox-label input[type="checkbox"] {
  margin: 0;
  width: 16px;
  height: 16px;
}

.setting-description {
  font-size: 13px;
  color: #6b7280;
  margin: 0;
  line-height: 1.4;
  margin-left: 28px;
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

.security-info {
  display: flex;
  flex-direction: column;
  gap: 16px;
  margin-bottom: 24px;
}

.security-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 0;
  border-bottom: 1px solid #f3f4f6;
}

.security-item:last-child {
  border-bottom: none;
}

.security-label {
  font-size: 14px;
  color: #6b7280;
  font-weight: 500;
}

.security-value {
  font-size: 14px;
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
  
  .info-grid, .form-grid, .stats-grid {
    grid-template-columns: 1fr;
  }
  
  .form-actions {
    flex-direction: column;
  }
  
  .security-item {
    flex-direction: column;
    align-items: flex-start;
    gap: 4px;
  }
}
</style>