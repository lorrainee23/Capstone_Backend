<template>
  <ion-page>
   <AppHeader title="Violator History" />
    <ion-content class="main-content">
      <!-- Filter Section -->
      <div class="filter-section">
        <ion-card class="filter-card">
          <ion-card-content class="filter-content">
            <ion-segment v-model="activeTab" @ionChange="handleSegmentChange" class="custom-segment">
              <ion-segment-button value="violators" class="segment-btn">
                <ion-label>Violators</ion-label>
              </ion-segment-button>
              <ion-segment-button value="all-violators" class="segment-btn">
                <ion-label>All Violators</ion-label>
              </ion-segment-button>
            </ion-segment>

            <!-- Search and Filters -->
            <div class="filters-wrapper">
              <ion-item class="search-item" fill="outline">
                <ion-icon :icon="searchOutline" slot="start" class="search-icon"></ion-icon>
                <ion-input
                  v-model="searchTerm"
                  placeholder="Search by violator name or license plate"
                  @ionInput="handleSearch"
                ></ion-input>
              </ion-item>

              <div class="filter-grid">
                <ion-item class="filter-item" fill="outline">
                  <ion-select
                    v-model="selectedDateRange"
                    placeholder="Date Range"
                    interface="popover"
                    @ionChange="applyFilters"
                  >
                    <ion-select-option value="today">Today</ion-select-option>
                    <ion-select-option value="week">This Week</ion-select-option>
                    <ion-select-option value="month">This Month</ion-select-option>
                    <ion-select-option value="all">All Time</ion-select-option>
                  </ion-select>
                </ion-item>

                <ion-item class="filter-item" fill="outline">
                  <ion-select
                    v-model="selectedViolationType"
                    placeholder="Violation Type"
                    interface="popover"
                    @ionChange="applyFilters"
                  >
                    <ion-select-option value="">All Types</ion-select-option>
                    <ion-select-option
                      v-for="type in violationTypes"
                      :key="type.id"
                      :value="type.name"
                    >
                      {{ type.name }}
                    </ion-select-option>
                  </ion-select>
                </ion-item>

                <ion-item class="filter-item" fill="outline">
                  <ion-select
                    v-model="selectedStatus"
                    placeholder="Status"
                    interface="popover"
                    @ionChange="applyFilters"
                  >
                    <ion-select-option value="">All Status</ion-select-option>
                    <ion-select-option value="Paid">Paid</ion-select-option>
                    <ion-select-option value="Pending">Pending</ion-select-option>
                    <ion-select-option value="Overdue">Overdue</ion-select-option>
                  </ion-select>
                </ion-item>
              </div>
            </div>
          </ion-card-content>
        </ion-card>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="loading-container">
        <ion-spinner name="crescent" class="loading-spinner"></ion-spinner>
        <p class="loading-text">Loading data...</p>
      </div>

      <!-- Violators List -->
      <div v-else class="content-section">
        <!-- Summary Cards -->
        <div class="summary-grid">
          <ion-card class="summary-card primary">
            <ion-card-content class="summary-content">
              <div class="summary-value">{{ violatorsSummary.total }}</div>
              <div class="summary-label">Total Violations</div>
            </ion-card-content>
          </ion-card>
          <ion-card class="summary-card success">
            <ion-card-content class="summary-content">
              <div class="summary-value">₱{{ violatorsSummary.totalFines }}</div>
              <div class="summary-label">Total Fines</div>
            </ion-card-content>
          </ion-card>
          <ion-card class="summary-card warning">
            <ion-card-content class="summary-content">
              <div class="summary-value">{{ violatorsSummary.pending }}</div>
              <div class="summary-label">Pending</div>
            </ion-card-content>
          </ion-card>
          <ion-card class="summary-card danger">
            <ion-card-content class="summary-content">
              <div class="summary-value">{{ violatorsSummary.paid }}</div>
              <div class="summary-label">Paid</div>
            </ion-card-content>
          </ion-card>
        </div>

        <!-- Violations List -->
        <ion-card v-if="filteredViolators.length > 0" class="list-card">
          <ion-list class="violations-list">
            <ion-item
              v-for="violation in filteredViolators"
              :key="violation.id"
              button
              class="violation-item"
              @click="openViolationDetail(violation)"
            >
              <div class="item-content">
                <div class="item-header">
                  <div class="violator-info">
                    <h3 class="violator-name">{{ getViolatorFullName(violation.violator) }}</h3>
                    <p class="plate-number">
                      <ion-icon :icon="carOutline" class="info-icon"></ion-icon>
                      {{ violation.vehicle?.plate_number || 'No Vehicle Info' }}
                    </p>
                    <p class="violation-type">
                      <ion-icon :icon="warningOutline" class="info-icon"></ion-icon>
                      {{ violation.violation?.name || 'Processing' }}
                    </p>
                    <!-- Show apprehending officer only in All Violators tab -->
                    <p v-if="activeTab === 'all-violators' && violation.officer" class="officer-info">
                      <ion-icon :icon="shieldCheckmarkOutline" class="info-icon"></ion-icon>
                      Officer: {{ getOfficerName(violation.officer) }}
                    </p>
                    <ion-badge 
                      v-if="violation.status"
                      :color="getViolationStatusColor(violation.status)"
                      class="status-badge"
                    >
                      {{ violation.status.toUpperCase() }}
                    </ion-badge>
                  </div>
                  <div class="amount-info">
                    <div class="amount">₱{{ violation.fine_amount || '0.00' }}</div>
                    <div v-if="violation.date_time" class="date">{{ formatDate(violation.date_time) }}</div>
                    <div v-if="violation.date_time" class="time">{{ formatTime(violation.date_time) }}</div>
                    <div v-if="violation.ticket_number" class="ticket">
                      #{{ violation.ticket_number }}
                    </div>
                  </div>
                </div>
                <div v-if="violation.location && violation.location !== 'N/A'" class="location">
                  <ion-icon :icon="locationOutline" class="location-icon"></ion-icon>
                  {{ violation.location }}
                </div>
              </div>
              <ion-icon :icon="chevronForwardOutline" slot="end" class="chevron"></ion-icon>
            </ion-item>
          </ion-list>
        </ion-card>

        <!-- No Violations -->
        <div v-else class="empty-state">
          <ion-card class="empty-card">
            <ion-card-content class="empty-content">
              <ion-icon :icon="documentTextOutline" class="empty-icon"></ion-icon>
              <h3 class="empty-title">No violations found</h3>
              <p class="empty-text">
                {{ activeTab === 'violators' ? 'You haven\'t apprehended any violators yet' : 'No violations found matching your filters' }}
              </p>
            </ion-card-content>
          </ion-card>
        </div>
      </div>

      <!-- Detail Modal -->
      <ion-modal :is-open="violationDetailModal" @didDismiss="violationDetailModal = false">
        <ion-header>
          <ion-toolbar>
            <ion-title>Violation Details</ion-title>
            <ion-button slot="end" fill="clear" @click="violationDetailModal = false">
              <ion-icon :icon="closeOutline"></ion-icon>
            </ion-button>
          </ion-toolbar>
        </ion-header>
        <ion-content class="ion-padding" v-if="selectedViolation">
          <div class="detail-section">
            <h3>Violator Information</h3>
            <ion-item>
              <ion-label>
                <h4>Name</h4>
                <p>{{ getViolatorFullName(selectedViolation.violator) }}</p>
              </ion-label>
            </ion-item>
            <ion-item>
              <ion-label>
                <h4>License Number</h4>
                <p>{{ selectedViolation.violator?.license_number || 'N/A' }}</p>
              </ion-label>
            </ion-item>
            <ion-item>
              <ion-label>
                <h4>Mobile Number</h4>
                <p>{{ selectedViolation.violator?.mobile_number || 'N/A' }}</p>
              </ion-label>
            </ion-item>
            <ion-item>
              <ion-label>
                <h4>Address</h4>
                <p>{{ getViolatorAddress(selectedViolation.violator) }}</p>
              </ion-label>
            </ion-item>
          </div>

          <div class="detail-section">
            <h3>Vehicle Information</h3>
            <ion-item>
              <ion-label>
                <h4>Plate Number</h4>
                <p>{{ selectedViolation.vehicle?.plate_number || 'N/A' }}</p>
              </ion-label>
            </ion-item>
            <ion-item>
              <ion-label>
                <h4>Make & Model</h4>
                <p>{{ selectedViolation.vehicle?.make || 'N/A' }} {{ selectedViolation.vehicle?.model || '' }}</p>
              </ion-label>
            </ion-item>
            <ion-item>
              <ion-label>
                <h4>Color</h4>
                <p>{{ selectedViolation.vehicle?.color || 'N/A' }}</p>
              </ion-label>
            </ion-item>
            <ion-item>
              <ion-label>
                <h4>Vehicle Type</h4>
                <p>{{ selectedViolation.vehicle?.vehicle_type || 'N/A' }}</p>
              </ion-label>
            </ion-item>
          </div>

          <div class="detail-section">
            <h3>Violation Details</h3>
            <ion-item>
              <ion-label>
                <h4>Violation Type</h4>
                <p>{{ selectedViolation.violation?.name || 'N/A' }}</p>
              </ion-label>
            </ion-item>
            <ion-item>
              <ion-label>
                <h4>Description</h4>
                <p>{{ selectedViolation.violation?.description || 'N/A' }}</p>
              </ion-label>
            </ion-item>
            <ion-item>
              <ion-label>
                <h4>Fine Amount</h4>
                <p>₱{{ selectedViolation.fine_amount || '0.00' }}</p>
              </ion-label>
            </ion-item>
            <ion-item>
              <ion-label>
                <h4>Location</h4>
                <p>{{ selectedViolation.location || 'N/A' }}</p>
              </ion-label>
            </ion-item>
            <ion-item>
              <ion-label>
                <h4>Date & Time</h4>
                <p>{{ formatDateTime(selectedViolation.date_time) }}</p>
              </ion-label>
            </ion-item>
            <ion-item>
              <ion-label>
                <h4>Status</h4>
                <p>
                  <ion-badge :color="getViolationStatusColor(selectedViolation.status)">
                    {{ selectedViolation.status?.toUpperCase() || 'N/A' }}
                  </ion-badge>
                </p>
              </ion-label>
            </ion-item>
            <ion-item v-if="activeTab === 'all-violators' && selectedViolation.officer">
              <ion-label>
                <h4>Apprehending Officer</h4>
                <p>{{ getOfficerName(selectedViolation.officer) }}</p>
              </ion-label>
            </ion-item>
          </div>
        </ion-content>
      </ion-modal>

      <!-- Refresh -->
      <ion-refresher slot="fixed" @ionRefresh="handleRefresh" class="custom-refresher">
        <ion-refresher-content
          pulling-icon="chevron-down-circle-outline"
          pulling-text="Pull to refresh"
          refreshing-spinner="circles"
          refreshing-text="Refreshing..."
        ></ion-refresher-content>
      </ion-refresher>

      <!-- Bottom spacing -->
      <div class="bottom-spacer"></div>
    </ion-content>
  </ion-page>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import AppHeader from "@/components/AppHeader.vue"
