<template>
  <SidebarLayout page-title="Enforcer Dashboard">
    <div class="enforcer-dashboard">
      <!-- Stats Cards -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon total">📋</div>
          <div class="stat-content">
            <div class="stat-number">{{ stats.total_apprehensions || 0 }}</div>
            <div class="stat-label">Total Apprehensions</div>
          </div>
        </div>
        
        <div class="stat-card">
          <div class="stat-icon today">📅</div>
          <div class="stat-content">
            <div class="stat-number">{{ stats.today_apprehensions || 0 }}</div>
            <div class="stat-label">Today's Apprehensions</div>
          </div>
        </div>
        
        <div class="stat-card">
          <div class="stat-icon pending">⏳</div>
          <div class="stat-content">
            <div class="stat-number">{{ stats.pending_apprehensions || 0 }}</div>
            <div class="stat-label">Pending Payments</div>
          </div>
        </div>
        
        <div class="stat-card">
          <div class="stat-icon paid">✅</div>
          <div class="stat-content">
            <div class="stat-number">{{ stats.paid_apprehensions || 0 }}</div>
            <div class="stat-label">Paid Violations</div>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="quick-actions">
        <h3>Quick Actions</h3>
        <div class="actions-grid">
          
          <router-link to="/enforcer/transactions" class="action-card">
            <div class="action-icon">💳</div>
            <div class="action-title">View Transactions</div>
            <div class="action-desc">Manage violation transactions</div>
          </router-link>
          
          <router-link to="/enforcer/performance" class="action-card">
            <div class="action-icon">📊</div>
            <div class="action-title">Performance Stats</div>
            <div class="action-desc">View your performance metrics</div>
          </router-link>
        </div>
      </div>

      <!-- Recent Transactions -->
      <div class="dashboard-card">
        <div class="card-header">
          <h3>Recent Transactions</h3>
          <router-link to="/enforcer/transactions" class="btn btn-primary btn-sm">
            View All
          </router-link>
        </div>
        <div class="table-container">
          <div v-if="loading" class="loading">
            <div class="spinner"></div>
          </div>
          <table v-else class="table">
            <thead>
              <tr>
                <th>Violator</th>
                <th>Violation</th>
                <th>Fine Amount</th>
                <th>Status</th>
                <th>Date</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="transaction in recentTransactions" :key="transaction.id">
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
                <td>{{ formatDate(transaction.date_time) }}</td>
                <td>
                  <div class="action-buttons">
                    <button @click="viewTransaction(transaction)" class="btn-icon-sm" title="View Details">
                      👁️
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
          
          <div v-if="recentTransactions.length === 0 && !loading" class="no-data">
            <div class="no-data-icon">📋</div>
            <h3>No transactions yet</h3>
            <p>Start recording violations to see them here.</p>
            <router-link to="/enforcer/violations" class="btn btn-primary">
              Record First Violation
            </router-link>
          </div>
        </div>
      </div>

      <!-- Weekly Performance Chart -->
      <div class="dashboard-card">
        <div class="card-header">
          <h3>Weekly Performance</h3>
        </div>
        <div class="chart-container">
          <div v-if="weeklyPerformance.length === 0" class="no-data">
            No performance data available
          </div>
          <div v-else class="simple-chart">
            <div 
              v-for="(day, index) in weeklyPerformance" 
              :key="index"
              class="chart-bar"
              :style="{ height: `${(day.count / maxPerformanceCount) * 100}%` }"
              :title="`${day.date}: ${day.count} apprehensions`"
            >
              <span class="bar-label">{{ day.count }}</span>
            </div>
          </div>
          <div class="chart-labels">
            <span v-for="(day, index) in weeklyPerformance" :key="index" class="chart-label">
              {{ formatChartDate(day.date) }}
            </span>
          </div>
        </div>
      </div>

      <!-- Transaction Details Modal -->
      <div v-if="showTransactionModal" class="modal-overlay" @click="closeTransactionModal">
        <div class="modal" @click.stop>
          <div class="modal-header">
            <h3>Transaction Details</h3>
            <button @click="closeTransactionModal" class="modal-close">✕</button>
          </div>
          <div class="modal-body" v-if="selectedTransaction">
            <div class="transaction-details">
              <div class="detail-section">
                <h4>Violator Information</h4>
                <div class="detail-grid">
                  <div class="detail-item">
                    <label>Name:</label>
                    <span>{{ selectedTransaction.violator?.first_name }}  {{ selectedTransaction.violator?.middle_name }} {{ selectedTransaction.violator?.last_name }}</span>
                  </div>
                  <div class="detail-item">
                    <label>License Number:</label>
                    <span>{{ selectedTransaction.violator?.license_number }}</span>
                  </div>
                  <div class="detail-item">
                    <label>Contact:</label>
                    <span>{{ selectedTransaction.violator?.mobile_number }}</span>
                  </div>
                </div>
              </div>
              
              <div class="detail-section">
                <h4>Violation Details</h4>
                <div class="detail-grid">
                  <div class="detail-item">
                    <label>Violation:</label>
                    <span>{{ selectedTransaction.violation?.name }}</span>
                  </div>
                  <div class="detail-item">
                    <label>Fine Amount:</label>
                    <span>₱{{ formatCurrency(selectedTransaction.fine_amount) }}</span>
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
                </div>
              </div>
              
              <div v-if="selectedTransaction.location" class="detail-section">
                <h4>Location</h4>
                <p>{{ selectedTransaction.location }}</p>
              </div>
              
              <div v-if="selectedTransaction.remarks" class="detail-section">
                <h4>Remarks</h4>
                <p>{{ selectedTransaction.remarks }}</p>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button @click="closeTransactionModal" class="btn btn-secondary">Close</button>
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
  name: 'EnforcerDashboard',
  components: {
    SidebarLayout
  },
  setup() {
    const loading = ref(true)
    const stats = ref({})
    const recentTransactions = ref([])
    const weeklyPerformance = ref([])
    const showTransactionModal = ref(false)
    const selectedTransaction = ref(null)
    
    const maxPerformanceCount = computed(() => {
      return Math.max(...weeklyPerformance.value.map(d => d.count), 1)
    })
    
    const loadDashboardData = async () => {
      try {
        loading.value = true
        const response = await enforcerAPI.dashboard()
        
        if (response.data.status === 'success') {
          const data = response.data.data
          stats.value = data.stats || {}
          recentTransactions.value = data.recent_transactions || []
          weeklyPerformance.value = data.weekly_performance || []
        }
      } catch (error) {
        console.error('Failed to load dashboard data:', error)
      } finally {
        loading.value = false
      }
    }
    
    const viewTransaction = (transaction) => {
      selectedTransaction.value = transaction
      showTransactionModal.value = true
    }
    
    const closeTransactionModal = () => {
      showTransactionModal.value = false
      selectedTransaction.value = null
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
    
    const formatChartDate = (dateString) => {
      if (!dateString) return ''
      return new Date(dateString).toLocaleDateString('en-PH', {
        month: 'short',
        day: 'numeric'
      })
    }
    
    onMounted(() => {
      loadDashboardData()
    })
    
    return {
      loading,
      stats,
      recentTransactions,
      weeklyPerformance,
      maxPerformanceCount,
      showTransactionModal,
      selectedTransaction,
      viewTransaction,
      closeTransactionModal,
      formatCurrency,
      formatDate,
      formatDateTime,
      formatChartDate
    }
  }
}
</script>

