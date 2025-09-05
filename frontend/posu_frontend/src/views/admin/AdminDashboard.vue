<template>
  <SidebarLayout page-title="Admin Dashboard">
    <div class="admin-dashboard">
      <!-- Header Section -->
      <header class="dashboard-header">
        <div class="header-content">
          <h1>Dashboard Overview</h1>
          <p>Monitor your system performance and key metrics</p>
        </div>
        <button class="refresh-btn" @click="loadDashboardData" aria-label="Refresh Dashboard">
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

      <!-- Stats Grid -->
<section class="stats-grid">
  <article 
    class="stat-card" 
    v-for="(stat, index) in statsConfig" 
    :key="index" 
    :aria-label="stat.label"
    :class="stat.colorClass"
  >
    <div class="stat-border"></div>
    <div class="stat-icon" v-html="stat.iconSvg" aria-hidden="true"></div>
    
    <div class="stat-content">
      <p class="stat-title">{{ stat.label }}</p>
      <p class="stat-value">{{ stat.value }}</p>
      <p class="stat-trend" :class="stat.trend?.type">
        <svg v-if="stat.trend?.type === 'up'" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="7 17 17 7"></polyline>
          <line x1="7" y1="7" x2="17" y2="7"></line>
          <line x1="17" y1="7" x2="17" y2="17"></line>
        </svg>
        <svg v-else-if="stat.trend?.type === 'down'" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="17 7 7 17"></polyline>
          <line x1="7" y1="17" x2="17" y2="17"></line>
          <line x1="7" y1="7" x2="7" y2="17"></line>
        </svg>
        <span>{{ stat.trend?.value }}</span>
      </p>
    </div>

    <div class="stat-arrow" aria-hidden="true">
      <svg width="20" height="20" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <line x1="5" y1="12" x2="19" y2="12"></line>
        <polyline points="12 5 19 12 12 19"></polyline>
      </svg>
    </div>
  </article>
