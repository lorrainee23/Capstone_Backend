<template>
  <SidebarLayout page-title="Send Notification">
    <div class="send-notification-container">
      <!-- Header Section -->
      <div class="page-header">
        <div class="header-content">
          <div class="header-left">
            <router-link to="/admin/notifications" class="back-button">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="15,18 9,12 15,6"/>
              </svg>
            </router-link>
            <div class="header-text">
              <h1 class="page-title">Send Notification</h1>
              <p class="page-subtitle">Create and send notifications to system users</p>
            </div>
          </div>
          <div class="header-actions">
            <router-link to="/admin/notifications" class="btn btn-secondary">
              <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"/>
                <line x1="6" y1="6" x2="18" y2="18"/>
              </svg>
              Cancel
            </router-link>
          </div>
        </div>
      </div>

      <div class="form-container">
        <!-- Main Form -->
        <div class="form-card">
          <div class="form-header">
            <h2 class="form-title">Notification Details</h2>
            <p class="form-subtitle">Fill in the details below to send a notification</p>
          </div>

          <form @submit.prevent="handleSubmit" class="notification-form">
            <!-- Target Audience -->
            <div class="form-group">
              <label class="form-label required">Target Audience</label>
              <p class="form-description">Select which user group should receive this notification</p>
              <div class="radio-group">
                <label 
                  v-for="role in availableTargetRoles" 
                  :key="role.value"
                  class="radio-option"
                  :class="{ active: form.target_type === role.value }"
                >
                  <input 
                    type="radio" 
                    :value="role.value" 
                    v-model="form.target_type"
                    class="radio-input"
                  />
                  <div class="radio-content">
                    <div class="radio-icon" :class="{ active: form.target_type === role.value }">
                      <svg viewBox="0 0 24 24" fill="currentColor">
                        <path :d="role.icon"></path>
                      </svg>
                    </div>
                    <div class="radio-text">
                      <div class="radio-title">{{ role.label }}</div>
                      <div class="radio-description">{{ role.description }}</div>
                    </div>
                  </div>
                </label>
              </div>
              <div v-if="errors.target_type" class="form-error">{{ errors.target_type[0] }}</div>
            </div>

            <!-- Target Specific User -->
            <div class="form-group" v-if="form.target_type">
              <label for="target_id" class="form-label">Specific User (Optional)</label>
              <p class="form-description">Leave empty to send to all {{ getTargetLabel(form.target_type) }} users</p>
              <div class="input-wrapper">
                <select
                  id="target_id"
                  v-model="form.target_id"
                  class="form-input"
                >
                  <option value="">All {{ getTargetLabel(form.target_type) }}</option>
                  <option 
                    v-for="user in filteredUsers" 
                    :key="user.id" 
                    :value="user.id"
                  >
                    {{ user.first_name }} {{ user.last_name }} ({{ user.email }})
                  </option>
                </select>
              </div>
            </div>

            <!-- Notification Type -->
            <div class="form-group">
              <label class="form-label required">Notification Type</label>
              <p class="form-description">Choose the type of notification to determine its priority and appearance</p>
              <div class="type-selector">
                <label 
                  v-for="type in notificationTypes" 
                  :key="type.value"
                  class="type-option"
                  :class="{ active: form.type === type.value }"
                >
                  <input 
                    type="radio" 
                    :value="type.value" 
                    v-model="form.type"
                    class="type-input"
                  />
                  <div class="type-content">
                    <div class="type-icon" :class="`type-${type.value}`">
                      <svg viewBox="0 0 24 24" fill="currentColor">
                        <path :d="type.icon"></path>
                      </svg>
                    </div>
                    <div class="type-details">
                      <div class="type-title">{{ type.label }}</div>
                      <div class="type-description">{{ type.description }}</div>
                    </div>
                  </div>
                </label>
              </div>
              <div v-if="errors.type" class="form-error">{{ errors.type[0] }}</div>
            </div>

            <!-- Title -->
            <div class="form-group">
              <label for="title" class="form-label required">Title</label>
              <p class="form-description">A clear, concise title for your notification (max 100 characters)</p>
              <div class="input-wrapper">
                <input 
                  id="title"
                  type="text" 
                  v-model="form.title"
                  class="form-input"
                  :class="{ error: errors.title }"
                  placeholder="Enter notification title..."
                  maxlength="100"
                />
                <div class="character-count">{{ form.title.length }}/100</div>
              </div>
              <div v-if="errors.title" class="form-error">{{ errors.title[0] }}</div>
            </div>

            <!-- Message -->
            <div class="form-group">
              <label for="message" class="form-label required">Message</label>
              <p class="form-description">The detailed content of your notification</p>
              <textarea 
                id="message"
                v-model="form.message"
                class="form-textarea"
                :class="{ error: errors.message }"
                placeholder="Enter your notification message here..."
                rows="6"
              ></textarea>
              <div v-if="errors.message" class="form-error">{{ errors.message[0] }}</div>
            </div>

            <!-- Live Preview Section -->
            <div class="form-group">
              <label class="form-label">Live Preview</label>
              <p class="form-description">See how your notification will appear to users</p>
              <div class="preview-container">
                <div v-if="!form.title && !form.message && !form.type" class="preview-placeholder">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                    <circle cx="12" cy="12" r="3"/>
                  </svg>
                  <p>Preview will appear here as you type</p>
                </div>
                
                <div v-else class="notification-preview">
                  <div class="preview-notification" :class="`preview-${form.type}`">
                    <div class="notification-icon" v-if="form.type">
                      <svg viewBox="0 0 24 24" fill="currentColor">
                        <path :d="getTypeIcon(form.type)"></path>
                      </svg>
                    </div>
                    <div class="notification-body">
                      <h4 class="notification-title">
                        {{ form.title || 'Notification Title' }}
                      </h4>
                      <p class="notification-message">
                        {{ form.message || 'Your notification message will appear here...' }}
                      </p>
                      <div class="notification-meta">
                        <span class="meta-item">
                          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                          </svg>
                          {{ getTargetLabel(form.target_type) || 'Target Audience' }}
                          {{ form.target_id ? `(ID: ${form.target_id})` : '' }}
                        </span>
                        <span class="meta-item">
                          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="12,6 12,12 16,14"/>
                          </svg>
                          Just now
                        </span>
                      </div>
                    </div>
                    <div class="preview-badge" v-if="form.type" :class="`badge-${form.type}`">
                      {{ getTypeLabel(form.type) }}
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
              <button 
                type="button" 
                @click="showPreview = true"
                class="btn btn-secondary"
                :disabled="!isFormValid || sending"
              >
                <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                  <circle cx="12" cy="12" r="3"/>
                </svg>
                Preview
              </button>
              <button 
                type="submit" 
                class="btn btn-primary"
                :disabled="!isFormValid || sending"
              >
                <svg v-if="sending" class="btn-icon spinner" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
                </svg>
                <svg v-else class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M3 3h18l-9 15L3 3z"/>
                </svg>
                {{ sending ? 'Sending...' : 'Send Notification' }}
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Success Modal -->
      <div v-if="showSuccess" class="modal-overlay" @click="closeSuccessModal">
        <div class="modal-content success-modal" @click.stop>
          <div class="success-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
              <polyline points="22,4 12,14.01 9,11.01"/>
            </svg>
          </div>
          <h3>Notification Sent Successfully!</h3>
          <p>Your notification has been delivered to {{ getDeliveryMessage() }}.</p>
          <div class="modal-actions">
            <button @click="resetForm" class="btn btn-secondary">Send Another</button>
            <router-link to="/admin/notifications" class="btn btn-primary">View All Notifications</router-link>
          </div>
        </div>
      </div>

      <!-- Preview Modal -->
      <div v-if="showPreview" class="modal-overlay" @click="showPreview = false">
        <div class="modal-content preview-modal" @click.stop>
          <div class="modal-header">
            <h3>Notification Preview</h3>
            <button @click="showPreview = false" class="close-button">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"/>
                <line x1="6" y1="6" x2="18" y2="18"/>
              </svg>
            </button>
          </div>
          <div class="modal-body">
            <div class="preview-notification large" :class="`preview-${form.type}`">
              <div class="notification-icon">
                <svg viewBox="0 0 24 24" fill="currentColor">
                  <path :d="getTypeIcon(form.type)"></path>
                </svg>
              </div>
              <div class="notification-body">
                <h4 class="notification-title">{{ form.title }}</h4>
                <p class="notification-message">{{ form.message }}</p>
                <div class="notification-meta">
                  <span class="meta-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                      <circle cx="9" cy="7" r="4"/>
                      <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                      <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                    {{ getTargetLabel(form.target_type) }}
                    {{ form.target_id ? `(ID: ${form.target_id})` : '' }}
                  </span>
                  <span class="meta-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <circle cx="12" cy="12" r="10"/>
                      <polyline points="12,6 12,12 16,14"/>
                    </svg>
                    {{ new Date().toLocaleDateString('en-PH', { 
                      year: 'numeric', 
                      month: 'short', 
                      day: 'numeric',
                      hour: '2-digit',
                      minute: '2-digit'
                    }) }}
                  </span>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button @click="showPreview = false" class="btn btn-secondary">Close Preview</button>
            <button @click="showPreview = false; handleSubmit()" class="btn btn-primary">Send Notification</button>
          </div>
        </div>
      </div>
    </div>
  </SidebarLayout>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import SidebarLayout from '@/components/SidebarLayout.vue'