<style scoped>
.enforcer-dashboard {
  max-width: 1400px;
  margin: 0 auto;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 24px;
  margin-bottom: 32px;
}

.stat-card {
  background: white;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  display: flex;
  align-items: center;
  gap: 16px;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 15px -3px rgba(0, 0, 0, 0.1);
}

.stat-icon {
  width: 60px;
  height: 60px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
}

.stat-icon.total { background: linear-gradient(135deg, #3b82f6, #1e40af); }
.stat-icon.today { background: linear-gradient(135deg, #10b981, #059669); }
.stat-icon.pending { background: linear-gradient(135deg, #f59e0b, #d97706); }
.stat-icon.paid { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }

.stat-number {
  font-size: 28px;
  font-weight: 700;
  color: #1f2937;
  line-height: 1;
}

.stat-label {
  font-size: 14px;
  color: #6b7280;
  margin-top: 4px;
}

.quick-actions {
  margin-bottom: 32px;
}

.quick-actions h3 {
  font-size: 20px;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 20px;
}

.actions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
}

.action-card {
  background: white;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  text-decoration: none;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  border: 2px solid transparent;
}

.action-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 15px -3px rgba(0, 0, 0, 0.1);
}

.action-card.primary {
  border-color: #3b82f6;
  background: linear-gradient(135deg, #eff6ff, #dbeafe);
}

.action-icon {
  font-size: 32px;
  margin-bottom: 16px;
}

.action-title {
  font-size: 16px;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 8px;
}

.action-desc {
  font-size: 14px;
  color: #6b7280;
  line-height: 1.4;
}

.dashboard-card {
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

.btn-sm {
  padding: 8px 16px;
  font-size: 14px;
}

.table-container {
  overflow-x: auto;
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

.chart-container {
  padding: 24px;
  height: 250px;
}

.simple-chart {
  display: flex;
  align-items: end;
  justify-content: space-around;
  height: 180px;
  gap: 8px;
  margin-bottom: 16px;
}

.chart-bar {
  background: linear-gradient(135deg, #3b82f6, #1e40af);
  border-radius: 4px 4px 0 0;
  min-height: 20px;
  flex: 1;
  max-width: 40px;
  position: relative;
  transition: opacity 0.2s ease;
  cursor: pointer;
}

.chart-bar:hover {
  opacity: 0.8;
}

.bar-label {
  position: absolute;
  top: -20px;
  left: 50%;
  transform: translateX(-50%);
  font-size: 12px;
  font-weight: 500;
  color: #374151;
}

.chart-labels {
  display: flex;
  justify-content: space-around;
}

.chart-label {
  font-size: 12px;
  color: #6b7280;
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
  margin-bottom: 20px;
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
  .stats-grid {
    grid-template-columns: 1fr;
  }
  
  .actions-grid {
    grid-template-columns: 1fr;
  }
  
  .detail-grid {
    grid-template-columns: 1fr;
  }
}
</style>
