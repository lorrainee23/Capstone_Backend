<template>
  <SidebarLayout page-title="Violation Management">
    <div class="admin-violations">
      <!-- Header Actions -->
       <header class="dashboard-header">
        <div class="header-content">
          <h1>Violation Types</h1>
          <p>Manage violation categories and fine amounts</p>
        </div>
        <button class="refresh-btn" @click="loadViolations" aria-label="Refresh Dashboard">
          <svg 
            width="20" 
            height="20" 
            viewBox="0 0 24 24" 
            fill="none" 
            stroke="currentColor" 
            stroke-width="2" 
            stroke-linecap="round" 
            stroke-linejoin="round"
          >
            <path d="M21 12a9 9 0 1 1-3-6.7" />
            <polyline points="21 3 21 9 15 9" />
          </svg>
          Refresh
        </button>
      </header>
      <div class="page-header">
        <div class="header-left">
          
        </div>
      </div>

      <!-- Search and Filters -->
      <div class="filters-card">
        <div class="filters-row">
          <div class="filter-group">
            <label class="form-label">Search</label>
            <input 
              v-model="searchQuery" 
              type="text" 
              class="form-input" 
              placeholder="Search violations..."
            />
          </div>
          <button @click="showCreateModal = true" class="btn-add btn-primary">
            <span class="btn-icon">➕</span>
            Add New Violation
          </button>
        </div>
      </div>

      <!-- Violations Table -->
      <div class="table-card">
        <div v-if="loading" class="loading">
          <div class="spinner"></div>
        </div>
        
        <div v-else>
          <table class="table">
            <thead>
              <tr>
                <th>No.</th>
                <th>Violation Name</th>
                <th>Description</th>
                <th>Fine Amount</th>
                <th>Created</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="violation in filteredViolations" :key="violation.id">
                <td>
                  <div class="violation-name">{{ violation.id }}</div>
                </td>
                <td>
                  <div class="violation-name">{{ violation.name }}</div>
                </td>
                <td>
                  <div class="violation-description">
                    {{ truncateText(violation.description, 80) }}
                  </div>
                </td>
                <td>
                  <div class="fine-amount">₱{{ formatCurrency(violation.fine_amount) }}</div>
                </td>
                <td>{{ formatDate(violation.created_at) }}</td>
                <td>
                  <div class="action-buttons">
                    <button @click="editViolation(violation)" class="btn-icon-sm btn-edit" title="Edit Transaction">
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                      </svg>
                    </button>
                    <button @click="viewViolationDetails(violation)" class="btn-icon-sm btn-success" title="View Details">
                      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
          
          <div v-if="filteredViolations.length === 0" class="no-data">
            <div class="no-data-icon">⚠️</div>
            <h3>No violations found</h3>
            <p>{{ searchQuery ? 'No violations match your search.' : 'No violation types have been created yet.' }}</p>
          </div>
        </div>
      </div>

      <!-- Create/Edit Violation Modal -->
      <div v-if="showCreateModal || showEditModal" class="modal-overlay" @click="closeModals">
        <div class="modal" @click.stop>
          <div class="modal-header">
            <h3>{{ showEditModal ? 'Edit Violation Type' : 'Create New Violation Type' }}</h3>
            <button @click="closeModals" class="modal-close">✕</button>
          </div>
          
          <form @submit.prevent="saveViolation" class="modal-body">
            <div v-if="error" class="alert alert-error">{{ error }}</div>
            
            <div class="form-group">
              <label class="form-label">Violation Name *</label>
              <input 
                v-model="violationForm.name" 
                type="text" 
                class="form-input" 
                placeholder="e.g., Speeding, Illegal Parking"
                maxlength="100"
                required 
              />
              <div class="form-help">Maximum 100 characters</div>
            </div>
            
            <div class="form-group">
              <label class="form-label">Description *</label>
              <textarea 
                v-model="violationForm.description" 
                class="form-input" 
                rows="4"
                placeholder="Detailed description of the violation..."
                required
              ></textarea>
            </div>
            
            <div class="form-group">
              <label class="form-label">Fine Amount (₱) *</label>
              <input 
                v-model.number="violationForm.fine_amount" 
                type="number" 
                class="form-input" 
                placeholder="0.00"
                min="0"
                step="0.01"
                required 
              />
              <div class="form-help">Enter the fine amount in Philippine Pesos</div>
            </div>
          </form>
          
          <div class="modal-footer">
            <button @click="closeModals" type="button" class="btn btn-secondary">Cancel</button>
            <button @click="saveViolation" type="submit" class="btn btn-primary" :disabled="saving">
              <span v-if="saving" class="spinner-small"></span>
              {{ saving ? 'Saving...' : (showEditModal ? 'Update Violation' : 'Create Violation') }}
            </button>
          </div>
        </div>
      </div>
    </div>
    <ModalComponent 
      :show="showDetailsModal"
      title="Violation Details"
      @close="closeDetails"
    >
  <div v-if="selectedViolation" class="violation-details">
    <div class="detail-section">
      <h4>Violation Information</h4>
      <div class="detail-grid">
        <div class="detail-item">
          <label>Name:</label>
          <span>{{ selectedViolation.name }}</span>
        </div>
        <div class="detail-item">
          <label>Fine Amount:</label>
          <span class="fine-amount">
            ₱{{ formatCurrency(selectedViolation.fine_amount) }}
          </span>
        </div>
        <div class="detail-item full-width">
          <label>Description:</label>
          <span>{{ selectedViolation.description }}</span>
        </div>
      </div><br>
      <div class="detail-item">
        <label>Total Cases:</label>
        <span>{{ selectedViolation.transactions_count }}</span>
      </div>
    </div>
    <div v-if="selectedViolation.transactions?.length" class="detail-section">
      <h4>Related Transactions</h4>
      <ul>
        <li v-for="(txn, index) in selectedViolation.transactions" :key="index">
          {{ formatDate(txn.date_time) }} - 
          ₱{{ formatCurrency(txn.fine_amount) }} - 
          {{ txn.status }}
        </li>
      </ul>
    </div>
  </div>
  <template #footer>
    <button @click="closeDetails" class="btn btn-secondary">Close</button>
  </template>
