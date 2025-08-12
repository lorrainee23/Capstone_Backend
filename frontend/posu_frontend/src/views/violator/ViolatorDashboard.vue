<template>
  <SidebarLayout page-title="My Dashboard">
    <div class="violator-dashboard">
      <!-- Welcome Section -->
      <div class="welcome-section">
        <div class="welcome-content">
          <h2>Welcome back, {{ userName }}!</h2>
          <p>Here's an overview of your traffic violation records and payment status.</p>
        </div>
        <div class="welcome-actions">
          <router-link to="/violator/history" class="btn btn-primary">
            View All Violations
          </router-link>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon total">📋</div>
          <div class="stat-content">
            <div class="stat-number">{{ stats.total_violations || 0 }}</div>
            <div class="stat-label">Total Violations</div>
          </div>
        </div>
        
        <div class="stat-card">
          <div class="stat-icon pending">⏳</div>
          <div class="stat-content">
            <div class="stat-number">{{ stats.pending_violations || 0 }}</div>
            <div class="stat-label">Pending Payments</div>
          </div>
        </div>
        
        <div class="stat-card">
          <div class="stat-icon paid">✅</div>
          <div class="stat-content">
            <div class="stat-number">{{ stats.paid_violations || 0 }}</div>
            <div class="stat-label">Paid Violations</div>
          </div>
        </div>
        
        <div class="stat-card">
          <div class="stat-icon amount">💰</div>
          <div class="stat-content">
            <div class="stat-number">₱{{ formatCurrency(stats.total_amount || 0) }}</div>
            <div class="stat-label">Total Amount</div>
          </div>
        </div>
      </div>

      <!-- Pending Payments Alert -->
      <div v-if="stats.pending_violations > 0" class="alert-card">
        <div class="alert-icon">⚠️</div>
        <div class="alert-content">
          <h3>Outstanding Payments</h3>
          <p>You have {{ stats.pending_violations }} pending violation{{ stats.pending_violations > 1 ? 's' : '' }} 
             with a total amount of ₱{{ formatCurrency(stats.pending_amount || 0) }}. 
             Please settle your payments to avoid additional penalties.</p>
        </div>
        <div class="alert-actions">
          <router-link to="/violator/history" class="btn btn-warning">
            Pay Now
          </router-link>
        </div>
      </div>

      <!-- Recent Violations -->
      <div class="dashboard-card">
        <div class="card-header">
          <h3>Recent Violations</h3>
          <router-link to="/violator/history" class="btn btn-primary btn-sm">
            View All
          </router-link>
        </div>
        <div class="violations-container">
          <div v-if="loading" class="loading">
            <div class="spinner"></div>
          </div>
          <div v-else-if="recentViolations.length === 0" class="no-data">
            <div class="no-data-icon">🎉</div>
            <h3>No violations found</h3>
            <p>Great! You have no traffic violations on record. Keep up the safe driving!</p>
          </div>
          <div v-else class="violations-list">
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
                  <div class="violation-location">📍 {{ violation.location }}</div>
                  <div class="violation-date">📅 {{ formatDateTime(violation.date_time) }}</div>
                  <div class="violation-enforcer">👮‍♂️ {{ violation.enforcer?.first_name }} {{ violation.enforcer?.last_name }}</div>
                </div>
              </div>
              <div class="violation-actions">
                <button @click="viewViolation(violation)" class="btn btn-secondary btn-sm">
                  View Details
                </button>
                <button 
                  v-if="violation.status === 'Pending'"
                  @click="payViolation(violation)" 
                  class="btn btn-success btn-sm"
                >
                  Pay Fine
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="quick-actions">
        <h3>Quick Actions</h3>
        <div class="actions-grid">
          <router-link to="/violator/history" class="action-card">
            <div class="action-icon">📋</div>
            <div class="action-title">Violation History</div>
            <div class="action-desc">View all your traffic violations</div>
          </router-link>
          
          <router-link to="/violator/profile" class="action-card">
            <div class="action-icon">👤</div>
            <div class="action-title">Update Profile</div>
            <div class="action-desc">Manage your personal information</div>
          </router-link>
          
          <router-link to="/violator/notifications" class="action-card">
            <div class="action-icon">🔔</div>
            <div class="action-title">Notifications</div>
            <div class="action-desc">Check important messages</div>
          </router-link>
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
import { ref, onMounted, computed } from 'vue'
import SidebarLayout from '@/components/SidebarLayout.vue'
import { violatorAPI } from '@/services/api'
import { useAuthStore } from '@/stores/auth'

