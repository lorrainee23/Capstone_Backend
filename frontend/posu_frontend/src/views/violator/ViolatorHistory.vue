<template>
  <SidebarLayout page-title="Violation History">
    <div class="violator-history">
      <!-- Header Section -->
      <div class="page-header">
        <div class="header-content">
          <h2>My Violation History</h2>
          <p>View your traffic violations and payment records. For payments, please visit the POSU office.</p>
        </div>
        <div class="header-stats">
          <div class="stat-item">
            <span class="stat-number">{{ totalViolations }}</span>
            <span class="stat-label">Total</span>
          </div>
          <div class="stat-item">
            <span class="stat-number pending">{{ pendingCount }}</span>
            <span class="stat-label">Unpaid</span>
          </div>
          <div class="stat-item">
            <span class="stat-number paid">{{ paidCount }}</span>
            <span class="stat-label">Paid</span>
          </div>
        </div>
      </div>

      <!-- Filters Section -->
      <div class="filters-section">
        <div class="filters-row">
          <div class="filter-group">
            <label>Status</label>
            <select v-model="filters.status" @change="applyFilters">
              <option value="">All Status</option>
              <option value="Pending">Pending</option>
              <option value="Paid">Paid</option>
            </select>
          </div>
          
          <div class="filter-group">
            <label>Search</label>
            <input 
              type="text" 
              v-model="filters.search" 
              @input="applyFilters"
              placeholder="Search violations..."
              class="search-input"
            >
          </div>
          
          <button @click="clearFilters" class="btn btn-secondary">
            Clear Filters
          </button>
        </div>
      </div>

      <!-- Payment Notice Card -->
      <div class="payment-notice-card" v-if="pendingViolations.length > 0">
        <div class="card-icon">🏢</div>
        <div class="card-content">
          <h3>Payment Information</h3>
          <div class="card-stats">
            <span class="amount">₱{{ formatCurrency(totalPendingAmount) }}</span>
            <span class="count">{{ pendingViolations.length }} unpaid violation{{ pendingViolations.length > 1 ? 's' : '' }}</span>
          </div>
          <div class="office-info">
            <p><strong>To pay your fines, visit the POSU office:</strong></p>
            <p>📍 San Fabian Bancheto</p>
            <p>🕐 Monday - Friday, 8:00 AM - 5:00 PM</p>
            <p>📞 09123456789</p>
            <p><small>Bring valid ID and reference your violation ID numbers</small></p>
          </div>
        </div>
      </div>

      <!-- Violations List -->
      <div class="violations-section">
        <div class="section-header">
          <h3>Violations ({{ filteredViolations.length }})</h3>
        </div>

        <div v-if="loading" class="loading">
          <div class="spinner"></div>
          <p>Loading violations...</p>
        </div>

        <div v-else-if="filteredViolations.length === 0" class="no-data">
          <div class="no-data-icon">📋</div>
          <h3>No violations found</h3>
          <p v-if="hasActiveFilters">Try adjusting your filters to see more results.</p>
          <p v-else>Great! You have no traffic violations on record.</p>
        </div>

        <!-- Violations Table -->
        <div v-else class="violations-table">
          <table>
            <thead>
              <tr>
                <th>Ticket Number</th>
                <th>Date</th>
                <th>Violation</th>
                <th>Location</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="violation in paginatedViolations" :key="violation.id">
                <td class="id-cell">#{{ violation.ticket_number }}</td>
                <td>{{ formatDateTime(violation.date_time) }}</td>
                <td>
                  <div class="violation-cell">
                    <span class="violation-name">{{ violation.violation?.name }}</span>
                    <span class="enforcer-name">by {{ violation.apprehending_officer?.first_name }} {{ violation.apprehending_officer?.middle_name }} {{ violation.apprehending_officer?.last_name }}</span>
                  </div>
                </td>
                <td>{{ violation.location }}</td>
                <td class="amount-cell">₱{{ formatCurrency(violation.fine_amount) }}</td>
                <td>
                  <span class="status-badge" :class="`status-${violation.status?.toLowerCase()}`">
                    {{ violation.status }}
                  </span>
                </td>
                <td>
                  <div class="action-buttons">
                    <button @click="viewViolation(violation)" class="btn btn-secondary btn-xs">
                      View Details
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="totalPages > 1" class="pagination">
          <button 
            @click="currentPage = 1" 
            :disabled="currentPage === 1"
            class="btn btn-secondary btn-sm"
          >
            First
          </button>
          <button 
            @click="currentPage--" 
            :disabled="currentPage === 1"
            class="btn btn-secondary btn-sm"
          >
            Previous
          </button>
          
          <span class="page-info">
            Page {{ currentPage }} of {{ totalPages }}
          </span>
          
          <button 
            @click="currentPage++" 
            :disabled="currentPage === totalPages"
            class="btn btn-secondary btn-sm"
          >
            Next
          </button>
          <button 
            @click="currentPage = totalPages" 
            :disabled="currentPage === totalPages"
            class="btn btn-secondary btn-sm"
          >
            Last
          </button>
        </div>
      </div>

      <!-- Export/Print Options -->
      <div class="export-section">
        <h3>Export Options</h3>
        <div class="export-buttons">
          <button @click="printViolations" class="btn btn-secondary">
            🖨️ Print Violations
          </button>
          <button @click="exportToPDF" class="btn btn-secondary">
            📄 Export to PDF
          </button>
        </div>
      </div>

      <!-- Violation Details Modal -->
      <div v-if="showViolationModal" class="modal-overlay" @click="closeViolationModal">
        <div class="modal" @click.stop>
          <div class="modal-header">
            <h3>Violation Details</h3>
            <button @click="closeViolationModal" class="modal-close">✕</button>
          </div>
          <div class="modal-body" v-if="selectedViolation">
            <div class="violation-details-modal">
              <div class="detail-section">
                <h4>Violation Information</h4>
                <div class="detail-grid">
                  <div class="detail-item">
                    <label>ticket_number:</label>
                    <span>#{{ selectedViolation.ticket_number }}</span>
                  </div>
                  <div class="detail-item">
                    <label>Violation Type:</label>
                    <span>{{ selectedViolation.violation?.name }}</span>
                  </div>
                  <div class="detail-item">
                    <label>Fine Amount:</label>
                    <span class="fine-amount">₱{{ formatCurrency(selectedViolation.fine_amount) }}</span>
                  </div>
                  <div class="detail-item">
                    <label>Status:</label>
                    <span class="status-badge" :class="`status-${selectedViolation.status?.toLowerCase()}`">
                      {{ selectedViolation.status }}
                    </span>
                  </div>
                  <div class="detail-item">
                    <label>Date & Time:</label>
                    <span>{{ formatDateTime(selectedViolation.date_time) }}</span>
                  </div>
                </div>
              </div>
              
              <div class="detail-section">
                <h4>Location & Details</h4>
                <div class="detail-item full-width">
                  <label>Location:</label>
                  <span>{{ selectedViolation.location }}</span>
                </div><br>
                <div class="detail-item full-width">
                  <label>Description:</label>
                  <span>{{ selectedViolation.violation?.description }}</span>
                </div>
                <div v-if="selectedViolation.remarks" class="detail-item full-width">
                  <label>Remarks:</label>
                  <span>{{ selectedViolation.remarks }}</span>
                </div>
              </div>
              
              <div class="detail-section">
                <h4>Enforcer Information</h4>
                <div class="detail-grid">
                  <div class="detail-item">
                    <label>Enforcer:</label>
                    <span>{{ selectedViolation.apprehending_officer?.apprehen }} {{ selectedViolation.apprehending_officer?.middle_name }} {{ selectedViolation.apprehending_officer?.last_name }}</span>
                  </div>
                  <div class="detail-item">
                    <label>Badge Number:</label>
                    <span>{{ selectedViolation.apprehending_officer?.id || 'N/A' }}</span>
                  </div>
                </div>
              </div>
              
              <div v-if="selectedViolation.payment_date" class="detail-section">
                <h4>Payment Information</h4>
                <div class="detail-grid">
                  <div class="detail-item">
                    <label>Payment Date:</label>
                    <span>{{ formatDateTime(selectedViolation.payment_date) }}</span>
                  </div>
                  <div class="detail-item">
                    <label>Payment Method:</label>
                    <span>{{ selectedViolation.payment_method || 'Office Payment' }}</span>
                  </div>
                </div>
              </div>

              <div v-if="selectedViolation.status === 'Pending'" class="payment-instructions">
                <div class="instructions-icon">💡</div>
                <div class="instructions-content">
                  <h4>Payment Instructions</h4>
                  <p>To pay this violation fine:</p>
                  <ol>
                    <li>Visit the POSU office during business hours</li>
                    <li>Bring a valid government-issued ID</li>
                    <li>Reference Violation ID: <strong>#{{ selectedViolation.ticket_number}}</strong></li>
                    <li>Pay the amount of <strong>₱{{ formatCurrency(selectedViolation.fine_amount) }}</strong></li>
                  </ol>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button @click="closeViolationModal" class="btn btn-secondary">Close</button>
            <button @click="printViolation(selectedViolation)" class="btn btn-primary">
              Print Details
            </button>
          </div>
        </div>
      </div>
    </div>
  </SidebarLayout>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import SidebarLayout from '@/components/SidebarLayout.vue'