import { adminAPI } from '@/services/api'
import { useAuthStore } from '@/stores/auth'

export default {
  name: 'SendNotification',
  components: {
    SidebarLayout
  },
  setup() {
    const allUsers = ref({ admins: [], deputies: [], enforcers: [] })
    const router = useRouter()
    const authStore = useAuthStore()
    const sending = ref(false)
    const showSuccess = ref(false)
    const showPreview = ref(false)
    const errors = ref({})

    const form = ref({
      target_type: '',
      target_id: '',
      title: '',
      message: '',
      type: ''
    })

    const allTargetRoles = ref([
      {
        value: 'admin',
        label: 'Administrators',
        description: 'Administrators',
        icon: 'M12 14l9-5-9-5-9 5 9 5z M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z',
        allowedFor: ['admin'] 
      },
      {
        value: 'head',
        label: 'Department Heads',
        description: 'Department heads',
        icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
        allowedFor: [] 
      },
      {
        value: 'deputy',
        label: 'Deputy Officers',
        description: 'Deputy officers',
        icon: 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z',
        allowedFor: ['head', 'deputy'] 
      },
      {
        value: 'enforcer',
        label: 'Enforcers',
        description: 'Traffic Enforcer',
        icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
        allowedFor: ['admin', 'head', 'deputy']
      },
      {
        value: 'violator',
        label: 'Violators',
        description: 'Traffic Violators',
        icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
        allowedFor: ['admin', 'head', 'deputy']
      }
    ])

    const notificationTypes = ref([
      {
        value: 'info',
        label: 'Information',
        description: 'General information and updates',
        icon: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
      },
      {
        value: 'alert',
        label: 'Alert',
        description: 'Important announcements requiring attention',
        icon: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'
      },
      {
        value: 'reminder',
        label: 'Reminder',
        description: 'Friendly reminders and follow-ups',
        icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'
      },
      {
        value: 'warning',
        label: 'Warning',
        description: 'Critical warnings and urgent notices',
        icon: 'M12 9v2m0 4h.01M5 19h14a2 2 0 001.84-1.236l-7-14a2 2 0 00-3.68 0l-7 14A2 2 0 005 19z'
      }
    ])

    const availableTargetRoles = computed(() => {
      const currentUser = authStore.state.user
      if (!currentUser || !currentUser.role) {
        return []
      }

      const userRole = currentUser.role.toLowerCase()
      
      let allowedTargets = []
      
      switch(userRole) {
        case 'head':
          allowedTargets = ['deputy', 'enforcer']
          break
        case 'deputy':
          allowedTargets = ['admin', 'enforcer']  
          break
        case 'admin':
          allowedTargets = ['admin', 'enforcer']
          break
        default:
          allowedTargets = []
      }
      
      return allTargetRoles.value.filter(role => 
        allowedTargets.includes(role.value)
      )
    })

    const isFormValid = computed(() => {
      return form.value.target_type && 
             form.value.title.trim() && 
             form.value.message.trim() && 
             form.value.type
    })

    const handleSubmit = async () => {
      if (!isFormValid.value || sending.value) return
      
      try {
        sending.value = true
        errors.value = {}
        
        const currentUser = authStore.state.user
        
        const notificationData = {
          sender_id: currentUser.id,
          sender_role: currentUser.role,
          target_type: form.value.target_type,
          target_id: form.value.target_id || null,
          title: form.value.title.trim(),
          message: form.value.message.trim(),
          type: form.value.type
        }
        
        await adminAPI.sendNotification(notificationData)
        
        showSuccess.value = true
      } catch (error) {
        if (error.response?.status === 422) {
          errors.value = error.response.data.errors || {}
        } else {
          console.error('Failed to send notification:', error)
        }
      } finally {
        sending.value = false
      }
    }

    const resetForm = () => {
      form.value = {
        target_type: '',
        target_id: '',
        title: '',
        message: '',
        type: ''
      }
      errors.value = {}
      showSuccess.value = false
    }

    const closeSuccessModal = () => {
      showSuccess.value = false
      router.push('/admin/notifications')
    }

    const getTargetLabel = (targetType) => {
      const targetData = allTargetRoles.value.find(r => r.value === targetType)
      return targetData?.label || ''
    }

    const getTypeLabel = (type) => {
      const typeData = notificationTypes.value.find(t => t.value === type)
      return typeData?.label || ''
    }

    const getTypeIcon = (type) => {
      const typeData = notificationTypes.value.find(t => t.value === type)
      return typeData?.icon || 'M15 17h5l-5 5v-5z'
    }

    const getDeliveryMessage = () => {
      const targetLabel = getTargetLabel(form.value.target_type)
      if (form.value.target_id) {
        return `the specific ${targetLabel.slice(0, -1).toLowerCase()} (ID: ${form.value.target_id})`
      }
      return `all ${targetLabel} users`
    }

    const filteredUsers = computed(() => {
    if (!form.value.target_type) return []
    switch (form.value.target_type) {
      case 'admin': return allUsers.value.admins
      case 'deputy': return allUsers.value.deputies
      case 'enforcer': return allUsers.value.enforcers
      default: return []
    }
  })

    onMounted(() => {
      const currentUser = authStore.state.user
      if (!currentUser) {
        router.push('/login')
        return
      }
      })

      onMounted(async () => {
        const { data } = await adminAPI.getAllUsers()
        allUsers.value = data
      })
      
    return {
      form,
      errors,
      sending,
      showSuccess,
      showPreview,
      availableTargetRoles,
      notificationTypes,
      isFormValid,
      handleSubmit,
      resetForm,
      closeSuccessModal,
      getTargetLabel,
      getTypeLabel,
      getTypeIcon,
      getDeliveryMessage,
      filteredUsers,
    }
  }
}
</script>

