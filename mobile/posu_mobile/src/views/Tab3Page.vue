<template>
  <ion-page>
    <AppHeader title="POSU Enforcer"/>
    <ion-content class="main-content">
      <!-- Alerts -->
      <div v-if="error || success" class="alert-container">
        <ion-card v-if="error" class="alert-card error-alert">
          <ion-card-content class="alert-content">
            <ion-icon :icon="alertCircleOutline" class="alert-icon error-icon"></ion-icon>
            <span>{{ error }}</span>
          </ion-card-content>
        </ion-card>
        
        <ion-card v-if="success" class="alert-card success-alert">
          <ion-card-content class="alert-content">
            <ion-icon :icon="checkmarkCircleOutline" class="alert-icon success-icon"></ion-icon>
            <span>{{ success }}</span>
          </ion-card-content>
        </ion-card>
      </div>

      <!-- Quick Search Section -->
      <ion-card class="main-card">
        <ion-card-header class="card-header">
          <div class="header-with-icon">
            <ion-icon :icon="searchOutline" class="section-icon"></ion-icon>
            <ion-card-title class="card-title">Quick Search Violator</ion-card-title>
          </div>
        </ion-card-header>
        <ion-card-content class="card-content">
          <div class="search-wrapper">
            <ion-item class="search-input" fill="outline">
              <ion-icon :icon="personOutline" slot="start" class="input-icon"></ion-icon>
              <ion-input
                v-model="searchQuery"
                placeholder="Enter name or license number"
                :disabled="isSearching"
                @keyup.enter="performSearch"
                @input="onSearchInput"
              ></ion-input>
              <ion-button
                v-if="searchQuery.length > 0"
                slot="end"
                fill="clear"
                @click="clearSearch"
                class="clear-button"
              >
                <ion-icon :icon="closeOutline"></ion-icon>
              </ion-button>
              <ion-button
                slot="end"
                fill="clear"
                :disabled="searchQuery.length < 2 || isSearching"
                @click="performSearch"
                class="search-button"
              >
                <ion-spinner v-if="isSearching" name="dots"></ion-spinner>
                <ion-icon v-else :icon="searchOutline"></ion-icon>
              </ion-button>
            </ion-item>
          </div>

          <!-- Search Results -->
          <div v-if="searchResults.length" class="search-results">
            <ion-list class="results-list">
              <ion-item
                v-for="record in searchResults"
                :key="record.id"
                button
                class="result-item"
                @click="selectExistingRecord(record)"
              >
                <div slot="start" class="result-photo">
                  <img :src="record.id_photo_url || '/api/placeholder/50/50'" alt="ID Photo" />
                </div>
                <ion-label>
                  <h2 class="result-name">{{ record.first_name }} {{ record.last_name }}</h2>
                  <p class="result-detail">
                    <ion-icon :icon="cardOutline" class="detail-icon"></ion-icon>
                    {{ record.license_number }}
                  </p>
                  <p class="result-detail">
                    <ion-icon :icon="callOutline" class="detail-icon"></ion-icon>
                    {{ record.mobile_number }}
                  </p>
                  <p class="result-detail">
                    <ion-icon :icon="locationOutline" class="detail-icon"></ion-icon>
                    Brgy. {{ record.barangay }}, {{ record.city }}
                  </p>
                  <div class="result-badges">
                    <ion-badge :color="record.professional ? 'primary' : 'secondary'" class="type-badge">
                      {{ record.professional ? "Professional" : "Non-Professional" }}
                    </ion-badge>
                    <ion-badge color="medium" class="count-badge">
                      {{ formatAttempt(record.transactions_count) }}
                    </ion-badge>
                  </div>
                </ion-label>
                <ion-icon :icon="chevronForwardOutline" slot="end" class="chevron-icon"></ion-icon>
              </ion-item>
            </ion-list>
          </div>

          <!-- Selected Record Notice -->
          <ion-item v-if="selectedRecord" class="selected-notice" color="primary" fill="outline">
            <ion-icon :icon="checkmarkCircleOutline" slot="start" color="primary"></ion-icon>
              <ion-label>
                <strong style="color: white;">Selected: {{ selectedRecord.first_name }} {{ selectedRecord.last_name }}</strong>
              </ion-label>
              <ion-button slot="end" color="light" fill="clear" @click="clearSelection">
                <ion-icon :icon="closeOutline"></ion-icon>
              </ion-button>
          </ion-item>
        </ion-card-content>
      </ion-card>

      <!-- Violator Information -->
      <ion-card class="main-card">
        <ion-card-header class="card-header">
          <div class="header-with-icon">
            <ion-icon :icon="personOutline" class="section-icon"></ion-icon>
            <ion-card-title class="card-title">Violator's Information</ion-card-title>
          </div>
        </ion-card-header>
        <ion-card-content class="card-content">
          <div class="form-grid">
            <ion-item class="form-input" fill="outline">
              <ion-input 
                label="First Name" 
                label-placement="floating"
                v-model="violationForm.first_name" 
                required
              ></ion-input>
            </ion-item>
            
            <ion-item class="form-input" fill="outline">
              <ion-input 
                label="Middle Name" 
                label-placement="floating"
                v-model="violationForm.middle_name"
              ></ion-input>
            </ion-item>
            
            <ion-item class="form-input" fill="outline">
              <ion-input 
                label="Last Name" 
                label-placement="floating"
                v-model="violationForm.last_name" 
                required
              ></ion-input>
            </ion-item>
            
            <ion-item class="form-input" fill="outline">
              <ion-input 
                label="Email Address" 
                label-placement="floating"
                type="email" 
                v-model="violationForm.email"
              ></ion-input>
            </ion-item>
            
            <ion-item class="form-input" fill="outline">
              <ion-input 
                label="Mobile Number" 
                label-placement="floating"
                v-model="violationForm.mobile_number" 
                maxlength="11"
                required
              ></ion-input>
            </ion-item>
            
            <ion-item class="form-input" fill="outline">
              <ion-select 
                label="License Type" 
                label-placement="floating"
                v-model="violationForm.professional"
              >
                <ion-select-option :value="1">Professional</ion-select-option>
                <ion-select-option :value="0">Non-Professional</ion-select-option>
              </ion-select>
            </ion-item>
            
            <ion-item class="form-input" fill="outline">
              <ion-input 
                label="License Number" 
                label-placement="floating"
                v-model="violationForm.license_number" 
                maxlength="16"
                required
              ></ion-input>
            </ion-item>
            
            <ion-item class="form-input" fill="outline">
              <ion-select 
                label="Gender" 
                label-placement="floating"
                v-model="violationForm.gender"
              >
                <ion-select-option :value="1">Male</ion-select-option>
                <ion-select-option :value="0">Female</ion-select-option>
              </ion-select>
            </ion-item>
          </div>
          
          <!-- Address Section -->
          <div class="subsection">
            <h4 class="subsection-title">
              <ion-icon :icon="locationOutline" class="subsection-icon"></ion-icon>
              Address Information
            </h4>
            <div class="form-grid">
              <ion-item class="form-input" fill="outline">
                <ion-input 
                  label="Barangay" 
                  label-placement="floating"
                  v-model="violationForm.barangay"
                  required
                ></ion-input>
              </ion-item>
              
              <ion-item class="form-input" fill="outline">
                <ion-input 
                  label="City" 
                  label-placement="floating"
                  v-model="violationForm.city"
                  required
                ></ion-input>
              </ion-item>
              
              <ion-item class="form-input" fill="outline">
                <ion-input 
                  label="Province" 
                  label-placement="floating"
                  v-model="violationForm.province"
                  required
                ></ion-input>
              </ion-item>
            </div>
          </div>
        </ion-card-content>
      </ion-card>

      <!-- Vehicle & Owner Information -->
      <ion-card class="main-card">
        <ion-card-header class="card-header">
          <div class="header-with-icon">
            <ion-icon :icon="carOutline" class="section-icon"></ion-icon>
            <ion-card-title class="card-title">Vehicle & Owner's Information</ion-card-title>
          </div>
        </ion-card-header>
        <ion-card-content class="card-content">
          <!-- Vehicle Info -->
          <div class="subsection">
            <h4 class="subsection-title">
              <ion-icon :icon="carOutline" class="subsection-icon"></ion-icon>
              Vehicle Details
            </h4>
            <div class="form-grid">
              <ion-item class="form-input" fill="outline">
                <ion-select 
                  label="Vehicle Type" 
                  label-placement="floating"
                  v-model="violationForm.vehicle_type"
                >
                  <ion-select-option value="Motor">Motor</ion-select-option>
                  <ion-select-option value="Van">Van</ion-select-option>
                  <ion-select-option value="Motorcycle">Motorcycle</ion-select-option>
                  <ion-select-option value="Car">Car</ion-select-option>
                  <ion-select-option value="SUV">SUV</ion-select-option>
                  <ion-select-option value="Truck">Truck</ion-select-option>
                  <ion-select-option value="Bus">Bus</ion-select-option>
                </ion-select>
              </ion-item>
              
              <ion-item class="form-input" fill="outline">
                <ion-input 
                  label="Plate Number" 
                  label-placement="floating"
                  v-model="violationForm.plate_number" 
                  maxlength="8"
                  required
                ></ion-input>
              </ion-item>
              
              <ion-item class="form-input" fill="outline">
                <ion-input 
                  label="Make" 
                  label-placement="floating"
                  v-model="violationForm.make"
                  required
                ></ion-input>
              </ion-item>
              
              <ion-item class="form-input" fill="outline">
                <ion-input 
                  label="Model" 
                  label-placement="floating"
                  v-model="violationForm.model"
                  required
                ></ion-input>
              </ion-item>
              
              <ion-item class="form-input" fill="outline">
                <ion-input 
                  label="Color" 
                  label-placement="floating"
                  v-model="violationForm.color" 
                  required
                ></ion-input>
              </ion-item>
            </div>
          </div>

          <!-- Owner Info -->
          <div class="subsection">
            <h4 class="subsection-title">
              <ion-icon :icon="personOutline" class="subsection-icon"></ion-icon>
              Owner Information
            </h4>
            <div class="form-grid">
              <ion-item class="form-input" fill="outline">
                <ion-input 
                  label="Owner First Name" 
                  label-placement="floating"
                  v-model="violationForm.owner_first_name"
                  required
                ></ion-input>
              </ion-item>
              
              <ion-item class="form-input" fill="outline">
                <ion-input 
                  label="Owner Middle Name" 
                  label-placement="floating"
                  v-model="violationForm.owner_middle_name"
                ></ion-input>
              </ion-item>
              
              <ion-item class="form-input" fill="outline">
                <ion-input 
                  label="Owner Last Name" 
                  label-placement="floating"
                  v-model="violationForm.owner_last_name"
                  required
                ></ion-input>
              </ion-item>
            </div>
            
            <h5 class="subsection-title small">
              <ion-icon :icon="locationOutline" class="subsection-icon"></ion-icon>
              Owner Address
            </h5>
            <div class="form-grid">
              <ion-item class="form-input" fill="outline">
                <ion-input 
                  label="Owner Barangay" 
                  label-placement="floating"
                  v-model="violationForm.owner_barangay"
                  required
                ></ion-input>
              </ion-item>
              
              <ion-item class="form-input" fill="outline">
                <ion-input 
                  label="Owner City" 
                  label-placement="floating"
                  v-model="violationForm.owner_city"
                  required
                ></ion-input>
              </ion-item>
              
              <ion-item class="form-input" fill="outline">
                <ion-input 
                  label="Owner Province" 
                  label-placement="floating"
                  v-model="violationForm.owner_province"
                  required
                ></ion-input>
              </ion-item>
            </div>
          </div>
        </ion-card-content>
      </ion-card>

      <!-- Violation Information -->
      <ion-card class="main-card">
        <ion-card-header class="card-header">
          <div class="header-with-icon">
            <ion-icon :icon="warningOutline" class="section-icon"></ion-icon>
            <ion-card-title class="card-title">Violation Information</ion-card-title>
          </div>
        </ion-card-header>
        <ion-card-content class="card-content">
          <div class="form-grid">
            <ion-item class="form-input" fill="outline">
              <ion-select 
                label="Violation Type" 
                label-placement="floating"
                v-model="violationForm.violation_id"
              >
                <ion-select-option
                  v-for="violation in violationTypes"
                  :key="violation.id"
                  :value="violation.id"
                >
                  {{ violation.name }} - ₱{{ formatCurrency(violation.fine_amount) }}
                </ion-select-option>
              </ion-select>
            </ion-item>
            
            <ion-item class="form-input" fill="outline">
              <ion-input 
                label="Violation Location" 
                label-placement="floating"
                v-model="violationForm.location"
                required
              ></ion-input>
            </ion-item>
          </div>
        </ion-card-content>
      </ion-card>

      <!-- Valid ID Photo -->
      <ion-card class="main-card">
        <ion-card-header class="card-header">
          <div class="header-with-icon">
            <ion-icon :icon="cameraOutline" class="section-icon"></ion-icon>
            <ion-card-title class="card-title">Valid ID Photo</ion-card-title>
          </div>
        </ion-card-header>
        <ion-card-content class="card-content">
          <ion-item class="upload-item" fill="outline">
            <ion-icon :icon="cameraOutline" slot="start" class="input-icon"></ion-icon>
            <ion-label>
              <h3>Upload ID Photo</h3>
              <p>Select an image file</p>
            </ion-label>
            <input 
              type="file" 
              accept="image/*" 
              @change="handleFileUpload" 
              class="file-input"
              ref="fileInput"
            />
            <ion-button slot="end" fill="clear" @click="$refs.fileInput.click()" class="upload-btn">
              <ion-icon :icon="cloudUploadOutline"></ion-icon>
            </ion-button>
          </ion-item>
        </ion-card-content>
      </ion-card>

      <!-- Action Buttons -->
      <div class="action-section">
        <ion-button
          expand="block"
          size="large"
          color="primary"
          class="primary-btn"
          :disabled="!isFormValid || recording"
          @click="recordViolation"
        >
          <ion-icon :icon="saveOutline" slot="start"></ion-icon>
          <ion-spinner v-if="recording" name="dots"></ion-spinner>
          <span v-else>Record Violation</span>
        </ion-button>

        <ion-button
          expand="block"
          size="large"
          fill="outline"
          color="medium"
          class="secondary-btn"
          :disabled="!recordedTransaction"
          @click="printTicket"
        >
          <ion-icon :icon="printOutline" slot="start"></ion-icon>
          Print Ticket
        </ion-button>

      </div>

      <!-- Bottom spacing -->
      <div class="bottom-spacer"></div>
    </ion-content>
  </ion-page>
