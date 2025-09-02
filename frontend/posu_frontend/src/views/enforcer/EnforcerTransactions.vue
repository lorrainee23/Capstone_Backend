<template>
    <SidebarLayout page-title="Transaction Management">
        <div class="enforcer-transactions">
            <!-- Header -->
            <div class="page-header">
                <div class="header-left">
                    <h2>My Transactions</h2>
                    <p>Manage violation transactions and payments</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="filters-card">
                <div class="filters-row">
                    <!-- Violation Type -->
                    <div class="filter-group">
                        <label class="form-label">Violation Type</label>
                        <select
                            v-model="filters.violation_id"
                            class="form-select"
                        >
                            <option value="">All Violations</option>
                            <option
                                v-for="v in violationTypes"
                                :key="v.id"
                                :value="v.id"
                            >
                                {{ v.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Vehicle Type -->
                    <div class="filter-group">
                        <label class="form-label">Vehicle Type</label>
                        <select
                            v-model="filters.vehicle_type"
                            class="form-select"
                        >
                            <option value="">All Vehicles</option>
                            <option value="Motor">Motor</option>
                            <option value="Van">Van</option>
                            <option value="Motorcycle">Motorcycle</option>
                            <option value="Truck">Truck</option>
                            <option value="Bus">Bus</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label class="form-label">Date Range</label>
                        <select v-model="filters.dateRange" class="form-select">
                            <option value="">All Time</option>
                            <option value="today">Today</option>
                            <option value="week">This Week</option>
                            <option value="month">This Month</option>
                        </select>
                    </div>

                    <!-- Repeat Offenders -->
                    <div class="filter-group">
                        <label class="form-label">Repeat Offenders</label>
                        <select v-model="filters.repeat_offender" class="form-select">
                            <option value="">All</option>
                            <option :value="true">Yes</option>
                            <option :value="false">No</option>
                        </select>
                    </div>
                    <!-- Search -->
                    <div class="filter-group">
                        <label class="form-label">Search Name</label>
                        <input
                            v-model="filters.search"
                            type="text"
                            class="form-input"
                            placeholder="Search by violator name or license..."
                        />
                    </div>
                    <div class="filter-group">
                        <label class="form-label">Search Address</label>
                        <input 
  v-model="filters.address" 
  type="text" 
  class="form-input"
  placeholder="Filter by Address"
/>
                    </div>
                    <div class="filter-group">
  <label class="form-label">Date From</label>
  <input type="date" v-model="filters.dateFrom" class="form-input" />
</div>

<div class="filter-group">
  <label class="form-label">Date To</label>
  <input type="date" v-model="filters.dateTo" class="form-input" />
</div>
                    
                </div>
            </div>
            

            <!-- Transactions Table -->
            <div class="table-card">
                <div v-if="loading" class="loading">
                    <div class="spinner"></div>
                </div>

                <div v-else>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Ticket Number</th>
                                <th>Violator</th>
                                <th>Violation</th>
                                <th>Vehicle Type</th>
                                <th>Repeat Offender</th>
                                <th>Address</th>
                                <th>Date</th>
                                <th>Fine Amount</th>
                                <th>Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr
                                v-for="transaction in displayedTransactions"
                                :key="transaction.id"
                            >
                                <td>
                                    <div class="transaction-id">
                                        #{{ transaction.ticket_number }}
                                    </div>
                                </td>
                                <td>
                                    <div class="violator-info">
                                        <div class="violator-name">
                                            {{
                                                transaction.violator?.first_name
                                            }}
                                            {{
                                                transaction.violator?.last_name
                                            }}
                                        </div>
                                        <div class="violator-license">
                                            {{
                                                transaction.violator
                                                    ?.license_number
                                            }}
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="violation-name">
                                        {{ transaction.violation?.name }}
                                    </div>
                                </td>
                                <td>
                                    <div class="vehicle-type">
                                        {{ transaction.vehicle?.vehicle_type || "N/A" }}
                                    </div>
                                </td>
                                <td>
                                    <div class="repeat-offender" :class="getAttemptClass(transaction.violator?.transactions_count)">
                                        {{ formatAttempt(transaction.violator?.transactions_count) }}
                                    </div>
                                </td>
                                <td>
                                    {{ transaction.violator.barangay }} {{ transaction.violator.city }} ,{{ transaction.violator.province }}
                                </td>
                                <td>
                                    {{ formatDateTime(transaction.date_time) }}
                                </td>
                                <td>
                                    <div class="fine-amount">
                                        ₱{{
                                            formatCurrency(
                                                transaction.fine_amount
                                            )
                                        }}
                                    </div>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button
                                            @click="
                                                viewTransaction(transaction)
                                            "
                                            class="btn-icon-sm"
                                            title="View Details"
                                        >
                                            👁️
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div class="pagination-container" v-if="paginationData.total > 0">
                        <div class="pagination-info">
                            <span class="pagination-text">
                                Showing {{ (paginationData.current_page - 1) * paginationData.per_page + 1 }} 
                                to {{ Math.min(paginationData.current_page * paginationData.per_page, paginationData.total) }} 
                                of {{ paginationData.total }} entries
                            </span>
                        </div>

                        <div class="pagination-controls">
                            <!-- Previous Button -->
                            <button
                                @click="goToPage(paginationData.current_page - 1)"
                                :disabled="paginationData.current_page === 1"
                                class="pagination-btn pagination-prev"
                            >
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/>
                                </svg>
                                Previous
                            </button>

                            <!-- Page Numbers -->
                            <div class="pagination-numbers">
                                <!-- First Page -->
                                <button
                                    v-if="paginationData.current_page > 3"
                                    @click="goToPage(1)"
                                    class="pagination-number"
                                >
                                    1
                                </button>
                                
                                <!-- Ellipsis -->
                                <span v-if="paginationData.current_page > 4" class="pagination-ellipsis">...</span>

                                <!-- Pages around current -->
                                <button
                                    v-for="page in visiblePages"
                                    :key="page"
                                    @click="goToPage(page)"
                                    :class="['pagination-number', { 'active': page === paginationData.current_page }]"
                                >
                                    {{ page }}
                                </button>

                                <span v-if="paginationData.current_page < paginationData.last_page - 3" class="pagination-ellipsis">...</span>

                                <!-- Last Page -->
                                <button
                                    v-if="paginationData.current_page < paginationData.last_page - 2"
                                    @click="goToPage(paginationData.last_page)"
                                    class="pagination-number"
                                >
                                    {{ paginationData.last_page }}
                                </button>
                            </div>

                            <!-- Next Button -->
                            <button
                                @click="goToPage(paginationData.current_page + 1)"
                                :disabled="paginationData.current_page === paginationData.last_page"
                                class="pagination-btn pagination-next"
                            >
                                Next
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/>
                                </svg>
                            </button>
                        </div>

                        <!-- Per Page Selector -->
                        <div class="per-page-selector">
                            <label>Show:</label>
                            <select v-model="perPage" @change="changePerPage" class="per-page-select">
                                <option value="15">15</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                            <span>per page</span>
                        </div>
                    </div>

                    <div
                        v-if="displayedTransactions.length === 0"
                        class="no-data"
                    >
                        <div class="no-data-icon">💳</div>
                        <h3>{{
                            hasFilters
                                ? "No transactions match your filters."
                                : "No transactions recorded yet."
                        }}</h3>
                    </div>
                </div>
            </div>

            <!-- Transaction Details Modal -->
            <ModalComponent
  :show="showDetailsModal"
  title="Transaction Details"
  @close="closeDetailsModal"
>
  <!-- Everything here is your detailed old modal body -->
  <div v-if="selectedTransaction" class="transaction-details">
    <!-- Transaction Information -->
    <div class="detail-section">
      <h4>Transaction Information</h4>
      <div class="detail-grid">
        <div class="detail-item">
          <label>Ticket Number:</label>
          <span>#{{ selectedTransaction.ticket_number }}</span>
        </div>
        <div class="detail-item">
          <label>Status:</label>
          <span
            class="status-badge"
            :class="`status-${selectedTransaction.status?.toLowerCase()}`"
          >
            {{ selectedTransaction.status }}
          </span>
        </div>
        <div class="detail-item">
          <label>Date & Time:</label>
          <span>{{ formatDateTime(selectedTransaction.date_time) }}</span>
        </div>
        <div class="detail-item">
          <label>Fine Amount:</label>
          <span class="fine-amount">
            ₱{{ formatCurrency(selectedTransaction.fine_amount) }}
          </span>
        </div>
      </div>
    </div>

    <!-- Violator Information -->
    <div class="detail-section">
      <h4>Violator Information</h4>
      <div class="detail-grid">
        <div class="detail-item">
          <label>Name:</label>
          <span>
            {{ selectedTransaction.violator?.first_name }}
            {{ selectedTransaction.violator?.last_name }}
          </span>
        </div>
        <div class="detail-item">
          <label>License Number:</label>
          <span>{{ selectedTransaction.violator?.license_number }}</span>
        </div>
        <div class="detail-item">
          <label>Phone:</label>
          <span>{{ selectedTransaction.violator?.mobile_number }}</span>
        </div>
        <div class="detail-item" v-if="selectedTransaction.violator?.email">
          <label>Email:</label>
          <span>{{ selectedTransaction.violator.email }}</span>
        </div>
        <div class="detail-item">
          <label>Repeat Offender:</label>
          <span>{{ formatAttempt(selectedTransaction.violator?.transactions_count) }}</span>
        </div>
      </div><br>
      <div class="detail-item full-width">
        <label>Address:</label>
        <span>
          {{
            (selectedTransaction.violator?.barangay || '') + ' ' +
            (selectedTransaction.violator?.city || '') +
            (selectedTransaction.violator?.province ? ', ' + selectedTransaction.violator.province : '')
          }}
        </span>
      </div>
    </div>

    <!-- Violation Details -->
    <div class="detail-section">
      <h4>Violation Details</h4>
      <div class="detail-grid">
        <div class="detail-item">
          <label>Violation Type:</label>
          <span>{{ selectedTransaction.violation?.name }}</span>
        </div>
        <div class="detail-item">
          <label>Location:</label>
          <span>{{ selectedTransaction.location }}</span>
        </div>
      </div><br>
      <div class="detail-item full-width">
        <label>Description:</label>
        <span>{{ selectedTransaction.violation?.description }}</span>
      </div>
      <div v-if="selectedTransaction.remarks" class="detail-item full-width">
        <label>Remarks:</label>
        <span>{{ selectedTransaction.remarks }}</span>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <template #footer>
    <button @click="closeDetailsModal" class="btn btn-secondary">Close</button>
  </template>
</ModalComponent>

        </div>
    </SidebarLayout>
</template>

<script>
import { ref, onMounted, computed } from "vue";
import SidebarLayout from "@/components/SidebarLayout.vue";
import ModalComponent from "@/components/ModalComponent.vue";
import { enforcerAPI } from "@/services/api";

export default {
    name: "EnforcerTransactions",
    components: {
        SidebarLayout,
        ModalComponent
    },
    setup() {
        const loading = ref(true);
        const allTransactions = ref([]);
        const showDetailsModal = ref(false);
        const selectedTransaction = ref(null);
        const violationTypes = ref([]);
        const perPage = ref(15);

        const paginationData = ref({
            current_page: 1,
            last_page: 1,
            per_page: 15,
            total: 0,
            from: 0,
            to: 0
        });

        const filters = ref({
            dateRange: "",     
            dateFrom: "",
            dateTo: "",
            search: "",
            violation_id: "",
            vehicle_type: "",
            repeat_offender: "",
        });

        const displayedTransactions = computed(() => {
            let filtered = Array.isArray(allTransactions.value) 
                ? [...allTransactions.value] 
                : [];

            // Search filter
            if (filters.value.search) {
                const search = filters.value.search.toLowerCase();
                filtered = filtered.filter(
                    (t) =>
                        t.violator?.first_name?.toLowerCase().includes(search) ||
                        t.violator?.last_name?.toLowerCase().includes(search) ||
                        t.violator?.license_number?.toLowerCase().includes(search) ||
                        t.violation?.name?.toLowerCase().includes(search)
                );
            }

            // Violation Type filter
            if (filters.value.violation_id) {
                filtered = filtered.filter(
                    (t) => t.violation?.id === filters.value.violation_id
                );
            }

            // Vehicle Type filter
            if (filters.value.vehicle_type) {
                filtered = filtered.filter(
                    (t) => t.vehicle?.vehicle_type === filters.value.vehicle_type
                );
            }

            // Date Range filter
            if (filters.value.dateRange) {
                const now = new Date();
                filtered = filtered.filter((t) => {
                    const date = new Date(t.date_time);
                    if (filters.value.dateRange === "today") {
                        return date.toDateString() === now.toDateString();
                    }
                    if (filters.value.dateRange === "week") {
                        const weekAgo = new Date();
                        weekAgo.setDate(now.getDate() - 7);
                        return date >= weekAgo && date <= now;
                    }
                    if (filters.value.dateRange === "month") {
                        return (
                            date.getMonth() === now.getMonth() &&
                            date.getFullYear() === now.getFullYear()
                        );
                    }
                    return true;
                });
            }

            // Repeat Offender filter
            if (filters.value.repeat_offender === true) {
                filtered = filtered.filter(t => (t.violator?.transactions_count || 0) >= 2);
            } else if (filters.value.repeat_offender === false) {
                filtered = filtered.filter(t => (t.violator?.transactions_count || 0) <= 1);
            }

            // Filter by address (barangay, city, province)
    if (filters.value.address) {
        const search = filters.value.address.toLowerCase();
        filtered = filtered.filter(tx => {
            const fullAddress = `${tx.violator?.barangay || ''} ${tx.violator?.city || ''} ${tx.violator?.province || ''}`.toLowerCase();
            return fullAddress.includes(search);
        });
    }
    //Date Range filter
if (filters.value.dateFrom && filters.value.dateTo) {
    filtered = filtered.filter(t => {
        if (!t.date_time) return false;

        const txDate = new Date(t.date_time).setHours(0, 0, 0, 0);
        const from   = new Date(filters.value.dateFrom).setHours(0, 0, 0, 0);
        const to     = new Date(filters.value.dateTo).setHours(23, 59, 59, 999);

        return txDate >= from && txDate <= to;
    });
}
            return filtered;
        });

        // Calculate visible page numbers for pagination
        const visiblePages = computed(() => {
            const current = paginationData.value.current_page;
            const last = paginationData.value.last_page;
            const pages = [];

            let start = Math.max(1, current - 2);
            let end = Math.min(last, current + 2);
            if (end - start < 4) {
                if (start === 1) {
                    end = Math.min(last, start + 4);
                } else if (end === last) {
                    start = Math.max(1, end - 4);
                }
            }

            for (let i = start; i <= end; i++) {
                pages.push(i);
            }

            return pages;
        });

        const loadViolationTypes = async () => {
            try {
                const response = await enforcerAPI.getViolationTypes();
                if (response.data.status === "success") {
                    violationTypes.value = response.data.data || [];
                }
            } catch (error) {
                console.error("Failed to load violation types:", error);
            }
        };

        const loadTransactions = async (page = 1) => {
            try {
                loading.value = true;
                const response = await enforcerAPI.getTransactions({
                    page: page,
                    per_page: perPage.value
                });

                if (response.data.status === "success") {
                    const data = response.data.data;
                    allTransactions.value = data.data || [];
                    paginationData.value = {
                        current_page: data.current_page,
                        last_page: data.last_page,
                        per_page: data.per_page,
                        total: data.total,
                        from: data.from,
                        to: data.to
                    };
                }
            } catch (error) {
                console.error("Failed to load transactions:", error);
            } finally {
                loading.value = false;
            }
        };

        const goToPage = (page) => {
            if (page >= 1 && page <= paginationData.value.last_page) {
                loadTransactions(page);
            }
        };

        const changePerPage = () => {
            paginationData.value.current_page = 1;
            loadTransactions(1);
        };

        const viewTransaction = (transaction) => {
            selectedTransaction.value = transaction;
            showDetailsModal.value = true;
        };

        const closeDetailsModal = () => {
            showDetailsModal.value = false;
            selectedTransaction.value = null;
        };

        const getAttemptClass = (count) => {
            if (!count || count <= 1) return 'attempt-first';
            if (count === 2) return 'attempt-second'; 
            if (count >= 3) return 'attempt-third';
            return '';
        };

        const formatAttempt = (count) => {
            if (!count || count === 0) return "0 Attempt";
            if (count === 1) return "1st Attempt";
            if (count === 2) return "2nd Attempt";
            if (count === 3) return "3rd Attempt";
            return `${count}th Attempt`;
        };

        const formatCurrency = (amount) => { 
            return Number(amount || 0).toFixed(2); 
        };

        const formatDateTime = (dateString) => {
            if (!dateString) return "";
            return new Date(dateString).toLocaleString("en-PH", {
                year: "numeric",
                month: "short",
                day: "numeric",
                hour: "2-digit",
                minute: "2-digit",
            });
        };

        onMounted(() => {
            loadTransactions();
            loadViolationTypes();
        });

        return {
            loading,
            allTransactions,
            displayedTransactions,
            filters,
            showDetailsModal,
            selectedTransaction,
            violationTypes,
            paginationData,
            visiblePages,
            perPage,
            loadViolationTypes,
            viewTransaction,
            closeDetailsModal,
            getAttemptClass,
            formatAttempt,
            formatCurrency,
            formatDateTime,
            goToPage,
            changePerPage,
        };
    },
};
</script>

<style scoped>
.enforcer-transactions {
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

.filters-card {
    background: white;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    margin-bottom: 24px;
}

.filters-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
}

.table-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.transaction-id {
    font-family: "Courier New", monospace;
    font-weight: 600;
    color: #1f2937;
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
    font-family: "Courier New", monospace;
}


.repeat-offender {
    font-weight: 500;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    text-align: center;
}

.attempt-first,
.attempt-second,
.attempt-third {
    display: inline-flex;
    align-items: center;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
    white-space: nowrap;
}
.attempt-first {
    background-color: #dbeafe;
    color: #1e40af;
    border: 1px solid #93c5fd;
}

.attempt-second {
    background-color: #fef3c7;
    color: #d97706;
    border: 1px solid #fcd34d;
}

.attempt-third {
    background-color: #fecaca;
    color: #dc2626;
    border: 1px solid #f87171;
}

.violation-name {
    font-weight: 500;
    color: #1f2937;
}

.vehicle-type {
    font-weight: 500;
    color: #374151;
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

/* Pagination Styles */
.pagination-container {
    padding: 20px 24px;
    border-top: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
}

.pagination-info {
    color: #6b7280;
    font-size: 14px;
}

.pagination-controls {
    display: flex;
    align-items: center;
    gap: 8px;
}

.pagination-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 12px;
    border: 1px solid #d1d5db;
    background: white;
    color: #374151;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s ease;
}

.pagination-btn:hover:not(:disabled) {
    background: #f9fafb;
    border-color: #9ca3af;
}

.pagination-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    color: #9ca3af;
}

.pagination-numbers {
    display: flex;
    align-items: center;
    gap: 4px;
}

.pagination-number {
    width: 36px;
    height: 36px;
    border: 1px solid #d1d5db;
    background: white;
    color: #374151;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}

.pagination-number:hover {
    background: #f9fafb;
    border-color: #9ca3af;
}

.pagination-number.active {
    background: #3b82f6;
    border-color: #3b82f6;
    color: white;
}

.pagination-ellipsis {
    padding: 0 8px;
    color: #9ca3af;
    font-weight: 500;
}

.per-page-selector {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    color: #6b7280;
}

.per-page-select {
    padding: 4px 8px;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    font-size: 14px;
    background: white;
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

/* Loading Spinner */
.loading {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 60px;
}

.spinner {
    width: 32px;
    height: 32px;
    border: 3px solid #f3f4f6;
    border-top: 3px solid #3b82f6;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
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

.status-badge { 
    display: inline-flex;
    align-items: center; 
    padding: 4px 8px; 
    border-radius: 12px; 
    font-size: 12px; 
    font-weight: 500; 
    text-transform: uppercase; 
}
</style>