<style scoped>
/* Page Header */
.page-header {
  background: linear-gradient(135deg, #1e3a8a, #3b82f6);
  border-radius: 16px;
  padding: 32px;
  margin-bottom: 32px;
  color: white;
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 16px;
}

.back-button {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  background: rgba(255, 255, 255, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.3);
  border-radius: 8px;
  color: white;
  text-decoration: none;
  transition: all 0.2s ease;
  backdrop-filter: blur(10px);
}

.back-button:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: translateY(-2px);
}

.back-button svg {
  width: 20px;
  height: 20px;
}

.page-title {
  font-size: 32px;
  font-weight: 700;
  margin: 0 0 8px 0;
}

.page-subtitle {
  font-size: 16px;
  opacity: 0.9;
  margin: 0;
}

.header-actions .btn {
  background: rgba(255, 255, 255, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.3);
  color: white;
  backdrop-filter: blur(10px);
}

.header-actions .btn:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: translateY(-2px);
}

/* Form Container */
.form-container {
  display: block;
  width: 100%;
}

/* Form Card */
.form-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  width: 100%;
}

.form-header {
  padding: 24px 32px;
  border-bottom: 1px solid #e5e7eb;
}

.form-title {
  font-size: 20px;
  font-weight: 600;
  color: #1f2937;
  margin: 0 0 8px 0;
}