</template>

<script setup>
import { ref, onMounted, reactive, computed } from 'vue'
import AppHeader from "@/components/AppHeader.vue"
import {
  IonPage, IonContent,
  IonCard, IonCardHeader, IonCardTitle, IonCardContent,
  IonList, IonItem, IonLabel, IonInput, IonSelect, IonSelectOption,
  IonButton, IonSpinner, IonIcon, IonBadge
} from '@ionic/vue'
import {
  searchOutline, personOutline, cardOutline, callOutline,
  locationOutline, chevronForwardOutline, checkmarkCircleOutline, closeOutline,
  carOutline, warningOutline, cameraOutline,
  cloudUploadOutline, saveOutline, alertCircleOutline
} from 'ionicons/icons'
import { bluetoothService } from '@/services/bluetooth.js'
import { enforcerAPI } from '@/services/api.js'
import Swal from "sweetalert2"

// Reactive data
const recording = ref(false)
const error = ref("")
const success = ref("")
const violationTypes = ref([])
const searchQuery = ref("")
const searchResults = ref([])
const selectedRecord = ref(null)
const isSearching = ref(false)
const fileInput = ref(null)
const recordedTransaction = ref(null)

const violationForm = reactive({
  first_name: "", 
  middle_name: "", 
  last_name: "", 
  email: "", 
  mobile_number: "",
  license_number: "", 
  gender: null, 
  barangay: "", 
  city: "", 
  province: "",
  professional: null, 
  owner_first_name: "", 
  owner_middle_name: "", 
  owner_last_name: "",
  owner_barangay: "", 
  owner_city: "", 
  owner_province: "", 
  vehicle_type: "",
  plate_number: "", 
  make: "", 
  model: "", 
  color: "", 
  violation_id: "", 
  location: ""
})

