<template>
  <SidebarLayout page-title="Reports & Analytics">
    <div class="admin-reports">
      <!-- Header -->
      <header class="dashboard-header">
        <div class="header-content">
          <h1>System Reports & Analytics</h1>
          <p>Generate comprehensive reports with multiple export formats</p>
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

      <!-- Report Generation Card -->
      <div class="report-card">
        <div class="card-header">
          <h3>Generate New Report</h3>
          <div class="card-subtitle">Create detailed reports with customizable parameters</div>
        </div>
        <div class="card-body">
          <form @submit.prevent="generateReport" class="report-form">
            <div class="form-section">
              <h4>Report Configuration</h4>
              <div class="form-row">
                <div class="form-group">
                  <label class="form-label">Report Type <span class="required">*</span></label>
                  <select v-model="reportForm.type" class="form-select" required>
                    <option value="">Select Report Type</option>
                    <option value="total_revenue">Total Revenue</option>
                    <option value="all_violators">All Violators</option>
                    <option value="common_violations">Common Violations</option>
                    <option value="enforcer_performance">Enforcer Performance</option>
                  </select>
                </div>
                <div class="form-group">
                  <label class="form-label">Date Range <span class="required">*</span></label>
                  <select v-model="reportForm.period" class="form-select" required>
                    <option value="">Select Period</option>
                    <option value="today">Today</option>
                    <option value="yesterday">Yesterday</option>
                    <option value="last_7_days">Last 7 Days</option>
                    <option value="last_30_days">Last 30 Days</option>
                    <option value="last_3_months">Last 3 Months</option>
                    <option value="last_6_months">Last 6 Months</option>
                    <option value="last_year">Last Year</option>
                    <option value="year_to_date">Year to Date</option>
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
            </div>

            <div class="form-section">
              <h4>Export Options</h4>
              <div class="export-options">
  <div class="export-group">
    <div class="export-item">
      <input v-model="reportForm.export_formats" type="checkbox" value="pdf" id="pdf" />
      <label for="pdf" class="export-label">
        <svg class="export-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"></path>
          <polyline points="14,2 14,8 20,8"></polyline>
          <path d="M16,13a2,2 0 0,1 -2,2 2,2 0 0,1 -2,-2 2,2 0 0,1 2,-2 2,2 0 0,1 2,2z"></path>
          <path d="M10,13h2v6h-2v-6z"></path>
        </svg>
        <div class="export-info">
          <div class="export-title">PDF Report</div>
          <div class="export-desc">Professional formatted document</div>
        </div>
      </label>
    </div>
    <div class="export-item">
      <input v-model="reportForm.export_formats" type="checkbox" value="excel" id="excel" />
      <label for="excel" class="export-label">
        <svg class="export-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
          <path d="M7 7h10v2H7zM7 11h10v2H7zM7 15h6v2H7z"></path>
        </svg>
        <div class="export-info">
          <div class="export-title">Excel Spreadsheet</div>
          <div class="export-desc">Data analysis and manipulation</div>
        </div>
      </label>
    </div>
    <div class="export-item">
      <input v-model="reportForm.export_formats" type="checkbox" value="word" id="word" />
      <label for="word" class="export-label">
        <svg class="export-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"></path>
          <polyline points="14,2 14,8 20,8"></polyline>
          <path d="M7,10 L9,16 L12,10 L15,16 L17,10"></path>
        </svg>
        <div class="export-info">
          <div class="export-title">Word Document</div>
          <div class="export-desc">Detailed narrative report</div>
        </div>
      </label>
    </div>
    
  </div>
</div>
            </div>



            <div class="form-actions">
              <button type="button" @click="previewReport" class="btn btn-secondary btn-sm" :disabled="!canGenerate">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                  <circle cx="12" cy="12" r="3"></circle>
                </svg>
                Preview Report
              </button>
              <button type="submit" class="btn btn-primary btn-sm" :disabled="generating || !canGenerate">
                <span v-if="generating" class="spinner"></span>
                <svg v-else class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                  <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                  <polyline points="7,10 12,15 17,10"></polyline>
                  <line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
                {{ generating ? 'Generating...' : 'Generate Report' }}
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Report History -->
      <div class="report-history">
        <div class="card-header">
          <h3>Report History</h3>
          <div class="header-actions">
  <button @click="clearHistory" class="btn btn-ghost btn-sm clear-history-btn" :disabled="recentReports.length === 0">
    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
      <polyline points="3,6 5,6 21,6"></polyline>
      <path d="M19,6v14a2,2 0 0,1 -2,2H7a2,2 0 0,1 -2,-2V6m3,0V4a2,2 0 0,1 2,-2h4a2,2 0 0,1 2,2v2"></path>
      <line x1="10" y1="11" x2="10" y2="17"></line>
      <line x1="14" y1="11" x2="14" y2="17"></line>
    </svg>
    Clear History
  </button>