.form-subtitle {
  color: #6b7280;
  margin: 0;
}

.notification-form {
  padding: 32px;
}

/* Form Groups */
.form-group {
  margin-bottom: 32px;
}

.form-group:last-child {
  margin-bottom: 0;
}

.form-label {
  display: block;
  font-size: 16px;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 4px;
}

.form-label.required::after {
  content: ' *';
  color: #ef4444;
}

.form-description {
  font-size: 14px;
  color: #6b7280;
  margin-bottom: 16px;
}

/* Radio Group */
.radio-group {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.radio-option {
  display: flex;
  cursor: pointer;
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  padding: 16px;
  transition: all 0.2s ease;
}

.radio-option:hover {
  border-color: #d1d5db;
  background: #f9fafb;
}

.radio-option.active {
  border-color: #3b82f6;
  background: #eff6ff;
}

.radio-input {
  display: none;
}

.radio-content {
  display: flex;
  align-items: center;
  gap: 12px;
  width: 100%;
}

.radio-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: white;
  color: #3b82f6;
  transition: all 0.2s ease;
}

.radio-icon.active {
  background: #3b82f6;
  color: white;
}

.radio-icon svg {
  width: 24px;
  height: 24px;
}

.radio-title {
  font-size: 16px;
  font-weight: 600;
  color: #1f2937;
}