import {
  IonPage, IonHeader, IonToolbar, IonTitle, IonContent,
  IonSegment, IonSegmentButton, IonLabel, IonCard, IonCardContent,
  IonItem, IonInput, IonSelect, IonSelectOption, IonList,
  IonSpinner, IonIcon, IonBadge, IonRefresher, IonRefresherContent,
  IonModal, IonButton
} from '@ionic/vue';
import { 
  searchOutline, documentTextOutline, carOutline, warningOutline, 
  locationOutline, chevronForwardOutline, shieldCheckmarkOutline,
  closeOutline
} from 'ionicons/icons';
import { enforcerAPI } from '@/services/api';

// Reactive data
const activeTab = ref('violators');
const loading = ref(false);
const searchTerm = ref('');
const selectedDateRange = ref('today');
const selectedViolationType = ref('');
const selectedStatus = ref('');

const transactions = ref([]);
const violationTypes = ref([]);
const currentUserId = ref(null);
const currentTime = ref('');
const currentDate = ref('');

const violationDetailModal = ref(false);
const selectedViolation = ref(null);

let timeInterval = null;

// Update date and time every second
const updateDateTime = () => {
  const now = new Date();
  currentTime.value = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
  currentDate.value = now.toLocaleDateString([], { weekday: 'short', month: 'short', day: 'numeric' });
};

