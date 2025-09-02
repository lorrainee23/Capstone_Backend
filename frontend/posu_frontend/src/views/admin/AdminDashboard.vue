<template>
  <SidebarLayout page-title="Admin Dashboard">
    <div class="admin-dashboard">
      <!-- Stats Cards -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon violators">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              <circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="m22 22-5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              <circle cx="19.5" cy="19.5" r="2.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </div>
          <div class="stat-content">
            <div class="stat-number">{{ stats.total_violators || 0 }}</div>
            <div class="stat-label">Total Violators</div>
          </div>
        </div>
        
        <div class="stat-card">
          <div class="stat-icon transactions">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <rect x="1" y="5" width="22" height="14" rx="2" stroke="currentColor" stroke-width="2"/>
              <path d="m1 9 22 0" stroke="currentColor" stroke-width="2"/>
            </svg>
          </div>
          <div class="stat-content">
            <div class="stat-number">{{ stats.total_transactions || 0 }}</div>
            <div class="stat-label">Total Transactions</div>
          </div>
        </div>
        
        <div class="stat-card">
          <div class="stat-icon pending">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
              <polyline points="12,6 12,12 16,14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </div>
          <div class="stat-content">
            <div class="stat-number">{{ stats.pending_transactions || 0 }}</div>
            <div class="stat-label">Pending Payments</div>
          </div>
        </div>
        
        <div class="stat-card">
          <div class="stat-icon revenue">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <line x1="12" y1="1" x2="12" y2="23" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </div>
          <div class="stat-content">
            <div class="stat-number">₱{{ formatCurrency(stats.pending_revenue || 0) }}</div>
            <div class="stat-label">Pending Revenue</div>
          </div>
        </div>
        
        <div class="stat-card">
          <div class="stat-icon enforcers">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M3 5c0-1.66 4-3 9-3s9 1.34 9 3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </div>
          <div class="stat-content">
            <div class="stat-number">{{ stats.active_enforcers || 0 }}</div>
            <div class="stat-label">Active Enforcers</div>
          </div>
        </div>
        
        <div class="stat-card">
          <div class="stat-icon repeat">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M21 3v5h-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M8 16l-5 5v-5h5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </div>
          <div class="stat-content">
            <div class="stat-number">{{ stats.repeat_offenders || 0 }}</div>
            <div class="stat-label">Repeat Offenders</div>
          </div>
        </div>
      </div>

      <!-- Charts and Tables Row -->
      <div class="dashboard-row">
        <!-- Weekly Trends Chart -->
        <div class="dashboard-card chart-card">
          <div class="card-header">
            <h3>Weekly Violation Trends</h3>
            <div class="chart-period">Last 7 days</div>
          </div>
          <div class="chart-container">
            <div  class="no-data">
              <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M3 3v18h18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="m19 9-5 5-4-4-3 3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
              <p>No data available</p>
            </div>
          </div>
        </div>

        <!-- Common Violations -->
        <div class="dashboard-card">
          <div class="card-header">
            <h3>Most Common Violations</h3>
            <div class="violation-summary">{{ commonViolations.length }} types</div>
          </div>
          <div class="violations-list">
            <div 
              v-for="(violation, index) in commonViolations" 
              :key="violation.id"
              class="violation-item"
            >
              <div class="violation-rank">#{{ index + 1 }}</div>
              <div class="violation-details">
                <div class="violation-name">{{ violation.name }}</div>
                <div class="violation-meta">
                  <span class="violation-count">{{ violation.transactions_count }} cases</span>
                  <span class="violation-amount">₱{{ formatCurrency(violation.fine_amount) }}</span>
                </div>
              </div>
              <div class="violation-progress">
                <div 
                  class="progress-bar"
                  :style="{ width: `${(violation.transactions_count / Math.max(...commonViolations.map(v => v.transactions_count))) * 100}%` }"
                ></div>
              </div>
            </div>
            <div v-if="commonViolations.length === 0" class="no-data">
              <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <line x1="12" y1="9" x2="12" y2="13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M12 17h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
              <p>No violations data available</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Enforcer Performance -->
      <div class="dashboard-card">
        <div class="card-header">
          <h3>Enforcer Performance</h3>
          <router-link to="/admin/users" class="btn btn-primary btn-sm">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              <circle cx="8.5" cy="7" r="4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              <line x1="20" y1="8" x2="20" y2="14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              <line x1="23" y1="11" x2="17" y2="11" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Manage Enforcers
          </router-link>
        </div>
        <div class="table-container">
          <table class="table">
            <thead>
              <tr>
                <th>Enforcer</th>
                <th>Total Apprehensions</th>
                <th>This Month</th>
                <th>Status</th>
                <th>Last Active</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="enforcer in enforcerPerformance" :key="enforcer.id">
                <td>
                  <div class="enforcer-info">
                    <div class="enforcer-avatar">
                      <img 
                        v-if="enforcer.image" 
                        :src="`http://127.0.0.1:8000/storage/${enforcer.image}`" 
                        alt="avatar" 
                        class="avatar-img"
                      />
                      <span v-else>
                        {{ getInitials(enforcer.first_name, enforcer.last_name) }}
                      </span>
                    </div>
                    <div>
                      <div class="enforcer-name">{{ enforcer.first_name }} {{ enforcer.last_name }}</div>
                      <div class="enforcer-email">{{ enforcer.email }}</div>
                    </div>
                  </div>
                </td>
                <td>
                  <div class="performance-cell">
                    <span class="performance-number">{{ enforcer.transactions_count || 0 }}</span>
                  </div>
                </td>
                <td>
                  <div class="performance-cell">
                    <span class="performance-number">{{ enforcer.monthly_apprehensions || 0 }}</span>
                  </div>
                </td>
                <td>
                  <span class="status-badge" :class="`status-${enforcer.status?.toLowerCase()}`">
                    {{ enforcer.status }}
                  </span>
                </td>
                <td>{{ formatDate(enforcer.updated_at) }}</td>
              </tr>
            </tbody>
          </table>
          <div v-if="enforcerPerformance.length === 0" class="no-data">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              <circle cx="8.5" cy="7" r="4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              <line x1="20" y1="8" x2="20" y2="14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <p>No enforcer data available</p>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="quick-actions">
        <h3>Quick Actions</h3>
        <div class="actions-grid">
          <router-link to="/admin/users" class="action-card">
            <div class="action-icon">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <circle cx="8.5" cy="7" r="4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <line x1="20" y1="8" x2="20" y2="14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <line x1="23" y1="11" x2="17" y2="11" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </div>
            <div class="action-title">Manage Users</div>
            <div class="action-desc">Add, edit, or remove system users</div>
          </router-link>
          
          <router-link to="/admin/violations" class="action-card">
            <div class="action-icon">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <line x1="12" y1="9" x2="12" y2="13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M12 17h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </div>
            <div class="action-title">Violation Types</div>
            <div class="action-desc">Manage violation categories and fines</div>
          </router-link>
          
          <router-link to="/admin/reports" class="action-card">
            <div class="action-icon">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M3 3v18h18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="m19 9-5 5-4-4-3 3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </div>
            <div class="action-title">Generate Reports</div>
            <div class="action-desc">Create detailed system reports</div>
          </router-link>
          
          <router-link to="/admin/notifications" class="action-card">
            <div class="action-icon">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M13.73 21a2 2 0 0 1-3.46 0" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </div>
            <div class="action-title">Send Notifications</div>
            <div class="action-desc">Broadcast messages to users</div>
          </router-link>
        </div>
      </div>
    </div>
  </SidebarLayout>
