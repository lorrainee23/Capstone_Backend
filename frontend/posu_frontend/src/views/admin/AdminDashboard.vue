<template>
  <SidebarLayout page-title="Admin Dashboard">
    <div class="admin-dashboard">
      <!-- Stats Cards -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon violators">👥</div>
          <div class="stat-content">
            <div class="stat-number">{{ stats.total_violators || 0 }}</div>
            <div class="stat-label">Total Violators</div>
          </div>
        </div>
        
        <div class="stat-card">
          <div class="stat-icon transactions">💳</div>
          <div class="stat-content">
            <div class="stat-number">{{ stats.total_transactions || 0 }}</div>
            <div class="stat-label">Total Transactions</div>
          </div>
        </div>
        
        <div class="stat-card">
          <div class="stat-icon pending">⏳</div>
          <div class="stat-content">
            <div class="stat-number">{{ stats.pending_transactions || 0 }}</div>
            <div class="stat-label">Pending Payments</div>
          </div>
        </div>
        
        <div class="stat-card">
          <div class="stat-icon revenue">💰</div>
          <div class="stat-content">
            <div class="stat-number">₱{{ formatCurrency(stats.total_revenue || 0) }}</div>
            <div class="stat-label">Total Revenue</div>
          </div>
        </div>
        
        <div class="stat-card">
          <div class="stat-icon enforcers">👮‍♂️</div>
          <div class="stat-content">
            <div class="stat-number">{{ stats.active_enforcers || 0 }}</div>
            <div class="stat-label">Active Enforcers</div>
          </div>
        </div>
        
        <div class="stat-card">
          <div class="stat-icon repeat">🔄</div>
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
          </div>
          <div class="chart-container">
            <div v-if="weeklyTrends.length === 0" class="no-data">
              No data available
            </div>
            <div v-else class="simple-chart">
              <div 
                v-for="(trend, index) in weeklyTrends" 
                :key="index"
                class="chart-bar"
                :style="{ height: `${(trend.count / maxTrendCount) * 100}%` }"
                :title="`${trend.date}: ${trend.count} violations`"
              >
                <span class="bar-label">{{ trend.count }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Common Violations -->
        <div class="dashboard-card">
          <div class="card-header">
            <h3>Most Common Violations</h3>
          </div>
          <div class="violations-list">
            <div 
              v-for="violation in commonViolations" 
              :key="violation.id"
              class="violation-item"
            >
              <div class="violation-name">{{ violation.name }}</div>
              <div class="violation-count">{{ violation.transactions_count }} cases</div>
              <div class="violation-amount">₱{{ formatCurrency(violation.fine_amount) }}</div>
            </div>
            <div v-if="commonViolations.length === 0" class="no-data">
              No violations data available
            </div>
          </div>
        </div>
      </div>

      <!-- Enforcer Performance -->
      <div class="dashboard-card">
        <div class="card-header">
          <h3>Enforcer Performance</h3>
          <router-link to="/admin/users" class="btn btn-primary btn-sm">
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
                      {{ getInitials(enforcer.first_name, enforcer.last_name) }}
                    </div>
                    <div>
                      <div class="enforcer-name">{{ enforcer.first_name }} {{ enforcer.last_name }}</div>
                      <div class="enforcer-email">{{ enforcer.email }}</div>
                    </div>
                  </div>
                </td>
                <td>{{ enforcer.total_apprehensions || 0 }}</td>
                <td>{{ enforcer.monthly_apprehensions || 0 }}</td>
                <td>
                  <span class="status-badge" :class="`status-${enforcer.status?.toLowerCase()}`">
                    {{ enforcer.status }}
                  </span>
                </td>
                <td>{{ formatDate(enforcer.last_login_at) }}</td>
              </tr>
            </tbody>
          </table>
          <div v-if="enforcerPerformance.length === 0" class="no-data">
            No enforcer data available
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="quick-actions">
        <h3>Quick Actions</h3>
        <div class="actions-grid">
          <router-link to="/admin/users" class="action-card">
            <div class="action-icon">👥</div>
            <div class="action-title">Manage Users</div>
            <div class="action-desc">Add, edit, or remove system users</div>
          </router-link>
          
          <router-link to="/admin/violations" class="action-card">
            <div class="action-icon">⚠️</div>
            <div class="action-title">Violation Types</div>
            <div class="action-desc">Manage violation categories and fines</div>
          </router-link>
          
          <router-link to="/admin/reports" class="action-card">
            <div class="action-icon">📊</div>
            <div class="action-title">Generate Reports</div>
            <div class="action-desc">Create detailed system reports</div>
          </router-link>
          
          <router-link to="/admin/notifications" class="action-card">
            <div class="action-icon">🔔</div>
            <div class="action-title">Send Notifications</div>
            <div class="action-desc">Broadcast messages to users</div>
          </router-link>
        </div>
      </div>
    </div>
  </SidebarLayout>
</template>

<script>
import { ref, onMounted, computed } from 'vue'
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
    const weeklyTrends = ref([])
    const commonViolations = ref([])
    const enforcerPerformance = ref([])
    
    const maxTrendCount = computed(() => {
      return Math.max(...weeklyTrends.value.map(t => t.count), 1)
    })
    
    const loadDashboardData = async () => {
      try {
        loading.value = true
        const response = await adminAPI.dashboard()
        
        if (response.data.status === 'success') {
          const data = response.data.data
          stats.value = data.stats || {}
          weeklyTrends.value = data.weekly_trends || []
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
    
    const getInitials = (firstName, lastName) => {
      return `${firstName?.charAt(0) || ''}${lastName?.charAt(0) || ''}`.toUpperCase()
    }
    
    onMounted(() => {
      loadDashboardData()
    })
    
    return {
      loading,
      stats,
      weeklyTrends,
      commonViolations,
      enforcerPerformance,
      maxTrendCount,
      formatCurrency,
      formatDate,
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

.stat-icon.violators { background: linear-gradient(135deg, #3b82f6, #1e40af); }
.stat-icon.transactions { background: linear-gradient(135deg, #10b981, #059669); }
.stat-icon.pending { background: linear-gradient(135deg, #f59e0b, #d97706); }
.stat-icon.revenue { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
.stat-icon.enforcers { background: linear-gradient(135deg, #ef4444, #dc2626); }
.stat-icon.repeat { background: linear-gradient(135deg, #6b7280, #4b5563); }

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

.dashboard-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 24px;
  margin-bottom: 32px;
}

.dashboard-card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
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

.chart-container {
  padding: 24px;
  height: 200px;
}

.simple-chart {
  display: flex;
  align-items: end;
  justify-content: space-around;
  height: 100%;
  gap: 8px;
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

.violations-list {
  padding: 24px;
}

.violation-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 0;
  border-bottom: 1px solid #f3f4f6;
}

.violation-item:last-child {
  border-bottom: none;
}

.violation-name {
  font-weight: 500;
  color: #1f2937;
  flex: 1;
}

.violation-count {
  font-size: 14px;
  color: #6b7280;
  margin: 0 16px;
}

.violation-amount {
  font-weight: 600;
  color: #059669;
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
  width: 40px;
  height: 40px;
  background: linear-gradient(135deg, #1e3a8a, #3b82f6);
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 14px;
}

.enforcer-name {
  font-weight: 500;
  color: #1f2937;
}

.enforcer-email {
  font-size: 12px;
  color: #6b7280;
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
  color: #6b7280;
  padding: 40px;
  font-style: italic;
}

@media (max-width: 1024px) {
  .dashboard-row {
    grid-template-columns: 1fr;
  }
  
  .stats-grid {
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  }
}

@media (max-width: 768px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }
  
  .actions-grid {
    grid-template-columns: 1fr;
  }
}
</style>
