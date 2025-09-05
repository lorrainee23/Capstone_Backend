<template>
  <ion-page>
    <ion-header>
      <ion-toolbar color="primary">
        <ion-title>
          <ion-icon :icon="documentTextOutline" class="title-icon"></ion-icon>
          Record Violation
        </ion-title>
      </ion-toolbar>
    </ion-header>

    <ion-content class="ion-padding">
      <!-- Alerts -->
      <ion-card v-if="error" class="alert-card error-card">
        <ion-card-content class="alert-content">
          <ion-icon :icon="alertCircleOutline" class="alert-icon"></ion-icon>
          <span>{{ error }}</span>
        </ion-card-content>
      </ion-card>
      
      <ion-card v-if="success" class="alert-card success-card">
        <ion-card-content class="alert-content">
          <ion-icon :icon="checkmarkCircleOutline" class="alert-icon"></ion-icon>
          <span>{{ success }}</span>
        </ion-card-content>
      </ion-card>

      <!-- Quick Search Section -->
      <ion-card class="search-card">
        <ion-card-header>
          <ion-card-title class="card-title">
            <ion-icon :icon="searchOutline" class="section-icon"></ion-icon>
            Quick Search Violator
          </ion-card-title>
        </ion-card-header>
        <ion-card-content>
          <div class="search-container">
            <ion-item class="search-item" fill="outline">
              <ion-icon :icon="personOutline" slot="start"></ion-icon>
              <ion-input
                v-model="searchQuery"
                placeholder="Enter name or license number"
                :disabled="isSearching"
                @keyup.enter="performSearch"
              ></ion-input>
              <ion-button
                slot="end"
                fill="clear"
                :disabled="searchQuery.length < 2 || isSearching"
                @click="performSearch"
              >
                <ion-spinner v-if="isSearching" name="dots"></ion-spinner>
                <ion-icon v-else :icon="searchOutline"></ion-icon>
              </ion-button>
            </ion-item>
          </div>

          <!-- Search Results -->
          <div v-if="searchResults.length" class="search-results">
            <ion-list>
              <ion-item
                v-for="record in searchResults"
                :key="record.id"
                button
                class="search-result-item"
                @click="selectExistingRecord(record)"
              >
<ion-avatar slot="start" class="rectangular-avatar">
  <img :src="record.id_photo_url || '/api/placeholder/50/50'" />