// Computed properties
const filteredViolators = computed(() => {
  let filtered = transactions.value;
  
  // Filter by tab (current user vs all)
  if (activeTab.value === 'violators' && currentUserId.value) {
    filtered = filtered.filter(t => t.apprehending_officer === currentUserId.value);
  }
  
  // Search filter
  if (searchTerm.value) {
    const search = searchTerm.value.toLowerCase();
    filtered = filtered.filter(t => 
      getViolatorFullName(t.violator).toLowerCase().includes(search) ||
      (t.vehicle?.plate_number || '').toLowerCase().includes(search) ||
      (t.violator?.license_number || '').toLowerCase().includes(search)
    );
  }
  
  // Violation type filter
  if (selectedViolationType.value) {
    filtered = filtered.filter(t => t.violation?.name === selectedViolationType.value);
  }
  
  // Status filter
  if (selectedStatus.value) {
    filtered = filtered.filter(t => t.status === selectedStatus.value);
  }
  
  // Date range filter
  if (selectedDateRange.value !== 'all') {
    filtered = filtered.filter(t => isWithinDateRange(t.date_time, selectedDateRange.value));
  }
  
  return filtered;
});

const violatorsSummary = computed(() => {
  const total = filteredViolators.value.length;
  const totalFines = filteredViolators.value.reduce((sum, v) => sum + parseFloat(v.fine_amount || 0), 0);
  const pending = filteredViolators.value.filter(v => (v.status || '').toLowerCase() === 'pending').length;
  const paid = filteredViolators.value.filter(v => (v.status || '').toLowerCase() === 'paid').length;
  
  return { 
    total, 
    totalFines: totalFines.toFixed(2),
    pending,
    paid
  };
});

