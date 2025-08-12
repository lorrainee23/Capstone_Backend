<template>
  <SidebarLayout page-title="Transaction Management">
    <div class="enforcer-transactions">
      <!-- Header -->
      <div class="page-header">
        <div class="header-left">
          <h2>My Transactions</h2>
          <p>Manage violation transactions and payments</p>
        </div>
      </div>

      <!-- Filters -->
      <div class="filters-card">
        <div class="filters-row">
          <div class="filter-group">
            <label class="form-label">Status</label>
            <select v-model="filters.status" class="form-select">
              <option value="">All Status</option>
              <option value="Pending">Pending</option>
              <option value="Paid">Paid</option>
            </select>
          </div>
          <div class="filter-group">
            <label class="form-label">Date Range</label>
            <select v-model="filters.dateRange" class="form-select">
              <option value="">All Time</option>
              <option value="today">Today</option>
              <option value="week">This Week</option>
              <option value="month">This Month</option>
            </select>
          </div>
          <div class="filter-group">
            <label class="form-label">Search</label>
            <input 
              v-model="filters.search" 
              type="text" 
              class="form-input" 
              placeholder="Search by violator name or license..."
            />
          </div>
        </div>
      </div>

      <!-- Transactions Table -->
      <div class="table-card">
        <div v-if="loading" class="loading">
          <div class="spinner"></div>
        </div>
        
        <div v-else>
          <table class="table">
            <thead>
              <tr>
                <th>Transaction ID</th>
                <th>Violator</th>
                <th>Violation</th>
                <th>Fine Amount</th>
                <th>Status</th>
                <th>Date</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="transaction in filteredTransactions" :key="transaction.id">
                <td>
                  <div class="transaction-id">#{{ transaction.id }}</div>
                </td>
                <td>
                  <div class="violator-info">
                    <div class="violator-name">{{ transaction.violator?.first_name }} {{ transaction.violator?.last_name }}</div>
                    <div class="violator-license">{{ transaction.violator?.license_number }}</div>
                  </div>
                </td>
                <td>
                  <div class="violation-name">{{ transaction.violation?.name }}</div>
                </td>
                <td>
                  <div class="fine-amount">₱{{ formatCurrency(transaction.fine_amount) }}</div>
                </td>
                <td>
                  <span class="status-badge" :class="`status-${transaction.status?.toLowerCase()}`">
                    {{ transaction.status }}
                  </span>
                </td>
                <td>{{ formatDateTime(transaction.date_time) }}</td>
                <td>
                  <div class="action-buttons">
                    <button @click="viewTransaction(transaction)" class="btn-icon-sm" title="View Details">
                      👁️
                    </button>
                    <button 
                      v-if="transaction.status === 'Pending'"
                      @click="markAsPaid(transaction)" 
                      class="btn-icon-sm btn-success" 
                      title="Mark as Paid"
                    >
                      ✅
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
          
          <div v-if="filteredTransactions.length === 0" class="no-data">
            <div class="no-data-icon">💳</div>
            <h3>No transactions found</h3>
            <p>{{ filters.search || filters.status || filters.dateRange ? 'No transactions match your filters.' : 'No transactions recorded yet.' }}</p>
          </div>
        </div>
      </div>

      <!-- Transaction Details Modal -->
      <div v-if="showDetailsModal" class="modal-overlay" @click="closeDetailsModal">
        <div class="modal modal-large" @click.stop>
          <div class="modal-header">
            <h3>Transaction Details</h3>
            <button @click="closeDetailsModal" class="modal-close">✕</button>
          </div>
          <div class="modal-body" v-if="selectedTransaction">
            <div class="transaction-details">
              <div class="detail-section">
                <h4>Transaction Information</h4>
                <div class="detail-grid">
                  <div class="detail-item">
                    <label>Transaction ID:</label>
                    <span>#{{ selectedTransaction.id }}</span>
                  </div>
                  <div class="detail-item">
                    <label>Status:</label>
                    <span class="status-badge" :class="`status-${selectedTransaction.status?.toLowerCase()}`">
                      {{ selectedTransaction.status }}
                    </span>
                  </div>
                  <div class="detail-item">
                    <label>Date & Time:</label>
                    <span>{{ formatDateTime(selectedTransaction.date_time) }}</span>
                  </div>
                  <div class="detail-item">
                    <label>Fine Amount:</label>
                    <span class="fine-amount">₱{{ formatCurrency(selectedTransaction.fine_amount) }}</span>
                  </div>
                </div>
              </div>
              
              <div class="detail-section">
                <h4>Violator Information</h4>
                <div class="detail-grid">
                  <div class="detail-item">
                    <label>Name:</label>
                    <span>{{ selectedTransaction.violator?.first_name }} {{ selectedTransaction.violator?.last_name }}</span>
                  </div>
                  <div class="detail-item">
                    <label>License Number:</label>
                    <span>{{ selectedTransaction.violator?.license_number }}</span>
                  </div>
                  <div class="detail-item">
                    <label>Phone:</label>
                    <span>{{ selectedTransaction.violator?.phone }}</span>
                  </div>
                  <div class="detail-item">
                    <label>Email:</label>
                    <span>{{ selectedTransaction.violator?.email }}</span>
                  </div>
                </div>
                <div class="detail-item full-width">
                  <label>Address:</label>
                  <span>{{ selectedTransaction.violator?.address }}</span>
                </div>
              </div>
              
              <div class="detail-section">
                <h4>Violation Details</h4>
                <div class="detail-grid">
                  <div class="detail-item">
                    <label>Violation Type:</label>
                    <span>{{ selectedTransaction.violation?.name }}</span>
                  </div>
                  <div class="detail-item">
                    <label>Location:</label>
                    <span>{{ selectedTransaction.location }}</span>
                  </div>
                </div>
                <div class="detail-item full-width">
                  <label>Description:</label>
                  <span>{{ selectedTransaction.violation?.description }}</span>
                </div>
                <div v-if="selectedTransaction.remarks" class="detail-item full-width">
                  <label>Remarks:</label>
                  <span>{{ selectedTransaction.remarks }}</span>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button @click="closeDetailsModal" class="btn btn-secondary">Close</button>
            <button 
              v-if="selectedTransaction?.status === 'Pending'"
              @click="markSelectedAsPaid" 
              class="btn btn-success"
            >
              Mark as Paid
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
import { enforcerAPI } from '@/services/api'