</ModalComponent>

  </SidebarLayout>
</template>

<script>
import { ref, onMounted, computed } from 'vue'
import SidebarLayout from '@/components/SidebarLayout.vue'
import ModalComponent from '@/components/ModalComponent.vue'
import { adminAPI } from '@/services/api'
import Swal from 'sweetalert2'

export default {
  name: 'AdminViolations',
  components: {
    SidebarLayout,
    ModalComponent,
  },
  setup() {
    const loading = ref(true)
    const saving = ref(false)
    const violations = ref([])
    const showCreateModal = ref(false)
    const showEditModal = ref(false)
    const error = ref('')
    const searchQuery = ref('')
    const sortOrder = ref('')
    const showDetailsModal = ref(false)
    const selectedViolation = ref(null)

    const closeDetails = () => {
      showDetailsModal.value = false
      selectedViolation.value = null
    }
    
    const violationForm = ref({
      name: '',
      description: '',
      fine_amount: ''
    })
    
    const editingViolation = ref(null)
    
    const filteredViolations = computed(() => {
      let filtered = violations.value
      
      // Search filter
      if (searchQuery.value) {
        const search = searchQuery.value.toLowerCase()
        filtered = filtered.filter(violation => 
          violation.name.toLowerCase().includes(search) ||
          violation.description.toLowerCase().includes(search)
        )
      }
      
      // Sort by fine amount
      if (sortOrder.value) {
        filtered = [...filtered].sort((a, b) => {
          if (sortOrder.value === 'asc') {
            return a.fine_amount - b.fine_amount
          } else {
            return b.fine_amount - a.fine_amount
          }
        })
      }
      
      return filtered
    })
    
    const loadViolations = async () => {
      try {
        loading.value = true
        const response = await adminAPI.getViolations()
        
        if (response.data.status === 'success') {
          violations.value = response.data.data || []
        }
      } catch (error) {
        console.error('Failed to load violations:', error)
      } finally {
        loading.value = false
      }
    }
    
    const saveViolation = async () => {
  try {
    saving.value = true

    let response
    if (showEditModal.value && editingViolation.value) {
      response = await adminAPI.updateViolation(editingViolation.value.id, violationForm.value)
    } else {
      response = await adminAPI.createViolation(violationForm.value)
    }

    if (response.data.status === 'success') {
      closeModals()
      await Swal.fire({
        icon: 'success',
        title: 'Success',
        text: response.data.message,
        timer: 1500,
        showConfirmButton: true
      })
      await loadViolations()
    } else {
      await Swal.fire({
        icon: 'error',
        title: 'Error',
        text: response.data.message || 'Failed to save violation'
      })
    }
  } catch (err) {
    await Swal.fire({
      icon: 'error',
      title: 'Error',
      text: err.response?.data?.message || 'Failed to save violation'
    })
  } finally {
    saving.value = false
  }
}
    
    const editViolation = (violation) => {
      editingViolation.value = violation
      violationForm.value = {
        name: violation.name,
        description: violation.description,
        fine_amount: violation.fine_amount
      }
      showEditModal.value = true
    }
    
    const viewViolationDetails = async (violation) => {
      try {
        const response = await adminAPI.getViolation(violation.id)
        if (response.data.status === 'success') {
          selectedViolation.value = response.data.data
          showDetailsModal.value = true
        } else {
          await Swal.fire('Error', response.data.message || 'Failed to load details.', 'error')
        }
      } catch (err) {
        const errorMessage = err.response?.data?.message || 'Failed to load violation details.'
        await Swal.fire('Error', errorMessage, 'error')
      }
    }
    
    const closeModals = () => {
      showCreateModal.value = false
      showEditModal.value = false
      editingViolation.value = null
      error.value = ''
      violationForm.value = {
        name: '',
        description: '',
        fine_amount: ''
      }
    }
    
    const formatCurrency = (amount) => {
      return new Intl.NumberFormat('en-PH').format(amount)
    }
    
    const formatDate = (dateString) => {
      if (!dateString) return ''
      return new Date(dateString).toLocaleDateString('en-PH', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      })
    }
    
    const truncateText = (text, maxLength) => {
      if (text.length <= maxLength) return text
      return text.substring(0, maxLength) + '...'
    }
    
    onMounted(() => {
      loadViolations()
    })
    
    return {
      loading,
      saving,
      violations,
      filteredViolations,
      searchQuery,
      sortOrder,
      showCreateModal,
      showEditModal,
      violationForm,
      error,
      saveViolation,
      editViolation,
      viewViolationDetails,
      closeDetails,
      showDetailsModal,
      selectedViolation,
      closeModals,
      formatCurrency,
      formatDate,
      truncateText
    }
  }
}
</script>

