<template>
  <SidebarLayout page-title="Record Violations">
    <div class="enforcer-violations">
      <!-- Header -->
      <div class="page-header">
        <div class="header-left">
          <h2>Record New Violation</h2>
          <p>Issue traffic violations and manage violator records</p>
        </div>
      </div>

      <!-- Violation Form -->
      <div class="violation-form-card">
        <div class="card-header">
          <h3>Violation Details</h3>
        </div>
        <div class="card-body">
          <form @submit.prevent="recordViolation" class="violation-form">
            <div v-if="error" class="alert alert-error">{{ error }}</div>
            <div v-if="success" class="alert alert-success">{{ success }}</div>

            <!-- Violator Selection -->
            <div class="form-section">
              <h4>Violator Information</h4>
              <div class="form-row">
                <div class="form-group">
                  <label class="form-label">Search Violator *</label>
                  <div class="search-container">
                    <input 
                      v-model="violatorSearch" 
                      type="text" 
                      class="form-input" 
                      placeholder="Enter license number or name..."
                      @input="searchViolators"
                      required
                    />
                    <div v-if="violatorResults.length > 0" class="search-results">
                      <div 
                        v-for="violator in violatorResults" 
                        :key="violator.id"
                        @click="selectViolator(violator)"
                        class="search-result-item"
                      >
                        <div class="violator-info">
                          <div class="violator-name">{{ violator.first_name }} {{ violator.last_name }}</div>
                          <div class="violator-details">{{ violator.license_number }} • {{ violator.phone }}</div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div v-if="selectedViolator" class="selected-violator">
                <div class="violator-card">
                  <div class="violator-avatar">
                    {{ getInitials(selectedViolator.first_name, selectedViolator.last_name) }}
                  </div>
                  <div class="violator-details">
                    <div class="violator-name">{{ selectedViolator.first_name }} {{ selectedViolator.last_name }}</div>
                    <div class="violator-info">{{ selectedViolator.license_number }} • {{ selectedViolator.phone }}</div>
                    <div class="violator-address">{{ selectedViolator.address }}</div>
                  </div>
                  <button @click="clearViolator" type="button" class="btn-clear">✕</button>
                </div>
              </div>
            </div>

            <!-- Violation Details -->
            <div class="form-section">
              <h4>Violation Information</h4>
              <div class="form-row">
                <div class="form-group">
                  <label class="form-label">Violation Type *</label>
                  <select v-model="violationForm.violation_id" class="form-select" required>
                    <option value="">Select Violation</option>
                    <option 
                      v-for="violation in violationTypes" 
                      :key="violation.id" 
                      :value="violation.id"
                    >
                      {{ violation.name }} - ₱{{ formatCurrency(violation.fine_amount) }}
                    </option>
                  </select>
                </div>
                <div class="form-group">
                  <label class="form-label">Date & Time *</label>
                  <input 
                    v-model="violationForm.date_time" 
                    type="datetime-local" 
                    class="form-input" 
                    required 
                  />
                </div>
              </div>
            </div>

            <!-- Location and Additional Details -->
            <div class="form-section">
              <h4>Additional Information</h4>
              <div class="form-group">
                <label class="form-label">Location *</label>
                <input 
                  v-model="violationForm.location" 
                  type="text" 
                  class="form-input" 
                  placeholder="Enter violation location..."
                  required 
                />
              </div>
              
              <div class="form-group">
                <label class="form-label">Remarks</label>
                <textarea 
                  v-model="violationForm.remarks" 
                  class="form-input" 
                  rows="4"
                  placeholder="Additional notes or observations..."
                ></textarea>
              </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
              <button @click="resetForm" type="button" class="btn btn-secondary">Reset Form</button>
              <button type="submit" class="btn btn-primary" :disabled="recording">
                <span v-if="recording" class="spinner-small"></span>
                {{ recording ? 'Recording...' : 'Record Violation' }}
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Recent Violations -->
      <div class="recent-violations">
        <div class="card-header">
          <h3>Recent Violations</h3>
          <router-link to="/enforcer/transactions" class="btn btn-primary btn-sm">
            View All Transactions
          </router-link>
        </div>
        <div class="violations-list">
          <div v-if="recentViolations.length === 0" class="no-data">
            <div class="no-data-icon">📋</div>
            <p>No violations recorded yet</p>
          </div>
          <div v-else>
            <div v-for="violation in recentViolations" :key="violation.id" class="violation-item">
              <div class="violation-info">
                <div class="violation-header">
                  <span class="violation-type">{{ violation.violation?.name }}</span>
                  <span class="violation-amount">₱{{ formatCurrency(violation.fine_amount) }}</span>
                  <span class="status-badge" :class="`status-${violation.status?.toLowerCase()}`">
                    {{ violation.status }}
                  </span>
                </div>
                <div class="violation-details">
                  <div class="violator-name">{{ violation.violator?.first_name }} {{ violation.violator?.last_name }}</div>
                  <div class="violation-meta">
                    {{ violation.location }} • {{ formatDateTime(violation.date_time) }}
                  </div>
                </div>
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
import { enforcerAPI } from '@/services/api'