// Methods
const loadData = async () => {
  loading.value = true;
  try {
    const response = await enforcerAPI.getTransactions();
    const transactionsData = response.data?.data?.data || response.data?.data || [];
    
    // Process transactions and add officer information
    const processedTransactions = await Promise.all(
      transactionsData.map(async (transaction) => {
        let officer = null;
        if (transaction.apprehending_officer && activeTab.value === 'all-violators') {
          try {
            officer = {
              id: transaction.apprehending_officer,
              first_name: "Officer",
              last_name: `#${transaction.apprehending_officer}`,
              badge_number: transaction.apprehending_officer
            };
          } catch (error) {
            console.error('Error fetching officer details:', error);
          }
        }
        
        return {
          ...transaction,
          officer
        };
      })
    );
    
    transactions.value = processedTransactions;
    
    // Extract unique violation types
    const uniqueTypes = transactionsData
      .filter(t => t.violation && t.violation.id && t.violation.name)
      .map(t => t.violation)
      .filter((violation, index, self) => 
        index === self.findIndex(v => v.id === violation.id)
      );
    violationTypes.value = uniqueTypes;
    
    // Get current user ID (you'll need to implement this based on your auth system)
    // currentUserId.value = await getCurrentUserId();
    currentUserId.value = 2; // Placeholder - replace with actual current user ID
    
  } catch (error) {
    console.error('Error loading data:', error);
    transactions.value = [];
    violationTypes.value = [];
  } finally {
    loading.value = false;
  }
};

const handleSegmentChange = (event) => {
  activeTab.value = event.detail.value;
  // Data will be filtered automatically through computed property
};

const searchTimeout = ref(null);

const handleSearch = (event) => {
  searchTerm.value = event.detail.value;
  clearTimeout(searchTimeout.value);
  searchTimeout.value = setTimeout(() => {
    // Filters applied through computed properties
  }, 500);
};

const applyFilters = () => {
  // Filters applied through computed properties
};

const isWithinDateRange = (dateTime, range) => {
  if (!dateTime) return false;
  
  const itemDate = new Date(dateTime);
  const now = new Date();
  
  switch (range) {
    case 'today':
      return itemDate.toDateString() === now.toDateString();
    case 'week': {
      const weekAgo = new Date();
      weekAgo.setDate(now.getDate() - 7);
      return itemDate >= weekAgo;
    }
    case 'month': {
      const monthAgo = new Date();
      monthAgo.setMonth(now.getMonth() - 1);
      return itemDate >= monthAgo;
    }
    default:
      return true;
  }
};