<style scoped>
.admin-dashboard {
  background-color: #f9fafb;
  padding: 32px;
  min-height: 100vh;
}

.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 32px;
background: linear-gradient(135deg, #1e3a8a, #3b82f6);
  padding: 40px;
  border-radius: 24px;
  color: white;
  box-shadow: 0 20px 40px rgba(102, 126, 234, 0.3);
}

.header-content h1 {
  color: white;
  margin-bottom: 4px;
  letter-spacing: -0.025em;
}

.header-content p {
  color: rgba(255, 255, 255, 0.9);
}

.refresh-btn {
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: white;
  padding: 12px 20px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 500;
  transition: all 0.3s ease;
  cursor: pointer;
  backdrop-filter: blur(10px);
}

.refresh-btn:hover {
  background: rgba(255, 255, 255, 0.2);
  transform: translateY(-2px);
}

.admin-violations {
  max-width: 1400px;
  margin: 0 auto;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 32px;
}

.header-left h2 {
  font-size: 28px;
  font-weight: 700;
  color: #1f2937;
  margin: 0 0 4px 0;
}

.header-left p {
  color: #6b7280;
  margin: 0;
}
.btn-add {
  position: relative;
  bottom: -30px;
    height: 45px;
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 300;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-block;
    text-align: center;
}
.btn-icon {
  margin-right: 8px;
}