</ion-avatar>
                <ion-label>
                  <h2 class="result-name">{{ record.first_name }} {{ record.last_name }}</h2>
                  <p class="result-detail">
                    <ion-icon :icon="cardOutline" class="inline-icon"></ion-icon>
                    {{ record.license_number }}
                  </p>
                  <p class="result-detail">
                    <ion-icon :icon="callOutline" class="inline-icon"></ion-icon>
                    {{ record.mobile_number }}
                  </p>
                  <p class="result-detail">
                    <ion-icon :icon="locationOutline" class="inline-icon"></ion-icon>
                    Brgy. {{ record.barangay }}, {{ record.city }}
                  </p>
                  <ion-badge :color="record.professional ? 'primary' : 'secondary'" class="license-badge">
                    {{ record.professional ? "Professional" : "Non-Professional" }}
                  </ion-badge>
                  <ion-badge :color="record.transactions_count ? 'medium' : 'tertiary' " class="attempt-badge">
                    {{ formatAttempt(record.transactions_count) }}
                  </ion-badge>
                </ion-label>
                <ion-icon :icon="chevronForwardOutline" slot="end" class="chevron-icon"></ion-icon>
              </ion-item>
            </ion-list>
          </div>

          <!-- Selected Record Notice -->
          <ion-item v-if="selectedRecord" class="selected-record" color="success" fill="outline">
            <ion-icon :icon="checkmarkCircleOutline" slot="start" color="success"></ion-icon>
            <ion-label>
              <strong>Selected: {{ selectedRecord.first_name }} {{ selectedRecord.last_name }}</strong>
            </ion-label>
            <ion-button slot="end" color="danger" fill="clear" @click="clearSelection">
              <ion-icon :icon="closeOutline"></ion-icon>
            </ion-button>
          </ion-item>
        </ion-card-content>
      </ion-card>

      <!-- Violator Information -->
      <ion-card class="form-card">
        <ion-card-header>
          <ion-card-title class="card-title">
            <ion-icon :icon="personOutline" class="section-icon"></ion-icon>
            Violator's Information
          </ion-card-title>
        </ion-card-header>
        <ion-card-content>
          <div class="form-grid">
            <ion-item class="form-item" fill="outline">
              <ion-icon :icon="personOutline" slot="start"></ion-icon>
              <ion-input 
                label="First Name" 
                label-placement="floating"
                v-model="violationForm.first_name" 
                required
              ></ion-input>
            </ion-item>
            
            <ion-item class="form-item" fill="outline">
              <ion-icon :icon="personOutline" slot="start"></ion-icon>
              <ion-input 
                label="Middle Name" 
                label-placement="floating"
                v-model="violationForm.middle_name"
              ></ion-input>
            </ion-item>
            
            <ion-item class="form-item" fill="outline">
              <ion-icon :icon="personOutline" slot="start"></ion-icon>
              <ion-input 
                label="Last Name" 
                label-placement="floating"
                v-model="violationForm.last_name" 
                required
              ></ion-input>
            </ion-item>
            
            <ion-item class="form-item" fill="outline">
              <ion-icon :icon="mailOutline" slot="start"></ion-icon>
              <ion-input 
                label="Email Address" 
                label-placement="floating"
                type="email" 
                v-model="violationForm.email"
                
              ></ion-input>
            </ion-item>
            
            <ion-item class="form-item" fill="outline">
              <ion-icon :icon="callOutline" slot="start"></ion-icon>
              <ion-input 
                label="Mobile Number" 
                label-placement="floating"
                v-model="violationForm.mobile_number" 
                maxlength="11"
                required
              ></ion-input>
            </ion-item>
            
            <ion-item class="form-item" fill="outline">
              <ion-icon :icon="cardOutline" slot="start"></ion-icon>
              <ion-select 
                label="License Type" 
                label-placement="floating"
                v-model="violationForm.professional"
              >
                <ion-select-option :value="1">Professional</ion-select-option>
                <ion-select-option :value="0">Non-Professional</ion-select-option>
              </ion-select>
            </ion-item>
            
            <ion-item class="form-item" fill="outline">
              <ion-icon :icon="cardOutline" slot="start"></ion-icon>
              <ion-input 
                label="License Number" 
                label-placement="floating"
                v-model="violationForm.license_number" 
                maxlength="16"
                required
              ></ion-input>
            </ion-item>
            
            <ion-item class="form-item" fill="outline">
              <ion-icon :icon="personOutline" slot="start"></ion-icon>
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
          <div class="address-section">
            <h4 class="subsection-title">
              <ion-icon :icon="locationOutline" class="subsection-icon"></ion-icon>
              Address Information
            </h4>
            <div class="form-grid">
              <ion-item class="form-item" fill="outline">
                <ion-icon :icon="locationOutline" slot="start"></ion-icon>
                <ion-input 
                  label="Barangay" 
                  label-placement="floating"
                  v-model="violationForm.barangay"
                  required
                ></ion-input>
              </ion-item>
              
              <ion-item class="form-item" fill="outline">
                <ion-icon :icon="locationOutline" slot="start"></ion-icon>
                <ion-input 
                  label="City" 
                  label-placement="floating"
                  v-model="violationForm.city"
                  required
                ></ion-input>
              </ion-item>
              
              <ion-item class="form-item" fill="outline">
                <ion-icon :icon="locationOutline" slot="start"></ion-icon>
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
      <ion-card class="form-card">
        <ion-card-header>
          <ion-card-title class="card-title">
            <ion-icon :icon="carOutline" class="section-icon"></ion-icon>
            Vehicle & Owner's Information
          </ion-card-title>
        </ion-card-header>
        <ion-card-content>
          <!-- Vehicle Info -->
          <div class="vehicle-section">
            <h4 class="subsection-title">
              <ion-icon :icon="carOutline" class="subsection-icon"></ion-icon>
              Vehicle Details
            </h4>
            <div class="form-grid">
              <ion-item class="form-item" fill="outline">
                <ion-icon :icon="carOutline" slot="start"></ion-icon>
                <ion-select 
                  label="Vehicle Type" 
                  label-placement="floating"
                  v-model="violationForm.vehicle_type"
                >
                  <ion-select-option value="Motor">Motor</ion-select-option>
                  <ion-select-option value="Van">Van</ion-select-option>
                  <ion-select-option value="Motorcycle">Motorcycle</ion-select-option>
                  <ion-select-option value="Truck">Truck</ion-select-option>
                  <ion-select-option value="Bus">Bus</ion-select-option>
                </ion-select>
              </ion-item>
              
              <ion-item class="form-item" fill="outline">
                <ion-icon :icon="documentsOutline" slot="start"></ion-icon>
                <ion-input 
                  label="Plate Number" 
                  label-placement="floating"
                  v-model="violationForm.plate_number" 
                  maxlength="8"
                  required
                ></ion-input>
              </ion-item>
              
              <ion-item class="form-item" fill="outline">
                <ion-icon :icon="carOutline" slot="start"></ion-icon>
                <ion-input 
                  label="Make" 
                  label-placement="floating"
                  v-model="violationForm.make"
                  required
                ></ion-input>
              </ion-item>
              
              <ion-item class="form-item" fill="outline">
                <ion-icon :icon="carOutline" slot="start"></ion-icon>
                <ion-input 
                  label="Model" 
                  label-placement="floating"
                  v-model="violationForm.model"
                  required
                ></ion-input>
              </ion-item>
            </div>
          </div>

          <!-- Owner Info -->
          <div class="owner-section">
            <h4 class="subsection-title">
              <ion-icon :icon="personOutline" class="subsection-icon"></ion-icon>
              Owner Information
            </h4>
            <div class="form-grid">
              <ion-item class="form-item" fill="outline">
                <ion-icon :icon="personOutline" slot="start"></ion-icon>
                <ion-input 
                  label="Owner First Name" 
                  label-placement="floating"
                  v-model="violationForm.owner_first_name"
                  required
                ></ion-input>
              </ion-item>
              
              <ion-item class="form-item" fill="outline">
                <ion-icon :icon="personOutline" slot="start"></ion-icon>
                <ion-input 
                  label="Owner Middle Name" 
                  label-placement="floating"
                  v-model="violationForm.owner_middle_name"
                ></ion-input>
              </ion-item>
              
              <ion-item class="form-item" fill="outline">
                <ion-icon :icon="personOutline" slot="start"></ion-icon>
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
              <ion-item class="form-item" fill="outline">
                <ion-icon :icon="locationOutline" slot="start"></ion-icon>
                <ion-input 
                  label="Owner Barangay" 
                  label-placement="floating"
                  v-model="violationForm.owner_barangay"
                  required
                ></ion-input>
              </ion-item>
              
              <ion-item class="form-item" fill="outline">
                <ion-icon :icon="locationOutline" slot="start"></ion-icon>
                <ion-input 
                  label="Owner City" 
                  label-placement="floating"
                  v-model="violationForm.owner_city"
                  required
                ></ion-input>
              </ion-item>
              
              <ion-item class="form-item" fill="outline">
                <ion-icon :icon="locationOutline" slot="start"></ion-icon>
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
      <ion-card class="form-card">
        <ion-card-header>
          <ion-card-title class="card-title">
            <ion-icon :icon="warningOutline" class="section-icon"></ion-icon>
            Violation Information
          </ion-card-title>
        </ion-card-header>
        <ion-card-content>
          <div class="form-grid">
            <ion-item class="form-item" fill="outline">
              <ion-icon :icon="warningOutline" slot="start"></ion-icon>
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
            
            <ion-item class="form-item" fill="outline">
              <ion-icon :icon="locationOutline" slot="start"></ion-icon>
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

      <!-- Additional Information -->
      <ion-card class="form-card">
        <ion-card-header>
          <ion-card-title class="card-title">
            <ion-icon :icon="addCircleOutline" class="section-icon"></ion-icon>
            Additional Information
          </ion-card-title>
        </ion-card-header>
        <ion-card-content>
          <ion-item class="form-item file-upload-item" fill="outline">
            <ion-icon :icon="cameraOutline" slot="start"></ion-icon>
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
            <ion-button slot="end" fill="clear" @click="$refs.fileInput.click()">
              <ion-icon :icon="cloudUploadOutline"></ion-icon>
            </ion-button>
          </ion-item>
          
          <ion-item class="form-item" fill="outline">
            <ion-icon :icon="chatboxOutline" slot="start"></ion-icon>
            <ion-textarea
              label="Additional Remarks"
              label-placement="floating"
              auto-grow="true"
              v-model="violationForm.remarks"
              placeholder="Enter any additional information or remarks..."
              rows="3"
            ></ion-textarea>
          </ion-item>
        </ion-card-content>
      </ion-card>

      <!-- Action Buttons -->
      <div class="action-buttons">
        <ion-button
          expand="block"
          size="large"
          color="primary"
          class="primary-button"
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
          color="medium"
          fill="outline"
          class="secondary-button"
          @click="resetForm"
        >
          <ion-icon :icon="refreshOutline" slot="start"></ion-icon>
          Reset Form
        </ion-button>
       

      </div>
    </ion-content>
  </ion-page>