</div>
        </div>
        
        <div class="history-filters">
          <div class="filter-group">
            <select v-model="historyFilter" class="form-select form-select-sm">
              <option value="">All Reports</option>
              <option value="violations">Violations</option>
              <option value="revenue">Revenue</option>
              <option value="enforcer_performance">Performance</option>
            </select>
            <input v-model="searchQuery" type="text" placeholder="Search reports..." class="form-input form-input-sm" />
          </div>
        </div>

        <div class="reports-list">
          <div v-if="filteredReports.length === 0" class="empty-state">
            <svg class="empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"></path>
              <polyline points="14,2 14,8 20,8"></polyline>
              <line x1="16" y1="13" x2="8" y2="13"></line>
              <line x1="16" y1="17" x2="8" y2="17"></line>
              <polyline points="10,9 9,9 8,9"></polyline>
            </svg>
            <h4>No reports found</h4>
            <p>Generate your first report to see it here</p>
          </div>
          
          <div v-else class="report-grid">
            <div v-for="report in filteredReports" :key="report.id" class="report-card-item">
              <div class="report-header">
                <div class="report-type-badge" :class="getReportTypeClass(report.type)">
                  {{ getReportTypeLabel(report.type) }}
                </div>
                <div class="report-date">{{ formatDate(report.created_at) }}</div>
              </div>
              
              <div class="report-content">
  <h4 class="report-title">{{ getReportTitle(report.type) }}</h4>
  <div class="report-meta">
    <span class="meta-item">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
        <line x1="16" y1="2" x2="16" y2="6"></line>
        <line x1="8" y1="2" x2="8" y2="6"></line>
        <line x1="3" y1="10" x2="21" y2="10"></line>
      </svg>
      {{getPeriodLabel(report.period) }}
    </span>
    <span class="meta-item">
  <template v-for="f in report.files" :key="f.filename">
  <span 
    class="file-badge" 
    :class="getFileClass(f.filename)"
  >
    {{ getFileLabel(f.filename) }}
  </span>
</template>

</span>

  </div>
</div>
              
              <div class="report-actions">
                <div class="action-group">
                  <button @click="viewReport(report)" class="btn btn-ghost btn-sm">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                      <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                      <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                    View
                  </button>
                  <button @click="downloadReport(report)" class="btn btn-primary btn-sm">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                      <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                      <polyline points="7,10 12,15 17,10"></polyline>
                      <line x1="12" y1="15" x2="12" y2="3"></line>
                    </svg>
                    Download
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Report Preview Modal -->
      <div v-if="showPreviewModal" class="modal-overlay" @click="closePreview">
        <div class="modal modal-xl" @click.stop>
          <div class="modal-header">
            <div class="modal-title">
              <h3>Report Preview</h3>
              <div class="modal-subtitle">{{ getReportTitle(reportData?.type) }}</div>
            </div>
            <button @click="closePreview" class="modal-close">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
              </svg>
            </button>
          </div>
          
          <div class="modal-body">
            <div v-if="reportData" class="report-preview">
              <div class="preview-header">
                <div class="preview-info">
                  <div class="info-item">
                    <strong>Generated:</strong> {{ formatDateTime(reportData.created_at) }}
                  </div>
                  <div class="info-item">
                    <strong>Period:</strong> {{ getPeriodLabel(reportData.period) }}
                  </div>
                  <div class="info-item">
                    <strong>Records:</strong> {{ reportData.total_records || 0 }}
                  </div>
                </div>
              </div>
              
              <div class="preview-content">
                <div class="content-tabs">
                  <button @click="activeTab = 'summary'" 
                          :class="['tab-btn', { active: activeTab === 'summary' }]">
                    Summary
                  </button>
                  <button @click="activeTab = 'data'" 
                          :class="['tab-btn', { active: activeTab === 'data' }]">
                    Raw Data
                  </button>
                </div>
                
                <div v-if="activeTab === 'summary'" class="tab-content">
                  <div class="summary-stats">
                    <div v-for="(value, key) in reportData.summary" :key="key" class="summary-item">
                      <span class="summary-label">{{ formatLabel(key) }}</span>
                      <span class="summary-value">{{ formatValue(value, key) }}</span>
                    </div>
                  </div>
                </div>
                
                <div v-if="activeTab === 'data'" class="tab-content">
                  <div class="data-table">
                    <pre>{{ JSON.stringify(reportData.reportContent, null, 2) }}</pre>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="modal-footer">
            <button @click="closePreview" class="btn btn-secondary">
              <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
              </svg>
              Close
            </button>
            <button @click="downloadCurrentReport" class="btn btn-primary">
              <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                <polyline points="7,10 12,15 17,10"></polyline>
                <line x1="12" y1="15" x2="12" y2="3"></line>
              </svg>
              Download All Formats
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
import { adminAPI } from '@/services/api'
import Swal from 'sweetalert2'