export default {
  name: 'ViolatorDashboard',
  components: {
    SidebarLayout
  },
  setup() {
    const { state } = useAuthStore()
    const loading = ref(true)
    const stats = ref({})
    const recentViolations = ref([])
    const showViolationModal = ref(false)
    const selectedViolation = ref(null)
    
    const userName = computed(() => {
      if (!state.user) return ''
      return `${state.user.first_name} ${state.user.last_name}`
    })
    
    const loadDashboardData = async () => {
      try {
        loading.value = true
        const response = await violatorAPI.dashboard()
        
        if (response.data.status === 'success') {
          const data = response.data.data
          stats.value = data.stats || {}
          recentViolations.value = data.recent_violations || []
        }
      } catch (error) {
        console.error('Failed to load dashboard data:', error)
        // Mock data for demo
        stats.value = {
          total_violations: 3,
          pending_violations: 1,
          paid_violations: 2,
          total_amount: 2500,
          pending_amount: 1000
        }
        
        recentViolations.value = [
          {
            id: 1,
            violation: { name: 'Speeding', description: 'Exceeding speed limit' },
            fine_amount: 1000,
            status: 'Pending',
            location: 'EDSA, Quezon City',
            date_time: new Date(Date.now() - 2 * 24 * 60 * 60 * 1000).toISOString(),
            enforcer: { first_name: 'Juan', last_name: 'Cruz' }
          },
          {
            id: 2,
            violation: { name: 'Illegal Parking', description: 'Parking in no parking zone' },
            fine_amount: 750,
            status: 'Paid',
            location: 'Ayala Avenue, Makati',
            date_time: new Date(Date.now() - 7 * 24 * 60 * 60 * 1000).toISOString(),
            enforcer: { first_name: 'Maria', last_name: 'Santos' }
          }
        ]
      } finally {
        loading.value = false
      }
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
      // In a real app, this would integrate with a payment gateway
      alert(`Payment feature for violation #${violation.id} would be implemented here.`)
    }
    
    const paySelectedViolation = () => {
      if (selectedViolation.value) {
        payViolation(selectedViolation.value)
        closeViolationModal()
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
      loadDashboardData()
    })
    
    return {
      loading,
      stats,
      recentViolations,
      showViolationModal,
      selectedViolation,
      userName,
      viewViolation,
      closeViolationModal,
      payViolation,
      paySelectedViolation,
      formatCurrency,
      formatDateTime
    }
  }
}
</script>

<style scoped>
.violator-dashboard {
  max-width: 1400px;
  margin: 0 auto;
}

.welcome-section {
  background: linear-gradient(135deg, #1e3a8a, #3b82f6);
  color: white;
  border-radius: 12px;
  padding: 32px;
  margin-bottom: 32px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.welcome-content h2 {
  font-size: 28px;
  font-weight: 700;
  margin: 0 0 8px 0;
}

.welcome-content p {
  font-size: 16px;
  opacity: 0.9;
  margin: 0;
}

.welcome-actions .btn {
  background: rgba(255, 255, 255, 0.2);
  color: white;
  border: 2px solid rgba(255, 255, 255, 0.3);
}

.welcome-actions .btn:hover {
  background: rgba(255, 255, 255, 0.3);
  border-color: rgba(255, 255, 255, 0.5);
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
.stat-icon.pending { background: linear-gradient(135deg, #f59e0b, #d97706); }
.stat-icon.paid { background: linear-gradient(135deg, #10b981, #059669); }
.stat-icon.amount { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }

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

.alert-card {
  background: linear-gradient(135deg, #fef3c7, #fde68a);
  border: 1px solid #f59e0b;
  border-radius: 12px;
  padding: 24px;
  margin-bottom: 32px;
  display: flex;
  align-items: center;
  gap: 16px;
}

.alert-icon {
  font-size: 32px;
}

.alert-content {
  flex: 1;
}

.alert-content h3 {
  font-size: 18px;
  font-weight: 600;
  color: #92400e;
  margin: 0 0 8px 0;
}

.alert-content p {
  color: #92400e;
  margin: 0;
  line-height: 1.5;
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

.violations-container {
  min-height: 200px;
}

.violations-list {
  padding: 24px;
}

.violation-item {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  padding: 20px 0;
  border-bottom: 1px solid #f3f4f6;
}

.violation-item:last-child {
  border-bottom: none;
}

.violation-header {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 12px;
}

.violation-type {
  font-weight: 600;
  color: #1f2937;
  flex: 1;
}

.violation-amount {
  font-weight: 600;
  color: #059669;
  font-size: 16px;
}

.violation-details {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.violation-location,
.violation-date,
.violation-enforcer {
  font-size: 14px;
  color: #6b7280;
  display: flex;
  align-items: center;
  gap: 8px;
}

.violation-actions {
  display: flex;
  flex-direction: column;
  gap: 8px;
  min-width: 120px;
}

.quick-actions {
  margin-top: 32px;
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
}

.action-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 15px -3px rgba(0, 0, 0, 0.1);
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
  .welcome-section {
    flex-direction: column;
    text-align: center;
    gap: 20px;
  }
  
  .stats-grid {
    grid-template-columns: 1fr;
  }
  
  .actions-grid {
    grid-template-columns: 1fr;
  }
  
  .violation-item {
    flex-direction: column;
    align-items: stretch;
    gap: 16px;
  }
  
  .violation-actions {
    flex-direction: row;
    justify-content: flex-start;
  }
  
  .detail-grid {
    grid-template-columns: 1fr;
  }
}
</style>