</template>

<script>
import { 
  IonPage,
  IonHeader,
  IonToolbar,
  IonTitle,
  IonContent,
  IonCard,
  IonCardHeader,
  IonCardTitle,
  IonCardContent,
  IonList,
  IonItem,
  IonLabel,
  IonInput,
  IonSelect,
  IonSelectOption,
  IonTextarea,
  IonButton,
  IonAvatar,
  IonSpinner,
  IonIcon,
  IonBadge
} from '@ionic/vue'

import { 
  documentTextOutline,
  searchOutline,
  personOutline,
  cardOutline,
  callOutline,
  locationOutline,
  chevronForwardOutline,
  checkmarkCircleOutline,
  closeOutline,
  mailOutline,
  carOutline,
  documentsOutline,
  businessOutline,
  warningOutline,
  addCircleOutline,
  cameraOutline,
  cloudUploadOutline,
  chatboxOutline,
  saveOutline,
  refreshOutline,
  alertCircleOutline
} from 'ionicons/icons'

import { ref, reactive, onMounted, computed } from "vue";
import { BluetoothSerial } from '@ionic-native/bluetooth-serial';

import { enforcerAPI } from "@/services/api";
import Swal from "sweetalert2";

export default {
  name: "RecordViolation",
  components: {
    IonPage,
    IonHeader,
    IonToolbar,
    IonTitle,
    IonContent,
    IonCard,
    IonCardHeader,
    IonCardTitle,
    IonCardContent,
    IonList,
    IonItem,
    IonLabel,
    IonInput,
    IonSelect,
    IonSelectOption,
    IonTextarea,
    IonButton,
    IonAvatar,
    IonSpinner,
    IonIcon,
    IonBadge
  },
  setup() {
    const recording = ref(false);
    const error = ref("");
    const success = ref("");
    const violationTypes = ref([]);
    const searchQuery = ref("");
    const searchResults = ref([]);
    const selectedRecord = ref(null);
    const isSearching = ref(false);

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
      violation_id: "",
      location: "",
      remarks: "",
    });

    const idPhoto = ref(null);

    // SweetAlert configuration for mobile responsiveness
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
    };

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
        violationForm.violation_id &&
        violationForm.location
      );
    });

    const handleFileUpload = (event) => {
      idPhoto.value = event.target.files[0];
    };

    const performSearch = async () => {
      if (searchQuery.value.length < 2) return;
      try {
        isSearching.value = true;
        searchResults.value = [];
        const response = await enforcerAPI.searchViolator({ search: searchQuery.value });
        searchResults.value = response.data.data || [];
      } catch (err) {
        searchResults.value = [];
        await Swal.fire({
          ...swalConfig,
          icon: "error",
          title: "Search Failed",
          text: "Search failed. Please try again.",
          confirmButtonColor: "#dc3545",
        });
      } finally {
        isSearching.value = false;
      }
    };

    const selectExistingRecord = (record) => {
  selectedRecord.value = record;
  searchQuery.value = "";
  searchResults.value = [];

  // Violator info
  violationForm.first_name = record.first_name || "";
  violationForm.middle_name = record.middle_name || "";
  violationForm.last_name = record.last_name || "";
  violationForm.email = record.email || "";
  violationForm.mobile_number = record.mobile_number || "";
  violationForm.license_number = record.license_number || "";
  violationForm.gender = record.gender !== null ? Number(record.gender) : null;
  violationForm.barangay = record.barangay || "";
  violationForm.city = record.city || "";
  violationForm.province = record.province || "";
  violationForm.professional = record.professional !== null ? Number(record.professional) : null;

    const vehicle = record.vehicles[0];
    violationForm.vehicle_type = vehicle.vehicle_type || "";
    violationForm.plate_number = vehicle.plate_number || "";
    violationForm.make = vehicle.make || "";
    violationForm.model = vehicle.model || "";

    violationForm.owner_first_name = vehicle.owner_first_name || "";
    violationForm.owner_middle_name = vehicle.owner_middle_name || "";
    violationForm.owner_last_name = vehicle.owner_last_name || "";
    violationForm.owner_barangay = vehicle.owner_barangay || "";
    violationForm.owner_city = vehicle.owner_city || "";
    violationForm.owner_province = vehicle.owner_province || "";
  
};


    const clearSelection = () => {
      selectedRecord.value = null;
      searchResults.value = [];
    };

    const loadViolationTypes = async () => {
      try {
        const response = await enforcerAPI.getViolationTypes();
        violationTypes.value = response.data.data || [];
      } catch (err) {
        console.error("Failed to load violation types:", err);
        await Swal.fire({
          ...swalConfig,
          icon: "error",
          title: "Loading Error",
          text: "Failed to load violation types.",
          confirmButtonColor: "#dc3545",
        });
      }
    };

    const recordViolation = async () => {
      if (!isFormValid.value) {
        await Swal.fire({
          ...swalConfig,
          icon: "error",
          title: "Incomplete Form",
          text: "Please fill in all required fields.",
          confirmButtonColor: "#3085d6",
        });
        return;
      }

      recording.value = true;
      error.value = "";
      success.value = "";

      try {
        let payload;
        if (idPhoto.value) {
          payload = new FormData();
          Object.keys(violationForm).forEach((key) => {
            if (violationForm[key] !== null && violationForm[key] !== "") {
              payload.append(key, violationForm[key]);
            }
          });
          payload.append("id_photo", idPhoto.value);
        } else {
          payload = { ...violationForm };
          Object.keys(payload).forEach((key) => {
            if (payload[key] === null || payload[key] === "") {
              delete payload[key];
            }
          });
        }

        await enforcerAPI.recordViolation(payload);

        await Swal.fire({
          ...swalConfig,
          icon: "success",
          title: "Success!",
          text: "Violation recorded successfully!",
          confirmButtonColor: "#28a745",
        });

        // Reset form after successful submission
        resetForm();
      } catch (err) {
        console.error("Error:", err.response?.data || err.message);
        await Swal.fire({
          ...swalConfig,
          icon: "error",
          title: "Error",
          text: err.response?.data?.message || "Failed to record violation. Please try again.",
          confirmButtonColor: "#dc3545",
        });
      } finally {
        recording.value = false;
      }
    };

    const resetForm = () => {
      Object.keys(violationForm).forEach((key) => {
        if (typeof violationForm[key] === "string") {
          violationForm[key] = "";
        } else {
          violationForm[key] = null;
        }
      });

      idPhoto.value = null;
      error.value = "";
      success.value = "";
      searchQuery.value = "";
      searchResults.value = [];
      selectedRecord.value = null;

      const fileInput = document.querySelector('input[type="file"]');
      if (fileInput) fileInput.value = "";
    };

    const formatCurrency = (amount) =>
      new Intl.NumberFormat("en-PH").format(amount || 0);
    
    const formatAttempt = (count) => {
      if (!count || count === 0) return "0 Attempt";
      if (count === 1) return "1st Attempt";
      if (count === 2) return "2nd Attempt";
      if (count === 3) return "3rd Attempt";
      return `${count}th Attempt`;
    };