</section>

      <!-- Main Content Grid -->
      <section class="content-grid">
        <!-- Weekly Trends Chart -->
        <section class="chart-card" aria-label="Weekly Violation Trends">
          <header class="card-header">
            <h3>Weekly Violation Trends</h3>
          </header>
          <div class="chart-container">
            <div v-if="weeklyData.length" class="chart-wrapper">
              <!-- Chart Content -->
              <div class="chart-bars">
                <div 
                  v-for="(data, index) in weeklyData" 
                  :key="index"
                  class="chart-bar"
                  @mouseenter="showTooltip($event, data)"
                  @mouseleave="hideTooltip"
                >
                  <div 
                    class="bar-fill"
                    :style="{ 
                      height: `${(data.count / maxWeeklyCount) * 100}%`,
                      minHeight: data.count > 0 ? '4px' : '0px'
                    }"
                  ></div>
                  <div class="bar-label">{{ formatChartDate(data.date) }}</div>
                </div>
              </div>
              
              <!-- Y-Axis -->
              <div class="chart-axis">
                <span v-for="tick in yAxisTicks" :key="tick">{{ tick }}</span>
              </div>
              
              <!-- Tooltip -->
              <div 
                v-if="tooltip.visible" 
                class="chart-tooltip"
                :style="{ left: tooltip.x + 'px', top: tooltip.y + 'px' }"
              >
                {{ tooltip.content }}
              </div>
            </div>
            <div v-else class="empty-state">
              <svg width="48" height="48" fill="none" stroke="#94a3b8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M3 3v18h18"></path>
                <path d="m19 9-5 5-4-4-3 3"></path>
              </svg>
              <p>No data available for the selected period</p>
            </div>
          </div>
        </section>

        <!-- Common Violations -->
        <section class="violations-card" aria-label="Top Violations">
          <header class="card-header">
            <h3>Top Violations</h3>
            <router-link to="/admin/violations" class="view-all-btn" aria-label="View all violations">
              View All
              <svg width="16" height="16" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" overflow="visible" aria-hidden="true">
                <line x1="5" y1="12" x2="19" y2="12"></line>
                <polyline points="12 5 19 12 12 19"></polyline>
              </svg>
            </router-link>
          </header>
          <div class="violations-list">
            <article 
              v-for="(violation, index) in commonViolations.slice(0, 5)" 
              :key="violation.id"
              class="violation-item"
              tabindex="0"
              :aria-label="`${violation.name}, ${violation.transactions_count} cases, ₱${formatCurrency(violation.fine_amount)} fine`"
            >
              <div class="violation-rank">{{ index + 1 }}</div>
              <div class="violation-info">
                <p class="violation-name">{{ violation.name }}</p>
                <p class="violation-stats">
                  <span class="cases">{{ violation.transactions_count }} cases</span>
                  <span class="amount">₱{{ formatCurrency(violation.fine_amount) }}</span>
                </p>
              </div>
              <div class="violation-progress" aria-hidden="true">
                <div 
                  class="progress-fill"
                  :style="{ width: `${(violation.transactions_count / maxViolationCount) * 100}%` }"
                ></div>
              </div>
            </article>
            <div v-if="commonViolations.length === 0" class="empty-state">
              <svg width="40" height="40" fill="none" stroke="#94a3b8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                <line x1="12" y1="9" x2="12" y2="13"></line>
                <path d="M12 17h.01"></path>
              </svg>
              <p>No violations data</p>
            </div>
          </div>
        </section>
      </section>

      <!-- Enforcer Performance -->
      <section class="performance-card" aria-label="Enforcer Performance">
        <header class="card-header">
          <div>
            <h3>Enforcer Performance</h3>
            <p class="subtitle">Track team productivity and activity</p>
          </div>
          <router-link to="/admin/users" class="primary-btn" aria-label="Manage Team">
            <svg width="20" height="20" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" overflow="visible" aria-hidden="true">
              <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
              <circle cx="8.5" cy="7" r="4"></circle>
              <line x1="20" y1="8" x2="20" y2="14"></line>
              <line x1="23" y1="11" x2="17" y2="11"></line>
            </svg>
            Manage Team
          </router-link>
        </header>
        <div class="table-wrapper">
          <table class="modern-table" role="table">
            <thead>
              <tr>
                <th scope="col">Enforcer</th>
                <th scope="col">Total Cases</th>
                <th scope="col">This Month</th>
                <th scope="col">Status</th>
                <th scope="col">Last Active</th>
                <th scope="col">Performance</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="enforcer in enforcerPerformance.slice(0, 8)" :key="enforcer.id" class="table-row" tabindex="0">
                <td>
                  <div class="enforcer-cell">
                    <div class="avatar" aria-hidden="true">
                      <img 
                        v-if="enforcer.image" 
                        :src="`http://127.0.0.1:8000/storage/${enforcer.image}`" 
                        :alt="`${enforcer.first_name} ${enforcer.last_name}`"
                      />
                      <span v-else class="avatar-initials">
                        {{ getInitials(enforcer.first_name, enforcer.last_name) }}
                      </span>
                    </div>
                    <div class="enforcer-details">
                      <p class="enforcer-name">{{ enforcer.first_name }} {{ enforcer.last_name }}</p>
                      <p class="enforcer-email">{{ enforcer.email }}</p>
                    </div>
                  </div>
                </td>
                <td><span class="metric-number">{{ enforcer.transactions_count || 0 }}</span></td>
                <td>
                  <div class="metric-bar" aria-label="Monthly apprehensions progress">
                    <div 
                      class="metric-fill"
                      :style="{ width: `${Math.min((enforcer.monthly_apprehensions || 0) / 50 * 100, 100)}%` }"
                    ></div>
                  </div>
                  <span class="metric-number">{{ enforcer.monthly_apprehensions || 0 }}</span>
                </td>
                <td>
                  <span class="status-pill" :class="`status-${enforcer.status?.toLowerCase()}`" :aria-label="enforcer.status">
                    <span class="status-dot"></span>
                    {{ enforcer.status }}
                  </span>
                </td>
                <td><time :datetime="enforcer.updated_at">{{ formatDate(enforcer.updated_at) }}</time></td>
                <td>
                  <div class="performance-score" :class="getPerformanceClass(enforcer.transactions_count)">
                    {{ getPerformanceScore(enforcer.transactions_count) }}%
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
          <div v-if="enforcerPerformance.length === 0" class="empty-state">
            <svg width="48" height="48" fill="none" stroke="#94a3b8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
              <circle cx="8.5" cy="7" r="4"></circle>
            </svg>
            <p>No enforcer data available</p>
          </div>
        </div>
      </section>

      <!-- Quick Actions -->
      <section class="actions-section" aria-label="Quick Actions">
        <h3 class="section-title">Quick Actions</h3>
        <div class="actions-grid">
          <router-link to="/admin/users" class="action-card" aria-label="Manage Users">
            <div class="action-icon users" aria-hidden="true">
              <svg width="24" height="24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="8.5" cy="7" r="4"></circle>
                <line x1="20" y1="8" x2="20" y2="14"></line>
                <line x1="23" y1="11" x2="17" y2="11"></line>
              </svg>
            </div>
            <div class="action-content">
              <p class="action-title">Manage Users</p>
              <p class="action-desc">Add, edit, or remove system users</p>
            </div>
            <div class="action-arrow" aria-hidden="true">
              <svg width="20" height="20" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="5" y1="12" x2="19" y2="12"></line>
                <polyline points="12 5 19 12 12 19"></polyline>
              </svg>
            </div>
          </router-link>

          <router-link to="/admin/violations" class="action-card" aria-label="Violation Types">
            <div class="action-icon violations" aria-hidden="true">
              <svg width="24" height="24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                <line x1="12" y1="9" x2="12" y2="13"></line>
                <path d="M12 17h.01"></path>
              </svg>
            </div>
            <div class="action-content">
              <p class="action-title">Violation Types</p>
              <p class="action-desc">Manage categories and fine amounts</p>
            </div>
            <div class="action-arrow" aria-hidden="true">
              <svg width="20" height="20" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="5" y1="12" x2="19" y2="12"></line>
                <polyline points="12 5 19 12 12 19"></polyline>
              </svg>
            </div>
          </router-link>

          <router-link to="/admin/reports" class="action-card" aria-label="Analytics">
            <div class="action-icon reports" aria-hidden="true">
              <svg width="24" height="24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                <polyline points="19 9 12 16 9 13 5 17"></polyline>
              </svg>
            </div>
            <div class="action-content">
              <p class="action-title">Analytics</p>
              <p class="action-desc">Generate detailed system reports</p>
            </div>
            <div class="action-arrow" aria-hidden="true">
              <svg width="20" height="20" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="5" y1="12" x2="19" y2="12"></line>
                <polyline points="12 5 19 12 12 19"></polyline>
              </svg>
            </div>
          </router-link>

          <router-link to="/admin/notifications" class="action-card" aria-label="Notifications">
            <div class="action-icon notifications" aria-hidden="true">
              <svg width="24" height="24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
              </svg>
            </div>
            <div class="action-content">
              <p class="action-title">Notifications</p>
              <p class="action-desc">Send alerts and updates</p>
            </div>
            <div class="action-arrow" aria-hidden="true">
              <svg width="20" height="20" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="5" y1="12" x2="19" y2="12"></line>
                <polyline points="12 5 19 12 12 19"></polyline>
              </svg>
            </div>
          </router-link>
        </div>
      </section>
    </div>
  </SidebarLayout>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
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
    
    const weeklyData = ref([])
    
    const statsConfig = computed(() => [
      {
        iconSvg: `
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="7" r="4"></circle>
            <path d="M5.5 21a6.5 6.5 0 0 1 13 0"></path>
          </svg>`,
        value: stats.value.total_violators || 0,
        label: 'Total Violators',
        trend: { type: 'up', value: '+12%' },
        colorClass: 'stat-blue'
      },
      {
        iconSvg: `
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
          </svg>`,
        value: stats.value.total_transactions || 0,
        label: 'Total Transactions',
        trend: { type: 'up', value: '+8%' },
        colorClass: 'stat-green'
      },
      {
        iconSvg: `
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="8" x2="12" y2="12"></line>
            <line x1="12" y1="16" x2="12" y2="16"></line>
          </svg>`,
        value: stats.value.pending_transactions || 0,
        label: 'Pending Payments',
        trend: { type: 'down', value: '-3%' },
        colorClass: 'stat-yellow'
      },
      {
        iconSvg: `
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#8b5cf6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 1v22"></path>
            <path d="M17 5H7a3 3 0 1 0 0 6h10a3 3 0 1 1 0 6H7"></path>
            <circle cx="12" cy="12" r="1"></circle>
          </svg>`,
        value: `₱${formatCurrency(stats.value.pending_revenue || 0)}`,
        label: 'Pending Revenue',
        trend: { type: 'up', value: '+15%' },
        colorClass: 'stat-purple'
      },
      {
        iconSvg: `
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#06b6d4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
            <circle cx="9" cy="7" r="4"></circle>
            <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
          </svg>`,
        value: stats.value.active_enforcers || 0,
        label: 'Active Enforcers',
        trend: { type: 'up', value: '+2%' },
        colorClass: 'stat-cyan'
      },
      {
        iconSvg: `
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
            <line x1="12" y1="9" x2="12" y2="13"></line>
            <path d="M12 17h.01"></path>
          </svg>`,
        value: stats.value.repeat_offenders || 0,
        label: 'Repeat Offenders',
        trend: { type: 'down', value: '-5%' },
        colorClass: 'stat-red'
      }
    ])
    
    const maxWeeklyCount = computed(() => {
      return Math.max(...weeklyData.value.map(d => d.count), 1)
    })
    
    const maxViolationCount = computed(() => {
      return Math.max(...commonViolations.value.map(v => v.transactions_count), 1)
    })
    
    const yAxisTicks = computed(() => {
      const max = maxWeeklyCount.value
      return [max, Math.floor(max * 0.75), Math.floor(max * 0.5), Math.floor(max * 0.25), 0]
    })
    
    const loadDashboardData = async () => {
      try {
        loading.value = true
        const response = await adminAPI.dashboard()
        console.log('Button Click')
        if (response.data.status === 'success') {
          const data = response.data.data
          stats.value = data.stats || {}
          commonViolations.value = data.common_violations || []
          enforcerPerformance.value = data.enforcer_performance || []
          weeklyData.value = data.weekly_trends || []
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
    
    const getPerformanceScore = (count) => {
      if (!count) return 0
      return Math.min(Math.round((count / 50) * 100), 100)
    }
    
    const getPerformanceClass = (count) => {
      const score = getPerformanceScore(count)
      if (score >= 80) return 'excellent'
      if (score >= 60) return 'good'
      if (score >= 40) return 'average'
      return 'poor'
    }
    
    const tooltip = ref({
      visible: false,
      x: 0,
      y: 0,
      content: ''
    })

    const showTooltip = (event, data) => {
  const barRect = event.currentTarget.getBoundingClientRect()
  const containerRect = event.currentTarget.parentElement.getBoundingClientRect()

  tooltip.value = {
    visible: true,
    x: barRect.left - containerRect.left + barRect.width / 2,
    y: barRect.top - containerRect.top, 
    content: `${formatChartDate(data.date)}: ${data.count} violations`
  }
}



    const hideTooltip = () => {
      tooltip.value.visible = false
    }
    
    onMounted(() => {
      loadDashboardData()
    })
    
    return {
      loading,
      stats,
      commonViolations,
      enforcerPerformance,
      statsConfig,
      weeklyData,
      maxWeeklyCount,
      maxViolationCount,
      yAxisTicks,
      loadDashboardData,
      formatCurrency,
      formatDate,
      formatChartDate,
      getInitials,
      getPerformanceScore,
      getPerformanceClass,
      tooltip,
      showTooltip,
      hideTooltip
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

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 20px;
  margin: 32px 0;
}

.stat-card {
  background: white;
  border: 1px solid rgba(0, 0, 0, 0.06);
  border-radius: 16px;
  padding: 20px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
  transition: all 0.2s ease;
  cursor: pointer;
  position: relative;
  overflow: hidden;
}

.stat-card:hover {
  background: #f9fafb;
  transform: translateY(-2px);
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
}

/* Colored left borders for stat cards */
.stat-border {
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 4px;
  transition: width 0.3s ease;
}

.stat-card.stat-blue .stat-border {
  background: linear-gradient(180deg, #3b82f6, #1e40af);
}

.stat-card.stat-green .stat-border {
  background: linear-gradient(180deg, #10b981, #059669);
}

.stat-card.stat-yellow .stat-border {
  background: linear-gradient(180deg, #f59e0b, #d97706);
}

.stat-card.stat-purple .stat-border {
  background: linear-gradient(180deg, #8b5cf6, #7c3aed);
}

.stat-card.stat-cyan .stat-border {
  background: linear-gradient(180deg, #06b6d4, #0891b2);
}

.stat-card.stat-red .stat-border {
  background: linear-gradient(180deg, #ef4444, #dc2626);
}

.stat-card:hover .stat-border {
  width: 6px;
}

.stat-icon {
  flex-shrink: 0;
  width: 48px;
  height: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(37, 99, 235, 0.05);
  border-radius: 12px;
  transition: all 0.3s ease;
}

.stat-card.stat-blue .stat-icon {
  background: rgba(59, 130, 246, 0.1);
}

.stat-card.stat-green .stat-icon {
  background: rgba(16, 185, 129, 0.1);
}

.stat-card.stat-yellow .stat-icon {
  background: rgba(245, 158, 11, 0.1);
}

.stat-card.stat-purple .stat-icon {
  background: rgba(139, 92, 246, 0.1);
}

.stat-card.stat-cyan .stat-icon {
  background: rgba(6, 182, 212, 0.1);
}

.stat-card.stat-red .stat-icon {
  background: rgba(239, 68, 68, 0.1);
}

.stat-card:hover .stat-icon {
  transform: scale(1.1);
}

.stat-content {
  flex: 1;
}

.stat-title {
  font-size: 0.875rem;
  font-weight: 500;
  color: #64748b;
  margin-bottom: 6px;
}

.stat-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: #0f172a;
}

.stat-trend {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 0.75rem;
  font-weight: 600;
}

.stat-trend.up {
  color: #059669;
}

.stat-trend.down {
  color: #dc2626;
}

.stat-arrow {
  flex-shrink: 0;
  color: #94a3b8;
  display: flex;
  align-items: center;
  transition: all 0.3s ease;
}

.stat-card:hover .stat-arrow {
  color: #64748b;
  transform: translateX(2px);
}

.content-grid {
  display: grid;
  grid-template-columns: 1.5fr 1fr;
  gap: 24px;
  margin-bottom: 40px;
}

.chart-card, .violations-card, .performance-card {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 24px;
  overflow: hidden;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
}

.performance-card {
  grid-column: 1 / -1;
}

.card-header {
  padding: 32px 32px 24px 32px;
  border-bottom: 1px solid rgba(0, 0, 0, 0.06);
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: rgba(248, 250, 252, 0.5);
}

.card-header h3 {
  font-size: 1.25rem;
  font-weight: 600;
  color: #0f172a;
  margin-bottom: 4px;
}

.subtitle {
  font-size: 0.875rem;
  color: #64748b;
}

.chart-controls {
  display: flex;
  gap: 4px;
  background: rgba(0, 0, 0, 0.05);
  padding: 4px;
  border-radius: 12px;
}

.control-btn {
  padding: 8px 16px;
  border: none;
  background: transparent;
  color: #64748b;
  font-size: 0.875rem;
  font-weight: 500;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.control-btn.active, .control-btn:hover {
  background: white;
  color: #1e40af;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.view-all-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 8px 16px;
  border-radius: 9999px; 
  background: #f1f5f9; 
  color: #2563eb;      
  font-size: 0.875rem;
  font-weight: 500;
  text-decoration: none;
  transition: all 0.3s ease;
  line-height: 1;
  transition: all 0.3s ease;
}

.view-all-btn:hover {
  background: #2563eb;
  color: white;
}

.view-all-btn svg {
  display: flex;          
  align-items: center;
  justify-content: center;
  position: relative;
  bottom: 3px;
  width: 16px;
  height: 16px;
  stroke: currentColor;
  flex-shrink: 0;
  transition: transform 0.2s ease;
}

.view-all-btn:hover svg {
  transform: translateX(3px);
}

.primary-btn {
  background: linear-gradient(135deg, #3b82f6, #1d4ed8);
  color: white;
  padding: 12px 20px;
  border-radius: 12px;
  text-decoration: none;
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 500;
  font-size: 0.875rem;
  transition: all 0.3s ease;
  border: none;
  cursor: pointer;
}

.primary-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3);
}

/* Enhanced Chart Styles */
.chart-container {
  padding: 32px;
  height: 320px;
  position: relative;
}

.chart-wrapper {
  height: 100%;
  display: flex;
  position: relative;
}

.chart-bars {
  flex: 1;
  display: flex;
  align-items: end;
  justify-content: space-between;
  height: 240px;
  margin-right: 60px;
  gap: 12px;
  padding-bottom: 40px;
}

.chart-bar {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  cursor: pointer;
  transition: all 0.3s ease;
  position: relative;
  max-width: 50px;
  height: 100%;
  justify-content: flex-end;
}

.bar-fill {
  width: 100%;
  background: linear-gradient(180deg, #3b82f6, #1e40af);
  border-radius: 8px 8px 0 0;
  transition: all 0.3s ease;
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
  position: relative;
  overflow: hidden;
  min-height: 4px;
}

.bar-fill::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 100%;
  background: linear-gradient(180deg, rgba(255,255,255,0.3) 0%, transparent 100%);
  opacity: 0;
  transition: opacity 0.3s ease;
}

.chart-bar:hover .bar-fill {
  background: linear-gradient(180deg, #2563eb, #1e40af);
  transform: scaleX(1.1);
  box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
}

.chart-bar:hover .bar-fill::before {
  opacity: 1;
}

.bar-label {
  margin-top: 12px;
  font-size: 0.75rem;
  color: #64748b;
  font-weight: 500;
  text-align: center;
}

.chart-axis {
  position: absolute;
  right: 0;
  top: 0;
  height: 240px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  align-items: flex-end;
  width: 40px;
}

.chart-axis span {
  font-size: 0.75rem;
  color: #94a3b8;
  font-weight: 500;
}

/* Chart Tooltip */
.chart-tooltip {
  position: fixed;
  background: rgba(0, 0, 0, 0.9);
  color: white;
  padding: 8px 12px;
  border-radius: 8px;
  font-size: 0.75rem;
  font-weight: 500;
  pointer-events: none;
  z-index: 1000;
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

.violations-list {
  padding: 24px 32px 32px 32px;
  max-height: 320px;
  overflow-y: auto;
}

.violation-item {
  display: flex;
  align-items: center;
  padding: 20px 0;
  border-bottom: 1px solid rgba(0, 0, 0, 0.06);
  gap: 20px;
  transition: all 0.2s ease;
}

.violation-item:last-child {
  border-bottom: none;
}

.violation-item:hover {
  background: rgba(59, 130, 246, 0.02);
  margin: 0 -16px;
  padding: 20px 16px;
  border-radius: 12px;
}

.violation-rank {
  width: 32px;
  height: 32px;
  background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.75rem;
  font-weight: 700;
  color: #475569;
  flex-shrink: 0;
}

.violation-info {
  flex: 1;
  min-width: 0;
}

.violation-name {
  font-weight: 600;
  color: #0f172a;
  margin-bottom: 6px;
  font-size: 0.875rem;
}

.violation-stats {
  display: flex;
  gap: 16px;
  align-items: center;
}

.cases {
  font-size: 0.75rem;
  color: #64748b;
  font-weight: 500;
}

.amount {
  font-weight: 700;
  color: #059669;
  font-size: 0.8rem;
}

.violation-progress {
  width: 80px;
  height: 4px;
  background: rgba(0, 0, 0, 0.08);
  border-radius: 2px;
  overflow: hidden;
  flex-shrink: 0;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #3b82f6, #1d4ed8);
  border-radius: 2px;
  transition: width 0.5s ease;
}

.table-wrapper {
  overflow-x: auto;
}

.modern-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.875rem;
}

.modern-table thead {
  background: rgba(248, 250, 252, 0.5);
}

.modern-table th {
  padding: 20px 24px;
  text-align: left;
  font-weight: 600;
  color: #475569;
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  border-bottom: 1px solid rgba(0, 0, 0, 0.06);
}

.table-row {
  transition: all 0.2s ease;
  border-bottom: 1px solid rgba(0, 0, 0, 0.04);
}

.table-row:hover {
  background: rgba(59, 130, 246, 0.02);
}

.modern-table td {
  padding: 20px 24px;
  vertical-align: middle;
}

.enforcer-cell {
  display: flex;
  align-items: center;
  gap: 16px;
}

.avatar {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #1e3a8a, #3b82f6);
  flex-shrink: 0;
  position: relative;
}

.avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.avatar-initials {
  color: white;
  font-weight: 600;
  font-size: 0.875rem;
}

.enforcer-details {
  min-width: 0;
}

.enforcer-name {
  font-weight: 600;
  color: #0f172a;
  font-size: 0.875rem;
  margin-bottom: 2px;
}

.enforcer-email {
  font-size: 0.75rem;
  color: #64748b;
}

.metric-number {
  font-weight: 700;
  color: #0f172a;
  font-size: 1.1rem;
}

.metric-bar {
  width: 60px;
  height: 4px;
  background: rgba(0, 0, 0, 0.08);
  border-radius: 2px;
  overflow: hidden;
  margin-bottom: 4px;
}

.metric-fill {
  height: 100%;
  background: linear-gradient(90deg, #3b82f6, #1d4ed8);
  border-radius: 2px;
  transition: width 0.5s ease;
}

.status-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 12px;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 500;
  text-transform: capitalize;
}

.status-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
}

.status-active {
  background: rgba(34, 197, 94, 0.1);
  color: #166534;
}

.status-active .status-dot {
  background: #22c55e;
}

.status-inactive {
  background: rgba(245, 158, 11, 0.1);
  color: #92400e;
}

.status-inactive .status-dot {
  background: #f59e0b;
}

.status-deactivated {
  background: rgba(239, 68, 68, 0.1);
  color: #991b1b;
}

.status-deactivated .status-dot {
  background: #ef4444;
}

.performance-score {
  font-weight: 700;
  font-size: 0.875rem;
  padding: 6px 12px;
  border-radius: 12px;
  text-align: center;
}

.performance-score.excellent {
  background: rgba(34, 197, 94, 0.1);
  color: #166534;
}

.performance-score.good {
  background: rgba(59, 130, 246, 0.1);
  color: #1e40af;
}

.performance-score.average {
  background: rgba(245, 158, 11, 0.1);
  color: #92400e;
}

.performance-score.poor {
  background: rgba(239, 68, 68, 0.1);
  color: #991b1b;
}

.actions-section {
  margin-top: 40px;
}

.section-title {
  font-size: 1.5rem;
  font-weight: 600;
  color: #0f172a;
  margin-bottom: 24px;
  text-align: center;
}

.actions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 24px;
}

.action-card {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 20px;
  padding: 32px;
  text-decoration: none;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  overflow: hidden;
  display: flex;
  align-items: center;
  gap: 20px;
}

.action-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 3px;
  background: linear-gradient(90deg, #3b82f6, #8b5cf6);
  transform: scaleX(0);
  transition: transform 0.3s ease;
}

.action-card:hover::before {
  transform: scaleX(1);
}

.action-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  background: rgba(255, 255, 255, 1);
}

.action-icon {
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #475569;
}

.action-content {
  flex: 1;
  min-width: 0;
}

.action-title {
  font-size: 1rem;
  font-weight: 600;
  color: #0f172a;
  margin-bottom: 4px;
}

.action-desc {
  font-size: 0.875rem;
  color: #64748b;
  line-height: 1.4;
}

.action-arrow {
  color: #94a3b8;
  transition: all 0.3s ease;
  flex-shrink: 0;
}

.action-card:hover .action-arrow {
  color: #3b82f6;
  transform: translateX(4px);
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 200px;
  color: #94a3b8;
  gap: 16px;
}

.empty-state p {
  font-size: 0.875rem;
  font-weight: 500;
}

/* Loading States */
.loading {
  opacity: 0.6;
  pointer-events: none;
}

/* Responsive Design */
@media (max-width: 1200px) {
  .content-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .admin-dashboard {
    padding: 20px;
  }
  
  .dashboard-header {
    flex-direction: column;
    gap: 20px;
    text-align: center;
  }
  
  .header-content h1 {
    font-size: 1.875rem;
  }
  
  .stat-value {
    font-size: 1.25rem;
  }
  
  .card-header {
    padding: 24px 20px 20px 20px;
    flex-direction: column;
    gap: 16px;
    align-items: flex-start;
  }
  
  .chart-container {
    padding: 20px;
    height: 280px;
  }
  
  .violations-list {
    padding: 20px;
  }
  
  .action-card {
    padding: 24px;
  }
  
  .actions-grid {
    grid-template-columns: 1fr;
  }
  
  .chart-controls {
    width: 100%;
    justify-content: center;
  }
  
  .modern-table {
    font-size: 0.8rem;
  }
  
  .modern-table th,
  .modern-table td {
    padding: 16px 12px;
  }
}

@media (max-width: 640px) {
  .violation-stats {
    flex-direction: column;
    align-items: flex-start;
    gap: 4px;
  }
  
  .action-card {
    flex-direction: column;
    text-align: center;
  }
  
  .action-arrow {
    transform: rotate(90deg);
  }
  
  .action-card:hover .action-arrow {
    transform: rotate(90deg) translateY(-4px);
  }
}

/* Scroll Styles */
.violations-list::-webkit-scrollbar,
.table-wrapper::-webkit-scrollbar {
  width: 6px;
}

.violations-list::-webkit-scrollbar-track,
.table-wrapper::-webkit-scrollbar-track {
  background: rgba(0, 0, 0, 0.05);
  border-radius: 3px;
}

.violations-list::-webkit-scrollbar-thumb,
.table-wrapper::-webkit-scrollbar-thumb {
  background: rgba(59, 130, 246, 0.3);
  border-radius: 3px;
}

.violations-list::-webkit-scrollbar-thumb:hover,
.table-wrapper::-webkit-scrollbar-thumb:hover {
  background: rgba(59, 130, 246, 0.5);
}

/* Animation Keyframes */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.stat-card {
  animation: fadeInUp 0.6s ease forwards;
}

.stat-card:nth-child(1) { animation-delay: 0.1s; }
.stat-card:nth-child(2) { animation-delay: 0.2s; }
.stat-card:nth-child(3) { animation-delay: 0.3s; }
.stat-card:nth-child(4) { animation-delay: 0.4s; }
.stat-card:nth-child(5) { animation-delay: 0.5s; }
.stat-card:nth-child(6) { animation-delay: 0.6s; }

.chart-card, .violations-card {
  animation: fadeInUp 0.6s ease 0.3s forwards;
  opacity: 0;
}

.performance-card {
  animation: fadeInUp 0.6s ease 0.4s forwards;
  opacity: 0;
}

.action-card {
  animation: fadeInUp 0.6s ease forwards;
  opacity: 0;
}

.action-card:nth-child(1) { animation-delay: 0.5s; }
.action-card:nth-child(2) { animation-delay: 0.6s; }
.action-card:nth-child(3) { animation-delay: 0.7s; }
.action-card:nth-child(4) { animation-delay: 0.8s; }
</style>