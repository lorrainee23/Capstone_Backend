<template>
  <SidebarLayout page-title="Violation Management">
    <div class="admin-violations">
      <!-- Header Actions -->
      <div class="page-header">
        <div class="header-left">
          <h2>Violation Types</h2>
          <p>Manage violation categories and fine amounts</p>
        </div>
        <div class="header-right">
          <button @click="showCreateModal = true" class="btn btn-primary">
            <span class="btn-icon">➕</span>
            Add New Violation
          </button>
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
          <div class="filter-group">
            <label class="form-label">Sort by Fine Amount</label>
            <select v-model="sortOrder" class="form-select">
              <option value="">Default Order</option>
              <option value="asc">Lowest to Highest</option>
              <option value="desc">Highest to Lowest</option>
            </select>
          </div>
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
                <th>Violation Name</th>
                <th>Description</th>
                <th>Fine Amount</th>
                <th>Total Cases</th>
                <th>Created</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="violation in filteredViolations" :key="violation.id">
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
                <td>
                  <div class="case-count">
                    {{ violation.transactions_count || 0 }} cases
                  </div>
                </td>
                <td>{{ formatDate(violation.created_at) }}</td>
                <td>
                  <div class="action-buttons">
                    <button @click="editViolation(violation)" class="btn-icon-sm" title="Edit">
                      ✏️
                    </button>
                    <button @click="deleteViolation(violation)" class="btn-icon-sm btn-danger" title="Delete">
                      🗑️
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
  </SidebarLayout>
</template>

<script>
import { ref, onMounted, computed } from 'vue'
import SidebarLayout from '@/components/SidebarLayout.vue'
import { adminAPI } from '@/services/api'
import Swal from 'sweetalert2'

export default {
  name: 'AdminViolations',
  components: {
    SidebarLayout
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
        error.value = ''
        
        let response
        if (showEditModal.value && editingViolation.value) {
          response = await adminAPI.updateViolation(editingViolation.value.id, violationForm.value)
        } else {
          response = await adminAPI.createViolation(violationForm.value)
        }
        
        if (response.data.success) {
          await loadViolations()
          closeModals()
        } else {
          error.value = response.data.message || 'Failed to save violation'
        }
      } catch (err) {
        error.value = err.response?.data?.message || 'Failed to save violation'
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
    
    const deleteViolation = async (violation) => {
      const result = await Swal.fire({
        title: 'Delete violation?',
        text: violation?.name || 'This violation',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete',
        cancelButtonText: 'Cancel'
      })
      if (result.isConfirmed) {
        try {
          await adminAPI.deleteViolation(violation.id)
          await loadViolations()
          await Swal.fire({
            title: 'Deleted',
            icon: 'success',
            timer: 1500,
            showConfirmButton: false
          })
        } catch (error) {
          console.error('Failed to delete violation:', error)
          await Swal.fire({
            title: 'Delete failed',
            text: 'Please try again.',
            icon: 'error'
          })
        }
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
      deleteViolation,
      closeModals,
      formatCurrency,
      formatDate,
      truncateText
    }
  }
}
</script>

<style scoped>
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
  gap: 8px;
}

.btn-icon-sm {
  width: 32px;
  height: 32px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
  transition: all 0.2s ease;
  background: #f3f4f6;
  color: #374151;
}

.btn-icon-sm:hover {
  background: #e5e7eb;
}

.btn-icon-sm.btn-danger {
  background: #fee2e2;
  color: #dc2626;
}

.btn-icon-sm.btn-danger:hover {
  background: #fecaca;
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