const connectAndPrint = async () => {
  const mac = "24:00:20:00:80:A2";

  try {
    const isEnabled = await BluetoothSerial.isEnabled();
    if (!isEnabled) {
      await Swal.fire({
        ...swalConfig,
        icon: "warning",
        title: "Bluetooth Off",
        text: "Please turn on Bluetooth to connect to the printer.",
        confirmButtonColor: "#ffc107"
      });
      return;
    }

    const isConnected = await BluetoothSerial.isConnected();
    if (!isConnected) {
      await Swal.fire({
        ...swalConfig,
        icon: "info",
        title: "Connecting...",
        text: "Connecting to printer, please wait...",
        confirmButtonColor: "#0d6efd"
      });
      await BluetoothSerial.connect(mac);
      await Swal.fire({
        ...swalConfig,
        icon: "success",
        title: "Connected!",
        text: "Bluetooth printer connected successfully.",
        confirmButtonColor: "#28a745"
      });
    } else {
      await Swal.fire({
        ...swalConfig,
        icon: "info",
        title: "Already Connected",
        text: "Printer is already connected via Bluetooth.",
        confirmButtonColor: "#0d6efd"
      });
    }

    await BluetoothSerial.write("Hello from Ionic!\n\n");

    await Swal.fire({
      ...swalConfig,
      icon: "success",
      title: "Printed!",
      text: "Message sent to printer successfully.",
      confirmButtonColor: "#28a745"
    });

  } catch (err) {
    await Swal.fire({
      ...swalConfig,
      icon: "error",
      title: "Bluetooth Error",
      text: err.message || "Failed to connect or print.",
      confirmButtonColor: "#dc3545"
    });
  }
};

    onMounted(loadViolationTypes);

    return {
      recording,
      error,
      success,
      violationTypes,
      violationForm,
      searchQuery,
      searchResults,
      selectedRecord,
      isSearching,
      isFormValid,
      performSearch,
      selectExistingRecord,
      clearSelection,
      recordViolation,
      resetForm,
      handleFileUpload,
      formatCurrency,
      formatAttempt,
      // Icons
      documentTextOutline,
      searchOutline,
      personOutline,
      cardOutline,
      callOutline,
      locationOutline,
      chevronForwardOutline,
      checkmarkCircleOutline,
      closeOutline,
      mailOutline,
      carOutline,
      documentsOutline,
      businessOutline,
      warningOutline,
      addCircleOutline,
      cameraOutline,
      cloudUploadOutline,
      chatboxOutline,
      saveOutline,
      refreshOutline,
      alertCircleOutline,
      printReceipt: connectAndPrint
    };
  },
};
</script>