.radio-description {
  font-size: 14px;
  color: #6b7280;
}

/* Type Selector */
.type-selector {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 12px;
}

.type-option {
  display: flex;
  cursor: pointer;
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  padding: 16px;
  transition: all 0.2s ease;
}

.type-option:hover {
  border-color: #d1d5db;
  background: #f9fafb;
}

.type-option.active {
  border-color: #3b82f6;
  background: #eff6ff;
}

.type-input {
  display: none;
}

.type-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  gap: 8px;
  width: 100%;
}

.type-icon {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
}

.type-icon.type-info { 
  background: #60a5fa; /* Light blue */
}
.type-icon.type-alert { 
  background: #f87171; /* Light red */
}
.type-icon.type-reminder { 
  background: #fb923c; /* Light orange */
}
.type-icon.type-warning { 
  background: #dc2626; /* Red */
}

.type-icon svg {
  width: 20px;
  height: 20px;
}

.type-title {
  font-size: 14px;
  font-weight: 600;
  color: #1f2937;
}

.type-description {
  font-size: 12px;
  color: #6b7280;
  line-height: 1.3;
}

/* Form Inputs */
.input-wrapper {
  position: relative;
}

.form-input {
  width: 100%;
  padding: 16px;
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  font-size: 16px;
  transition: all 0.2s ease;
  background: white;
}

.form-input:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
  outline: none;
}

.form-input.error {
  border-color: #ef4444;
}

