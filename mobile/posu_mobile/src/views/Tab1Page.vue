<template>
  <ion-page>
    <ion-header>
      <ion-toolbar>
        <ion-title>Record Violation</ion-title>
      </ion-toolbar>
    </ion-header>

    <ion-content class="ion-padding">
      <!-- Alerts -->
      <ion-card v-if="error" color="danger">
        <ion-card-content>{{ error }}</ion-card-content>
      </ion-card>
      <ion-card v-if="success" color="success">
        <ion-card-content>{{ success }}</ion-card-content>
      </ion-card>

      <!-- Quick Search -->
      <ion-card class="search-section">
        <ion-card-header>
          <ion-card-title>Quick Search Violator 🔍</ion-card-title>
        </ion-card-header>
        <ion-card-content>
          <div class="search-container">
            <input
              type="text"
              placeholder="Name or license number"
              v-model="searchQuery"
              @keyup.enter="performSearch"
              :disabled="isSearching"
              class="search-input"
            />
            <button
              type="button"
              :disabled="searchQuery.length < 2 || isSearching"
              @click="performSearch"
              class="search-btn"
            >
              <span v-if="isSearching">Searching...</span>
              <span v-else>Search</span>
            </button>
          </div>

          <ion-list v-if="searchResults.length">
            <ion-item
              v-for="record in searchResults"
              :key="record.id"
              button
              @click="selectExistingRecord(record)"
            >
              <ion-thumbnail slot="start">
                <img :src="record.id_photo_url" />
              </ion-thumbnail>
              <ion-label>
                <h2>{{ record.first_name }} {{ record.last_name }}</h2>
                <p>License: {{ record.license_number }} | Mobile: {{ record.mobile_number }}</p>
                <p>Type: {{ record.professional ? "Professional" : "Non-Professional" }}</p>
                <p>Address: Brgy. {{ record.barangay }}, {{ record.city }}, {{ record.province }}</p>
                <p>{{ formatAttempt(record.transactions_count) }}</p>
              </ion-label>
            </ion-item>
          </ion-list>

          <div v-if="selectedRecord" class="selected-record-notice">
            ✅ Selected: {{ selectedRecord.first_name }} {{ selectedRecord.last_name }}
            <button type="button" @click="clearSelection" class="clear-btn">Clear</button>
          </div>
        </ion-card-content>
      </ion-card>

      <!-- Violator Info Form -->
      <ion-card>
        <ion-card-header>
          <ion-card-title>Violator's Information</ion-card-title>
        </ion-card-header>
        <ion-card-content>
          <form @submit.prevent="recordViolation">
            <div class="form-grid">
              <div class="form-row">
                <div class="form-group">
                  <label>First Name *</label>
                  <input type="text" v-model="violationForm.first_name" required />
                </div>
                <div class="form-group">
                  <label>Middle Name</label>
                  <input type="text" v-model="violationForm.middle_name" />
                </div>
                <div class="form-group">
                  <label>Last Name *</label>
                  <input type="text" v-model="violationForm.last_name" required />
                </div>
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label>Email</label>
                  <input type="email" v-model="violationForm.email" />
                </div>
                <div class="form-group">
                  <label>Mobile Number *</label>
                  <input type="text" v-model="violationForm.mobile_number" maxlength="11" required />
                </div>
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label>License Type *</label>
                  <select v-model.number="violationForm.professional" required>
                    <option value="">Select license type</option>
                    <option :value="1">Professional</option>
                    <option :value="0">Non-Professional</option>
                  </select>
                </div>
                <div class="form-group">
                  <label>License Number *</label>
                  <input type="text" v-model="violationForm.license_number" maxlength="16" required />
                </div>
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label>Gender *</label>
                  <select v-model.number="violationForm.gender" required>
                    <option value="">Select gender</option>
                    <option :value="1">Male</option>
                    <option :value="0">Female</option>
                  </select>
                </div>
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label>Barangay *</label>
                  <input type="text" v-model="violationForm.barangay" required />
                </div>
                <div class="form-group">
                  <label>City *</label>
                  <input type="text" v-model="violationForm.city" required />
                </div>
                <div class="form-group">
                  <label>Province *</label>
                  <input type="text" v-model="violationForm.province" required />
                </div>
              </div>
            </div>
          </form>
        </ion-card-content>
      </ion-card>

      <!-- Vehicle & Owner Info -->
      <ion-card>
        <ion-card-header>
          <ion-card-title>Vehicle & Owner's Information</ion-card-title>
        </ion-card-header>
        <ion-card-content>
          <div class="form-grid">
            <div class="form-row">
              <div class="form-group">
                <ion-item>
                  <ion-label>Vehicle Type *</ion-label>
                  <select 
                    v-model="violationForm.vehicle_type"
                    placeholder="Select type"
                    required
                  >
                    <option value="Motor">Motor</option>
                    <option value="Van">Van</option>
                    <option value="Motorcycle">Motorcycle</option>
                    <option value="Truck">Truck</option>
                    <option value="Bus">Bus</option>
                  </select>
                </ion-item>
              </div>
              <div class="form-group">
                <label>Plate Number *</label>
                <input type="text" v-model="violationForm.plate_number" maxlength="8" required />
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>Make *</label>
                <input type="text" v-model="violationForm.make" required />
              </div>
              <div class="form-group">
                <label>Model *</label>
                <input type="text" v-model="violationForm.model" required />
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>Owner First Name *</label>
                <input type="text" v-model="violationForm.owner_first_name" required />
              </div>
              <div class="form-group">
                <label>Owner Middle Name</label>
                <input type="text" v-model="violationForm.owner_middle_name" />
              </div>
              <div class="form-group">
                <label>Owner Last Name *</label>
                <input type="text" v-model="violationForm.owner_last_name" required />
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>Owner Barangay *</label>
                <input type="text" v-model="violationForm.owner_barangay" required />
              </div>
              <div class="form-group">
                <label>Owner City *</label>
                <input type="text" v-model="violationForm.owner_city" required />
              </div>
              <div class="form-group">
                <label>Owner Province *</label>
                <input type="text" v-model="violationForm.owner_province" required />
              </div>
            </div>
          </div>
        </ion-card-content>
      </ion-card>

      <!-- Violation Details -->
      <ion-card>
        <ion-card-header>
          <ion-card-title>Violation Information</ion-card-title>
        </ion-card-header>
        <ion-card-content>
          <div class="form-grid">
            <div class="form-row">
              <div class="form-group">
                <ion-item>
                  <ion-label>Violation Type *</ion-label>
                  <select 
                    v-model="violationForm.violation_id"
                    placeholder="Select violation"
                    required
                  >
                    <option
                      v-for="violation in violationTypes"
                      :key="violation.id"
                      :value="violation.id"
                    >
                      {{ violation.name }} - ₱{{ formatCurrency(violation.fine_amount) }}
                    </option>
                  </select>
                </ion-item>
              </div>
              <div class="form-group">
                <label>Location *</label>
                <input type="text" v-model="violationForm.location" required />
              </div>
            </div>
          </div>
        </ion-card-content>
      </ion-card>

      <!-- Additional Info -->
      <ion-card>
        <ion-card-header>
          <ion-card-title>Additional Information</ion-card-title>
        </ion-card-header>
        <ion-card-content>
          <div class="form-grid">
            <div class="form-row">
              <div class="form-group">
                <label>Upload ID Photo</label>
                <input type="file" accept="image/*" @change="handleFileUpload" />
              </div>
            </div>
            <div class="form-row">
              <div class="form-group full-width">
                <label>Remarks</label>
                <textarea rows="3" v-model="violationForm.remarks" placeholder="Enter any additional remarks..."></textarea>
              </div>
            </div>
          </div>
        </ion-card-content>
      </ion-card>

      <!-- Actions -->
      <button 
        type="button"
        @click="recordViolation" 
        :disabled="!isFormValid || recording"
        class="submit-btn"
      >
        <span v-if="recording">Recording...</span>
        <span v-else>Record Violation</span>
      </button>
      
      <button type="button" @click="resetForm" class="reset-btn">
        Reset Form
      </button>
    </ion-content>
  </ion-page>
