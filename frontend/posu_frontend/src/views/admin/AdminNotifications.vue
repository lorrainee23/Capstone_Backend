<template>
  <SidebarLayout page-title="Notifications">
    <div class="admin-notifications">
      <!-- Header -->
      <div class="page-header">
        <div class="header-left">
          <h2>Send Notifications</h2>
          <p>Broadcast messages to system users</p>
        </div>
      </div>

      <!-- Send Notification Form -->
      <div class="notification-card">
        <div class="card-header">
          <h3>Compose New Notification</h3>
        </div>
        <div class="card-body">
          <form @submit.prevent="sendNotification" class="notification-form">
            <div v-if="error" class="alert alert-error">{{ error }}</div>
            <div v-if="success" class="alert alert-success">{{ success }}</div>

            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Target Audience *</label>
                <select v-model="notificationForm.target_role" class="form-select" required>
                  <option value="">Select Target</option>
                  <option value="Admin">Administrators</option>
                  <option value="Enforcer">Enforcers</option>
                  <option value="Violator">Violators</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">Notification Type *</label>
                <select v-model="notificationForm.type" class="form-select" required>
                  <option value="">Select Type</option>
                  <option value="info">Information</option>
                  <option value="alert">Alert</option>
                  <option value="reminder">Reminder</option>
                  <option value="warning">Warning</option>
                </select>
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">Title *</label>
              <input 
                v-model="notificationForm.title" 
                type="text" 
                class="form-input" 
                placeholder="Enter notification title..."
                maxlength="100"
                required 
              />
              <div class="form-help">Maximum 100 characters</div>
            </div>

            <div class="form-group">
              <label class="form-label">Message *</label>
              <textarea 
                v-model="notificationForm.message" 
                class="form-input" 
                rows="6"
                placeholder="Enter your message here..."
                required
              ></textarea>
              <div class="form-help">{{ notificationForm.message.length }} characters</div>
            </div>

            <div class="form-group">
              <div class="notification-preview" v-if="notificationForm.title || notificationForm.message">
                <h4>Preview</h4>
                <div class="preview-notification" :class="`preview-${notificationForm.type}`">
                  <div class="preview-header">
                    <span class="preview-type">{{ getTypeLabel(notificationForm.type) }}</span>
                    <span class="preview-target">To: {{ notificationForm.target_role || 'Select target' }}</span>
                  </div>
                  <div class="preview-title">{{ notificationForm.title || 'Enter title...' }}</div>
                  <div class="preview-message">{{ notificationForm.message || 'Enter message...' }}</div>
                </div>
              </div>
            </div>

            <div class="form-actions">
              <button type="button" @click="resetForm" class="btn btn-secondary">Clear Form</button>
              <button type="submit" class="btn btn-primary" :disabled="sending">
                <span v-if="sending" class="spinner-small"></span>
                {{ sending ? 'Sending...' : 'Send Notification' }}
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Quick Templates -->
      <div class="templates-section">
        <h3>Quick Templates</h3>
        <div class="templates-grid">
          <div 
            v-for="template in templates" 
            :key="template.id"
            @click="useTemplate(template)"
            class="template-card"
          >
            <div class="template-icon" :class="`icon-${template.type}`">
              {{ getTypeIcon(template.type) }}
            </div>
            <div class="template-content">
              <div class="template-title">{{ template.title }}</div>
              <div class="template-desc">{{ template.description }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Notifications -->
      <div class="recent-notifications">
        <div class="card-header">
          <h3>Recent Notifications</h3>
        </div>
        <div class="notifications-list">
          <div v-if="recentNotifications.length === 0" class="no-data">
            <div class="no-data-icon">🔔</div>
            <p>No notifications sent yet</p>
          </div>
          <div v-else>
            <div v-for="notification in recentNotifications" :key="notification.id" class="notification-item">
              <div class="notification-info">
                <div class="notification-header">
                  <span class="notification-type" :class="`type-${notification.type}`">
                    {{ getTypeLabel(notification.type) }}
                  </span>
                  <span class="notification-target">{{ notification.target_role }}</span>
                  <span class="notification-date">{{ formatDate(notification.created_at) }}</span>
                </div>
                <div class="notification-title">{{ notification.title }}</div>
                <div class="notification-message">{{ truncateText(notification.message, 100) }}</div>
              </div>
              <div class="notification-actions">
                <button @click="viewNotification(notification)" class="btn btn-secondary btn-sm">
                  View
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </SidebarLayout>
</template>

<script>
import { ref, onMounted } from 'vue'
import SidebarLayout from '@/components/SidebarLayout.vue'
import { adminAPI } from '@/services/api'

export default {
  name: 'AdminNotifications',
  components: {
    SidebarLayout
  },
  setup() {
    const sending = ref(false)
    const error = ref('')
    const success = ref('')
    const recentNotifications = ref([])
    
    const notificationForm = ref({
      target_role: '',
      type: '',
      title: '',
      message: ''
    })
    
    const templates = ref([
      {
        id: 1,
        type: 'info',
        title: 'System Maintenance',
        description: 'Scheduled maintenance notification',
        content: {
          title: 'Scheduled System Maintenance',
          message: 'The POSU Echague system will undergo scheduled maintenance on [DATE] from [TIME] to [TIME]. During this period, the system may be temporarily unavailable. We apologize for any inconvenience.'
        }
      },
      {
        id: 2,
        type: 'reminder',
        title: 'Payment Reminder',
        description: 'Payment due reminder for violators',
        content: {
          title: 'Payment Reminder',
          message: 'This is a friendly reminder that you have pending violation payments. Please settle your outstanding fines to avoid additional penalties. You can view your violations and make payments through your dashboard.'
        }
      },
      {
        id: 3,
        type: 'alert',
        title: 'Policy Update',
        description: 'Important policy changes',
        content: {
          title: 'Important Policy Update',
          message: 'We have updated our traffic violation policies effective [DATE]. Please review the new guidelines in your dashboard. These changes may affect fine amounts and enforcement procedures.'
        }
      },
      {
        id: 4,
        type: 'warning',
        title: 'Account Security',
        description: 'Security warning notification',
        content: {
          title: 'Account Security Alert',
          message: 'We have detected unusual activity on your account. If this was not you, please change your password immediately and contact support. Always keep your login credentials secure.'
        }
      }
    ])
    
    const sendNotification = async () => {
      try {
        sending.value = true
        error.value = ''
        success.value = ''
        
        const response = await adminAPI.sendNotification(notificationForm.value)
        
        if (response.data.success) {
          success.value = 'Notification sent successfully!'
          resetForm()
          // Add to recent notifications
          recentNotifications.value.unshift({
            ...notificationForm.value,
            id: Date.now(),
            created_at: new Date().toISOString()
          })
        } else {
          error.value = response.data.message || 'Failed to send notification'
        }
      } catch (err) {
        error.value = err.response?.data?.message || 'Failed to send notification'
      } finally {
        sending.value = false
      }
    }
    
    const resetForm = () => {
      notificationForm.value = {
        target_role: '',
        type: '',
        title: '',
        message: ''
      }
      error.value = ''
      success.value = ''
    }
    
    const useTemplate = (template) => {
      notificationForm.value = {
        target_role: template.type === 'reminder' ? 'Violator' : '',
        type: template.type,
        title: template.content.title,
        message: template.content.message
      }
    }
    
    const viewNotification = (notification) => {
      alert(`Title: ${notification.title}\n\nMessage: ${notification.message}`)
    }
    
    const getTypeLabel = (type) => {
      const labels = {
        info: 'Information',
        alert: 'Alert',
        reminder: 'Reminder',
        warning: 'Warning'
      }
      return labels[type] || type
    }
    
    const getTypeIcon = (type) => {
      const icons = {
        info: 'ℹ️',
        alert: '🚨',
        reminder: '⏰',
        warning: '⚠️'
      }
      return icons[type] || '📢'
    }
    
    const formatDate = (dateString) => {
      if (!dateString) return ''
      return new Date(dateString).toLocaleDateString('en-PH', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
    }
    
    const truncateText = (text, maxLength) => {
      if (text.length <= maxLength) return text
      return text.substring(0, maxLength) + '...'
    }
    
    onMounted(() => {
      // Load recent notifications if available
    })
    
    return {
      sending,
      error,
      success,
      notificationForm,
      templates,
      recentNotifications,
      sendNotification,
      resetForm,
      useTemplate,
      viewNotification,
      getTypeLabel,
      getTypeIcon,
      formatDate,
      truncateText
    }
  }
}
</script>

<style scoped>
.admin-notifications {
  max-width: 1400px;
  margin: 0 auto;
}

.page-header {
  margin-bottom: 32px;
}

.page-header h2 {
  font-size: 28px;
  font-weight: 700;
  color: #1f2937;
  margin: 0 0 4px 0;
}

.page-header p {
  color: #6b7280;
  margin: 0;
}

.notification-card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  margin-bottom: 32px;
  overflow: hidden;
}

.card-header {
  padding: 24px;
  border-bottom: 1px solid #e5e7eb;
}

.card-header h3 {
  font-size: 18px;
  font-weight: 600;
  color: #1f2937;
  margin: 0;
}

.card-body {
  padding: 24px;
}

.notification-form .form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
  margin-bottom: 20px;
}