<style scoped>
/* Global Styling */
ion-content {
  --background: #f8f9fa;
}

/* Header */
.title-icon {
  margin-right: 8px;
  font-size: 1.2em;
}

/* Alert Cards */
.alert-card {
  margin-bottom: 16px;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.error-card {
  --background: #fee;
  border-left: 4px solid var(--ion-color-danger);
}

.success-card {
  --background: #efe;
  border-left: 4px solid var(--ion-color-success);
}

.alert-content {
  display: flex;
  align-items: center;
  gap: 12px;
}

.alert-icon {
  font-size: 1.2em;
}

/* Cards */
.search-card,
.form-card {
  margin-bottom: 20px;
  border-radius: 16px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  --background: #ffffff;
}

.card-title {
  display: flex;
  align-items: center;
  font-weight: 600;
  color: var(--ion-color-primary);
}

.section-icon {
  margin-right: 8px;
  font-size: 1.3em;
}

/* Search Section */
.search-container {
  margin-bottom: 16px;
}

.search-item {
  border-radius: 12px;
  border: 2px solid var(--ion-color-light);
  transition: all 0.3s ease;
}

.search-item:hover,
.search-item:focus-within {
  border-color: var(--ion-color-primary);
  box-shadow: 0 0 0 3px rgba(var(--ion-color-primary-rgb), 0.1);
}

.search-results {
  margin-top: 16px;
}

.search-result-item {
  border-radius: 12px;
  margin-bottom: 8px;
  background: #f8f9fa;
  transition: all 0.3s ease;
}

.search-result-item:hover {
  background: #e9ecef;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.result-name {
  font-weight: 600;
  color: var(--ion-color-dark);
  margin-bottom: 4px;
}

.result-detail {
  display: flex;
  align-items: center;
  margin: 2px 0;
  font-size: 0.9em;
  color: var(--ion-color-medium);
}

.inline-icon {
  margin-right: 6px;
  font-size: 0.9em;
}

.license-badge,
.attempt-badge {
  margin: 4px 4px 0 0;
  font-size: 0.75em;
}

.chevron-icon {
  color: var(--ion-color-medium);
}

.selected-record {
  margin-top: 16px;
  border-radius: 12px;
  background: rgba(var(--ion-color-success-rgb), 0.1);
}

/* Form Styling */
.form-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 16px;
  margin-bottom: 16px;
}