const idPhoto = ref(null)

// SweetAlert configuration
const swalConfig = {
  heightAuto: false, 
  width: '90%',
  customClass: {
    container: 'swal-mobile-container', 
    popup: 'swal-mobile-popup',
    title: 'swal-mobile-title', 
    content: 'swal-mobile-content',
    confirmButton: 'swal-mobile-button', 
    cancelButton: 'swal-mobile-button'
  }
}

// Computed properties
const isFormValid = computed(() => {
  return (
    violationForm.first_name && 
    violationForm.last_name && 
    violationForm.mobile_number &&
    violationForm.license_number && 
    violationForm.gender !== null && 
    violationForm.barangay &&
    violationForm.city && 
    violationForm.province && 
    violationForm.professional !== null &&
    violationForm.owner_first_name && 
    violationForm.owner_last_name && 
    violationForm.owner_barangay &&
    violationForm.owner_city && 
    violationForm.owner_province && 
    violationForm.vehicle_type &&
    violationForm.plate_number && 
    violationForm.make && 
    violationForm.model &&
    violationForm.color && 
    violationForm.violation_id && 
    violationForm.location
  )
})

const clearSearch = () => {
  searchQuery.value = ""
  searchResults.value = []
}

// Format currency
const formatCurrency = (amount) => {
  return new Intl.NumberFormat("en-PH").format(amount || 0)
}

