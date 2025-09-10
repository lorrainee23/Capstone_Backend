<template>
  <ion-page>
    <AppHeader title="Profile"/>
    <ion-content :fullscreen="true" class="ion-padding custom-content">
      <!-- Profile Picture Section -->
      <ion-card class="profile-card">
        <ion-card-content>
          <div class="profile-picture-section">
            <div class="image-container">
              <img 
                :src="profileData.image || '/assets/photos.png'" 
                alt="Profile Picture"
                class="profile-image"
                @error="handleImageError"
              />
              <div class="image-overlay" @click="selectImage">
                <ion-icon :icon="camera" class="camera-icon"></ion-icon>
              </div>
              <input 
                ref="imageInput" 
                type="file" 
                accept="image/*" 
                @change="handleImageChange"
                style="display: none"
              />
            </div>
            <div class="profile-info">
              <h2>{{ profileData.full_name || 'Loading...' }}</h2>
              <p>{{ profileData.email }}</p>
              <ion-badge :color="profileData.status === 'active' ? 'success' : 'medium'">
                {{ profileData.status || 'Unknown' }}
              </ion-badge>
            </div>
          </div>
        </ion-card-content>
      </ion-card>

      <!-- Profile Form -->
      <div class="section-header">
        <h2>Personal Information</h2>
      </div>

      <ion-card class="form-card">
        <ion-card-content>
          <form @submit.prevent="updateProfile">
            <ion-item class="form-item">
              <ion-label position="stacked">First Name</ion-label>
              <ion-input
                v-model="formData.first_name"
                placeholder="Enter your first name"
                :class="{ 'ion-invalid': errors.first_name }"
                @ionInput="formData.first_name = $event.target.value"
              ></ion-input>
              <ion-note v-if="errors.first_name" slot="error" color="danger">
                {{ errors.first_name[0] }}
              </ion-note>
            </ion-item>

            <ion-item class="form-item">
              <ion-label position="stacked">Middle Name</ion-label>
              <ion-input
                v-model="formData.middle_name"
                placeholder="Enter your middle name (optional)"
              ></ion-input>
            </ion-item>

            <ion-item class="form-item">
              <ion-label position="stacked">Last Name</ion-label>
              <ion-input
                v-model="formData.last_name"
                placeholder="Enter your last name"
                :class="{ 'ion-invalid': errors.last_name }"
              ></ion-input>
              <ion-note v-if="errors.last_name" slot="error" color="danger">
                {{ errors.last_name[0] }}
              </ion-note>
            </ion-item>

            <ion-item class="form-item" disabled>
              <ion-label position="stacked">Username</ion-label>
              <ion-input
                :value="profileData.username"
                readonly
                class="readonly-input"
              ></ion-input>
              <ion-note slot="helper">Username cannot be changed</ion-note>
            </ion-item>

            <ion-item class="form-item" disabled>
              <ion-label position="stacked">Email</ion-label>
              <ion-input
                :value="profileData.email"
                readonly
                class="readonly-input"
              ></ion-input>
              <ion-note slot="helper">Email cannot be changed</ion-note>
            </ion-item>

            <div class="form-actions">
              <ion-button
                expand="block"
                type="submit"
                :disabled="isUpdating"
                class="action-btn"
                color="primary"
              >
                <ion-icon :icon="save" slot="start"></ion-icon>
                {{ isUpdating ? 'Updating...' : 'Update Profile' }}
              </ion-button>
            </div>
          </form>
        </ion-card-content>
      </ion-card>

      <!-- Password Change Section -->
      <div class="section-header">
        <h2>Security Settings</h2>
      </div>

      <ion-card class="form-card">
        <ion-card-content>
          <form @submit.prevent="changePassword">
            <ion-item class="form-item">
              <ion-label position="stacked">Current Password</ion-label>
              <ion-input
                v-model="passwordData.current_password"
                type="password"
                placeholder="Enter current password"
                :class="{ 'ion-invalid': passwordErrors.current_password }"
              ></ion-input>
              <ion-note v-if="passwordErrors.current_password" slot="error" color="danger">
                {{ passwordErrors.current_password[0] }}
              </ion-note>
            </ion-item>

            <ion-item class="form-item">
              <ion-label position="stacked">New Password</ion-label>
              <ion-input
                v-model="passwordData.new_password"
                type="password"
                placeholder="Enter new password"
                :class="{ 'ion-invalid': passwordErrors.new_password }"
              ></ion-input>
              <ion-note v-if="passwordErrors.new_password" slot="error" color="danger">
                {{ passwordErrors.new_password[0] }}
              </ion-note>
            </ion-item>

            <ion-item class="form-item">
              <ion-label position="stacked">Confirm New Password</ion-label>
              <ion-input
                v-model="passwordData.new_password_confirmation"
                type="password"
                placeholder="Confirm new password"
              ></ion-input>
            </ion-item>

            <div class="form-actions">
              <ion-button
                expand="block"
                type="submit"
                :disabled="isChangingPassword"
                class="action-btn"
                color="secondary"
              >
                <ion-icon :icon="lockClosed" slot="start"></ion-icon>
                {{ isChangingPassword ? 'Changing...' : 'Change Password' }}
              </ion-button>
            </div>
          </form>
        </ion-card-content>
      </ion-card>

      <!-- Account Info -->
      <ion-card class="info-card">
        <ion-card-content>
          <div class="account-info">
            <div class="info-item">
              <ion-icon :icon="calendar" class="info-icon"></ion-icon>
              <div>
                <h4>Account Created</h4>
                <p>{{ formatDate(profileData.created_at) }}</p>
              </div>
            </div>
            <div class="info-item">
              <ion-icon :icon="refresh" class="info-icon"></ion-icon>
              <div>
                <h4>Last Updated</h4>
                <p>{{ formatDate(profileData.updated_at) }}</p>
              </div>
            </div>
          </div>
        </ion-card-content>
      </ion-card>

      <!-- Logout Button -->
      <div class="form-actions">
        <ion-button
          expand="block"
          color="danger"
          @click="logout"
        >
          <ion-icon :icon="logOut" slot="start"></ion-icon>
          Logout
        </ion-button>
      </div>

    </ion-content>
  </ion-page>