</template>

<script>
import { ref, reactive, onMounted, computed } from "vue";
import { enforcerAPI } from "@/services/api";
import Swal from 'sweetalert2';

export default {
  name: "EnforcerViolations",
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

    // Computed property to check if form is valid
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
        const response = await enforcerAPI.searchViolators(searchQuery.value);
        searchResults.value = response.data.data || [];
      } catch {
        searchResults.value = [];
        await Swal.fire({
          icon: 'error',
          title: 'Search Failed',
          text: 'Search failed. Please try again.',
          confirmButtonColor: '#dc3545'
        });
      } finally {
        isSearching.value = false;
      }
    };

    const selectExistingRecord = (record) => {
      selectedRecord.value = record;
      searchQuery.value = "";
      searchResults.value = [];

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
          icon: 'error',
          title: 'Loading Error',
          text: 'Failed to load violation types.',
          confirmButtonColor: '#dc3545'
        });
      }
    };

    const recordViolation = async () => {
      if (!isFormValid.value) {
        await Swal.fire({
          icon: 'error',
          title: 'Incomplete Form',
          text: 'Please fill in all required fields.',
          confirmButtonColor: '#3085d6'
        });
        return;
      }

      recording.value = true;
      error.value = "";
      success.value = "";

      try {
        // Create FormData if there's a file to upload
        let payload;
        if (idPhoto.value) {
          payload = new FormData();
          
          // Append all form fields
          Object.keys(violationForm).forEach(key => {
            if (violationForm[key] !== null && violationForm[key] !== "") {
              payload.append(key, violationForm[key]);
            }
          });
          
          // Append the photo
          payload.append('id_photo', idPhoto.value);
        } else {
          // Send as JSON if no file
          payload = { ...violationForm };
          // Remove null/empty values
          Object.keys(payload).forEach(key => {
            if (payload[key] === null || payload[key] === "") {
              delete payload[key];
            }
          });
        }

        console.log("Submitting payload:", payload);
        const response = await enforcerAPI.recordViolation(payload);
        console.log("Response:", response.data);
        
        await Swal.fire({
          icon: 'success',
          title: 'Success!',
          text: 'Violation recorded successfully!',
          confirmButtonColor: '#28a745'
        });

      } catch (err) {
        console.error("Error:", err.response?.data || err.message);
        
        await Swal.fire({
          icon: 'error',
          title: 'Error',
          text: err.response?.data?.message || 'Failed to record violation. Please try again.',
          confirmButtonColor: '#dc3545'
        });
      } finally {
        recording.value = false;
      }
    };

    const resetForm = () => {
      Object.keys(violationForm).forEach(key => {
        if (typeof violationForm[key] === 'string') {
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
      
      // Reset file input
      const fileInput = document.querySelector('input[type="file"]');
      if (fileInput) fileInput.value = '';
    };

    const formatCurrency = (amount) => new Intl.NumberFormat("en-PH").format(amount || 0);
    const formatAttempt = (count) => {
      if (!count || count === 0) return "0 Attempt";
      if (count === 1) return "1st Attempt";
      if (count === 2) return "2nd Attempt";
      if (count === 3) return "3rd Attempt";
      return `${count}th Attempt`;
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
    };
  },
};
</script>

<style scoped>
/* Form styling similar to login */
.form-grid {
  width: 100%;
}

.form-row {
  display: flex;
  gap: 16px;
  margin-bottom: 16px;
  flex-wrap: wrap;
}

.form-group {
  flex: 1;
  min-width: 200px;
}

.form-group.full-width {
  width: 100%;
  flex: none;
}

.form-group label {
  display: block;
  font-weight: 600;
  margin-bottom: 8px;
  color: var(--ion-color-dark);
}

.form-group input,
.form-group select,
.form-group textarea {
  width: 100%;
  padding: 12px;
  border: 2px solid #e1e5e9;
  border-radius: 8px;
  font-size: 16px;
  font-family: inherit;
  color: #000;
  transition: border-color 0.3s;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  outline: none;
  border-color: var(--ion-color-primary);
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-group textarea {
  resize: vertical;
  min-height: 80px;
}

/* Search container */
.search-container {
  display: flex;
  gap: 8px;
  margin-bottom: 16px;
}

.search-input {
  flex: 1;
  padding: 12px;
  border: 2px solid #e1e5e9;
  border-radius: 8px;
  font-size: 16px;
  color: #000;
}

.search-btn {
  padding: 12px 20px;
  background: var(--ion-color-primary);
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
}

.search-btn:disabled {
  background: #ccc;
  cursor: not-allowed;
}

/* Selected record notice */
.selected-record-notice {
  background-color: #d4edda;
  border: 1px solid #c3e6cb;
  border-radius: 8px;
  padding: 12px;
  margin-top: 12px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.clear-btn {
  padding: 4px 12px;
  background: #dc3545;
  color: white;
  border: none;
  border-radius: 4px;
  font-size: 12px;
  cursor: pointer;
}

/* Action buttons */
.submit-btn {
  width: 100%;
  padding: 16px;
  background: var(--ion-color-primary);
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 16px;
  font-weight: 600;
  margin-bottom: 12px;
  cursor: pointer;
  transition: background 0.3s;
}

.submit-btn:disabled {
  background: #ccc;
  cursor: not-allowed;
}

.reset-btn {
  width: 100%;
  padding: 16px;
  background: var(--ion-color-medium);
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  margin-bottom: 20px;
}

/* Debug card */
.debug-card {
  margin-top: 20px;
}

.debug-card pre {
  font-size: 12px;
  max-height: 300px;
  overflow-y: auto;
  background: #f8f9fa;
  padding: 12px;
  border-radius: 4px;
}

/* Responsive design */
@media (max-width: 768px) {
  .form-row {
    flex-direction: column;
  }
  
  .form-group {
    min-width: unset;
  }
}
</style>