// Format attempt
const formatAttempt = (count) => {
  if (!count || count === 0) return "0 Attempt"
  if (count === 1) return "1st Attempt"
  if (count === 2) return "2nd Attempt"
  if (count === 3) return "3rd Attempt"
  return `${count}th Attempt`
}

// Handle file upload
const handleFileUpload = (event) => {
  const file = event.target.files[0]
  if (file) {
    // Validate file size (2MB max)
    if (file.size > 2048 * 1024) {
      Swal.fire({
        ...swalConfig,
        icon: "error",
        title: "File Too Large",
        text: "Image size must be less than 2MB",
        confirmButtonColor: "#dc3545",
      })
      return
    }

    // Validate file type
    const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif']
    if (!allowedTypes.includes(file.type)) {
      Swal.fire({
        ...swalConfig,
        icon: "error",
        title: "Invalid File Type",
        text: "Please select a valid image file (JPEG, PNG, JPG, GIF)",
        confirmButtonColor: "#dc3545",
      })
      return
    }

    idPhoto.value = file
  }
}

// Search functionality
const performSearch = async () => {
  if (searchQuery.value.length < 2) return
  
  try {
    isSearching.value = true
    searchResults.value = []
    const response = await enforcerAPI.searchViolator({ search: searchQuery.value })
    searchResults.value = response.data.data || []
  } catch (err) {
    searchResults.value = []
    await Swal.fire({
      ...swalConfig,
      icon: "error",
      title: "Search Failed",
      text: "Search failed. Please try again.",
      confirmButtonColor: "#dc3545",
    })
  } finally {
    isSearching.value = false
  }
}