</template>

<script setup>
import { ref, onMounted, reactive } from 'vue'
import AppHeader from "@/components/AppHeader.vue"
import {
  IonPage,
  IonContent,
  IonButton,
  IonCard,
  IonCardContent,
  IonIcon,
  IonBadge,
  IonItem,
  IonLabel,
  IonInput,
  IonNote,
  alertController,
  toastController
} from '@ionic/vue'
import {
  camera,
  save,
  lockClosed,
  calendar,
  refresh,
  logOut
} from 'ionicons/icons'
import { enforcerAPI, authAPI } from '@/services/api.js'

const imageInput = ref(null)

const profileData = reactive({
  id: null,
  first_name: '',
  middle_name: '',
  last_name: '',
  full_name: '',
  username: '',
  email: '',
  image: null,
  status: '',
  created_at: '',
  updated_at: ''
})

const formData = reactive({
  first_name: '',
  middle_name: '',
  last_name: '',
  image: null
})

const passwordData = reactive({
  current_password: '',
  new_password: '',
  new_password_confirmation: ''
})

const errors = ref({})
const passwordErrors = ref({})
const isUpdating = ref(false)
const isChangingPassword = ref(false)

// Show toast notification
const showToast = async (message = 'primary') => {
  const toast = await toastController.create({
    message,
    duration: 3000,
    position: 'bottom',
    cssClass: 'custom-toast'
  })
  await toast.present()
}

// Show alert dialog
const showAlert = async (header, message) => {
  const alert = await alertController.create({
    header,
    message,
    buttons: ['OK']
  })
  await alert.present()
}