const formatDate = (dateTime) => {
  if (!dateTime) return 'N/A';
  return new Date(dateTime).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};

const formatTime = (dateTime) => {
  if (!dateTime) return 'N/A';
  return new Date(dateTime).toLocaleTimeString('en-US', {
    hour: '2-digit',
    minute: '2-digit'
  });
};

const formatDateTime = (dateTime) => {
  if (!dateTime) return 'N/A';
  return new Date(dateTime).toLocaleString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

const getViolationStatusColor = (status) => {
  switch (status?.toLowerCase()) {
    case 'paid': return 'success';
    case 'pending': return 'warning';
    case 'overdue': return 'danger';
    default: return 'medium';
  }
};

const getViolatorFullName = (violator) => {
  if (!violator) return 'N/A';
  const first = violator.first_name || '';
  const middle = violator.middle_name || '';
  const last = violator.last_name || '';
  return `${first} ${middle} ${last}`.replace(/\s+/g, ' ').trim() || 'N/A';
};

const getViolatorAddress = (violator) => {
  if (!violator) return 'N/A';
  const parts = [violator.barangay, violator.city, violator.province].filter(Boolean);
  return parts.join(', ') || 'N/A';
};

const getOfficerName = (officer) => {
  if (!officer) return 'N/A';
  const first = officer.first_name || '';
  const last = officer.last_name || '';
  return `${first} ${last}`.trim() || `Officer #${officer.badge_number || officer.id}`;
};

const openViolationDetail = (violation) => {
  selectedViolation.value = violation;
  violationDetailModal.value = true;
};

const handleRefresh = async (event) => {
  await loadData();
  event.target.complete();
};

onMounted(() => {
  updateDateTime();
  timeInterval = setInterval(updateDateTime, 1000);
  loadData();
});

onUnmounted(() => {
  if (timeInterval) {
    clearInterval(timeInterval);
  }
});
</script>

<style scoped> 
/* Content Styles */
.custom-content {
  --background: linear-gradient(135deg, #f8fafc, #e2e8f0);
}
/* Filter Section */
.filter-section {
  padding: 16px;
  background: linear-gradient(180deg, rgba(59, 130, 246, 0.05) 0%, rgba(248, 250, 252, 1) 100%);
}

.filter-card {
  border-radius: 20px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  border: 1px solid #e2e8f0;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  margin: 0;
}

.filter-content {
  padding: 20px;
}

.custom-segment {
  --background: #f1f5f9;
  border-radius: 12px;
  margin-bottom: 20px;
}

.segment-btn {
  --background-checked: #3b82f6;
  --color-checked: white;
  --color: #64748b;
  --border-radius: 8px;
  font-weight: 600;
}

.filters-wrapper {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.search-item {
  --border-radius: 16px;
  --border-width: 2px;
  --border-color: #e2e8f0;
  --highlight-color-focused: #3b82f6;
  background: white;
}

.search-icon {
  color: #94a3b8;
}

.filter-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 12px;
}

.filter-item {
  --border-radius: 12px;
  --border-width: 2px;
  --border-color: #e2e8f0;
  --highlight-color-focused: #3b82f6;
  background: white;
}

/* Loading */
.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 20px;
}

.loading-spinner {
  --color: #3b82f6;
  width: 40px;
  height: 40px;
  margin-bottom: 16px;
}

.loading-text {
  color: #64748b;
  font-size: 16px;
  margin: 0;
}

/* Content Section */
.content-section {
  padding: 0 16px;
}

/* Summary Cards */
.summary-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 16px;
  margin-bottom: 20px;
}

.summary-card {
  border-radius: 16px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  border: none;
  margin: 0;
}