.character-count {
  position: absolute;
  right: 16px;
  top: 50%;
  transform: translateY(-50%);
  font-size: 12px;
  color: #6b7280;
  background: white;
  padding: 0 4px;
}

.form-textarea {
  width: 100%;
  padding: 16px;
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  font-size: 16px;
  font-family: inherit;
  resize: vertical;
  min-height: 120px;
  transition: all 0.2s ease;
  background: white;
}

.form-textarea:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
  outline: none;
}

.form-textarea.error {
  border-color: #ef4444;
}

.form-error {
  color: #ef4444;
  font-size: 14px;
  margin-top: 8px;
}

/* Live Preview Container */
.preview-container {
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  background: #f8fafc;
  overflow: hidden;
  width: 100%;
}

.preview-placeholder {
  text-align: center;
  color: #9ca3af;
  padding: 40px 20px;
}

.preview-placeholder svg {
  width: 48px;
  height: 48px;
  margin-bottom: 16px;
}

.preview-placeholder p {
  margin: 0;
}

/* Notification Preview */
.notification-preview {
  padding: 20px;
  width: 100%;
}

.preview-notification {
  padding: 20px;
  display: flex;
  gap: 16px;
  align-items: flex-start;
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
  position: relative;
  border-left: 4px solid transparent;
  width: 100%;
}

.preview-notification.large {
  padding: 24px;
}

.preview-info { 
  border-left-color: #60a5fa;
}
.preview-alert { 
  border-left-color: #f87171;
}
.preview-reminder { 
  border-left-color: #fb923c;
}
.preview-warning { 
  border-left-color: #dc2626;
}

.notification-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  color: white;
}

.preview-info .notification-icon { 
  background: #60a5fa;
}
.preview-alert .notification-icon { 
  background: #f87171;
}
.preview-reminder .notification-icon { 
  background: #fb923c;
}
.preview-warning .notification-icon { 
  background: #dc2626;
}

.notification-icon svg {
  width: 24px;
  height: 24px;
}

.notification-body {
  flex: 1;
  min-width: 0;
}

.notification-title {
  font-size: 18px;
  font-weight: 600;
  color: #1f2937;
  margin: 0 0 8px 0;
  line-height: 1.4;
}

.notification-message {
  color: #6b7280;
  line-height: 1.5;
  margin: 0 0 16px 0;
}

.notification-meta {
  display: flex;
  gap: 16px;
  align-items: center;
  flex-wrap: wrap;
}

.meta-item {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  color: #9ca3af;
}

.meta-item svg {
  width: 16px;
  height: 16px;
}

.preview-badge {
  position: absolute;
  top: 16px;
  right: 16px;
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.badge-info { 
  background: #60a5fa; 
  color: white; 
}
.badge-alert { 
  background: #f87171; 
  color: white; 
}
.badge-reminder { 
  background: #fb923c; 
  color: white; 
}
.badge-warning { 
  background: #dc2626; 
  color: white; 
}

/* Form Actions */
.form-actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
  padding-top: 24px;
  border-top: 1px solid #e5e7eb;
  margin-top: 32px;
}

/* Buttons */
.btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 12px 20px;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  text-decoration: none;
  white-space: nowrap;
  justify-content: center;
}

.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-icon {
  width: 16px;
  height: 16px;
  flex-shrink: 0;
}