</template>

<script>
import { ref, onMounted } from 'vue'
import SidebarLayout from '@/components/SidebarLayout.vue'
import { adminAPI } from '@/services/api'

export default {
  name: 'AdminDashboard',
  components: {
    SidebarLayout
  },
  setup() {
    const loading = ref(true)
    const stats = ref({})
    const commonViolations = ref([])
    const enforcerPerformance = ref([])
    
    const loadDashboardData = async () => {
      try {
        loading.value = true
        const response = await adminAPI.dashboard()
        
        if (response.data.status === 'success') {
          const data = response.data.data
          stats.value = data.stats || {}
          commonViolations.value = data.common_violations || []
          enforcerPerformance.value = data.enforcer_performance || []
        }
      } catch (error) {
        console.error('Failed to load dashboard data:', error)
      } finally {
        loading.value = false
      }
    }
    
    const formatCurrency = (amount) => {
      return new Intl.NumberFormat('en-PH').format(amount)
    }
    
    const formatDate = (dateString) => {
      if (!dateString) return 'Never'
      return new Date(dateString).toLocaleDateString('en-PH', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      })
    }
    
    const formatChartDate = (dateString) => {
      if (!dateString) return ''
      return new Date(dateString).toLocaleDateString('en-PH', {
        month: 'short',
        day: 'numeric'
      })
    }
    
    const getInitials = (firstName, lastName) => {
      return `${firstName?.charAt(0) || ''}${lastName?.charAt(0) || ''}`.toUpperCase()
    }
    
    onMounted(() => {
      loadDashboardData()
    })
    
    return {
      loading,
      stats,
      commonViolations,
      enforcerPerformance,
      formatCurrency,
      formatDate,
      formatChartDate,
      getInitials
    }
  }
}
</script>