.form-help {
  font-size: 12px;
  color: #6b7280;
  margin-top: 4px;
}

.notification-preview {
  margin-top: 20px;
}

.notification-preview h4 {
  font-size: 16px;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 12px;
}

.preview-notification {
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  padding: 16px;
  background: #f9fafb;
}

.preview-info { border-color: #3b82f6; }
.preview-alert { border-color: #ef4444; }
.preview-reminder { border-color: #f59e0b; }
.preview-warning { border-color: #f97316; }

.preview-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
  font-size: 12px;
}

.preview-type {
  background: #e5e7eb;
  padding: 2px 8px;
  border-radius: 12px;
  font-weight: 500;
}

.preview-target {
  color: #6b7280;
}

.preview-title {
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 8px;
}

.preview-message {
  color: #4b5563;
  line-height: 1.5;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 24px;
}

.templates-section {
  margin-bottom: 32px;
}

.templates-section h3 {
  font-size: 20px;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 20px;
}

.templates-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 20px;
}

.template-card {
  background: white;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  gap: 16px;
}

.template-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 15px -3px rgba(0, 0, 0, 0.1);
}

.template-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
}

.icon-info { background: #dbeafe; }
.icon-alert { background: #fee2e2; }
.icon-reminder { background: #fef3c7; }
.icon-warning { background: #fed7aa; }

.template-title {
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 4px;
}

.template-desc {
  font-size: 14px;
  color: #6b7280;
}

.recent-notifications {
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.notifications-list {
  padding: 24px;
}

.notification-item {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  padding: 16px 0;
  border-bottom: 1px solid #f3f4f6;
}

.notification-item:last-child {
  border-bottom: none;
}

.notification-header {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 8px;
  font-size: 12px;
}

.notification-type {
  padding: 2px 8px;
  border-radius: 12px;
  font-weight: 500;
  text-transform: uppercase;
}

.type-info { background: #dbeafe; color: #1e40af; }
.type-alert { background: #fee2e2; color: #dc2626; }
.type-reminder { background: #fef3c7; color: #d97706; }
.type-warning { background: #fed7aa; color: #ea580c; }

.notification-target {
  color: #6b7280;
}

.notification-date {
  color: #9ca3af;
}

.notification-title {
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 4px;
}

.notification-message {
  color: #6b7280;
  line-height: 1.4;
}

.btn-sm {
  padding: 8px 16px;
  font-size: 14px;
}

.no-data {
  text-align: center;
  padding: 40px;
}

.no-data-icon {
  font-size: 48px;
  margin-bottom: 16px;
}

.no-data p {
  color: #6b7280;
  margin: 0;
}

.spinner-small {
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top: 2px solid white;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-right: 8px;
}

@media (max-width: 768px) {
  .notification-form .form-row {
    grid-template-columns: 1fr;
  }
  
  .templates-grid {
    grid-template-columns: 1fr;
  }
  
  .template-card {
    flex-direction: column;
    text-align: center;
  }
  
  .notification-item {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }
  
  .form-actions {
    flex-direction: column;
  }
}
</style>