.btn-primary {
  background: linear-gradient(135deg, #3b82f6, #1d4ed8);
  color: white;
  box-shadow: 0 2px 4px rgba(59, 130, 246, 0.4);
}

.btn-primary:hover:not(:disabled) {
  background: linear-gradient(135deg, #2563eb, #1e40af);
  transform: translateY(-1px);
  box-shadow: 0 4px 8px rgba(59, 130, 246, 0.4);
}

.btn-secondary {
  background: #f3f4f6;
  color: #374151;
  border: 1px solid #d1d5db;
}

.btn-secondary:hover:not(:disabled) {
  background: #e5e7eb;
  border-color: #9ca3af;
  transform: translateY(-1px);
}

.spinner {
  animation: spin 1s linear infinite;
}

/* Modals */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 20px;
}

.modal-content {
  background: white;
  border-radius: 16px;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  max-width: 600px;
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
}

.success-modal {
  padding: 40px;
  text-align: center;
}

.success-icon {
  width: 80px;
  height: 80px;
  background: linear-gradient(135deg, #10b981, #059669);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 24px;
  color: white;
}

.success-icon svg {
  width: 40px;
  height: 40px;
}

.success-modal h3 {
  font-size: 24px;
  font-weight: 700;
  color: #1f2937;
  margin: 0 0 12px 0;
}

.success-modal p {
  color: #6b7280;
  margin: 0 0 32px 0;
  font-size: 16px;
}

.modal-actions {
  display: flex;
  gap: 12px;
  justify-content: center;
}

.preview-modal {
  max-width: 700px;
}

.modal-header {
  padding: 24px 32px;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h3 {
  font-size: 20px;
  font-weight: 600;
  color: #1f2937;
  margin: 0;
}

.close-button {
  background: none;
  border: none;
  cursor: pointer;
  padding: 8px;
  border-radius: 6px;
  color: #6b7280;
  transition: all 0.2s ease;
}

.close-button:hover {
  background: #f3f4f6;
  color: #374151;
}

.close-button svg {
  width: 20px;
  height: 20px;
}

.modal-body {
  padding: 32px;
}

.modal-footer {
  padding: 24px 32px;
  border-top: 1px solid #e5e7eb;
  display: flex;
  gap: 12px;
  justify-content: flex-end;
}

/* Animations */
@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

/* Responsive Design */
@media (max-width: 1024px) {
  .send-notification-container {
    padding: 0 20px;
  }
  
  .header-content {
    flex-direction: column;
    align-items: flex-start;
    gap: 16px;
  }
  
  .header-left {
    width: 100%;
  }
  
  .form-actions {
    flex-direction: column-reverse;
  }
  
  .type-selector {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .send-notification-container {
    padding: 0 16px;
  }
  
  .page-header {
    padding: 24px;
  }
  
  .page-title {
    font-size: 24px;
  }
  
  .notification-form {
    padding: 24px;
  }
  
  .form-header {
    padding: 20px 24px;
  }
  
  .modal-content {
    margin: 20px;
    max-width: calc(100% - 40px);
  }
  
  .success-modal {
    padding: 32px 24px;
  }
  
  .modal-header {
    padding: 20px 24px;
  }
  
  .modal-body {
    padding: 24px;
  }
  
  .modal-footer {
    padding: 20px 24px;
    flex-direction: column;
  }
  
  .modal-actions {
    flex-direction: column;
  }
  
  .notification-meta {
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
  }
}

@media (max-width: 480px) {
  .send-notification-container {
    padding: 0 12px;
  }
  
  .radio-option {
    padding: 12px;
  }
  
  .radio-icon {
    width: 40px;
    height: 40px;
  }
  
  .radio-icon svg {
    width: 20px;
    height: 20px;
  }
  
  .type-option {
    padding: 12px;
  }
  
  .type-icon {
    width: 32px;
    height: 32px;
  }
  
  .type-icon svg {
    width: 16px;
    height: 16px;
  }
  
  .form-input,
  .form-textarea {
    padding: 12px;
    font-size: 14px;
  }
  
  .character-count {
    right: 12px;
  }
  
  .preview-notification {
    padding: 16px;
    gap: 12px;
  }
  
  .notification-icon {
    width: 40px;
    height: 40px;
  }
  
  .notification-icon svg {
    width: 20px;
    height: 20px;
  }
  
  .notification-title {
    font-size: 16px;
  }
  
  .preview-badge {
    position: relative;
    top: auto;
    right: auto;
    margin-top: 12px;
    align-self: flex-start;
  }
}
</style>