.filters-card {
  background: white;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  margin-bottom: 24px;
}

.filters-row {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 20px;
}

.table-card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.violation-name {
  font-weight: 600;
  color: #1f2937;
}

.violation-description {
  color: #6b7280;
  line-height: 1.4;
}

.fine-amount {
  font-weight: 600;
  color: #059669;
  font-size: 16px;
}

.case-count {
  color: #6b7280;
  font-size: 14px;
}

.action-buttons {
  display: flex;
  align-items: center;
  justify-content: flex-start;
  gap: 8px;
}

.btn-icon-sm {
  width: 36px;
  height: 36px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
  background: #f8fafc;
  color: #475569;
}

.btn-icon-sm:hover {
  background: #e2e8f0;
  transform: scale(1.05);
}

.btn-icon-sm.btn-danger {
  background: #fee2e2;
  color: #dc2626;
}

.btn-icon-sm.btn-danger:hover {
  background: #fecaca;
}
.btn-icon-sm.btn-warning {
  background: #fff3cd; 
  color: #ae8406;         
}
.btn-icon-sm.btn-success {
  background: #d1fae5; 
  color: #047857;      
}
.btn-icon-sm.btn-success:hover {
  background: #a7f3d0;  
}
.btn-icon-sm.btn-warning:hover {
  background: #ffe58f;      
}
.btn-icon-sm.btn-view {
  color: #6366f1; 
}

.btn-icon-sm.btn-view:hover {
  background-color: #e0e7ff;
}

.btn-icon-sm.btn-edit {
  color: #3b82f6;
}

.btn-icon-sm.btn-edit:hover {
  background-color: #dbeafe; 
}

.btn-icon-sm.btn-success:hover {
  background: #bbf7d0;
}

.no-data {
  text-align: center;
  padding: 60px 20px;
}

.no-data-icon {
  font-size: 48px;
  margin-bottom: 16px;
}

.no-data h3 {
  font-size: 18px;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 8px;
}

.no-data p {
  color: #6b7280;
  margin: 0;
}

/* Modal Styles */
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

.modal {
  background: white;
  border-radius: 12px;
  width: 100%;
  max-width: 500px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

.modal-header {
  padding: 24px;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h3 {
  font-size: 18px;
  font-weight: 600;
  color: #1f2937;
  margin: 0;
}

.modal-close {
  background: none;
  border: none;
  font-size: 20px;
  cursor: pointer;
  color: #6b7280;
  padding: 4px;
  border-radius: 4px;
}

.modal-close:hover {
  background: #f3f4f6;
}

.modal-body {
  padding: 24px;
}

.form-help {
  font-size: 12px;
  color: #6b7280;
  margin-top: 4px;
}

.modal-footer {
  padding: 24px;
  border-top: 1px solid #e5e7eb;
  display: flex;
  justify-content: flex-end;
  gap: 12px;
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

.violation-details {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.detail-section h4 {
  font-size: 16px;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 12px;
}

.detail-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.detail-item {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.detail-item.full-width {
  grid-column: 1 / -1;
}

.detail-item label {
  font-size: 12px;
  font-weight: 500;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.detail-item span {
  color: #1f2937;
}

@media (max-width: 768px) {
  .page-header {
    flex-direction: column;
    align-items: stretch;
    gap: 16px;
  }
  
  .filters-row {
    grid-template-columns: 1fr;
  }
  
  .modal {
    margin: 20px;
  }
}
</style>
