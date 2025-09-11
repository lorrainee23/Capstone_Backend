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

      <!-- Stats Filter Section -->
      <section class="stats-filter">
        <div class="filter-controls">
          <h3>Statistics Overview</h3>
          <div class="filter-buttons">
            <button 
              v-for="period in filterPeriods" 
              :key="period.value"
              @click="selectedPeriod = period.value"
              :class="['filter-btn', { active: selectedPeriod === period.value }]"
            >
              {{ period.label }}
            </button>
          </div>
        </div>
      </section>

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
            <p class="stat-value" :class="stat.valueColorClass">{{ stat.value }}</p>
            <p class="stat-trend" :class="stat.trend?.type">
				<!-- Up Arrow -->
				<svg v-if="stat.trend?.type === 'up'" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
					<polyline points="7 17 17 7"></polyline>
					<line x1="7" y1="7" x2="17" y2="7"></line>
					<line x1="17" y1="7" x2="17" y2="17"></line>
				</svg>

				<!-- Down Arrow -->
				<svg v-else-if="stat.trend?.type === 'down'" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
					<polyline points="17 7 7 17"></polyline>
					<line x1="7" y1="17" x2="17" y2="17"></line>
					<line x1="7" y1="7" x2="7" y2="17"></line>
				</svg>

				<!-- Neutral Arrow -->
				<svg v-else width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
					<line x1="6" y1="12" x2="18" y2="12"></line>
					<polyline points="12 6 18 12 12 18"></polyline>
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
        <!-- Trends Chart -->
        <section class="chart-card" aria-label="Violation Trends">
          <!-- Update your chart header section -->
          <header class="card-header">
            <h3>Violation Trends</h3>
            <div class="chart-controls">
              <!-- Chart period buttons -->
              <div class="control-group">
                <button 
                  v-for="period in chartPeriods" 
                  :key="period.value"
                  @click="selectedChartPeriod = period.value"
                  :class="['control-btn', { active: selectedChartPeriod === period.value }]"
                >
                  {{ period.label }}
                </button>
              </div>
              
              <!-- Year picker (only show when monthly is selected) -->
              <div v-if="selectedChartPeriod === 'monthly'" class="year-picker">
                <select 
                  v-model="selectedYear" 
                  class="year-select"
                  aria-label="Select Year"
                >
                  <option v-for="year in availableYears" :key="year" :value="year">
                    {{ year }}
                  </option>
                </select>
              </div>
            </div>
          </header>
          <div class="chart-container">
            <div v-if="chartData.length" class="chart-wrapper">
              <!-- Chart Content -->
              <div class="chart-bars">
                <div 
                  v-for="(data, index) in chartData" 
                  :key="index"
                  class="chart-bar"
                  @mouseenter="showTooltip($event, data)"
                  @mouseleave="hideTooltip"
                >
                  <div 
                    class="bar-fill"
                    :style="{ 
                      height: `${(data.count / maxChartCount) * 100}%`,
                      minHeight: data.count > 0 ? '4px' : '0px'
                    }"
                  ></div>
                  <div class="bar-label">{{ formatChartLabel(data, selectedChartPeriod) }}</div>
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
          <div class="performance-controls">
            <button 
              @click="showEditTargetModal = true"
              class="edit-target-btn"
              aria-label="Edit Target"
            >
              <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 20h9"></path>
                <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
              </svg>
              Target: {{ performanceTarget }}
            </button>
            <router-link to="/admin/users" class="primary-btn" aria-label="Manage Enforcers">
              <svg width="20" height="20" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" overflow="visible" aria-hidden="true">
                <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="8.5" cy="7" r="4"></circle>
                <line x1="20" y1="8" x2="20" y2="14"></line>
                <line x1="23" y1="11" x2="17" y2="11"></line>
              </svg>
              Manage Enforcers
            </router-link>
          </div>
        </header>
        <div class="table-wrapper">
          <table class="modern-table" role="table">
            <thead>
              <tr>
                <th scope="col">Enforcer</th>
                <th scope="col">Total Cases</th>
                <th scope="col">This {{ getPeriodLabel(selectedChartPeriod) }}</th>
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
                <td><span class="metric-number">{{ enforcer.transactions?.length || 0 }}</span></td>
                <td>
                  <div class="metric-bar" aria-label="Apprehensions progress">
                    <div 
                      class="metric-fill"
                      :style="{ width: `${Math.min((getPeriodApprehensions(enforcer.transactions) / performanceTarget) * 100, 100)}%` }"
                    ></div>
                  </div>
                  <span class="metric-number">{{ getPeriodApprehensions(enforcer.transactions) || 0 }}</span>
                </td>
                <td>
                  <span class="status-pill" :class="`status-${enforcer.status?.toLowerCase()}`" :aria-label="enforcer.status">
                    <span class="status-dot"></span>
                    {{ enforcer.status }}
                  </span>
                </td>
                <td><time :datetime="enforcer.updated_at">{{ formatDate(enforcer.updated_at) }}</time></td>
                <td>
                  <div class="performance-score" :class="getPerformanceClass(enforcer.transactions?.length || 0)">
                    {{ getPerformanceScore(enforcer.transactions?.length || 0) }}%
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

          <router-link to="/admin/reports" class="action-card" aria-label="Reports">
            <div class="action-icon reports" aria-hidden="true">
              <svg width="24" height="24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
                <polyline points="10 9 9 9 8 9"></polyline>
              </svg>
            </div>
            <div class="action-content">
              <p class="action-title">Reports</p>
              <p class="action-desc">Generate detailed system reports</p>
            </div>
            <div class="action-arrow" aria-hidden="true">
              <svg width="20" height="20" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="5" y1="12" x2="19" y2="12"></line>
                <polyline points="12 5 19 12 12 19"></polyline>
              </svg>
            </div>
          </router-link>

          <router-link to="/admin/transactions" class="action-card" aria-label="Transactions">
            <div class="action-icon transactions" aria-hidden="true">
              <svg width="24" height="24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polygon points="12 2 15 9 22 9 16 14 18 21 12 17 6 21 8 14 2 9 9 9" />
            </svg>
            </div>
            <div class="action-content">
              <p class="action-title">Transactions</p>
              <p class="action-desc">View and manage all transactions</p>
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

          <router-link to="/admin/archives" class="action-card" aria-label="Archives">
            <div class="action-icon archives" aria-hidden="true">
              <svg width="24" height="24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="21 8 21 21 3 21 3 8"></polyline>
                <rect x="1" y="3" width="22" height="5"></rect>
                <line x1="10" y1="12" x2="14" y2="12"></line>
              </svg>
            </div>
            <div class="action-content">
              <p class="action-title">Archives</p>
              <p class="action-desc">View archived records and data</p>
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

      <!-- Edit Target Modal -->
      <div v-if="showEditTargetModal" class="modal-overlay" @click="closeModal">
        <div class="modal-content" @click.stop>
          <div class="modal-header">
            <h3>Edit Performance Target</h3>
            <button @click="closeModal" class="close-btn">
              <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
              </svg>
            </button>
          </div>
          <div class="modal-body">
            <label for="target-input">Target Cases per {{ getPeriodLabel(selectedChartPeriod) }}</label>
            <input 
              id="target-input"
              type="number" 
              v-model="newTarget" 
              min="1" 
              max="1000"
              class="target-input"
            >
          </div>
          <div class="modal-footer">
            <button @click="closeModal" class="cancel-btn">Cancel</button>
            <button @click="updateTarget" class="save-btn">Save Changes</button>
          </div>
        </div>
      </div>
    </div>
  </SidebarLayout>