// Select existing record
const selectExistingRecord = (record) => {
  selectedRecord.value = record
  searchQuery.value = ""
  searchResults.value = []

  // Fill violator info
  violationForm.first_name = record.first_name || ""
  violationForm.middle_name = record.middle_name || ""
  violationForm.last_name = record.last_name || ""
  violationForm.email = record.email || ""
  violationForm.mobile_number = record.mobile_number || ""
  violationForm.license_number = record.license_number || ""
  violationForm.gender = record.gender !== null ? Number(record.gender) : null
  violationForm.barangay = record.barangay || ""
  violationForm.city = record.city || ""
  violationForm.province = record.province || ""
  violationForm.professional = record.professional !== null ? Number(record.professional) : null

  // Fill vehicle info if available
  if (record.vehicles && record.vehicles[0]) {
    const vehicle = record.vehicles[0]
    violationForm.vehicle_type = vehicle.vehicle_type || ""
    violationForm.plate_number = vehicle.plate_number || ""
    violationForm.make = vehicle.make || ""
    violationForm.model = vehicle.model || ""
    violationForm.color = vehicle.color || ""
    violationForm.owner_first_name = vehicle.owner_first_name || ""
    violationForm.owner_middle_name = vehicle.owner_middle_name || ""
    violationForm.owner_last_name = vehicle.owner_last_name || ""
    violationForm.owner_barangay = vehicle.owner_barangay || ""
    violationForm.owner_city = vehicle.owner_city || ""
    violationForm.owner_province = vehicle.owner_province || ""
  }
}

// Clear selection
const clearSelection = () => {
  selectedRecord.value = null
  searchResults.value = []

  // Clear all form inputs when clearing selection
  Object.keys(violationForm).forEach((key) => {
    if (typeof violationForm[key] === "string") {
      violationForm[key] = ""
    } else {
      violationForm[key] = null
    }
  })

  // Clear photo
  idPhoto.value = null
  if (fileInput.value) {
    fileInput.value.value = ""
  }
}

// Handle search input changes
const onSearchInput = (event) => {
  const value = event.target.value
  if (value.length === 0) {
    searchResults.value = []
  }
}

// Load violation types
const loadViolationTypes = async () => {
  try {
    const response = await enforcerAPI.getViolationTypes()
    violationTypes.value = response.data.data || []
  } catch (err) {
    console.error("Failed to load violation types:", err)
    await Swal.fire({
      ...swalConfig,
      icon: "error",
      title: "Loading Error",
      text: "Failed to load violation types.",
      confirmButtonColor: "#dc3545",
    })
  }
}