<style scoped>
.admin-dashboard {
  max-width: 1400px;
  margin: 0 auto;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 24px;
  margin-bottom: 32px;
}

.stat-card {
  background: white;
  border-radius: 16px;
  padding: 28px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  display: flex;
  align-items: center;
  gap: 20px;
  transition: all 0.3s ease;
  border: 1px solid rgba(0, 0, 0, 0.05);
}

.stat-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.stat-icon {
  width: 64px;
  height: 64px;
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
}

.stat-icon.violators { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
.stat-icon.transactions { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
.stat-icon.pending { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
.stat-icon.revenue { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }
.stat-icon.enforcers { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }
.stat-icon.repeat { background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); }

.stat-number {
  font-size: 32px;
  font-weight: 800;
  color: #1f2937;
  line-height: 1;
  margin-bottom: 4px;
}

.stat-label {
  font-size: 14px;
  color: #6b7280;
  font-weight: 500;
}

.dashboard-row {
  display: grid;
  grid-template-columns: 1.2fr 0.8fr;
  gap: 24px;
  margin-bottom: 32px;
}

.dashboard-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  overflow: hidden;
  border: 1px solid rgba(0, 0, 0, 0.05);
}

.card-header {
  padding: 24px;
  border-bottom: 1px solid #f1f5f9;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
}

.card-header h3 {
  font-size: 18px;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
}

.chart-period, .violation-summary {
  font-size: 12px;
  color: #64748b;
  background: #e2e8f0;
  padding: 4px 12px;
  border-radius: 20px;
  font-weight: 500;
}

.btn-sm {
  padding: 8px 16px;
  font-size: 14px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.chart-container {
  padding: 32px 24px;
  height: 280px;
  position: relative;
}

.modern-chart {
  height: 100%;
  position: relative;
  display: flex;
}

.chart-grid {
  display: flex;
  align-items: end;
  justify-content: space-between;
  height: 100%;
  flex: 1;
  gap: 12px;
  margin-right: 40px;
}

.chart-column {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  height: 100%;
}

.chart-bar-container {
  height: 180px;
  display: flex;
  align-items: end;
  width: 100%;
  position: relative;
}

.chart-bar {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 8px 8px 0 0;
  width: 100%;
  max-width: 40px;
  margin: 0 auto;
  position: relative;
  transition: all 0.3s ease;
  cursor: pointer;
  box-shadow: 0 4px 6px -1px rgba(102, 126, 234, 0.3);
}

.chart-bar:hover {
  transform: scaleY(1.05);
  box-shadow: 0 8px 15px -3px rgba(102, 126, 234, 0.4);
}

.bar-value {
  position: absolute;
  top: -28px;
  left: 50%;
  transform: translateX(-50%);
  font-size: 12px;
  font-weight: 700;
  color: #374151;
  background: white;
  padding: 4px 8px;
  border-radius: 6px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  border: 1px solid #e5e7eb;
}

.chart-label {
  margin-top: 12px;
  font-size: 11px;
  font-weight: 600;
  color: #64748b;
  text-align: center;
}

.chart-y-axis {
  position: absolute;
  right: 8px;
  top: 32px;
  height: 180px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  align-items: flex-end;
}

.y-axis-label {
  font-size: 10px;
  color: #94a3b8;
  font-weight: 500;
}

.violations-list {
  padding: 24px;
  max-height: 280px;
  overflow-y: auto;
}

.violation-item {
  display: flex;
  align-items: center;
  padding: 16px 0;
  border-bottom: 1px solid #f1f5f9;
  gap: 16px;
}

.violation-item:last-child {
  border-bottom: none;
}

.violation-rank {
  width: 32px;
  height: 32px;
  background: linear-gradient(135deg, #f3f4f6, #e5e7eb);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  font-weight: 700;
  color: #374151;
  flex-shrink: 0;
}

.violation-details {
  flex: 1;
  min-width: 0;
}

.violation-name {
  font-weight: 600;
  color: #1e293b;
  margin-bottom: 4px;
  font-size: 14px;
}

.violation-meta {
  display: flex;
  gap: 16px;
  align-items: center;
}

.violation-count {
  font-size: 12px;
  color: #64748b;
  font-weight: 500;
}

.violation-amount {
  font-weight: 700;
  color: #059669;
  font-size: 13px;
}

.violation-progress {
  width: 60px;
  height: 4px;
  background: #f1f5f9;
  border-radius: 2px;
  overflow: hidden;
  flex-shrink: 0;
}

.progress-bar {
  height: 100%;
  background: linear-gradient(90deg, #667eea, #764ba2);
  border-radius: 2px;
  transition: width 0.3s ease;
}

.table-container {
  overflow-x: auto;
}

.enforcer-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.enforcer-avatar {
  width: 45px;
  height: 45px;
  border-radius: 50%;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #1e3a8a, #3b82f6);
  font-weight: bold;
  color: white;
  font-size: 14px;
  flex-shrink: 0;
}

.avatar-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.enforcer-name {
  font-weight: 600;
  color: #1e293b;
  font-size: 14px;
}

.enforcer-email {
  font-size: 12px;
  color: #64748b;
  margin-top: 2px;
}

.performance-cell {
  display: flex;
  align-items: center;
}

.performance-number {
  font-weight: 700;
  color: #1e293b;
  font-size: 16px;
}

.quick-actions {
  margin-top: 32px;
}

.quick-actions h3 {
  font-size: 24px;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 24px;
}

.actions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 24px;
}

.action-card {
  background: white;
  border-radius: 16px;
  padding: 28px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  text-decoration: none;
  transition: all 0.3s ease;
  border: 1px solid rgba(0, 0, 0, 0.05);
  position: relative;
  overflow: hidden;
}

.action-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 3px;
  background: linear-gradient(90deg, #667eea, #764ba2);
  transform: scaleX(0);
  transition: transform 0.3s ease;
}

.action-card:hover::before {
  transform: scaleX(1);
}

.action-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.action-icon {
  width: 56px;
  height: 56px;
  background: linear-gradient(135deg, #f8fafc, #e2e8f0);
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 20px;
  color: #475569;
  transition: all 0.3s ease;
}

.action-card:hover .action-icon {
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: white;
  transform: scale(1.1);
}

.action-title {
  font-size: 16px;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 8px;
}

.action-desc {
  font-size: 14px;
  color: #64748b;
  line-height: 1.5;
}

.no-data {
  text-align: center;
  color: #64748b;
  padding: 48px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
}

.no-data svg {
  color: #cbd5e1;
}

.no-data p {
  margin: 0;
  font-weight: 500;
}

.status-badge {
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.status-active {
  background: #dcfce7;
  color: #166534;
}

.status-inactive {
  background: #fef3c7;
  color: #92400e;
}

.status-deactivate {
  background: #fee2e2;
  color: #991b1b;
}

@media (max-width: 1024px) {
  .dashboard-row {
    grid-template-columns: 1fr;
  }
  
  .stats-grid {
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  }
}

@media (max-width: 768px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }
  
  .actions-grid {
    grid-template-columns: 1fr;
  }
  
  .chart-grid {
    gap: 8px;
  }
  
  .violation-meta {
    flex-direction: column;
    align-items: flex-start;
    gap: 4px;
  }
}
</style>