export default {
  name: 'AdminReports',
  components: {
    SidebarLayout
  },
  setup() {
    const generating = ref(false)
    const refreshing = ref(false)
    const quickStats = ref({})
    const recentReports = ref([])
    const showPreviewModal = ref(false)
    const reportData = ref(null)
    const activeTab = ref('summary')
    const historyFilter = ref('')
    const searchQuery = ref('')
    const lastUpdated = ref('')
    
    const reportForm = ref({
      type: '',
      period: '',
      start_date: '',
      end_date: '',
      export_formats: ['pdf'],
      include_charts: false,
      include_details: false,
      include_summary: false,
      include_comparison: false
    })

    const generateSummaryFromData = (records) => {
  if (!Array.isArray(records)) return {}

  const summary = {}
  summary.total_violations = records.length
  summary.total_fines = records.reduce((sum, r) => sum + Number(r.fine_amount || 0), 0)

  const pending = records.filter(r => r.status === 'Pending').length
  const paid = records.filter(r => r.status === 'Paid').length
  summary.pending_count = pending
  summary.paid_count = paid

  return summary
}
    const canGenerate = computed(() => {
      const hasBase = reportForm.value.type && reportForm.value.period && reportForm.value.export_formats.length > 0
      if (reportForm.value.period === 'custom') {
        return hasBase && reportForm.value.start_date && reportForm.value.end_date
      }
      return hasBase
    })
    
    const filteredReports = computed(() => {
      let filtered = recentReports.value
      
      if (historyFilter.value) {
        filtered = filtered.filter(report => report.type === historyFilter.value)
      }
      
      if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase()
        filtered = filtered.filter(report => 
          getReportTitle(report.type).toLowerCase().includes(query) ||
          report.period.toLowerCase().includes(query)
        )
      }
      
      return filtered
    })
    
    // Simple file generation 
    const downloadFile = (base64, filename, mimeType) => {
  try {
    const binary = Uint8Array.from(atob(base64), c => c.charCodeAt(0));
    const blob = new Blob([binary], { type: mimeType });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
  } catch (e) {
    console.error('Failed to download file:', e);
  }
};
    
    // Report generation
const generateReport = async () => {
  if (!canGenerate.value) return;
  
  try {
    generating.value = true;

    const payload = {
      type: reportForm.value.type,
      period: reportForm.value.period,
      start_date: reportForm.value.period === 'custom' ? reportForm.value.start_date : undefined,
      end_date: reportForm.value.period === 'custom' ? reportForm.value.end_date : undefined,
      export_formats: reportForm.value.export_formats
    };

    const response = await adminAPI.generateReport(payload);
    const data = response.data?.data || {};

    // Prepare report record
    const reportRecord = {
      id: data.id || Date.now(),
      type: payload.type,
      period: payload.period,
      formats: payload.export_formats,
      created_at: data.created_at || new Date().toISOString(),
      reportContent: data.report || data,
      total_records: data.total_records || data.report?.length || 0,
      summary: data.summary || generateSummaryFromData(data.report || []),
      files: Array.isArray(data.files) ? data.files : []
    };

    recentReports.value.unshift(reportRecord);

    if (reportRecord.files.length > 0) {
  for (const f of reportRecord.files) {
    if (f.filename) {
      try {
        const response = await adminAPI.downloadReportFile(f.filename);
        const blob = new Blob([response.data], { type: f.mimeType || 'application/octet-stream' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = f.filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        setTimeout(() => URL.revokeObjectURL(link.href), 100);
      } catch (err) {
        console.error('Failed to download file:', err);
        alert(`Failed to download ${f.filename}`);
      }
    }
  }
} else {
  const jsonContent = JSON.stringify(reportRecord.reportContent, null, 2);
  downloadFile(
    jsonContent,
    `${reportRecord.type}_report_${reportRecord.id}.json`,
    'application/json'
  );
}

    await Swal.fire({
      title: '✅ Report Generated & Downloaded',
      text: `Formats: ${payload.export_formats.join(', ').toUpperCase()}`,
      icon: 'success',
      confirmButtonText: 'OK'
    });

    resetForm();
  } catch (error) {
    console.error('Failed to generate report:', error);

    let errorMessage = 'Failed to generate report. Please try again.';
    const message = error?.response?.data?.message;
    const errors = error?.response?.data?.errors;

    if (errors) {
      const firstKey = Object.keys(errors)[0];
      errorMessage = `${firstKey} - ${errors[firstKey]?.[0] || ''}`;
    } else if (message) {
      errorMessage = message;
    }

    await Swal.fire({
      title: '❌ Error',
      text: errorMessage,
      icon: 'error',
      confirmButtonText: 'OK'
    });
  } finally {
    generating.value = false;
  }
};

    const previewReport = async () => {
  if (!canGenerate.value) return;

  try {
    generating.value = true;

    const payload = {
      type: reportForm.value.type,
      period: reportForm.value.period,
      start_date: reportForm.value.period === 'custom' ? reportForm.value.start_date : undefined,
      end_date: reportForm.value.period === 'custom' ? reportForm.value.end_date : undefined
    };

    const response = await adminAPI.generateReport(payload);
    const data = response.data?.data;

console.log('API response:', data);
    reportData.value = {
  id: data.id || Date.now(),
  type: payload.type,
  period: payload.period,
  created_at: data.created_at || new Date().toISOString(),
  reportContent: Array.isArray(data.report) ? data.report : [], 
  total_records: data.report?.length || 0,
  summary: data.summary || generateSummaryFromData(data.report || [])
};


    showPreviewModal.value = true;
    activeTab.value = 'summary';

  } catch (error) {
    console.error('Failed to preview report:', error);
    alert('Failed to load report preview. Please try again.');
  } finally {
    generating.value = false;
  }
};
    
    const viewReport = (report) => {
      reportData.value = report
      showPreviewModal.value = true
      activeTab.value = 'summary'
    }
    
    const downloadReport = async (report) => {
  try {
    if (report.files && report.files.length > 0) {
      for (const fileInfo of report.files) {
        if (fileInfo.filename) {
          const response = await adminAPI.downloadReportFile(fileInfo.filename);

          const blob = new Blob([response.data], { type: fileInfo.mimeType || 'application/octet-stream' });
          const url = URL.createObjectURL(blob);

          const link = document.createElement('a');
          link.href = url;
          link.download = fileInfo.filename;
          document.body.appendChild(link);
          link.click();
          document.body.removeChild(link);
          URL.revokeObjectURL(url);
        }
      }
    } else {
      const content = JSON.stringify(report.reportContent, null, 2);
      const blob = new Blob([content], { type: 'application/json' });
      const link = document.createElement('a');
      link.href = URL.createObjectURL(blob);
      link.download = `${report.type}_report_${report.id}.json`;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    }
  } catch (error) {
    console.error('Failed to download report:', error);
    alert('Failed to download report. Please try again.');
  }
};
    
    const downloadCurrentReport = () => {
      if (reportData.value) {
        downloadReport(reportData.value)
      }
    }
    
    const closePreview = () => {
      showPreviewModal.value = false
      reportData.value = null
    }
    
    const clearHistory = async () => {
  const result = await Swal.fire({
    title: 'Are you sure?',
    text: 'This will clear all report history (soft delete).',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Yes, clear it!',
    cancelButtonText: 'Cancel',
  });

  if (result.isConfirmed) {
    try {
      await adminAPI.clearReportHistory();
      recentReports.value = [];
      await Swal.fire({
        title: '✅ Cleared!',
        text: 'Report history has been cleared successfully.',
        icon: 'success',
        confirmButtonText: 'OK'
      });
    } catch (error) {
      console.error('Failed to clear report history:', error);
      await Swal.fire({
        title: '❌ Error',
        text: 'Failed to clear report history. Please try again.',
        icon: 'error',
        confirmButtonText: 'OK'
      });
    }
  }
};

    
    const refreshStats = async () => {
      try {
        refreshing.value = true
        await loadQuickStats()
        lastUpdated.value = new Date().toLocaleString()
        alert('Statistics refreshed successfully!')
      } catch (error) {
        console.error('Failed to refresh stats:', error)
        alert('Failed to refresh statistics. Please try again.')
      } finally {
        refreshing.value = false
      }
    }
    
    const resetForm = () => {
      reportForm.value = {
        type: '',
        period: '',
        start_date: '',
        end_date: '',
        export_formats: [''],
        include_charts: true,
        include_details: false,
        include_summary: true,
        include_comparison: false
      }
    }
    
    const getReportTitle = (type) => {
      const titles = {
        total_revenue: 'Total Revenue',
        all_violators: 'All Violators',
        common_violations: 'Common Violations',
        enforcer_performance: 'Enforcer Performance'
      }
      return titles[type] || 'System Report'
    }
    
    const getReportTypeLabel = (type) => {
      const labels = {
        total_revenue: 'Total Revenue',
        all_violators: 'All Violators',
        common_violations: 'Common Violations',
        enforcer_performance: 'Enforcer Performance'
      }
      return labels[type] || type
    }
    
    const getReportTypeClass = (type) => {
  const classes = {
    total_revenue: 'badge-success',          
    all_violators: 'badge-danger',          
    common_violations: 'badge-warning',      
    enforcer_performance: 'badge-info'      
  }
  return classes[type] || 'badge-secondary'
}
    const loadReportHistory = async () => {
  try {
    const response = await adminAPI.getReportHistory()
    recentReports.value = response.data?.data || []
  } catch (error) {
    console.error('Failed to load report history:', error)
  }
}
    const formatNumber = (num) => {
      return new Intl.NumberFormat('en-PH').format(num)
    }
    
    const formatCurrency = (amount) => {
      return new Intl.NumberFormat('en-PH').format(amount)
    }
    
    const formatDate = (dateString) => {
      if (!dateString) return ''
      return new Date(dateString).toLocaleDateString('en-PH', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
    }
    const formatDateTime = (dateString) => {
      if (!dateString) return ''
      return new Date(dateString).toLocaleString('en-PH', {
        year: 'numeric', month: 'long', day: 'numeric',
        hour: '2-digit', minute: '2-digit'
      })
    }
    const getPeriodLabel = (period) => {
      const map = {
        today: 'Today',
        yesterday: 'Yesterday',
        last_7_days: 'Last 7 Days',
        last_30_days: 'Last 30 Days',
        last_3_months: 'Last 3 Months',
        last_6_months: 'Last 6 Months',
        last_year: 'Last Year',
        year_to_date: 'Year to Date',
        custom: 'Custom Range'
      }
      return map[period] || period
    }
    
    const formatLabel = (key) => {
      return key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
    }
    
    const formatValue = (value, key) => {
      if (key.includes('amount') || key.includes('revenue') || key.includes('collected') || key.includes('fines')) {
        return `₱${formatCurrency(value)}`
      }
      if (key.includes('rate') || key.includes('percentage')) {
        return `${value}%`
      }
      if (typeof value === 'number' && value > 1000) {
        return formatNumber(value)
      }
      return value
    }
    
    const loadQuickStats = async () => {
      try {
        const response = await adminAPI.getQuickStats()
        const stats = response.data?.data || {}
        quickStats.value = {
          total_violations: Number(stats.total_violations) || 0,
          total_revenue: Number(stats.total_revenue) || 0,
          pending_amount: Number(stats.pending_amount) || 0,
          pending_count: Number(stats.pending_count) || 0,
          collection_rate: Number(stats.collection_rate) || 0,
          violations_change: Number(stats.violations_change) || 0,
          revenue_change: Number(stats.revenue_change) || 0
        }
        lastUpdated.value = new Date().toLocaleString()
      } catch (error) {
        console.error('Failed to load quick stats:', error)
        quickStats.value = {
          total_violations: 0,
          total_revenue: 0,
          pending_amount: 0,
          pending_count: 0,
          collection_rate: 0,
          violations_change: 0,
          revenue_change: 0
        }
      }
    }
    const getFileLabel = (filename) => {
  const ext = filename.split('.').pop().toLowerCase()
  switch (ext) {
    case 'pdf': return 'PDF'
    case 'xlsx': return 'EXCEL'
    case 'xls': return 'EXCEL'
    case 'docx': return 'WORD'
    case 'doc': return 'WORD'
    default: return ext.toUpperCase()
  }
    }

    const getFileClass = (filename) => {
  const ext = filename.split('.').pop().toLowerCase()
  switch (ext) {
    case 'pdf': return 'pdf'
    case 'xlsx':
    case 'xls': return 'xlsx'
    case 'docx':
    case 'doc': return 'docx'
    default: return ''
  }
}
    onMounted(() => {
      loadQuickStats()
      loadReportHistory()
    })
    
    return {
      generating,
      refreshing,
      quickStats,
      recentReports,
      showPreviewModal,
      reportData,
      activeTab,
      historyFilter,
      searchQuery,
      lastUpdated,
      reportForm,
      canGenerate,
      filteredReports,
      generateReport,
      previewReport,
      viewReport,
      downloadReport,
      downloadCurrentReport,
      closePreview,
      clearHistory,
      refreshStats,
      getReportTitle,
      getReportTypeLabel,
      getReportTypeClass,
      formatNumber,
      formatCurrency,
      formatDate,
      formatLabel,
      formatValue,
      formatDateTime,
      getPeriodLabel,
      generateSummaryFromData,
      loadReportHistory,
      getFileLabel,
      getFileClass,
    }
  }
}
</script>

<style scoped>
/* Header Styles */
.admin-reports {
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

/* Card Styles */
.report-card, .stats-dashboard, .report-history {
  background: white;
  border-radius: 16px;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
  margin-bottom: 32px;
  overflow: hidden;
  border: 1px solid rgba(229, 231, 235, 0.8);
}

.card-header {
  padding: 24px;
  border-bottom: 1px solid #f3f4f6;
  background: linear-gradient(135deg, #fafbff 0%, #f8fafc 100%);
}

.card-header h3 {
  font-size: 20px;
  font-weight: 700;
  color: #111827;
  margin: 0 0 4px 0;
}

.card-subtitle {
  color: #6b7280;
  font-size: 14px;
}

.card-body {
  padding: 32px;
}

/* Form Styles */
.form-section {
  margin-bottom: 32px;
  padding-bottom: 24px;
  border-bottom: 1px solid #f3f4f6;
}

.form-section:last-child {
  border-bottom: none;
}

.form-section h4 {
  font-size: 16px;
  font-weight: 600;
  color: #374151;
  margin: 0 0 20px 0;
  display: flex;
  align-items: center;
  gap: 8px;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 24px;
  margin-bottom: 20px;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-label {
  font-weight: 500;
  color: #374151;
  margin-bottom: 8px;
  font-size: 14px;
}

.required {
  color: #ef4444;
}

.form-select, .form-input {
  padding: 12px 16px;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  font-size: 14px;
  transition: all 0.2s;
  background: white;
}

.form-select:focus, .form-input:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-select-sm, .form-input-sm {
  padding: 8px 12px;
  font-size: 14px;
}

/* Export Options */
.export-options {
  margin-top: 16px;
}

.export-group {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 12px;
}

.export-item {
  position: relative;
}

.export-item input[type="checkbox"] {
  position: absolute;
  opacity: 0;
}

.export-label {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 14px;
  border: 1.5px solid #e5e7eb;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
  background: #fafbfc;
}

.export-label:hover {
  background: #f1f5f9;
  border-color: #cbd5e1;
}

/* PDF Export - Red/Orange */
.export-item:nth-child(1) input:checked + .export-label {
  background: #fed7d7;
  border-color: #f87171; 
}

.export-item:nth-child(1) input:checked + .export-label .export-icon {
  color: #dc2626; 
}

.export-item:nth-child(1) input:checked + .export-label .export-title {
  color: #b91c1c; 
}

/* Excel Export - Green */
.export-item:nth-child(2) input:checked + .export-label {
  background: #d1fae5;
  border-color: #10b981;
}

.export-item:nth-child(2) input:checked + .export-label .export-icon {
  color: #059669;
}

.export-item:nth-child(2) input:checked + .export-label .export-title {
  color: #047857;
}

/* Word Export - Blue */
.export-item:nth-child(3) input:checked + .export-label {
  background: #dbeafe; 
  border-color: #3b82f6; 
}

.export-item:nth-child(3) input:checked + .export-label .export-icon {
  color: #2563eb;
}

.export-item:nth-child(3) input:checked + .export-label .export-title {
  color: #1e40af; 
}

.export-icon {
  width: 20px;
  height: 20px;
  color: #6b7280;
  transition: color 0.2s ease;
}

.export-info {
  flex: 1;
}

.export-title {
  font-weight: 500;
  color: #374151;
  font-size: 14px;
  transition: color 0.2s ease;
  line-height: 1.2;
}

.export-desc {
  font-size: 12px;
  color: #6b7280;
  margin-top: 2px;
  line-height: 1.3;
}

/* Consistent styling for both export options and report options */
.options-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 12px;
}

.option-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 14px 16px;
  border: 1.5px solid #e5e7eb;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
  background: #fafbfc;
}

.option-item:hover {
  border-color: #cbd5e1;
  background: #f1f5f9;
}

.option-item input:checked {
  accent-color: #3b82f6;
}

/* Light color variants for checked options */
.option-item:has(input:checked) {
  background: #eff6ff;
  border-color: #3b82f6;
}

.option-item:has(input:checked) .option-icon {
  color: #2563eb;
}

.option-item:has(input:checked) .option-title {
  color: #1e40af;
}

.option-content {
  display: flex;
  align-items: center;
  gap: 12px;
}

.option-icon {
  width: 18px;
  height: 18px;
  color: #6b7280;
  transition: color 0.2s ease;
}

.option-title {
  font-weight: 500;
  color: #374151;
  font-size: 14px;
  transition: color 0.2s ease;
  line-height: 1.2;
}

.option-desc {
  font-size: 12px;
  color: #6b7280;
  margin-top: 2px;
  line-height: 1.3;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .export-group {
    grid-template-columns: 1fr;
  }
  
  .options-grid {
    grid-template-columns: 1fr;
  }
  
  .export-label, .option-item {
    padding: 12px 14px;
  }
}

/* Add some subtle shadows for depth */
.export-label, .option-item {
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
}

.export-item input:checked + .export-label,
.option-item:has(input:checked) {
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
}

/* Form Actions */
.form-actions {
  display: flex;
  gap: 16px;
  justify-content: flex-end;
  padding-top: 24px;
  border-top: 1px solid #f3f4f6;
}

/* Button Styles */
.btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 12px 24px;
  border-radius: 8px;
  font-weight: 500;
  font-size: 14px;
  border: none;
  cursor: pointer;
  transition: all 0.2s;
  text-decoration: none;
}

.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-primary {
  background: linear-gradient(135deg, #3b82f6, #2563eb);
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: linear-gradient(135deg, #2563eb, #1d4ed8);
  transform: translateY(-1px);
  box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.3);
}

.btn-secondary {
  background: #f8fafc;
  color: #475569;
  border: 1px solid #e2e8f0;
}

.btn-secondary:hover:not(:disabled) {
  background: #f1f5f9;
  border-color: #cbd5e1;
}

.btn-ghost {
  background: transparent;
  color: #6b7280;
}

.btn-ghost:hover {
  background: #f9fafb;
  color: #374151;
}

.btn-sm {
  padding: 8px 16px;
  font-size: 12px;
}

/* Stats Dashboard */
.dashboard-statistics-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 24px;
  border-bottom: 1px solid #eef2f7;
  background: #ffffff;
}

.dashboard-statistics-header h3 {
  font-size: 20px;
  font-weight: 700;
  color: #111827;
  margin: 0;
}

.last-updated {
  font-size: 12px;
  color: #6b7280;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 16px;
  padding: 20px;
}

.stat-card {
  padding: 20px;
  border-radius: 12px;
  background: #ffffff;
  border: 1px solid #e5e7eb;
  transition: background 0.2s ease;
}

.stat-card.primary { border-left: 4px solid #bfdbfe; }
.stat-card.success { border-left: 4px solid #bbf7d0; }
.stat-card.warning { border-left: 4px solid #fde68a; }
.stat-card.info { border-left: 4px solid #bae6fd; }

.stat-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.stat-title {
  font-weight: 500;
  color: #6b7280;
  font-size: 13px;
}

.stat-icon {
  width: 20px;
  height: 20px;
  opacity: 0.7;
}

.stat-value {
  font-size: 24px;
  font-weight: 700;
  color: #0f172a;
  line-height: 1;
  margin-bottom: 6px;
}

.stat-change {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 12px;
  font-weight: 500;
}

.stat-change.positive { color: #059669; }
.stat-change.negative { color: #dc2626; }
.stat-change.neutral { color: #6b7280; }

.stat-subtitle {
  font-size: 12px;
  color: #94a3b8;
  margin-top: 4px;
}

.progress-bar {
  width: 100%;
  height: 6px;
  background: #eef2f7;
  border-radius: 3px;
  overflow: hidden;
  margin-top: 8px;
}

.progress-fill {
  height: 100%;
  background: #93c5fd;
  transition: width 0.3s ease;
}

/* Report History */
.history-filters {
  padding: 16px 24px;
  border-bottom: 1px solid #f3f4f6;
  background: #fafbfc;
}

.filter-group {
  display: flex;
  gap: 16px;
  align-items: center;
}

.reports-list {
  padding: 24px;
}

.report-grid {
  display: grid;
  gap: 16px;
}

.report-card-item {
  padding: 20px;
  border: 2px solid #f3f4f6;
  border-radius: 12px;
  transition: all 0.2s;
  background: white;
}

.report-card-item:hover {
  border-color: #e5e7eb;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.report-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.report-type-badge {
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.badge-primary { background: #dbeafe; color: #1d4ed8; }
.badge-success { background: #d1fae5; color: #059669; }
.badge-danger { background: #fee2e2; color: #dc2626; }
.badge-warning { background: #fef3c7; color: #d97706; }
.badge-info { background: #e0f2fe; color: #0284c7; }
.badge-secondary { background: #f1f5f9; color: #475569; }
.badge-purple { background: #f3e8ff; color: #7c3aed; }
.badge-orange { background: #fed7aa; color: #ea580c; }

.report-date {
  font-size: 12px;
  color: #6b7280;
}

.report-title {
  font-size: 16px;
  font-weight: 600;
  color: #111827;
  margin: 0 0 8px 0;
}

.report-meta {
  display: flex;
  gap: 16px;
  margin-bottom: 16px;
}

.meta-item {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 12px;
  color: #6b7280;
}
.meta-item svg {
  width: 36px;
  height: 26px;
  display: inline-block;
  vertical-align: middle;
}

.report-actions {
  display: flex;
  justify-content: flex-end;
}

.action-group {
  display: flex;
  gap: 8px;
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 60px 20px;
}

.empty-icon {
  width: 64px;
  height: 64px;
  color: #d1d5db;
  margin: 0 auto 16px;
}

.empty-state h4 {
  font-size: 18px;
  font-weight: 600;
  color: #374151;
  margin: 0 0 8px 0;
}

.empty-state p {
  color: #6b7280;
  margin: 0;
}

.file-badge {
  display: inline-block;
  background-color: #f3f3f3;
  color: #333;
  font-size: 0.7rem;
  font-weight: 600;
  padding: 2px 6px;
  border-radius: 4px;
  margin-right: 4px;
}
.file-badge::after {
  content: '';
  margin-right: 4px;
}
.file-badge.pdf { background-color: #e53e3e; color: white; }
.file-badge.xlsx { background-color: #38a169; color: white; }
.file-badge.docx { background-color: #3182ce; color: white; }

/* Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 20px;
  backdrop-filter: blur(4px);
}

.modal {
  background: white;
  border-radius: 16px;
  width: 100%;
  max-width: 600px;
  max-height: 90vh;
  overflow: hidden;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}

.modal-xl {
  max-width: 1000px;
}

.modal-header {
  padding: 24px;
  border-bottom: 1px solid #f3f4f6;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #fafbfc;
}

.modal-title h3 {
  font-size: 20px;
  font-weight: 700;
  color: #111827;
  margin: 0;
}

.modal-subtitle {
  font-size: 14px;
  color: #6b7280;
  margin-top: 4px;
}

.modal-close {
  background: none;
  border: none;
  font-size: 20px;
  cursor: pointer;
  color: #6b7280;
  padding: 8px;
  border-radius: 8px;
  transition: all 0.2s;
}

.modal-close:hover {
  background: #f3f4f6;
  color: #374151;
}

.modal-body {
  padding: 24px;
  max-height: 60vh;
  overflow-y: auto;
}

.modal-footer {
  padding: 24px;
  border-top: 1px solid #f3f4f6;
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  background: #fafbfc;
}

/* Preview Content */
.preview-header {
  margin-bottom: 24px;
  padding-bottom: 16px;
  border-bottom: 2px solid #f3f4f6;
}

.preview-info {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
}

.info-item {
  font-size: 14px;
  color: #374151;
}

.content-tabs {
  display: flex;
  border-bottom: 2px solid #f3f4f6;
  margin-bottom: 24px;
}

.tab-btn {
  padding: 12px 24px;
  border: none;
  background: none;
  cursor: pointer;
  font-weight: 500;
  color: #6b7280;
  border-bottom: 2px solid transparent;
  transition: all 0.2s;
}

.tab-btn.active {
  color: #3b82f6;
  border-bottom-color: #3b82f6;
}

.tab-content {
  min-height: 300px;
}

.summary-stats {
  display: grid;
  gap: 12px;
}

.summary-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 16px;
  background: #f8fafc;
  border-radius: 8px;
}

.summary-label {
  font-weight: 500;
  color: #374151;
}

.summary-value {
  font-weight: 600;
  color: #111827;
}

.data-table {
  background: #f8fafc;
  border-radius: 8px;
  padding: 16px;
  font-family: 'Monaco', 'Menlo', monospace;
  font-size: 12px;
  overflow-x: auto;
}

/* Animations */
.spinner {
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top: 2px solid white;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Dropdown */
.dropdown {
  position: relative;
  display: inline-block;
}

.dropdown-toggle {
  display: flex;
  align-items: center;
  gap: 8px;
}

.dropdown-menu {
  position: absolute;
  top: 100%;
  right: 0;
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
  z-index: 10;
  min-width: 120px;
}

.dropdown-item {
  display: block;
  width: 100%;
  padding: 8px 12px;
  border: none;
  background: none;
  text-align: left;
  font-size: 14px;
  color: #374151;
  cursor: pointer;
  transition: background 0.2s;
}

.dropdown-item:hover {
  background: #f3f4f6;
}

.dropdown-item:first-child {
  border-radius: 8px 8px 0 0;
}

.dropdown-item:last-child {
  border-radius: 0 0 8px 8px;
}

/* Responsive Design */
@media (max-width: 1024px) {
  .form-row {
    grid-template-columns: 1fr;
  }
  
  .stats-grid {
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  }
  
  .export-group {
    grid-template-columns: 1fr;
  }
  
  .options-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .admin-reports {
    padding: 0 12px;
  }
  
  .page-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 16px;
  }
  
  .page-header h2 {
    font-size: 24px;
  }
  
  .card-body {
    padding: 20px;
  }
  
  .form-actions {
    flex-direction: column-reverse;
  }
  
  .btn {
    justify-content: center;
  }
  
  .stats-grid {
    grid-template-columns: 1fr;
  }
  
  .report-card-item {
    padding: 16px;
  }
  
  .report-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
  }
  
  .report-actions {
    justify-content: flex-start;
    margin-top: 16px;
  }
  
  .filter-group {
    flex-direction: column;
    gap: 12px;
  }
  
  .modal {
    margin: 10px;
    max-width: calc(100vw - 20px);
  }
  
  .modal-header {
    padding: 16px;
  }
  
  .modal-body {
    padding: 16px;
  }
  
  .modal-footer {
    padding: 16px;
    flex-direction: column-reverse;
  }
  
  .preview-info {
    grid-template-columns: 1fr;
  }
  
  .content-tabs {
    overflow-x: auto;
  }
  
  .tab-btn {
    white-space: nowrap;
    min-width: 100px;
  }
}

@media (max-width: 480px) {
  .page-header h2 {
    font-size: 20px;
  }
  
  .card-header {
    padding: 16px;
  }
  
  .card-body {
    padding: 16px;
  }
  
  .dashboard-statistics-header {
    padding: 16px;
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
  }
  
  .stats-grid {
    padding: 16px;
    gap: 16px;
  }
  
  .stat-card {
    padding: 16px;
  }
  
  .stat-value {
    font-size: 24px;
  }
  
  .reports-list {
    padding: 16px;
  }
  
  .action-group {
    flex-direction: column;
    width: 100%;
  }
  
  .btn-sm {
    padding: 10px 16px;
    justify-content: center;
  }
}

/* Print Styles */
@media print {
  .admin-reports {
    max-width: none;
    margin: 0;
    padding: 0;
  }
  
  .page-header,
  .form-actions,
  .report-actions,
  .header-actions,
  .modal-overlay {
    display: none !important;
  }
  
  .report-card,
  .stats-dashboard,
  .report-history {
    box-shadow: none;
    border: 1px solid #e5e7eb;
    break-inside: avoid;
  }
  
  .card-body {
    padding: 20px;
  }
  
  .stats-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
  }
  
  .stat-card {
    border: 1px solid #e5e7eb;
    padding: 16px;
  }
}

/* Dark Mode Support */
@media (prefers-color-scheme: dark) {
  .admin-reports {
    background: #0f172a;
    color: #e2e8f0;
  }
  
  .report-card,
  .stats-dashboard,
  .report-history {
    background: #1e293b;
    border-color: #334155;
  }
  
  .card-header {
    background: #1e293b;
    border-color: #334155;
  }
  
  .page-header h2 {
    color: #f1f5f9;
  }
  
  .page-header p {
    color: #94a3b8;
  }
  
  .form-select,
  .form-input {
    background: #334155;
    border-color: #475569;
    color: #e2e8f0;
  }
  
  .form-select:focus,
  .form-input:focus {
    border-color: #3b82f6;
    background: #475569;
  }
  
  .btn-secondary {
    background: #334155;
    color: #e2e8f0;
    border-color: #475569;
  }
  
  .btn-secondary:hover {
    background: #475569;
  }
  
  .stat-card {
    background: #334155;
  }
  
  .modal {
    background: #1e293b;
  }
  
  .modal-header,
  .modal-footer {
    background: #334155;
    border-color: #475569;
  }
}

/* Focus Styles for Accessibility */
.btn:focus-visible,
.form-select:focus-visible,
.form-input:focus-visible {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
}

/* High Contrast Mode */
@media (prefers-contrast: high) {
  .report-card,
  .stats-dashboard,
  .report-history {
    border: 2px solid #000;
  }
  
  .btn-primary {
    background: #000;
    border: 2px solid #000;
  }
  
  .btn-secondary {
    background: #fff;
    border: 2px solid #000;
    color: #000;
  }
  
  .form-select,
  .form-input {
    border: 2px solid #000;
  }
}

/* Reduced Motion */
@media (prefers-reduced-motion: reduce) {
  * {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
  
  .animate-spin {
    animation: none;
  }
}

/* Loading States */
.loading {
  position: relative;
  pointer-events: none;
}

.loading::after {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(255, 255, 255, 0.8);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1;
}

/* Tooltips */
.tooltip {
  position: relative;
}

.tooltip::before {
  content: attr(data-tooltip);
  position: absolute;
  bottom: 125%;
  left: 50%;
  transform: translateX(-50%);
  background: #1f2937;
  color: white;
  padding: 8px 12px;
  border-radius: 6px;
  font-size: 12px;
  white-space: nowrap;
  opacity: 0;
  visibility: hidden;
  transition: all 0.2s;
  z-index: 100;
}

.tooltip:hover::before {
  opacity: 1;
  visibility: visible;
}

/* Custom Scrollbars */
.modal-body::-webkit-scrollbar,
.data-table::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}

.modal-body::-webkit-scrollbar-track,
.data-table::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 3px;
}

.modal-body::-webkit-scrollbar-thumb,
.data-table::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 3px;
}

.modal-body::-webkit-scrollbar-thumb:hover,
.data-table::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

</style>