// Record violation
const recordViolation = async () => {
  if (!isFormValid.value) {
    await Swal.fire({
      ...swalConfig,
      icon: "error",
      title: "Incomplete Form",
      text: "Please fill in all required fields.",
      confirmButtonColor: "#3085d6",
    })
    return
  }

  recording.value = true
  error.value = ""
  success.value = ""

  try {
    let payload
    if (idPhoto.value) {
      payload = new FormData()
      Object.keys(violationForm).forEach((key) => {
        if (violationForm[key] !== null && violationForm[key] !== "") {
          payload.append(key, violationForm[key])
        }
      })
      payload.append("id_photo", idPhoto.value)
    } else {
      payload = { ...violationForm }
      Object.keys(payload).forEach((key) => {
        if (payload[key] === null || payload[key] === "") {
          delete payload[key]
        }
      })
    }

    const response = await enforcerAPI.recordViolation(payload)
    recordedTransaction.value = response.data.data.transaction

    await Swal.fire({
      ...swalConfig,
      icon: "success",
      title: "Success!",
      text: `Violation recorded successfully! Ticket Number: ${response.data.data.transaction.ticket_number}`,
      confirmButtonColor: "#28a745",
      showCancelButton: true,
      confirmButtonText: "Print Ticket",
      cancelButtonText: "Record Another"
    }).then((result) => {
      if (result.isConfirmed) {
        printTicket()
      } else if (result.dismiss === Swal.DismissReason.cancel) {
        scrollToTop()
        resetForm()
      }
    })

  } catch (err) {
    console.error("Error:", err.response?.data || err.message)
    await Swal.fire({
      ...swalConfig,
      icon: "error",
      title: "Error",
      text: err.response?.data?.message || "Failed to record violation. Please try again.",
      confirmButtonColor: "#dc3545",
    })
  } finally {
    recording.value = false
  }
}

// Updated print ticket function with better flow
const printTicket = async () => {
  if (!bluetoothService.isConnected) {
    await Swal.fire({
      ...swalConfig,
      icon: "warning",
      title: "Printer Not Connected",
      text: "Please connect to a Bluetooth printer first.",
      confirmButtonColor: "#ffc107",
    });
    return;
  }

  if (!recordedTransaction.value) {
    await Swal.fire({
      ...swalConfig,
      icon: "error",
      title: "No Transaction Found",
      text: "Please record a violation first.",
      confirmButtonColor: "#dc3545",
    });
    return;
  }

  const ticketNumber = recordedTransaction.value.ticket_number;
  const violation = violationTypes.value.find(
    (v) => v.id === violationForm.violation_id
  )?.name || "N/A";
  const professionalStatus = violationForm.professional ? "Professional" : "Non-Professional";

  // ESC/POS commands
  const smallFont = '\x1D\x21\x00';
  const resetFont = '\x1D\x21\x00';

  const ticketLines = [
    smallFont,
    "REPUBLIC OF THE PHILIPPINES",
    "Province of Isabela",
    "MUNICIPALITY OF ECHAGUE",
    "       CITATION TICKET",
    `No. ${ticketNumber}`,
    "",
    "-----------------------------",
    `Driver's Name:`,
    `    ${violationForm.first_name} ${violationForm.middle_name} ${violationForm.last_name}`,
    `Address:`,
    `    ${violationForm.barangay} ${violationForm.city}, ${violationForm.province}`,
    `License No.: ${violationForm.license_number}`,
    professionalStatus,
    `Make: ${violationForm.make}`,
    `Model: ${violationForm.model}`,
    `Plate No.: ${violationForm.plate_number}`,
    `Owner's Name:`,
    `    ${violationForm.owner_first_name} ${violationForm.owner_middle_name} ${violationForm.owner_last_name}`,
    `Owner's Address:`,
    `    ${violationForm.owner_barangay}, ${violationForm.owner_city}, ${violationForm.owner_province}`,
    `Violation: ${violation}`,
    "",
    "You are hereby directed to report to the Echague Police Station",
    "within seven (7) days from the date hereof for appropriate action.",
    "",
    "If you contest the Citation Ticket issued, you may pay the fine",
    "to the Municipal Treasurer after reporting to the station.",
    "Failure to report or settle within seven (7) days will result",
    "to the filing of this violation to the appropriate court.",
    "",
    "-----------------------------",
    "Driver's Signature:",
    "", 
    "_______________",
    "",
    "Apprehending Officer:",
    "",
    "_______________",
    "-----------------------------",
    resetFont
  ];

  const ticketText = ticketLines.join("\n");

  try {
    await bluetoothService.printText(ticketText);

    await Swal.fire({
      ...swalConfig,
      icon: "success",
      title: "Printed Successfully!",
      confirmButtonColor: "#28a745",
      showCancelButton: true,
      confirmButtonText: "Record Another",
      cancelButtonText: "Done"
    }).then((result) => {
      if (result.isConfirmed) {
        scrollToTop()
        resetForm()
      }
    });

  } catch (err) {
    console.error("Print error:", err);
    await Swal.fire({
      ...swalConfig,
      icon: "error",
      title: "Print Failed",
      text: "Failed to print the ticket. Please try again.",
      confirmButtonColor: "#dc3545",
    });
  }
};