import { violatorAPI } from '@/services/api'

export default {
  name: 'ViolatorHistory',
  components: {
    SidebarLayout
  },
  setup() {
    const loading = ref(true)
    const violations = ref([])
    const currentPage = ref(1)
    const itemsPerPage = ref(10)
    const showViolationModal = ref(false)
    const selectedViolation = ref(null)
    
    const filters = ref({
      status: '',
      search: ''
    })
    
    const loadViolations = async () => {
      try {
        loading.value = true
        const response = await violatorAPI.getHistory()
        
        if (response.data.status === 'success') {
          violations.value = response.data.data.data
        }
      } catch (error) {
        console.error('Failed to load violations:', error)
      } finally {
        loading.value = false
      }
    }
    
    const filteredViolations = computed(() => {
      let result = violations.value
      
      if (filters.value.status) {
        result = result.filter(v => v.status === filters.value.status)
      }
      
      if (filters.value.search) {
        const search = filters.value.search.toLowerCase()
        result = result.filter(v => 
          v.violation?.name.toLowerCase().includes(search) ||
          v.location.toLowerCase().includes(search) ||
          `${v.apprehending_officer?.first_name} ${v.apprehending_officer?.middle_name} ${v.sapprehending_officer?.last_name}`.toLowerCase().includes(search) ||
          v.id.toString().includes(search)
        )
      }
      
      return result.sort((a, b) => new Date(b.date_time) - new Date(a.date_time))
    })
    
    const paginatedViolations = computed(() => {
      const start = (currentPage.value - 1) * itemsPerPage.value
      const end = start + itemsPerPage.value
      return filteredViolations.value.slice(start, end)
    })
    
    const totalPages = computed(() => {
      return Math.ceil(filteredViolations.value.length / itemsPerPage.value)
    })
    
    const totalViolations = computed(() => violations.value.length)
    const pendingCount = computed(() => violations.value.filter(v => v.status === 'Pending').length)
    const paidCount = computed(() => violations.value.filter(v => v.status === 'Paid').length)
    
    const pendingViolations = computed(() => violations.value.filter(v => v.status === 'Pending'))
    const totalPendingAmount = computed(() => 
  pendingViolations.value.reduce((sum, v) => sum + Number(v.fine_amount || 0), 0)
)

    
    const hasActiveFilters = computed(() => 
      filters.value.status || filters.value.search
    )
    
    const applyFilters = () => {
      currentPage.value = 1
    }
    
    const clearFilters = () => {
      filters.value = {
        status: '',
        search: ''
      }
      currentPage.value = 1
    }
    
    const viewViolation = (violation) => {
      selectedViolation.value = violation
      showViolationModal.value = true
    }
    
    const closeViolationModal = () => {
      showViolationModal.value = false
      selectedViolation.value = null
    }
    
    const printViolations = () => {
      alert('Print functionality would open a print-friendly version of your violations list.')
    }
    
    const exportToPDF = () => {
      alert('Export functionality would generate a PDF of your violations history.')
    }
    
    const printViolation = (violation) => {
      alert(`Print functionality would generate a detailed report for violation #${violation.ticket_number}.`)
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
    
    onMounted(() => {
      loadViolations()
    })
    
    return {
      loading,
      violations,
      currentPage,
      filters,
      showViolationModal,
      selectedViolation,
      filteredViolations,
      paginatedViolations,
      totalPages,
      totalViolations,
      pendingCount,
      paidCount,
      pendingViolations,
      totalPendingAmount,
      hasActiveFilters,
      applyFilters,
      clearFilters,
      viewViolation,
      closeViolationModal,
      printViolations,
      exportToPDF,
      printViolation,
      formatCurrency,
      formatDateTime
    }
  }
}
</script>

<style scoped>
.violator-history {
  max-width: 1400px;
  margin: 0 auto;
}

.page-header {
  background: linear-gradient(135deg, #1e3a8a, #3b82f6);
  color: white;
  border-radius: 12px;
  padding: 32px;
  margin-bottom: 32px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.header-content h2 {
  font-size: 28px;
  font-weight: 700;
  margin: 0 0 8px 0;
}

.header-content p {
  font-size: 16px;
  opacity: 0.9;
  margin: 0;
}

.header-stats {
  display: flex;
  gap: 32px;
}

.stat-item {
  text-align: center;
}

.stat-number {
  display: block;
  font-size: 32px;
  font-weight: 700;
  line-height: 1;
}

.stat-number.pending {
  color: #fbbf24;
}

.stat-number.paid {
  color: #34d399;
}

.stat-label {
  display: block;
  font-size: 14px;
  opacity: 0.8;
  margin-top: 4px;
}

.filters-section {
  background: white;
  border-radius: 12px;
  padding: 24px;
  margin-bottom: 24px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.filters-row {
  display: flex;
  gap: 20px;
  align-items: end;
  flex-wrap: wrap;
}

.filter-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
  min-width: 150px;
}

.filter-group label {
  font-size: 14px;
  font-weight: 500;
  color: #374151;
}

.filter-group select,
.search-input {
  padding: 10px 12px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 14px;
  transition: border-color 0.2s ease;
}

.filter-group select:focus,
.search-input:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.payment-notice-card {
  background: white;
  border-radius: 12px;
  padding: 24px;
  margin-bottom: 24px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  border-left: 4px solid #3b82f6;
  display: flex;
  align-items: flex-start;
  gap: 20px;
}

.card-icon {
  font-size: 32px;
  flex-shrink: 0;
}

.card-content h3 {
  font-size: 18px;
  font-weight: 600;
  color: #1f2937;
  margin: 0 0 12px 0;
}

.card-stats {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-bottom: 16px;
}

.card-stats .amount {
  font-size: 24px;
  font-weight: 700;
  color: #059669;
}

.card-stats .count {
  font-size: 14px;
  color: #6b7280;
}

.office-info {
  background: #f8fafc;
  padding: 16px;
  border-radius: 8px;
  border-left: 3px solid #3b82f6;
}

.office-info p {
  margin: 0 0 8px 0;
  font-size: 14px;
  color: #374151;
}

.office-info p:last-child {
  margin-bottom: 0;
}

.office-info small {
  color: #6b7280;
  font-style: italic;
}

.violations-section {
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  margin-bottom: 32px;
}

.section-header {
  padding: 24px;
  border-bottom: 1px solid #e5e7eb;
}

.section-header h3 {
  font-size: 18px;
  font-weight: 600;
  color: #1f2937;
  margin: 0;
}

.violations-table {
  overflow-x: auto;
}

.violations-table table {
  width: 100%;
  border-collapse: collapse;
}

.violations-table th {
  background: #f9fafb;
  padding: 16px;
  text-align: left;
  font-weight: 600;
  color: #374151;
  border-bottom: 1px solid #e5e7eb;
}

.violations-table td {
  padding: 16px;
  border-bottom: 1px solid #f3f4f6;
  vertical-align: top;
}

.id-cell {
  font-weight: 600;
  color: #3b82f6;
}

.violation-cell {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.violation-name {
  font-weight: 500;
  color: #1f2937;
}

.enforcer-name {
  font-size: 12px;
  color: #6b7280;
}

.amount-cell {
  font-weight: 600;
  color: #059669;
}

.action-buttons {
  display: flex;
  gap: 8px;
}

.btn-xs {
  padding: 4px 8px;
  font-size: 12px;
}

.export-section {
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  padding: 24px;
  margin-bottom: 32px;
}

.export-section h3 {
  font-size: 18px;
  font-weight: 600;
  color: #1f2937;
  margin: 0 0 16px 0;
}

.export-buttons {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}

.pagination {
  padding: 24px;
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 12px;
  border-top: 1px solid #e5e7eb;
}

.page-info {
  font-size: 14px;
  color: #6b7280;
  margin: 0 16px;
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

.payment-instructions {
  background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
  border: 1px solid #0ea5e9;
  border-radius: 8px;
  padding: 16px;
  margin-top: 16px;
  display: flex;
  align-items: flex-start;
  gap: 12px;
}

.instructions-icon {
  font-size: 20px;
  flex-shrink: 0;
  margin-top: 2px;
}

.instructions-content h4 {
  font-size: 14px;
  font-weight: 600;
  color: #0c4a6e;
  margin: 0 0 8px 0;
}

.instructions-content p {
  font-size: 13px;
  color: #075985;
  margin: 0 0 8px 0;
}

.instructions-content ol {
  font-size: 13px;
  color: #075985;
  margin: 0;
  padding-left: 16px;
}

.instructions-content li {
  margin-bottom: 4px;
}

/* Modal styles remain the same as previous components */
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
  max-width: 600px;
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

.modal-footer {
  padding: 24px;
  border-top: 1px solid #e5e7eb;
  display: flex;
  justify-content: flex-end;
  gap: 12px;
}

.violation-details-modal {
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

.fine-amount {
  font-weight: 600;
  color: #059669;
  font-size: 16px;
}

@media (max-width: 768px) {
  .page-header {
    flex-direction: column;
    text-align: center;
    gap: 20px;
  }
  
  .filters-row {
    flex-direction: column;
    align-items: stretch;
  }
  
  .payment-notice-card {
    flex-direction: column;
    gap: 16px;
  }
  
  .violations-table {
    font-size: 14px;
  }
  
  .detail-grid {
    grid-template-columns: 1fr;
  }
  
  .export-buttons {
    flex-direction: column;
  }
}
</style>