.form-item {
  border-radius: 12px;
  border: 2px solid var(--ion-color-light);
  transition: all 0.3s ease;
  background: #ffffff;
}

.form-item:hover,
.form-item:focus-within {
  border-color: var(--ion-color-primary);
  box-shadow: 0 0 0 3px rgba(var(--ion-color-primary-rgb), 0.1);
}

.required-field {
  border-left: 4px solid var(--ion-color-warning);
}

/* Subsections */
.address-section,
.vehicle-section,
.owner-section {
  margin-top: 24px;
}

.subsection-title {
  display: flex;
  align-items: center;
  margin: 16px 0 12px 0;
  font-weight: 600;
  color: var(--ion-color-dark);
  font-size: 1.1em;
}

.subsection-title.small {
  font-size: 1em;
  margin: 12px 0 8px 0;
}

.subsection-icon {
  margin-right: 8px;
  color: var(--ion-color-primary);
}

/* File Upload */
.file-upload-item {
  position: relative;
}

.file-input {
  position: absolute;
  opacity: 0;
  pointer-events: none;
}

/* Action Buttons */
.action-buttons {
  margin-top: 32px;
  padding: 0 0 32px 0;
}
/* Make avatar rectangular */
.rectangular-avatar {
  --border-radius: 8px;
  width: 100px;
  height: 80px;
}

