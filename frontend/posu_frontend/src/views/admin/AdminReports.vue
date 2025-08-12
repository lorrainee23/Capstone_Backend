<template>
  <SidebarLayout page-title="Reports & Analytics">
    <div class="admin-reports">
      <!-- Header -->
      <div class="page-header">
        <div class="header-left">
          <h2>System Reports</h2>
          <p>Generate comprehensive reports and analytics</p>
        </div>
      </div>

      <!-- Report Generation Card -->
      <div class="report-card">
        <div class="card-header">
          <h3>Generate New Report</h3>
        </div>
        <div class="card-body">
          <form @submit.prevent="generateReport" class="report-form">
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Report Type *</label>
                <select v-model="reportForm.type" class="form-select" required>
                  <option value="">Select Report Type</option>
                  <option value="violations">Violations Summary</option>
                  <option value="revenue">Revenue Report</option>
                  <option value="enforcer_performance">Enforcer Performance</option>
                  <option value="repeat_offenders">Repeat Offenders</option>
                  <option value="monthly_summary">Monthly Summary</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">Date Range *</label>
                <select v-model="reportForm.period" class="form-select" required>
                  <option value="">Select Period</option>
                  <option value="last_7_days">Last 7 Days</option>
                  <option value="last_30_days">Last 30 Days</option>
                  <option value="last_3_months">Last 3 Months</option>
                  <option value="last_6_months">Last 6 Months</option>
                  <option value="last_year">Last Year</option>
                  <option value="custom">Custom Range</option>
                </select>
              </div>
            </div>

            <div v-if="reportForm.period === 'custom'" class="form-row">
              <div class="form-group">
                <label class="form-label">Start Date</label>
                <input v-model="reportForm.start_date" type="date" class="form-input" required />
              </div>
              <div class="form-group">
                <label class="form-label">End Date</label>
                <input v-model="reportForm.end_date" type="date" class="form-input" required />
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">Additional Filters</label>
              <div class="checkbox-group">
                <label class="checkbox-item">
                  <input v-model="reportForm.include_charts" type="checkbox" />
                  <span>Include Charts and Graphs</span>
                </label>
                <label class="checkbox-item">
                  <input v-model="reportForm.include_details" type="checkbox" />
                  <span>Include Detailed Breakdown</span>
                </label>
                <label class="checkbox-item">
                  <input v-model="reportForm.export_pdf" type="checkbox" />
                  <span>Export as PDF</span>
                </label>
              </div>
            </div>

            <button type="submit" class="btn btn-primary" :disabled="generating">
              <span v-if="generating" class="spinner-small"></span>
              {{ generating ? 'Generating...' : 'Generate Report' }}
            </button>
          </form>
        </div>
      </div>

      <!-- Quick Stats -->
      <div class="stats-section">
        <h3>Quick Statistics</h3>
        <div class="stats-grid">
          <div class="stat-item">
            <div class="stat-icon">📊</div>
            <div class="stat-content">
              <div class="stat-number">{{ quickStats.total_violations || 0 }}</div>
              <div class="stat-label">Total Violations</div>
            </div>
          </div>
          <div class="stat-item">
            <div class="stat-icon">💰</div>
            <div class="stat-content">
              <div class="stat-number">₱{{ formatCurrency(quickStats.total_revenue || 0) }}</div>
              <div class="stat-label">Total Revenue</div>
            </div>
          </div>
          <div class="stat-item">
            <div class="stat-icon">⏳</div>
            <div class="stat-content">
              <div class="stat-number">₱{{ formatCurrency(quickStats.pending_amount || 0) }}</div>
              <div class="stat-label">Pending Payments</div>
            </div>
          </div>
          <div class="stat-item">
            <div class="stat-icon">📈</div>
            <div class="stat-content">
              <div class="stat-number">{{ quickStats.collection_rate || 0 }}%</div>
              <div class="stat-label">Collection Rate</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Reports -->
      <div class="recent-reports">
        <div class="card-header">
          <h3>Recent Reports</h3>
        </div>
        <div class="reports-list">
          <div v-if="recentReports.length === 0" class="no-data">
            <div class="no-data-icon">📋</div>
            <p>No reports generated yet</p>
          </div>
          <div v-else>
            <div v-for="report in recentReports" :key="report.id" class="report-item">
              <div class="report-info">
                <div class="report-title">{{ getReportTitle(report.type) }}</div>
                <div class="report-meta">
                  {{ formatDate(report.created_at) }} • {{ report.period }}
                </div>
              </div>
              <div class="report-actions">
                <button @click="downloadReport(report)" class="btn btn-secondary btn-sm">
                  Download
                </button>
                <button @click="viewReport(report)" class="btn btn-primary btn-sm">
                  View
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Report Preview Modal -->
      <div v-if="showPreviewModal" class="modal-overlay" @click="closePreview">
        <div class="modal modal-large" @click.stop>
          <div class="modal-header">
            <h3>Report Preview</h3>
            <button @click="closePreview" class="modal-close">✕</button>
          </div>
          <div class="modal-body">
            <div v-if="reportData" class="report-preview">
              <div class="report-summary">
                <h4>{{ getReportTitle(reportData.type) }}</h4>
                <p>Generated on {{ formatDate(reportData.created_at) }}</p>
                <p>Period: {{ reportData.period }}</p>
              </div>
              <div class="report-content">
                <pre>{{ JSON.stringify(reportData.data, null, 2) }}</pre>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button @click="closePreview" class="btn btn-secondary">Close</button>
            <button @click="downloadCurrentReport" class="btn btn-primary">Download</button>
          </div>
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
  name: 'AdminReports',
  components: {
    SidebarLayout
  },
  setup() {
    const generating = ref(false)
    const quickStats = ref({})
    const recentReports = ref([])
    const showPreviewModal = ref(false)
    const reportData = ref(null)
    
    const reportForm = ref({
      type: '',
      period: '',
      start_date: '',
      end_date: '',
      include_charts: true,
      include_details: false,
      export_pdf: false
    })
    
    const generateReport = async () => {
      try {
        generating.value = true
        const response = await adminAPI.generateReport(reportForm.value)
        
        if (response.data.success) {
          // Add to recent reports
          recentReports.value.unshift(response.data.data)
          
          // Reset form
          reportForm.value = {
            type: '',
            period: '',
            start_date: '',
            end_date: '',
            include_charts: true,
            include_details: false,
            export_pdf: false
          }
          
          alert('Report generated successfully!')
        }
      } catch (error) {
        console.error('Failed to generate report:', error)
        alert('Failed to generate report. Please try again.')
      } finally {
        generating.value = false
      }
    }
    
    const viewReport = (report) => {
      reportData.value = report
      showPreviewModal.value = true
    }
    
    const downloadReport = (report) => {
      // Create download link
      const blob = new Blob([JSON.stringify(report.data, null, 2)], { type: 'application/json' })
      const url = window.URL.createObjectURL(blob)
      const link = document.createElement('a')
      link.href = url
      link.download = `${report.type}_report_${report.created_at}.json`
      link.click()
      window.URL.revokeObjectURL(url)
    }
    
    const downloadCurrentReport = () => {
      if (reportData.value) {
        downloadReport(reportData.value)
      }
    }
    
    const closePreview = () => {
      showPreviewModal.value = false
      reportData.value = null
    }
    
    const getReportTitle = (type) => {
      const titles = {
        violations: 'Violations Summary Report',
        revenue: 'Revenue Analysis Report',
        enforcer_performance: 'Enforcer Performance Report',
        repeat_offenders: 'Repeat Offenders Report',
        monthly_summary: 'Monthly Summary Report'
      }
      return titles[type] || 'System Report'
    }
    
    const formatCurrency = (amount) => {
      return new Intl.NumberFormat('en-PH').format(amount)
    }
    
    const formatDate = (dateString) => {
      if (!dateString) return ''
      return new Date(dateString).toLocaleDateString('en-PH', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      })
    }
    
    const loadQuickStats = async () => {
      try {
        // This would typically come from a dedicated stats endpoint
        // For now, we'll use mock data
        quickStats.value = {
          total_violations: 1250,
          total_revenue: 875000,
          pending_amount: 125000,
          collection_rate: 85
        }
      } catch (error) {
        console.error('Failed to load quick stats:', error)
      }
    }
    
    onMounted(() => {
      loadQuickStats()
    })
    
    return {
      generating,
      quickStats,
      recentReports,
      showPreviewModal,
      reportData,
      reportForm,
      generateReport,
      viewReport,
      downloadReport,
      downloadCurrentReport,
      closePreview,
      getReportTitle,
      formatCurrency,
      formatDate
    }
  }
}
</script>