// Scroll to top function
const scrollToTop = () => {
  const content = document.querySelector('ion-content');
  if (content) {
    content.scrollToTop(500);
  }
}

// Reset form
const resetForm = () => {
  Object.keys(violationForm).forEach((key) => {
    if (typeof violationForm[key] === "string") {
      violationForm[key] = ""
    } else {
      violationForm[key] = null
    }
  })

  idPhoto.value = null
  error.value = ""
  success.value = ""
  searchQuery.value = ""
  searchResults.value = []
  selectedRecord.value = null
  recordedTransaction.value = null

  if (fileInput.value) {
    fileInput.value.value = ""
  }
}

// Mount lifecycle
onMounted(() => {
  loadViolationTypes()
})
</script>

<style scoped>
/* Global Styling */
.main-content {
  --background: #f8fafc;
  --padding-start: 16px;
  --padding-end: 16px;
  --padding-top: 0;
}

/* Header */
.custom-header {
  --background: linear-gradient(135deg, #1e40af, #3b82f6);
  --color: white;
  box-shadow: 0 2px 16px rgba(30, 64, 175, 0.15);
}

.header-title {
  font-weight: 600;
}

.title-content {
  display: flex;
  align-items: center;
  justify-content: center;
}

.title-icon {
  margin-right: 8px;
  font-size: 1.3em;
}

/* Alert Cards */
.alert-container {
  margin-bottom: 16px;
}

.alert-card {
  border-radius: 16px;
  box-shadow: none;
  border: 1px solid;
  margin-bottom: 8px;
}

.error-alert {
  --background: #fef2f2;
  border-color: #fecaca;
}

.success-alert {
  --background: #f0fdf4;
  border-color: #bbf7d0;
}

.alert-content {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 16px;
}

.alert-icon {
  font-size: 1.2em;
}

.error-icon {
  color: #dc2626;
}

.success-icon {
  color: #16a34a;
}

/* Main Cards */
.main-card {
  border-radius: 20px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  border: 1px solid #e2e8f0;
  margin-bottom: 20px;
  --background: white;
}

.card-header {
  padding: 24px 24px 16px 24px;
  border-bottom: 1px solid #f1f5f9;
}

.header-with-icon {
  display: flex;
  align-items: center;
}

.section-icon {
  margin-right: 12px;
  font-size: 1.4em;
  color: #3b82f6;
}

.card-title {
  font-size: 1.3em;
  font-weight: 600;
  color: #1e293b;
  margin: 0;
}

.card-content {
  padding: 24px;
}

/* Search Section */
.search-wrapper {
  margin-bottom: 20px;
}

.search-input {
  --border-radius: 16px;
  --border-width: 2px;
  --border-color: #e2e8f0;
  --highlight-color-focused: #3b82f6;
  background: white;
  margin-bottom: 0;
}

.search-input:hover {
  --border-color: #3b82f6;
}

.search-button {
  --color: #3b82f6;
}

.search-results {
  margin-top: 20px;
}

.results-list {
  background: transparent;
}

.result-item {
  --border-radius: 16px;
  --background: white;
  margin-bottom: 12px;
  border: 1px solid #f1f5f9;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  transition: all 0.2s ease;
}

.result-item:hover {
  --background: #f8fafc;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.result-photo {
  width: 70px;
  height: 50px;
  border: 2px solid #e2e8f0;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
}

.result-photo img {
  width: 100%;
  height: 100%;
  object-fit: cover; 
  border-radius: 0; 
}

.result-name {
  font-weight: 600;
  color: #1e293b;
  margin-bottom: 6px;
}

.result-detail {
  display: flex;
  align-items: center;
  margin: 4px 0;
  font-size: 0.9em;
  color: #64748b;
}

.detail-icon {
  margin-right: 6px;
  font-size: 0.9em;
  color: #94a3b8;
}

.result-badges {
  margin-top: 8px;
  display: flex;
  gap: 6px;
  flex-wrap: wrap;
}

.type-badge,
.count-badge {
  font-size: 0.75em;
  font-weight: 500;
  --padding-start: 8px;
  --padding-end: 8px;
}

.chevron-icon {
  color: #94a3b8;
}

.selected-notice {
  margin-top: 16px;
  --border-radius: 16px;
  --background: #f0fdf4;
  border: 1px solid #bbf7d0;
}

/* Form Elements */
.form-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 20px;
  margin-bottom: 20px;
}

