<template>
  <SidebarLayout page-title="Performance Statistics">
    <div class="enforcer-performance">
      <!-- Header -->
      <div class="page-header">
        <div class="header-left">
          <h2>My Performance</h2>
          <p>Track your enforcement statistics and achievements</p>
        </div>
      </div>

      <!-- Performance Stats -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon total">📊</div>
          <div class="stat-content">
            <div class="stat-number">{{ performanceStats.total_apprehensions || 0 }}</div>
            <div class="stat-label">Total Apprehensions</div>
          </div>
        </div>
        
        <div class="stat-card">
          <div class="stat-icon monthly">📅</div>
          <div class="stat-content">
            <div class="stat-number">{{ performanceStats.monthly_apprehensions || 0 }}</div>
            <div class="stat-label">This Month</div>
          </div>
        </div>
        
        <div class="stat-card">
          <div class="stat-icon average">📈</div>
          <div class="stat-content">
            <div class="stat-number">{{ performanceStats.daily_average || 0 }}</div>
            <div class="stat-label">Daily Average</div>
          </div>
        </div>
        
        <div class="stat-card">
          <div class="stat-icon revenue">💰</div>
          <div class="stat-content">
            <div class="stat-number">₱{{ formatCurrency(performanceStats.total_fines || 0) }}</div>
            <div class="stat-label">Total Fines Issued</div>
          </div>
        </div>
      </div>

      <!-- Charts Section -->
      <div class="charts-row">
        <!-- Monthly Performance Chart -->
        <div class="chart-card">
          <div class="card-header">
            <h3>Monthly Performance</h3>
          </div>
          <div class="chart-container">
            <div v-if="monthlyData.length === 0" class="no-data">
              No performance data available
            </div>
            <div v-else class="simple-chart">
              <div 
                v-for="(month, index) in monthlyData" 
                :key="index"
                class="chart-bar"
                :style="{ height: `${(month.count / maxMonthlyCount) * 100}%` }"
                :title="`${month.month}: ${month.count} apprehensions`"
              >
                <span class="bar-label">{{ month.count }}</span>
              </div>
            </div>
            <div class="chart-labels">
              <span v-for="(month, index) in monthlyData" :key="index" class="chart-label">
                {{ month.month }}
              </span>
            </div>
          </div>
        </div>

        <!-- Violation Types Distribution -->
        <div class="chart-card">
          <div class="card-header">
            <h3>Violation Types</h3>
          </div>
          <div class="violations-breakdown">
            <div v-if="violationBreakdown.length === 0" class="no-data">
              No violation data available
            </div>
            <div v-else>
              <div 
                v-for="violation in violationBreakdown" 
                :key="violation.type"
                class="violation-stat"
              >
                <div class="violation-info">
                  <div class="violation-name">{{ violation.type }}</div>
                  <div class="violation-count">{{ violation.count }} cases</div>
                </div>
                <div class="violation-bar">
                  <div 
                    class="violation-progress" 
                    :style="{ width: `${(violation.count / maxViolationCount) * 100}%` }"
                  ></div>
                </div>
                <div class="violation-percentage">
                  {{ Math.round((violation.count / totalViolations) * 100) }}%
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Performance Metrics -->
      <div class="metrics-section">
        <h3>Performance Metrics</h3>
        <div class="metrics-grid">
          <div class="metric-card">
            <div class="metric-header">
              <h4>Collection Rate</h4>
              <span class="metric-value">{{ performanceStats.collection_rate || 0 }}%</span>
            </div>
            <div class="metric-bar">
              <div 
                class="metric-progress success" 
                :style="{ width: `${performanceStats.collection_rate || 0}%` }"
              ></div>
            </div>
            <div class="metric-desc">Percentage of fines collected</div>
          </div>
          
          <div class="metric-card">
            <div class="metric-header">
              <h4>Response Time</h4>
              <span class="metric-value">{{ performanceStats.avg_response_time || 0 }} min</span>
            </div>
            <div class="metric-bar">
              <div 
                class="metric-progress warning" 
                :style="{ width: `${Math.min((performanceStats.avg_response_time || 0) / 30 * 100, 100)}%` }"
              ></div>
            </div>
            <div class="metric-desc">Average response time to violations</div>
          </div>
          
          <div class="metric-card">
            <div class="metric-header">
              <h4>Accuracy Rate</h4>
              <span class="metric-value">{{ performanceStats.accuracy_rate || 0 }}%</span>
            </div>
            <div class="metric-bar">
              <div 
                class="metric-progress info" 
                :style="{ width: `${performanceStats.accuracy_rate || 0}%` }"
              ></div>
            </div>
            <div class="metric-desc">Accuracy of violation records</div>
          </div>
        </div>
      </div>

      <!-- Recent Activity -->
      <div class="activity-section">
        <div class="card-header">
          <h3>Recent Activity</h3>
        </div>
        <div class="activity-list">
          <div v-if="recentActivity.length === 0" class="no-data">
            <div class="no-data-icon">📋</div>
            <p>No recent activity</p>
          </div>
          <div v-else>
            <div v-for="activity in recentActivity" :key="activity.id" class="activity-item">
              <div class="activity-icon">{{ getActivityIcon(activity.type) }}</div>
              <div class="activity-content">
                <div class="activity-title">{{ activity.title }}</div>
                <div class="activity-desc">{{ activity.description }}</div>
                <div class="activity-time">{{ formatDateTime(activity.created_at) }}</div>
              </div>
            </div>
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
  name: 'EnforcerPerformance',
  components: {
    SidebarLayout
  },
  setup() {
    const loading = ref(true)
    const performanceStats = ref({})
    const monthlyData = ref([])
    const violationBreakdown = ref([])
    const recentActivity = ref([])
    
    const maxMonthlyCount = computed(() => {
      return Math.max(...monthlyData.value.map(m => m.count), 1)
    })
    
    const maxViolationCount = computed(() => {
      return Math.max(...violationBreakdown.value.map(v => v.count), 1)
    })
    
    const totalViolations = computed(() => {
      return violationBreakdown.value.reduce((sum, v) => sum + v.count, 0)
    })
    
    const loadPerformanceData = async () => {
      try {
        loading.value = true
        const response = await enforcerAPI.getPerformanceStats()
        
        if (response.data.status === 'success') {
          const data = response.data.data
          performanceStats.value = data.stats || {}
          monthlyData.value = data.monthly_data || []
          violationBreakdown.value = data.violation_breakdown || []
          recentActivity.value = data.recent_activity || []
        }
      } catch (error) {
        console.error('Failed to load performance data:', error)
        // Mock data for demo
        performanceStats.value = {
          total_apprehensions: 156,
          monthly_apprehensions: 23,
          daily_average: 3.2,
          total_fines: 125000,
          collection_rate: 85,
          avg_response_time: 12,
          accuracy_rate: 94
        }
        
        monthlyData.value = [
          { month: 'Jan', count: 18 },
          { month: 'Feb', count: 22 },
          { month: 'Mar', count: 19 },
          { month: 'Apr', count: 25 },
          { month: 'May', count: 21 },
          { month: 'Jun', count: 23 }
        ]
        
        violationBreakdown.value = [
          { type: 'Speeding', count: 45 },
          { type: 'Illegal Parking', count: 32 },
          { type: 'Running Red Light', count: 28 },
          { type: 'No Helmet', count: 25 },
          { type: 'Others', count: 26 }
        ]
        
        recentActivity.value = [
          {
            id: 1,
            type: 'violation',
            title: 'Recorded Speeding Violation',
            description: 'Issued violation to John Doe for exceeding speed limit',
            created_at: new Date(Date.now() - 2 * 60 * 60 * 1000).toISOString()
          },
          {
            id: 2,
            type: 'payment',
            title: 'Payment Confirmed',
            description: 'Fine payment received for violation #123',
            created_at: new Date(Date.now() - 4 * 60 * 60 * 1000).toISOString()
          },
          {
            id: 3,
            type: 'violation',
            title: 'Recorded Parking Violation',
            description: 'Issued violation for illegal parking on Main St.',
            created_at: new Date(Date.now() - 6 * 60 * 60 * 1000).toISOString()
          }
        ]
      } finally {
        loading.value = false
      }
    }
    
    const getActivityIcon = (type) => {
      const icons = {
        violation: '📝',
        payment: '💰',
        update: '✏️',
        achievement: '🏆'
      }
      return icons[type] || '📋'
    }
    
    const formatCurrency = (amount) => {
      return new Intl.NumberFormat('en-PH').format(amount)
    }
    
    const formatDateTime = (dateString) => {
      if (!dateString) return ''
      return new Date(dateString).toLocaleString('en-PH', {
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
    }
    
    onMounted(() => {
      loadPerformanceData()
    })
    
    return {
      loading,
      performanceStats,
      monthlyData,
      violationBreakdown,
      recentActivity,
      maxMonthlyCount,
      maxViolationCount,
      totalViolations,
      getActivityIcon,
      formatCurrency,
      formatDateTime
    }
  }
}
</script>

<style scoped>
.enforcer-performance {
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
.stat-icon.monthly { background: linear-gradient(135deg, #10b981, #059669); }
.stat-icon.average { background: linear-gradient(135deg, #f59e0b, #d97706); }
.stat-icon.revenue { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }

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

.charts-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 24px;
  margin-bottom: 32px;
}

.chart-card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.card-header {
  padding: 24px;
  border-bottom: 1px solid #e5e7eb;
}

.card-header h3 {
  font-size: 18px;
  font-weight: 600;
  color: #1f2937;
  margin: 0;
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

.violations-breakdown {
  padding: 24px;
}

.violation-stat {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-bottom: 16px;
}

.violation-stat:last-child {
  margin-bottom: 0;
}

.violation-info {
  min-width: 120px;
}

.violation-name {
  font-weight: 500;
  color: #1f2937;
  font-size: 14px;
}

.violation-count {
  font-size: 12px;
  color: #6b7280;
}

.violation-bar {
  flex: 1;
  height: 8px;
  background: #f3f4f6;
  border-radius: 4px;
  overflow: hidden;
}

.violation-progress {
  height: 100%;
  background: linear-gradient(135deg, #3b82f6, #1e40af);
  transition: width 0.3s ease;
}

.violation-percentage {
  min-width: 40px;
  text-align: right;
  font-size: 12px;
  font-weight: 500;
  color: #6b7280;
}

.metrics-section {
  margin-bottom: 32px;
}

.metrics-section h3 {
  font-size: 20px;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 20px;
}

.metrics-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 24px;
}

.metric-card {
  background: white;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.metric-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.metric-header h4 {
  font-size: 16px;
  font-weight: 600;
  color: #1f2937;
  margin: 0;
}

.metric-value {
  font-size: 18px;
  font-weight: 700;
  color: #1f2937;
}

.metric-bar {
  height: 8px;
  background: #f3f4f6;
  border-radius: 4px;
  overflow: hidden;
  margin-bottom: 8px;
}

.metric-progress {
  height: 100%;
  transition: width 0.3s ease;
}

.metric-progress.success { background: linear-gradient(135deg, #10b981, #059669); }
.metric-progress.warning { background: linear-gradient(135deg, #f59e0b, #d97706); }
.metric-progress.info { background: linear-gradient(135deg, #3b82f6, #1e40af); }

.metric-desc {
  font-size: 12px;
  color: #6b7280;
}

.activity-section {
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.activity-list {
  padding: 24px;
}

.activity-item {
  display: flex;
  align-items: flex-start;
  gap: 16px;
  padding: 16px 0;
  border-bottom: 1px solid #f3f4f6;
}

.activity-item:last-child {
  border-bottom: none;
}

.activity-icon {
  width: 40px;
  height: 40px;
  background: #f3f4f6;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
}

.activity-title {
  font-weight: 500;
  color: #1f2937;
  margin-bottom: 4px;
}

.activity-desc {
  font-size: 14px;
  color: #6b7280;
  margin-bottom: 4px;
}

.activity-time {
  font-size: 12px;
  color: #9ca3af;
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

@media (max-width: 1024px) {
  .charts-row {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }
  
  .metrics-grid {
    grid-template-columns: 1fr;
  }
}
</style>