</template>

<script>
import { ref, computed, onMounted, watch } from 'vue'
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
    const monthlyData = ref([])
    const yearlyData = ref([])
    const trendsData = ref({})
    
    // Filter states
    const selectedPeriod = ref('month')
    const selectedChartPeriod = ref('monthly')
    const performanceTarget = ref(30)
    const newTarget = ref(5)
    const showEditTargetModal = ref(false)
    const selectedYear = ref(new Date().getFullYear())
    
    // Filter options
    const filterPeriods = [
      { label: 'All Time', value: 'all' },
      { label: 'This Year', value: 'year' },
      { label: 'This Month', value: 'month' },
      { label: 'This Week', value: 'week' }
    ]
    
    const chartPeriods = [
      { label: 'Weekly', value: 'weekly' },
      { label: 'Monthly', value: 'monthly' },
      { label: 'Yearly', value: 'yearly' }
    ]
    
    const statsConfig = computed(() => {
    const percentage = trendsData.value.transactions?.percentage || 0
		const direction = trendsData.value.transactions?.direction || '0'
		const trendDisplay = `${percentage > 0 ? '+' : ''}${percentage}%`
      
      return [
        {
          iconSvg: `
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="7" r="4"></circle>
              <path d="M5.5 21a6.5 6.5 0 0 1 13 0"></path>
            </svg>`,
          value: stats.value.total_violators || 0,
          label: 'Total Violators',
          trend: direction !== '0' 
        ? { type: direction, value: trendDisplay } 
        : { type: null, value: '0%' },
          colorClass: 'stat-blue',
          
        },
        {
          iconSvg: `
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
            </svg>`,
          value: stats.value.total_transactions || 0,
          label: 'Total Transactions',
          trend: direction !== '0' 
        ? { type: direction, value: trendDisplay } 
        : { type: null, value: '0%' },
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
			trend: direction !== '0' 
        ? { type: direction, value: trendDisplay } 
        : { type: null, value: '0%' },
          colorClass: 'stat-yellow'
        },
        {
          iconSvg: `
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"></circle>
              <path d="M9 12l2 2 4-4"></path>
            </svg>`,
          value: stats.value.paid_transactions || 0,
          label: 'Paid Transactions',
				trend: direction !== '0' 
        ? { type: direction, value: trendDisplay } 
        : { type: null, value: '0%' },
          colorClass: 'stat-green'
        },
        {
          iconSvg: `
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 1v22"></path>
              <path d="M17 5H7a3 3 0 1 0 0 6h10a3 3 0 1 1 0 6H7"></path>
              <circle cx="12" cy="12" r="1"></circle>
            </svg>`,
          value: `₱${formatCurrency(stats.value.total_revenue || 0)}`,
          label: 'Total Revenue',
			trend: direction !== '0' 
        ? { type: direction, value: trendDisplay } 
        : { type: null, value: '0%' },
          colorClass: 'stat-green',
          valueColorClass: 'text-green-600'
        },
        {
          iconSvg: `
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#8b5cf6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
            </svg>`,
          value: `₱${formatCurrency(stats.value.pending_revenue || 0)}`,
          label: 'Pending Revenue',
			trend: direction !== '0' 
        ? { type: direction, value: trendDisplay } 
        : { type: null, value: '0%' },
          colorClass: 'stat-purple',
          valueColorClass: 'text-green-600'
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
			trend: direction !== '0' 
        ? { type: direction, value: trendDisplay } 
        : { type: null, value: '0%' },
          colorClass: 'stat-red'
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
			trend: direction !== '0' 
        ? { type: direction, value: trendDisplay } 
        : { type: null, value: '0%' },
          colorClass: 'stat-cyan'
        }
      ]
    })

    const availableYears = computed(() => {
      if (!monthlyData.value.length) return [new Date().getFullYear()]
      return [...new Set(monthlyData.value.map(item => item.year))].sort((a, b) => b - a)
    })
    
    const chartData = computed(() => {
  switch (selectedChartPeriod.value) {
    case 'monthly':
      return monthlyData.value
        .filter(item => item.year === selectedYear.value)
        .map(item => ({
          ...item,
          date: `${item.year}-${String(item.month).padStart(2, '0')}-01`,
          period: item.month
        }))
    case 'yearly':
      return yearlyData.value.map(item => ({
        ...item,
        date: `${item.year}-01-01`,
        period: item.year
      }))
    default:
      return weeklyData.value
  }
})
    
    const maxChartCount = computed(() => {
      return Math.max(...chartData.value.map(d => d.count), 1)
    })
    
    const maxViolationCount = computed(() => {
      return Math.max(...commonViolations.value.map(v => v.transactions_count), 1)
    })
    
    const yAxisTicks = computed(() => {
      const max = maxChartCount.value
      return [max, Math.floor(max * 0.75), Math.floor(max * 0.5), Math.floor(max * 0.25), 0]
    })
    
    const loadDashboardData = async () => {
      try {
        loading.value = true

        const requestParams = {
      period: selectedPeriod.value,
      chart_period: selectedChartPeriod.value
    }

    
    const response = await adminAPI.dashboard(requestParams)
    
        if (response.data.status === 'success') {
          const data = response.data.data
          stats.value = data.stats || {}
          commonViolations.value = data.common_violations || []
          enforcerPerformance.value = data.enforcer_performance || []
          weeklyData.value = data.weekly_trends || []
          monthlyData.value = data.monthly_trends || []
          yearlyData.value = data.yearly_trends || []
          trendsData.value = data.trends || {}
        }
      } catch (error) {
        console.error('Failed to load dashboard data:', error)
      } finally {
        loading.value = false
      }
    }

    const getPeriodApprehensions = (transactions) => {
      if (!transactions || !Array.isArray(transactions)) return 0
      
      const now = new Date()
      
      return transactions.filter(t => {
        const date = new Date(t.date_time)
        
        if (selectedChartPeriod.value === 'monthly') {
          return date.getMonth() === now.getMonth() && date.getFullYear() === now.getFullYear()
        } else if (selectedChartPeriod.value === 'yearly') {
          return date.getFullYear() === now.getFullYear()
        } else {
          // Weekly - last 7 days
          const weekAgo = new Date(now.getTime() - (7 * 24 * 60 * 60 * 1000))
          return date >= weekAgo && date <= now
        }
      }).length
    }

    const formatCurrency = (amount) => {
      return new Intl.NumberFormat('en-PH').format(amount || 0)
    }
    
    const formatDate = (dateString) => {
      if (!dateString) return 'Never'
      return new Date(dateString).toLocaleDateString('en-PH', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      })
    }
    
    const formatChartLabel = (dataPoint, period) => {
      if (period === 'yearly') {
        return dataPoint.period || dataPoint.year
      } else if (period === 'monthly') {
        const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
        const monthIndex = (dataPoint.period || dataPoint.month) - 1
        return monthNames[monthIndex] || 'Unknown'
      } else {
        // Weekly - format as date
        if (dataPoint.date) {
          const date = new Date(dataPoint.date)
          return date.toLocaleDateString('en-PH', { month: 'short', day: 'numeric' })
        }
        return 'Unknown'
      }
    }
    
    const getPeriodLabel = (period) => {
      switch (period) {
        case 'monthly': return 'Month'
        default: return 'Month'
      }
    }
    
    const getInitials = (firstName, lastName) => {
      return `${firstName?.charAt(0) || ''}${lastName?.charAt(0) || ''}`.toUpperCase()
    }
    
    const getPerformanceScore = (count) => {
      if (!count) return 0
      return Math.min(Math.round((count / performanceTarget.value) * 100), 100)
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
        content: `${formatChartLabel(data, selectedChartPeriod.value)}: ${data.count} violations`
      }
    }

    const hideTooltip = () => {
      tooltip.value.visible = false
    }
    
    const updateTarget = () => {
      performanceTarget.value = parseInt(newTarget.value) || 5
      showEditTargetModal.value = false
    }
    
    const closeModal = () => {
      showEditTargetModal.value = false
      newTarget.value = performanceTarget.value
    }
    const handlePeriodChange = (period) => {
    selectedPeriod.value = period
      }

      watch([selectedPeriod, selectedChartPeriod], () => {
      loadDashboardData()
    })
    
    onMounted(() => {
      loadDashboardData()
    })
    
    return {
      loading,
      stats,
      commonViolations,
      enforcerPerformance,
      statsConfig,
      chartData,
      maxChartCount,
      maxViolationCount,
      yAxisTicks,
      selectedPeriod,
      selectedChartPeriod,
      filterPeriods,
      chartPeriods,
      performanceTarget,
      newTarget,
      showEditTargetModal,
      loadDashboardData,
      formatCurrency,
      formatDate,
      formatChartLabel,
      getPeriodLabel,
      getInitials,
      getPerformanceScore,
      getPerformanceClass,
      tooltip,
      showTooltip,
      hideTooltip,
      getPeriodApprehensions,
      updateTarget,
      closeModal,
      handlePeriodChange,
      selectedYear,
      availableYears
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

/* Stats Filter Section */
.stats-filter {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 20px;
  padding: 24px 32px;
  margin-bottom: 24px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
}

.filter-controls {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.filter-controls h3 {
  font-size: 1.25rem;
  font-weight: 600;
  color: #0f172a;
  margin: 0;
}

.filter-buttons {
  display: flex;
  gap: 4px;
  background: rgba(0, 0, 0, 0.05);
  padding: 4px;
  border-radius: 12px;
}

.filter-btn {
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

.filter-btn.active, .filter-btn:hover {
  background: white;
  color: #1e40af;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
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

/* Add this to your CSS */
.chart-controls {
  display: flex;
  align-items: center;
  gap: 1rem;
  flex-wrap: wrap;
}

.control-group {
  display: flex;
  gap: 0.5rem;
}

.year-picker {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.year-select {
  padding: 0.5rem 0.75rem;
  border: 2px solid #e2e8f0;
  border-radius: 0.5rem;
  background-color: white;
  color: #1f2937;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  outline: none;
  min-width: 80px;
}

.year-select:hover {
  border-color: #3b82f6;
}

.year-select:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .chart-controls {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.75rem;
  }
  
  .control-group {
    order: 1;
  }
  
  .year-picker {
    order: 2;
  }
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

.performance-controls {
  display: flex;
  gap: 12px;
  align-items: center;
}

.edit-target-btn {
  background: rgba(59, 130, 246, 0.1);
  color: #1e40af;
  border: 1px solid rgba(59, 130, 246, 0.2);
  padding: 8px 16px;
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  gap: 6px;
}

.edit-target-btn:hover {
  background: rgba(59, 130, 246, 0.2);
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
  align-items: flex-end;
  justify-content: center;
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

/* Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  animation: fadeIn 0.3s ease;
}

.modal-content {
  background: white;
  border-radius: 20px;
  width: 100%;
  max-width: 400px;
  margin: 20px;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  animation: slideUp 0.3s ease;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 24px 24px 0 24px;
}

.modal-header h3 {
  font-size: 1.25rem;
  font-weight: 600;
  color: #0f172a;
  margin: 0;
}

.close-btn {
  background: none;
  border: none;
  color: #64748b;
  cursor: pointer;
  padding: 4px;
  border-radius: 8px;
  transition: all 0.2s ease;
}

.close-btn:hover {
  background: rgba(0, 0, 0, 0.05);
  color: #374151;
}

.modal-body {
  padding: 24px;
}

.modal-body label {
  display: block;
  font-size: 0.875rem;
  font-weight: 500;
  color: #374151;
  margin-bottom: 8px;
}

.target-input {
  width: 100%;
  padding: 12px 16px;
  border: 1px solid #d1d5db;
  border-radius: 12px;
  font-size: 1rem;
  transition: all 0.2s ease;
  background: white;
}

.target-input:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.modal-footer {
  display: flex;
  gap: 12px;
  padding: 0 24px 24px 24px;
}

.cancel-btn, .save-btn {
  flex: 1;
  padding: 12px 20px;
  border-radius: 12px;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  border: none;
}

.cancel-btn {
  background: #f8fafc;
  color: #64748b;
  border: 1px solid #e2e8f0;
}

.cancel-btn:hover {
  background: #f1f5f9;
  color: #475569;
}

.save-btn {
  background: linear-gradient(135deg, #3b82f6, #1d4ed8);
  color: white;
}

.save-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
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

/* Animation Keyframes */
@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

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
.action-card:nth-child(5) { animation-delay: 0.9s; }
.action-card:nth-child(6) { animation-delay: 1.0s; }

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
    padding: 24px;
  }
  
  .header-content h1 {
    font-size: 1.875rem;
  }
  
  .filter-controls {
    flex-direction: column;
    gap: 16px;
    align-items: stretch;
  }
  
  .filter-buttons {
    justify-content: center;
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
  
  .performance-controls {
    flex-direction: column;
    width: 100%;
    gap: 8px;
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
  
  .modal-content {
    margin: 10px;
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
</style>