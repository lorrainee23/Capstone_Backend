<template>
  <SidebarLayout page-title="Violation History">
    <div class="violator-history">
      <!-- Header Section -->
      <div class="page-header">
        <div class="header-content">
          <h2>My Violation History</h2>
          <p>View and manage all your traffic violations and payment records.</p>
        </div>
        <div class="header-stats">
          <div class="stat-item">
            <span class="stat-number">{{ totalViolations }}</span>
            <span class="stat-label">Total</span>
          </div>
          <div class="stat-item">
            <span class="stat-number pending">{{ pendingCount }}</span>
            <span class="stat-label">Pending</span>
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
              <option value="Overdue">Overdue</option>
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

      <!-- Summary Cards -->
      <div class="summary-cards" v-if="pendingViolations.length > 0">
        <div class="summary-card urgent">
          <div class="card-icon">⚠️</div>
          <div class="card-content">
            <h3>Outstanding Payments</h3>
            <div class="card-stats">
              <span class="amount">₱{{ formatCurrency(totalPendingAmount) }}</span>
              <span class="count">{{ pendingViolations.length }} violation{{ pendingViolations.length > 1 ? 's' : '' }}</span>
            </div>
            <button @click="payAllPending" class="btn btn-warning btn-sm">
              Pay All Pending
            </button>
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
                <td>{{ formatDate(violation.date_time) }}</td>
                <td>
                  <div class="violation-cell">
                    <span class="violation-name">{{ violation.violation?.name }}</span>
                    <span class="enforcer-name">by {{ violation.enforcer?.first_name }} {{ violation.enforcer?.last_name }}</span>
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
                      View
                    </button>
                    <button 
                      v-if="violation.status === 'Pending'"
                      @click="payViolation(violation)" 
                      class="btn btn-success btn-xs"
                    >
                      Pay
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
                </div>
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
                    <span>{{ selectedViolation.enforcer?.first_name }} {{ selectedViolation.enforcer?.last_name }}</span>
                  </div>
                  <div class="detail-item">
                    <label>Badge Number:</label>
                    <span>{{ selectedViolation.enforcer?.id || 'N/A' }}</span>
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
                    <span>{{ selectedViolation.payment_method || 'N/A' }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button @click="closeViolationModal" class="btn btn-secondary">Close</button>
            <button 
              v-if="selectedViolation?.status === 'Pending'"
              @click="paySelectedViolation" 
              class="btn btn-success"
            >
              Pay Fine
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
        const response = await violatorAPI.history()
        
        if (response.data.status === 'success') {
          violations.value = response.data.data || []
        }
      } catch (error) {
        console.error('Failed to load violations:', error)
        // Mock data for demo
        violations.value = [
          {
            id: 1,
            violation: { name: 'Speeding', description: 'Exceeding speed limit by 20 km/h' },
            fine_amount: 1000,
            status: 'Pending',
            location: 'EDSA, Quezon City',
            date_time: new Date(Date.now() - 2 * 24 * 60 * 60 * 1000).toISOString(),
            enforcer: { first_name: 'Juan', last_name: 'Cruz' },
            remarks: 'Caught by speed camera'
          },
          {
            id: 2,
            violation: { name: 'Illegal Parking', description: 'Parking in no parking zone' },
            fine_amount: 750,
            status: 'Paid',
            location: 'Ayala Avenue, Makati',
            date_time: new Date(Date.now() - 7 * 24 * 60 * 60 * 1000).toISOString(),
            enforcer: { first_name: 'Maria', last_name: 'Santos' },
            payment_date: new Date(Date.now() - 5 * 24 * 60 * 60 * 1000).toISOString(),
            payment_method: 'Online Payment'
          },
          {
            id: 3,
            violation: { name: 'Running Red Light', description: 'Failure to stop at red light' },
            fine_amount: 1500,
            status: 'Paid',
            location: 'Ortigas Avenue, Pasig',
            date_time: new Date(Date.now() - 14 * 24 * 60 * 60 * 1000).toISOString(),
            enforcer: { first_name: 'Pedro', last_name: 'Reyes' },
            payment_date: new Date(Date.now() - 10 * 24 * 60 * 60 * 1000).toISOString(),
            payment_method: 'Cash Payment'
          }
        ]
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
          `${v.enforcer?.first_name} ${v.enforcer?.last_name}`.toLowerCase().includes(search)
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
      pendingViolations.value.reduce((sum, v) => sum + v.fine_amount, 0)
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
    
    const payViolation = (violation) => {
      alert(`Payment feature for violation #${violation.id} would be implemented here.`)
    }
    
    const paySelectedViolation = () => {
      if (selectedViolation.value) {
        payViolation(selectedViolation.value)
        closeViolationModal()
      }
    }
    
    const payAllPending = () => {
      alert(`Payment feature for all pending violations (₱${formatCurrency(totalPendingAmount.value)}) would be implemented here.`)
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
      payViolation,
      paySelectedViolation,
      payAllPending,
      formatCurrency,
      formatDate,
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

.summary-cards {
  margin-bottom: 24px;
}

.summary-card {
  background: white;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  display: flex;
  align-items: center;
  gap: 20px;
}

.summary-card.urgent {
  border-left: 4px solid #f59e0b;
  background: linear-gradient(135deg, #fef3c7, #fde68a);
}

.card-icon {
  font-size: 32px;
}

.card-content {
  flex: 1;
}

.card-content h3 {
  font-size: 18px;
  font-weight: 600;
  color: #92400e;
  margin: 0 0 8px 0;
}

.card-stats {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-bottom: 12px;
}

.card-stats .amount {
  font-size: 24px;
  font-weight: 700;
  color: #92400e;
}

.card-stats .count {
  font-size: 14px;
  color: #92400e;
  opacity: 0.8;
}

.violations-section {
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.section-header {
  padding: 24px;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: center;
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

/* Modal styles */
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
  
  .violations-table {
    font-size: 14px;
  }
  
  .detail-grid {
    grid-template-columns: 1fr;
  }
}
</style>