.summary-card.primary {
  --background: linear-gradient(135deg, #dbeafe, #bfdbfe);
}

.summary-card.success {
  --background: linear-gradient(135deg, #dcfce7, #bbf7d0);
}

.summary-card.warning {
  --background: linear-gradient(135deg, #fef3c7, #fde68a);
}

.summary-card.danger {
  --background: linear-gradient(135deg, #fecaca, #fca5a5);
}

.summary-content {
  padding: 20px;
  text-align: center;
}

.summary-value {
  font-size: 24px;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 4px;
}

.summary-label {
  font-size: 14px;
  color: #64748b;
  font-weight: 500;
}

/* List Cards */
.list-card {
  border-radius: 20px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  border: 1px solid #e2e8f0;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  margin: 0;
}

.violations-list {
  background: transparent;
}

.violation-item {
  --background: transparent;
  --border-color: #f1f5f9;
  --padding-start: 20px;
  --padding-end: 20px;
  --padding-top: 16px;
  --padding-bottom: 16px;
  transition: all 0.2s ease;
}

.violation-item:hover {
  --background: #f8fafc;
}

.item-content {
  width: 100%;
}

.item-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 8px;
}

.violator-info {
  flex: 1;
}

.violator-name {
  font-size: 18px;
  font-weight: 600;
  color: #1e293b;
  margin: 0 0 8px 0;
}

.plate-number,
.violation-type,
.officer-info {
  display: flex;
  align-items: center;
  margin: 4px 0;
  font-size: 14px;
  color: #64748b;
}

.info-icon {
  margin-right: 6px;
  font-size: 14px;
  color: #94a3b8;
}

.status-badge {
  font-size: 12px;
  font-weight: 600;
  margin-top: 8px;
  --padding-start: 8px;
  --padding-end: 8px;
}

.amount-info {
  text-align: right;
  min-width: 120px;
}

.amount {
  font-size: 18px;
  font-weight: 700;
  color: #059669;
  margin-bottom: 4px;
}

.date,
.time,
.ticket {
  font-size: 12px;
  color: #64748b;
  margin: 2px 0;
}

.location {
  display: flex;
  align-items: center;
  margin-top: 8px;
  font-size: 14px;
  color: #64748b;
  padding: 8px 12px;
  background: #f8fafc;
  border-radius: 8px;
}

.location-icon {
  margin-right: 6px;
  font-size: 14px;
  color: #94a3b8;
}

.chevron {
  color: #cbd5e1;
  font-size: 16px;
}

/* Empty State */
.empty-state {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 300px;
}

.empty-card {
  border-radius: 20px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  border: 1px solid #e2e8f0;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  max-width: 300px;
  margin: 0;
}

.empty-content {
  padding: 40px 20px;
  text-align: center;
}

.empty-icon {
  font-size: 64px;
  color: #cbd5e1;
  margin-bottom: 16px;
}

.empty-title {
  font-size: 18px;
  font-weight: 600;
  color: #374151;
  margin: 0 0 8px 0;
}

.empty-text {
  font-size: 14px;
  color: #64748b;
  margin: 0;
}

/* Detail Modal */
.detail-section {
  margin-bottom: 24px;
}

.detail-section h3 {
  color: #1e293b;
  font-size: 18px;
  font-weight: 600;
  margin: 0 0 16px 0;
  padding-bottom: 8px;
  border-bottom: 2px solid #e2e8f0;
}

.detail-section ion-item {
  --background: #f8fafc;
  --border-radius: 12px;
  margin-bottom: 8px;
}

.detail-section ion-label h4 {
  color: #374151;
  font-size: 14px;
  font-weight: 600;
  margin: 0 0 4px 0;
}

.detail-section ion-label p {
  color: #64748b;
  font-size: 14px;
  margin: 0;
}

.custom-refresher {
  --color: #3b82f6;
}

.bottom-spacer {
  height: 40px;
}

/* Responsive Design */
@media (max-width: 768px) {
  .summary-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
  }
  
  .summary-content {
    padding: 16px 12px;
  }
  
  .summary-value {
    font-size: 20px;
  }
  
  .summary-label {
    font-size: 12px;
  }
  
  .filter-grid {
    grid-template-columns: 1fr;
    gap: 12px;
  }
}

@media (max-width: 480px) {
  .filter-section {
    padding: 12px;
  }
  
  .content-section {
    padding: 0 12px;
  }
  
  .summary-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 8px;
  }
  
  .item-header {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .amount-info {
    text-align: left;
    margin-top: 12px;
    min-width: auto;
  }
  
  .violator-name {
    font-size: 16px;
  }
  
  .amount {
    font-size: 16px;
  }
  
  .custom-header {
    --min-height: 70px;
    --padding-top: 25px;
  }
  
}
</style>