.rectangular-avatar img {
  border-radius: 6px;
  object-fit: contain;
}
.primary-button {
  --border-radius: 16px;
  --box-shadow: 0 4px 16px rgba(var(--ion-color-primary-rgb), 0.3);
  margin-bottom: 16px;
  font-weight: 600;
  --padding-top: 16px;
  --padding-bottom: 16px;
}

.secondary-button {
  --border-radius: 16px;
  --border-width: 2px;
  font-weight: 600;
  --padding-top: 16px;
  --padding-bottom: 16px;
}

/* SweetAlert Mobile Fixes */
:global(.swal-mobile-container) {
  z-index: 20000 !important;
  padding: 10px !important;
}

:global(.swal-mobile-popup) {
  border-radius: 12px !important;
  padding: 20px !important;
  max-width: 90vw !important;
  width: 100% !important;
  margin: 0 auto !important;
}

:global(.swal-mobile-title) {
  font-size: 1.2rem !important;
  line-height: 1.4 !important;
  margin-bottom: 15px !important;
}

:global(.swal-mobile-content) {
  font-size: 1rem !important;
  line-height: 1.5 !important;
  text-align: center !important;
}

:global(.swal-mobile-button) {
  border-radius: 8px !important;
  padding: 12px 24px !important;
  font-size: 1rem !important;
  font-weight: 600 !important;
  min-height: 44px !important;
}