// Format date
const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString([], {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// Handle image error
const handleImageError = (event) => {
  event.target.src = '/assets/photo.png'
}

// Select image
const selectImage = () => {
  imageInput.value.click()
}

// Handle image change
const handleImageChange = (event) => {
  const file = event.target.files[0]
  if (file) {
    // Validate file size (2MB max)
    if (file.size > 2048 * 1024) {
      showToast('Image size must be less than 2MB', 'danger')
      return
    }

    // Validate file type
    const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif']
    if (!allowedTypes.includes(file.type)) {
      showToast('Please select a valid image file (JPEG, PNG, JPG, GIF)', 'danger')
      return
    }

    formData.image = file

    // Preview image
    const reader = new FileReader()
    reader.onload = (e) => {
      profileData.image = e.target.result
    }
    reader.readAsDataURL(file)
  }
}

// Load profile data
const loadProfile = async () => {
  try {
    const response = await enforcerAPI.getProfile()
    const data = response.data.data
    Object.assign(profileData, data)
    
    formData.first_name = data.first_name
    formData.middle_name = data.middle_name || ''
    formData.last_name = data.last_name
  } catch (error) {
    console.error('Profile load error:', error)
    showAlert('Error', 'Failed to load profile data')
  }
}

const updateProfile = async () => {
  errors.value = {}
  isUpdating.value = true

  try {
    const formDataToSend = new FormData()
    formDataToSend.append('first_name', formData.first_name)
    formDataToSend.append('middle_name', formData.middle_name)
    formDataToSend.append('last_name', formData.last_name)
    
    if (formData.image) formDataToSend.append('image', formData.image)

    const response = await enforcerAPI.updateProfile(formDataToSend)
    
    profileData.first_name = formData.first_name
    profileData.middle_name = formData.middle_name
    profileData.last_name = formData.last_name
    profileData.full_name = `${formData.first_name} ${formData.middle_name} ${formData.last_name}`.replace(/\s+/g, ' ').trim()
    
    showToast(response.data.message || 'Profile updated successfully!', 'success')
  } catch (error) {
    console.error('Profile update error:', error)
    if (error.response?.data?.errors) errors.value = error.response.data.errors
    else showAlert('Error', error.response?.data?.message || 'Failed to update profile')
  } finally {
    isUpdating.value = false
  }
}

// Change password
const changePassword = async () => {
  passwordErrors.value = {}
  isChangingPassword.value = true

  try {
    const response = await enforcerAPI.changePassword(passwordData)
    
    passwordData.current_password = ''
    passwordData.new_password = ''
    passwordData.new_password_confirmation = ''
    
    showToast(response.data.message || 'Password changed successfully!', 'success')
  } catch (error) {
    console.error('Password change error:', error)
    if (error.response?.data?.errors) passwordErrors.value = error.response.data.errors
    else showAlert('Error', error.response?.data?.message || 'Failed to change password')
  } finally {
    isChangingPassword.value = false
  }
}

// Logout
// Logout with confirmation
const logout = async () => {
  const alert = await alertController.create({
    header: 'Confirm Logout',
    message: 'Are you sure you want to logout?',
    buttons: [
      {
        text: 'Cancel',
        role: 'cancel'
      },
      {
        text: 'Logout',
        role: 'destructive',
        handler: async () => {
          try {
            await authAPI.logout()
          } catch (error) {
            console.warn('Logout API failed, proceeding with local logout')
          } finally {
            localStorage.removeItem('auth_token')
            localStorage.removeItem('user_data')
            window.location.href = '/login'
          }
        }
      }
    ]
  })

  await alert.present()
}

onMounted(() => {
  loadProfile()
})
</script>

<style>

/* Content Styles */
.custom-content {
  --background: linear-gradient(135deg, #f8fafc, #e2e8f0);
}

/* Profile Picture Section */
.profile-card {
  margin-bottom: 24px;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.profile-picture-section {
  display: flex;
  align-items: center;
  gap: 20px;
}

.image-container {
  position: relative;
  width: 100px;
  height: 100px;
}

.profile-image {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  object-fit: cover;
  border: 4px solid #1e40af;
}

.image-overlay {
  position: absolute;
  bottom: 0;
  right: 0;
  background: #1e40af;
  border-radius: 50%;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
}

.image-overlay:hover {
  background: #3b82f6;
  transform: scale(1.1);
}

.camera-icon {
  color: white;
  font-size: 1rem;
}

.profile-info {
  flex: 1;
}

.profile-info h2 {
  margin: 0 0 8px 0;
  font-size: 1.4rem;
  font-weight: 600;
  color: #1e40af;
}

.profile-info p {
  margin: 0 0 8px 0;
  color: var(--ion-color-medium);
  font-size: 0.9rem;
}

/* Section Headers */
.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin: 24px 0 16px 0;
}

.section-header h2 {
  margin: 0;
  font-size: 1.3rem;
  font-weight: 600;
  color: #1e40af;
}

/* Form Cards */
.form-card, .info-card {
  margin-bottom: 24px;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.form-item {
  --background: transparent;
  --border-color: rgba(30, 64, 175, 0.2);
  --color: #1e40af;
  margin-bottom: 16px;
  border-radius: 8px;
}

.form-item.item-has-focus {
  --border-color: #1e40af;
}

.readonly-input {
  opacity: 0.6;
}

.form-actions {
  margin-top: 24px;
}

.action-btn {
  height: 56px;
  font-weight: 600;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

/* Account Info */
.account-info {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.info-item {
  display: flex;
  align-items: center;
  gap: 16px;
}

.info-icon {
  font-size: 1.5rem;
  color: #1e40af;
}

.info-item h4 {
  margin: 0;
  font-size: 1rem;
  font-weight: 600;
  color: #1e40af;
}

.info-item p {
  margin: 4px 0 0 0;
  color: var(--ion-color-medium);
  font-size: 0.9rem;
}
/* Custom Toast Style */
.custom-toast {
  --background: #1e40af; 
  --color: #ffffff;   
  --border-radius: 12px;
  --box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  text-align: center;
  font-weight: 600;
}

/* Responsive adjustments */
@media (max-width: 480px) {
  .profile-picture-section {
    flex-direction: column;
    text-align: center;
  }
  
  .image-container {
    margin-bottom: 16px;
  }
}
</style>