.form-input {
  --border-radius: 16px;
  --border-width: 2px;
  --border-color: #e2e8f0;
  --highlight-color-focused: #3b82f6;
  background: white;
  transition: all 0.2s ease;
}

.form-input:hover {
  --border-color: #3b82f6;
}

.input-icon {
  color: #94a3b8;
  margin-right: 12px;
}

/* Subsections */
.subsection {
  margin-top: 32px;
}

.subsection-title {
  display: flex;
  align-items: center;
  margin: 0 0 16px 0;
  font-weight: 600;
  color: #374151;
  font-size: 1.1em;
}

.subsection-title.small {
  font-size: 1em;
  margin: 20px 0 12px 0;
}

.subsection-icon {
  margin-right: 8px;
  color: #3b82f6;
  font-size: 1.2em;
}

/* File Upload */
.upload-item {
  --border-radius: 16px;
  --border-width: 2px;
  --border-color: #e2e8f0;
  --highlight-color-focused: #3b82f6;
  background: white;
  position: relative;
}

.file-input {
  position: absolute;
  opacity: 0;
  pointer-events: none;
}

.upload-btn {
  --color: #3b82f6;
}

/* Action Buttons */
.action-section {
  margin-top: 40px;
  padding: 0 8px;
}

.primary-btn {
  --border-radius: 16px;
  --background: linear-gradient(135deg, #1e40af, #3b82f6);
  --background-activated: linear-gradient(135deg, #1d4ed8, #2563eb);
  --color: white;
  height: 56px;
  margin-bottom: 16px;
  font-weight: 600;
  box-shadow: 0 4px 16px rgba(59, 130, 246, 0.3);
  transition: all 0.3s ease;
}

.primary-btn:hover:not([disabled]) {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(59, 130, 246, 0.4);
}

.secondary-btn {
  --border-radius: 16px;
  --border-width: 2px;
  --border-color: #94a3b8;
  --color: #64748b;
  height: 56px;
  font-weight: 600;
  transition: all 0.3s ease;
}

.secondary-btn:hover {
  --border-color: #64748b;
  --color: #475569;
}

.bottom-spacer {
  height: 40px;
}

/* Loading States */
ion-spinner {
  --color: currentColor;
}

.primary-btn ion-spinner {
  --color: white;
  margin-right: 8px;
}

.clear-button {
  --color: #94a3b8;
  margin-right: -8px;
}

.clear-button:hover {
  --color: #64748b;
}

.selected-notice {
  margin-top: 16px;
  --border-radius: 16px;
  --background: #3b82f6;
  border: 1px solid #2563eb;
}

.search-button {
  --color: #3b82f6;
}

/* Responsive Design */
@media (max-width: 768px) {
  .form-grid {
    grid-template-columns: 1fr;
    gap: 16px;
  }
  
  .card-header {
    padding: 20px 20px 12px 20px;
  }
  
  .card-content {
    padding: 20px;
  }
  
  .main-content {
    --padding-start: 12px;
    --padding-end: 12px;
  }
}

@media (max-width: 480px) {
  .main-content {
    --padding-start: 8px;
    --padding-end: 8px;
  }
  
  .main-card {
    border-radius: 16px;
    margin-bottom: 16px;
  }
  
  .card-header {
    padding: 16px 16px 12px 16px;
  }
  
  .card-content {
    padding: 16px;
  }
  
  .card-title {
    font-size: 1.2em;
  }
  
  .section-icon {
    font-size: 1.3em;
  }
}
</style>