/* Responsive Design */
@media (max-width: 768px) {
  .form-grid {
    grid-template-columns: 1fr;
    gap: 12px;
  }
  
  .card-title {
    font-size: 1.1em;
  }
  
  .section-icon {
    font-size: 1.2em;
  }
  
  /* Enhanced SweetAlert mobile styling */
  :global(.swal-mobile-popup) {
    max-width: 95vw !important;
    margin: 5px auto !important;
  }
}

@media (max-width: 480px) {
  ion-content {
    --padding-start: 12px;
    --padding-end: 12px;
  }
  
  .search-card,
  .form-card {
    margin-bottom: 16px;
    border-radius: 12px;
  }
  
  .form-item {
    border-radius: 8px;
  }
  
  .primary-button,
  .secondary-button {
    --border-radius: 12px;
  }
  
  /* Ultra-mobile SweetAlert fixes */
  :global(.swal-mobile-popup) {
    max-width: 98vw !important;
    padding: 15px !important;
  }
  
  :global(.swal-mobile-title) {
    font-size: 1.1rem !important;
  }
  
  :global(.swal-mobile-content) {
    font-size: 0.9rem !important;
  }
  
  :global(.swal-mobile-button) {
    padding: 10px 20px !important;
    font-size: 0.9rem !important;
    min-height: 40px !important;
  }
}

/* Loading States */
.search-item ion-spinner {
  --color: var(--ion-color-primary);
}

.primary-button ion-spinner {
  --color: white;
  margin-right: 8px;
}

/* Focus States */
ion-input:focus-within,
ion-select:focus-within,
ion-textarea:focus-within {
  --highlight-color-focused: var(--ion-color-primary);
}

/* Custom Select Styling */
ion-select {
  --placeholder-color: var(--ion-color-medium);
}

/* Avatar Styling */
ion-avatar {
  width: 50px;
  height: 50px;
  border: 2px solid var(--ion-color-light);
}

ion-avatar img {
  border-radius: 50%;
  object-fit: cover;
}

/* Badge Styling */
ion-badge {
  --padding-start: 8px;
  --padding-end: 8px;
  --padding-top: 4px;
  --padding-bottom: 4px;
  font-weight: 500;
}

/* Animation Classes */
.search-result-item,
.form-item,
.alert-card {
  animation: slideInUp 0.3s ease-out;
}

@keyframes slideInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Hover Effects */
ion-button:hover:not([disabled]) {
  transform: translateY(-2px);
  transition: transform 0.2s ease;
}

/* Disabled States */
ion-button[disabled] {
  opacity: 0.6;
}

.form-item[disabled] {
  opacity: 0.7;
  background: var(--ion-color-light);
}
</style>