<style scoped>
.admin-reports {
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

.report-card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  margin-bottom: 32px;
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

.card-body {
  padding: 24px;
}

.report-form .form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
  margin-bottom: 20px;
}

.checkbox-group {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.checkbox-item {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
}

.checkbox-item input[type="checkbox"] {
  margin: 0;
}

.stats-section {
  margin-bottom: 32px;
}

.stats-section h3 {
  font-size: 20px;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 20px;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
}

.stat-item {
  background: white;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  display: flex;
  align-items: center;
  gap: 16px;
}

.stat-icon {
  font-size: 32px;
}

.stat-number {
  font-size: 24px;
  font-weight: 700;
  color: #1f2937;
  line-height: 1;
}

.stat-label {
  font-size: 14px;
  color: #6b7280;
  margin-top: 4px;
}

.recent-reports {
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.reports-list {
  padding: 24px;
}

.report-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 0;
  border-bottom: 1px solid #f3f4f6;
}

.report-item:last-child {
  border-bottom: none;
}

.report-title {
  font-weight: 500;
  color: #1f2937;
  margin-bottom: 4px;
}

.report-meta {
  font-size: 14px;
  color: #6b7280;
}

.report-actions {
  display: flex;
  gap: 8px;
}

.btn-sm {
  padding: 8px 16px;
  font-size: 14px;
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
  max-width: 500px;
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
  max-height: 60vh;
  overflow-y: auto;
}

.modal-footer {
  padding: 24px;
  border-top: 1px solid #e5e7eb;
  display: flex;
  justify-content: flex-end;
  gap: 12px;
}

.report-preview {
  font-family: 'Courier New', monospace;
}

.report-summary {
  margin-bottom: 24px;
  padding-bottom: 16px;
  border-bottom: 1px solid #e5e7eb;
}

.report-summary h4 {
  font-size: 16px;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 8px;
}

.report-content pre {
  background: #f9fafb;
  padding: 16px;
  border-radius: 8px;
  font-size: 12px;
  overflow-x: auto;
}

.spinner-small {
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top: 2px solid white;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-right: 8px;
}

@media (max-width: 768px) {
  .report-form .form-row {
    grid-template-columns: 1fr;
  }
  
  .stats-grid {
    grid-template-columns: 1fr;
  }
  
  .report-item {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }
  
  .modal-large {
    max-width: 95vw;
  }
}
</style>