export default {
  name: 'EnforcerViolations',
  components: {
    SidebarLayout
  },
  setup() {
    const recording = ref(false)
    const error = ref('')
    const success = ref('')
    const violatorSearch = ref('')
    const violatorResults = ref([])
    const selectedViolator = ref(null)
    const violationTypes = ref([])
    const recentViolations = ref([])
    
    const violationForm = ref({
      violation_id: '',
      date_time: '',
      location: '',
      remarks: ''
    })
    
    const loadViolationTypes = async () => {
      try {
        const response = await enforcerAPI.getViolationTypes()
        if (response.data.status === 'success') {
          violationTypes.value = response.data.data || []
        }
      } catch (error) {
        console.error('Failed to load violation types:', error)
      }
    }
    
    const loadRecentViolations = async () => {
      try {
        const response = await enforcerAPI.getTransactions()
        if (response.data.status === 'success') {
          recentViolations.value = (response.data.data || []).slice(0, 5)
        }
      } catch (error) {
        console.error('Failed to load recent violations:', error)
      }
    }
    
    const searchViolators = async () => {
      if (violatorSearch.value.length < 2) {
        violatorResults.value = []
        return
      }
      
      try {
        const response = await enforcerAPI.getViolators()
        if (response.data.status === 'success') {
          const allViolators = response.data.data || []
          const search = violatorSearch.value.toLowerCase()
          violatorResults.value = allViolators.filter(violator => 
            violator.first_name.toLowerCase().includes(search) ||
            violator.last_name.toLowerCase().includes(search) ||
            violator.license_number.toLowerCase().includes(search)
          ).slice(0, 5)
        }
      } catch (error) {
        console.error('Failed to search violators:', error)
      }
    }
    
    const selectViolator = (violator) => {
      selectedViolator.value = violator
      violatorSearch.value = `${violator.first_name} ${violator.last_name} (${violator.license_number})`
      violatorResults.value = []
    }
    
    const clearViolator = () => {
      selectedViolator.value = null
      violatorSearch.value = ''
      violatorResults.value = []
    }
    
    const recordViolation = async () => {
      if (!selectedViolator.value) {
        error.value = 'Please select a violator'
        return
      }
      
      try {
        recording.value = true
        error.value = ''
        success.value = ''
        
        const data = {
          ...violationForm.value,
          violator_id: selectedViolator.value.id
        }
        
        const response = await enforcerAPI.recordViolation(data)
        
        if (response.data.success) {
          success.value = 'Violation recorded successfully!'
          resetForm()
          await loadRecentViolations()
        } else {
          error.value = response.data.message || 'Failed to record violation'
        }
      } catch (err) {
        error.value = err.response?.data?.message || 'Failed to record violation'
      } finally {
        recording.value = false
      }
    }
    
    const resetForm = () => {
      violationForm.value = {
        violation_id: '',
        date_time: '',
        location: '',
        remarks: ''
      }
      clearViolator()
      error.value = ''
      success.value = ''
    }
    
    const formatCurrency = (amount) => {
      return new Intl.NumberFormat('en-PH').format(amount)
    }
    
    const formatDateTime = (dateString) => {
      if (!dateString) return ''
      return new Date(dateString).toLocaleString('en-PH', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
    }
    
    const getInitials = (firstName, lastName) => {
      return `${firstName?.charAt(0) || ''}${lastName?.charAt(0) || ''}`.toUpperCase()
    }
    
    // Set default date/time to now
    const setDefaultDateTime = () => {
      const now = new Date()
      const localDateTime = new Date(now.getTime() - now.getTimezoneOffset() * 60000)
      violationForm.value.date_time = localDateTime.toISOString().slice(0, 16)
    }
    
    onMounted(() => {
      loadViolationTypes()
      loadRecentViolations()
      setDefaultDateTime()
    })
    
    return {
      recording,
      error,
      success,
      violatorSearch,
      violatorResults,
      selectedViolator,
      violationTypes,
      recentViolations,
      violationForm,
      searchViolators,
      selectViolator,
      clearViolator,
      recordViolation,
      resetForm,
      formatCurrency,
      formatDateTime,
      getInitials
    }
  }
}
</script>

<style scoped>
.enforcer-violations {
  max-width: 1200px;
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

.violation-form-card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  margin-bottom: 32px;
  overflow: hidden;
}

.card-header {
  padding: 24px;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: center;
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

.form-section {
  margin-bottom: 32px;
  padding-bottom: 24px;
  border-bottom: 1px solid #f3f4f6;
}

.form-section:last-of-type {
  border-bottom: none;
}

.form-section h4 {
  font-size: 16px;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 16px;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

.search-container {
  position: relative;
}

.search-results {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: white;
  border: 1px solid #e5e7eb;
  border-top: none;
  border-radius: 0 0 8px 8px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  z-index: 10;
  max-height: 200px;
  overflow-y: auto;
}

.search-result-item {
  padding: 12px 16px;
  cursor: pointer;
  border-bottom: 1px solid #f3f4f6;
  transition: background-color 0.2s ease;
}

.search-result-item:hover {
  background: #f9fafb;
}

.search-result-item:last-child {
  border-bottom: none;
}

.violator-info .violator-name {
  font-weight: 500;
  color: #1f2937;
}

.violator-info .violator-details {
  font-size: 12px;
  color: #6b7280;
}

.selected-violator {
  margin-top: 16px;
}

.violator-card {
  background: #f9fafb;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  padding: 16px;
  display: flex;
  align-items: center;
  gap: 16px;
  position: relative;
}

.violator-avatar {
  width: 48px;
  height: 48px;
  background: linear-gradient(135deg, #1e3a8a, #3b82f6);
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 16px;
}

.violator-card .violator-details {
  flex: 1;
}

.violator-card .violator-name {
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 4px;
}

.violator-card .violator-info {
  font-size: 14px;
  color: #6b7280;
  margin-bottom: 2px;
}

.violator-card .violator-address {
  font-size: 12px;
  color: #9ca3af;
}

.btn-clear {
  position: absolute;
  top: 8px;
  right: 8px;
  background: #fee2e2;
  color: #dc2626;
  border: none;
  border-radius: 50%;
  width: 24px;
  height: 24px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  transition: background-color 0.2s ease;
}

.btn-clear:hover {
  background: #fecaca;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 24px;
  padding-top: 24px;
  border-top: 1px solid #e5e7eb;
}

.recent-violations {
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.violations-list {
  padding: 24px;
}

.violation-item {
  padding: 16px 0;
  border-bottom: 1px solid #f3f4f6;
}

.violation-item:last-child {
  border-bottom: none;
}

.violation-header {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 8px;
}

.violation-type {
  font-weight: 600;
  color: #1f2937;
  flex: 1;
}

.violation-amount {
  font-weight: 600;
  color: #059669;
}

.violation-details .violator-name {
  font-weight: 500;
  color: #1f2937;
  margin-bottom: 4px;
}

.violation-meta {
  font-size: 14px;
  color: #6b7280;
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
  .form-row {
    grid-template-columns: 1fr;
  }
  
  .form-actions {
    flex-direction: column;
  }
  
  .violation-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
  }
}
</style>