export default {
  name: 'EnforcerTransactions',
  components: {
    SidebarLayout
  },
  setup() {
    const loading = ref(true)
    const transactions = ref([])
    const showDetailsModal = ref(false)
    const selectedTransaction = ref(null)
    
    const filters = ref({
      status: '',
      dateRange: '',
      search: ''
    })
    
    const filteredTransactions = computed(() => {
      let filtered = transactions.value
      
      // Status filter
      if (filters.value.status) {
        filtered = filtered.filter(t => t.status === filters.value.status)
      }
      
      // Date range filter
      if (filters.value.dateRange) {
        const now = new Date()
        const today = new Date(now.getFullYear(), now.getMonth(), now.getDate())
        
        filtered = filtered.filter(t => {
          const transactionDate = new Date(t.date_time)
          
          switch (filters.value.dateRange) {
            case 'today':
              return transactionDate >= today
            case 'week': {
              const weekAgo = new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000)
              return transactionDate >= weekAgo
            }
            case 'month': {
              const monthAgo = new Date(today.getFullYear(), today.getMonth() - 1, today.getDate())
              return transactionDate >= monthAgo
            }
            default:
              return true
          }
        })
      }
      
      // Search filter
      if (filters.value.search) {
        const search = filters.value.search.toLowerCase()
        filtered = filtered.filter(t => 
          t.violator?.first_name?.toLowerCase().includes(search) ||
          t.violator?.last_name?.toLowerCase().includes(search) ||
          t.violator?.license_number?.toLowerCase().includes(search) ||
          t.violation?.name?.toLowerCase().includes(search)
        )
      }
      
      return filtered.sort((a, b) => new Date(b.date_time) - new Date(a.date_time))
    })
    
    const loadTransactions = async () => {
      try {
        loading.value = true
        const response = await enforcerAPI.getTransactions()
        
        if (response.data.status === 'success') {
          transactions.value = response.data.data || []
        }
      } catch (error) {
        console.error('Failed to load transactions:', error)
      } finally {
        loading.value = false
      }
    }
    
    const viewTransaction = (transaction) => {
      selectedTransaction.value = transaction
      showDetailsModal.value = true
    }
    
    const closeDetailsModal = () => {
      showDetailsModal.value = false
      selectedTransaction.value = null
    }
    
    const markAsPaid = async (transaction) => {
      if (confirm(`Mark transaction #${transaction.id} as paid?`)) {
        try {
          await enforcerAPI.updateTransaction(transaction.id, { status: 'Paid' })
          await loadTransactions()
        } catch (error) {
          console.error('Failed to update transaction:', error)
          alert('Failed to update transaction status')
        }
      }
    }
    
    const markSelectedAsPaid = async () => {
      if (selectedTransaction.value) {
        await markAsPaid(selectedTransaction.value)
        closeDetailsModal()
      }
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
      loadTransactions()
    })
    
    return {
      loading,
      transactions,
      filteredTransactions,
      filters,
      showDetailsModal,
      selectedTransaction,
      viewTransaction,
      closeDetailsModal,
      markAsPaid,
      markSelectedAsPaid,
      formatCurrency,
      formatDateTime
    }
  }
}
</script>

<style scoped>
.enforcer-transactions {
  max-width: 1400px;
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

.filters-card {
  background: white;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  margin-bottom: 24px;
}

.filters-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
}

.table-card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.transaction-id {
  font-family: 'Courier New', monospace;
  font-weight: 600;
  color: #1f2937;
}

.violator-info {
  display: flex;
  flex-direction: column;
}

.violator-name {
  font-weight: 500;
  color: #1f2937;
}

.violator-license {
  font-size: 12px;
  color: #6b7280;
  font-family: 'Courier New', monospace;
}

.violation-name {
  font-weight: 500;
  color: #1f2937;
}

.fine-amount {
  font-weight: 600;
  color: #059669;
  font-size: 16px;
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

.btn-icon-sm.btn-success {
  background: #d1fae5;
  color: #059669;
}

.btn-icon-sm.btn-success:hover {
  background: #a7f3d0;
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
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

.modal-large {
  max-width: 800px;
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

.transaction-details {
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
  .filters-row {
    grid-template-columns: 1fr;
  }
  
  .detail-grid {
    grid-template-columns: 1fr;
  }
  
  .modal-large {
    max-width: 95vw;
  }
}
</style>
