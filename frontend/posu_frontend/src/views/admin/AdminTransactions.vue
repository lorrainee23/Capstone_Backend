<template>
    <SidebarLayout page-title="Transaction Management">
        <div class="enforcer-transactions">
            <!-- Header -->
            <header class="dashboard-header">
        <div class="header-content">
          <h1>Violators Transaction</h1>
          <p>Manage Violators Transactions and Payments Status</p>
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
                        <label class="form-label">Search</label>
                        <input
                            v-model="filters.search"
                            type="text"
                            class="form-input"
                            placeholder="Search by violator name or license..."
                        />
                    </div>
                    <div class="filter-group">
                        <label class="form-label">Search</label>
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
                                <th>Apprehending Officer</th>
                                <th>Violator</th>
                                <th>Violation</th>
                                <th>Vehicle Type</th>
                                <th>Repeat Offender</th>
                                <th>Address</th>
                                <th>Date</th>
											<th>Fine Amount</th>
                                <th>Remarks</th>
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
                                    <div class="officer-info">
													<div class="user-avatar">
<img 
                        v-if="transaction.apprehending_officer?.image" 
                        :src="`http://127.0.0.1:8000/storage/${transaction.apprehending_officer?.image}`" 
                        alt="avatar" 
                        class="avatar-img"
                      />
                      <span v-else>
                        {{ getInitials(transaction.apprehending_officer?.first_name || 'N/A', transaction.apprehending_officer?.last_name || '' ) }}
                      </span>
</div>
                                        <div class="officer-name">
                                            {{ transaction.apprehending_officer?.first_name || 'N/A' }}     {{ transaction.apprehending_officer?.middle_name || '' }}
    {{ transaction.apprehending_officer?.last_name || '' }}
                                        </div>
													
                                    </div>
                                </td>
                                <td>
                                    <div class="violator-info">
                                        <div class="violator-name">
														<div class="user-avatar">
<img 
                        v-if="transaction.violator?.image" 
                        :src="`http://127.0.0.1:8000/storage/${transaction.violator?.image}`" 
                        alt="avatar" 
                        class="avatar-img"
                      />
                      <span v-else>
                        {{ getInitials(transaction.violator?.first_name || 'N/A', transaction.violator?.last_name || '' ) }}
                      </span>
							
</div>	

                                        </div>
													
                                        <div class="violator-name">
														{{ transaction.violator?.first_name || 'N/A' }}     {{ transaction.violator?.middle_name || '' }}
    {{ transaction.violator?.last_name || '' }}
	<div class="violator-license"> {{
                                                transaction.violator?.license_number
                                            }}</div>
                                           
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
                                       {{ transaction.violator?.barangay }} {{ transaction.violator?.city }}{{ transaction.violator?.province ? ', ' + transaction.violator.province : '' }}
                                </td>
                                <td class="date-time">
                                  <div class="date">{{ formatDateTime(transaction.date_time).date }}</div>
                                  <div class="time">{{ formatDateTime(transaction.date_time).time }}</div>
                                </td>
                                <td>
                                    <div class="fine-amount">
                                        <span class="total-amount">₱{{ formatCurrency(transaction.fine_amount) }}</span>
                                    </div>
                                </td>
                                <td >
                              <span class="payment-badge" :class="`payment-${transaction.status?.toLowerCase() || 'pending'}`">
                                {{ transaction.status || 'Pending' }}
                              </span>
                            </td>
                            <td>
                        <div class="action-buttons">
                        <button @click="viewTransaction(transaction)" class="btn-icon-sm btn-edit" title="View Transaction Details">
                          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                          </svg>
                        </button>
                        <button @click="markAsPaid(transaction)" class="btn-icon-sm btn-success"
                        :disabled="transaction.status?.toLowerCase() === 'paid' && userRole === 'Admin'"
                        :title="transaction.status?.toLowerCase() === 'paid' && userRole === 'Admin' ? 'Already Paid (Admin cannot re-Mark)' : 'Mark as Paid'"
                        >
                          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect x="2" y="3" width="20" height="14" rx="2" ry="2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <line x1="8" y1="21" x2="16" y2="21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <line x1="12" y1="17" x2="12" y2="21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                          </svg>
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
            <!-- Edit Transaction Modal -->
            <div v-if="showEditModal" class="modal-overlay" @click="closeEditModal">
              <div class="modal modal-large" @click.stop>
                <div class="modal-header">
                  <h3>Edit Transaction</h3>
                  <button @click="closeEditModal" class="modal-close">✕</button>
                </div>
                <div class="modal-body" v-if="selectedTransaction">
                  <div class="form-group">
                    <label>Fine Amount</label>
                    <input type="number" v-model="selectedTransaction.fine_amount" class="form-input" />
                  </div>
                  <div class="form-group">
                    <label>Status</label>
                    <select v-model="selectedTransaction.status" class="form-select">
                      <option value="Pending">Pending</option>
                      <option value="Paid">Paid</option>
                    </select>
                  </div>
                  <div class="form-group">
                    <label>Remarks</label>
                    <textarea v-model="selectedTransaction.remarks" class="form-input"></textarea>
                  </div>
                </div>
                <div class="modal-footer">
                  <button @click="updateTransaction" class="btn btn-primary">Save Changes</button>
                  <button @click="closeEditModal" class="btn btn-secondary">Cancel</button>
                </div>
              </div>
            </div>
            <!-- Transaction Details Modal -->
            <div
                v-if="showDetailsModal"
                class="modal-overlay"
                @click="closeDetailsModal"
            >
                <div class="modal modal-large" @click.stop>
                    <div class="modal-header">
                        <h3>Transaction Details</h3>
                        <button @click="closeDetailsModal" class="modal-close">
                            ✕
                        </button>
                    </div>
                    <div class="modal-body" v-if="selectedTransaction">
                        <div class="transaction-details">
                            <div class="detail-section">
                                <h4>Transaction Information</h4>
                                <div class="detail-grid">
                                    <div class="detail-item">
                                        <label>Ticket Number:</label>
                                        <span
                                            >#{{
                                                selectedTransaction.ticket_number
                                            }}</span
                                        >
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
                                        <span>
                                          {{formatDateTime(selectedTransaction.date_time).date +" " +
                                            formatDateTime(selectedTransaction.date_time).time
                                          }}
                                        </span>
                                    </div>
                                    <div class="detail-item">
                                        <label>Fine Amount:</label>
                                        <span class="fine-amount"
                                            >₱{{
                                                formatCurrency(
                                                    selectedTransaction.fine_amount
                                                )
                                            }}</span
                                        >
                                    </div>
                                </div>
                            </div>

                            <div class="detail-section">
                                <h4>Apprehending Officer</h4>
                                <div class="detail-grid">
                                    <div class="detail-item">
                                        <label>Officer Name:</label>
                                        <span>
                                            {{ selectedTransaction.apprehending_officer?.first_name || 'N/A' }} {{ selectedTransaction.apprehending_officer?.middle_name || '' }} 
                                            {{ selectedTransaction.apprehending_officer?.last_name || '' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="detail-section">
                                <h4>Vehicle Information</h4>
                                <div class="detail-grid">
                                    <div class="detail-item">
                                        <label>Owner Name:</label>
                                        <span
                                            >{{
                                                selectedTransaction.vehicle
                                                    ?.owner_first_name
                                            }} {{
                                                selectedTransaction.vehicle
                                                    ?.owner_middle_name
                                            }}
                                            {{
                                                selectedTransaction.vehicle
                                                    ?.owner_last_name
                                            }}</span
                                        >
                                    </div>
                                    <div class="detail-item">
                                        <label>Plate Number:</label>
                                        <span>{{
                                            selectedTransaction.vehicle
                                                ?.plate_number
                                        }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <label>Make:</label>
                                        <span>{{
                                            selectedTransaction.vehicle
                                                ?.make
                                        }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <label>Model:</label>
                                        <span>{{
                                            selectedTransaction.vehicle
                                                ?.model
                                        }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <label>Color:</label>
                                        <span>{{
                                            selectedTransaction.vehicle
                                                ?.color
                                        }}</span>
                                    </div>
                                </div><br>
                                <div class="detail-item full-width">
                                    <label>Address:</label>
                                    <span>
                                        {{
                                            (selectedTransaction.vehicle?.owner_barangay || '') + ' ' +
                                            (selectedTransaction.vehicle?.owner_city || '') +
                                            (selectedTransaction.vehicle?.owner_province ? ', ' + selectedTransaction.vehicle.province : '')
                                        }}
                                    </span>
                                </div>
                            </div>

                            <div class="detail-section">
                                <h4>Violator Information</h4>
                                <div class="detail-grid">
                                    <div class="detail-item">
                                        <label>Name:</label>
                                        <span
                                            >{{
                                                selectedTransaction.violator
                                                    ?.first_name
                                            }}
                                            {{
                                                selectedTransaction.violator
                                                    ?.last_name
                                            }}</span
                                        >
                                    </div>
                                    <div class="detail-item">
                                        <label>License Number:</label>
                                        <span>{{
                                            selectedTransaction.violator
                                                ?.license_number
                                        }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <label>Phone:</label>
                                        <span>{{
                                            selectedTransaction.violator
                                                ?.mobile_number
                                        }}</span>
                                    </div>
                                    <div
                                        class="detail-item"
                                        v-if="
                                            selectedTransaction.violator?.email
                                        "
                                    >
                                        <label>Email:</label>
                                        <span>{{
                                            selectedTransaction.violator.email
                                        }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <label>Repeat Offender:</label>
                                        <span> {{ formatAttempt(selectedTransaction.violator?.transactions_count) }}  </span>
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

                            <div class="detail-section">
                                <h4>Violation Details</h4>
                                <div class="detail-grid">
                                    <div class="detail-item">
                                        <label>Violation Type:</label>
                                        <span>{{
                                            selectedTransaction.violation?.name
                                        }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <label>Location:</label>
                                        <span>{{
                                            selectedTransaction.location
                                        }}</span>
                                    </div>
                                </div><br>
                                <div class="detail-item full-width">
                                    <label>Description:</label>
                                    <span>{{
                                        selectedTransaction.violation
                                            ?.description
                                    }}</span>
                                </div>
                                <div
                                    v-if="selectedTransaction.remarks"
                                    class="detail-item full-width"
                                >
                                    <label>Remarks:</label>
                                    <span>{{
                                        selectedTransaction.remarks
                                    }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button
                            @click="closeDetailsModal"
                            class="btn btn-secondary"
                        >
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </SidebarLayout>
</template>
<script>
import { ref, onMounted, computed } from "vue";
import SidebarLayout from "@/components/SidebarLayout.vue";
import { adminAPI } from "@/services/api";
import { useAuthStore } from "@/stores/auth";
import Swal from "sweetalert2";

export default {
    name: "EnforcerTransactions",
    components: {
        SidebarLayout,
    },
    setup() {
        const authStore = useAuthStore();
        const userRole = computed(() => authStore.state.user?.role);
        const loading = ref(true);
        const allTransactions = ref([]);
        const showDetailsModal = ref(false);
        const selectedTransaction = ref(null);
        const violationTypes = ref([]);
        const perPage = ref(15);
        const error = ref('');

        // BACKEND pagination data - comes from Laravel
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
            address: ""
        });

        // FRONTEND filtering - applied to current page data only
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
                    (t) => t.violation?.id === parseInt(filters.value.violation_id)
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

            // Custom Date Range filter
            if (filters.value.dateFrom && filters.value.dateTo) {
                filtered = filtered.filter(t => {
                    if (!t.date_time) return false;

                    const txDate = new Date(t.date_time).setHours(0, 0, 0, 0);
                    const from   = new Date(filters.value.dateFrom).setHours(0, 0, 0, 0);
                    const to     = new Date(filters.value.dateTo).setHours(23, 59, 59, 999);

                    return txDate >= from && txDate <= to;
                });
            }

            // Repeat Offender filter
            if (filters.value.repeat_offender === true) {
                filtered = filtered.filter(t => (t.violator?.transactions_count || 0) >= 2);
            } else if (filters.value.repeat_offender === false) {
                filtered = filtered.filter(t => (t.violator?.transactions_count || 0) <= 1);
            }

            // Address filter
            if (filters.value.address) {
                const search = filters.value.address.toLowerCase();
                filtered = filtered.filter(tx => {
                    const fullAddress = `${tx.violator?.barangay || ''} ${tx.violator?.city || ''} ${tx.violator?.province || ''}`.toLowerCase();
                    return fullAddress.includes(search);
                });
            }

            return filtered;
        });

        // Calculate visible page numbers for pagination (uses BACKEND pagination data)
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

        const markAsPaid = async (transaction) => {
            const newStatus = transaction.status?.toLowerCase() === 'paid' ? 'Pending' : 'Paid';

            const result = await Swal.fire({
                title: `Mark as ${newStatus}?`,
                html: `Mark Ticket Number <strong>#${transaction.ticket_number}</strong> as <strong>${newStatus}</strong>? <br><br> This action cannot be undone`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: `Yes, Mark as ${newStatus}`,
                cancelButtonText: 'Cancel',
                confirmButtonColor: newStatus === 'Paid' ? '#059669' : '#f59e0b',
            });

            if (result.isConfirmed) {
                try {
                    await adminAPI.updateTransaction(transaction.id, { status: newStatus });
                    // Reload current page to see updated data
                    await loadTransactions(paginationData.value.current_page);

                    Swal.fire({
                        title: 'Updated',
                        text: `Ticket Number has been marked as ${newStatus}.`,
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    });
                } catch (error) {
                    console.error('Failed to update transaction:', error);
                    Swal.fire({
                        title: 'Update failed',
                        text: 'Could not update transaction. Please try again.',
                        icon: 'error'
                    });
                }
            }
        };

        const loadViolationTypes = async () => {
            try {
                const response = await adminAPI.getViolations();
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
                const response = await adminAPI.getTransactions({
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

        // BACKEND pagination navigation
        const goToPage = (page) => {
            if (page >= 1 && page <= paginationData.value.last_page) {
                loadTransactions(page); // Load new page from backend
            }
        };

        const changePerPage = () => {
            loadTransactions(1); // Load first page with new per_page
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
            const dateObj = new Date(dateString);
            const date = dateObj.toLocaleDateString("en-PH", {
                year: "numeric",
                month: "short",
                day: "numeric",
            });
            const time = dateObj.toLocaleTimeString("en-PH", {
                hour: "2-digit",
                minute: "2-digit",
            });
            return { date, time };
        };

        const getInitials = (firstName, lastName) => {
            return `${firstName?.charAt(0) || ''}${lastName?.charAt(0) || ''}`.toUpperCase()
        }
        
        onMounted(() => {
            loadTransactions();
            loadViolationTypes();
        });

        return {
            loading,
            error,
            allTransactions,
            displayedTransactions, // Use this in template - it has frontend filtering applied
            filters,
            showDetailsModal,
            selectedTransaction,
            violationTypes,
            paginationData, // Backend pagination data
            visiblePages,
            perPage,
            loadViolationTypes,
            viewTransaction,
            markAsPaid,
            closeDetailsModal,
            getAttemptClass,
            formatAttempt,
            formatCurrency,
            formatDateTime,
            goToPage,
            changePerPage,
            getInitials,
            userRole,
        };
    },
};
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
.transaction-details {
  padding: 8px;
}

.detail-section {
  margin-bottom: 24px;
}

.detail-section h4 {
  font-size: 16px;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 16px;
  padding-bottom: 8px;
  border-bottom: 1px solid #e5e7eb;
}

.detail-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 16px;
}

.detail-item {
  display: flex;
  flex-direction: column;
}

.detail-item label {
  font-size: 12px;
  color: #6b7280;
  margin-bottom: 4px;
  font-weight: 500;
}

.detail-item span {
  font-size: 14px;
  color: #111827;
  font-weight: 500;
}

.detail-item.full-width {
  grid-column: 1 / -1;
}

.total-amount {
    font-weight: 600;
    color: #059669;
    font-size: 16px;
}

.status-badge,
.payment-badge {
    display: inline-flex;
    align-items: center;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
    white-space: nowrap;
}

.payment-paid {
    background-color: #def7ec;
    color: #057a55;
    border: 1px solid #a7f3d0;
}

.payment-pending {
    background-color: #fef3c7;
    color: #d97706;
    border: 1px solid #fcd34d;
}

.payment-overdue {
    background-color: #fde8e8;
    color: #c81e1e;
    border: 1px solid #fecaca;
}

.tabs-container {
    margin-bottom: 24px;
}
.header-left h2 {
  font-size: 28px;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 4px 0;
}

.header-left p {
  color: #64748b;
  margin: 0;
}

.tab-navigation {
  display: flex;
  gap: 4px;
  margin-bottom: 32px;
  background: #f1f5f9;
  padding: 4px;
  border-radius: 12px;
  width: fit-content;
}

.tab-button {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 20px;
  background: transparent;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  color: #64748b;
  cursor: pointer;
  transition: all 0.2s ease;
  font-size: 14px;
}

.tab-button.active {
  background: white;
  color: #1e293b;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.tab-count {
  background: #e2e8f0;
  color: #475569;
  padding: 2px 8px;
  border-radius: 10px;
  font-size: 11px;
  font-weight: 700;
}

.tab-button.active .tab-count {
  background: #667eea;
  color: white;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.content-header {
  display: flex;
  justify-content: flex-end;
  margin-bottom: 24px;
}

.filters-card {
  background: white;
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  margin-bottom: 24px;
  border: 1px solid rgba(0, 0, 0, 0.05);
}

.filters-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
}

.user-info, .violator-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.user-avatar, .violator-avatar, .officer-avatar{
  width: 35px;
  height: 35px;
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

.transaction-id {
  font-family: "Courier New", monospace;
  font-weight: 400;
  color: #1e293b;
  font-size: 13px;
}

.violator-info, .officer-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.violator-license {
  font-size: 0.5rem;
  color: #6c757d;
}

.officer-name {
  font-weight: 500;
  color: #1e293b;
  font-size: 14px;
}

.officer-badge {
  font-size: 12px;
  color: #64748b;
  font-family: "Courier New", monospace;
  margin-top: 2px;
}
td.date-time {
  text-align: left; /* or center if you want */
}

td.date-time .date {
  font-weight: 600;
  font-size: 14px;
}

td.date-time .time {
  font-size: 12px;
  color: #64748b; /* lighter color for time */
}
.repeat-offender {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 6px 12px;
  border-radius: 16px;
  font-size: 11px;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.attempt-first {
  background: linear-gradient(135deg, #dbeafe, #bfdbfe);
  color: #1e40af;
}

.attempt-second {
  background: linear-gradient(135deg, #fef3c7, #fde68a);
  color: #d97706;
}

.attempt-third {
  background: linear-gradient(135deg, #fee2e2, #fecaca);
  color: #dc2626;
}

.address-info {
  font-size: 6px;
  color: #475569;
  max-width: 110px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.vehicle-type {
  font-weight: 600;
  color: #374151;
  background: #f1f5f9;
  padding: 4px 8px;
  border-radius: 6px;
  font-size: 12px;
  text-align: center;
}
/* Pagination Styles */
.pagination-container {
  padding: 20px 24px;
  border-top: 1px solid #f1f5f9;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 16px;
  background: #f8fafc;
}

.pagination-info {
  color: #64748b;
  font-size: 14px;
  font-weight: 500;
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
  border: 1px solid #e2e8f0;
  background: white;
  color: #475569;
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 500;
  transition: all 0.2s ease;
}

.pagination-btn:hover:not(:disabled) {
  background: #f1f5f9;
  border-color: #cbd5e1;
  transform: translateY(-1px);
}

.pagination-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  color: #94a3b8;
}

.pagination-numbers {
  display: flex;
  align-items: center;
  gap: 4px;
}

.pagination-number {
  width: 36px;
  height: 36px;
  border: 1px solid #e2e8f0;
  background: white;
  color: #475569;
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 600;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
}

.pagination-number:hover {
  background: #f1f5f9;
  border-color: black;
  transform: translateY(-1px);
}

.pagination-number.active {
  background: white;
  border-color: #667eea;
  color: black;
  box-shadow: 0 4px 6px rgba(102, 126, 234, 0.3);
}

.pagination-ellipsis {
  padding: 0 8px;
  color: #94a3b8;
  font-weight: 500;
}

.per-page-selector {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  color: #64748b;
  font-weight: 500;
}

.per-page-select {
  padding: 6px 10px;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  font-size: 14px;
  background: white;
  color: #475569;
  font-weight: 500;
}

.user-name, .violator-name {
  font-weight: 600;
  color: #1e293b;
  font-size: 14px;
}

.user-email, .violator-phone {
  font-size: 12px;
  color: #64748b;
  margin-top: 2px;
}

.license-plate {
  background: #1e293b;
  color: white;
  padding: 4px 8px;
  border-radius: 4px;
  font-family: 'Courier New', monospace;
  font-weight: 700;
  font-size: 12px;
  letter-spacing: 1px;
}

.violation-info {
  display: flex;
  flex-direction: column;
}

.violation-name {
  font-weight: 600;
  color: #1e293b;
  font-size: 14px;
}

.violation-category {
  font-size: 12px;
  color: #64748b;
  margin-top: 2px;
}

.total-amount {
  font-weight: 700;
  color: #059669;
  font-size: 15px;
}

.role-badge {
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.role-admin {
  background: linear-gradient(135deg, #fee2e2, #fecaca);
  color: #991b1b;
}

.role-enforcer {
  background: linear-gradient(135deg, #dbeafe, #bfdbfe);
  color: #1e40af;
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
  background: linear-gradient(135deg, #dcfce7, #bbf7d0);
  color: #166534;
}

.status-inactive {
  background: linear-gradient(135deg, #fef3c7, #fde68a);
  color: #92400e;
}

.status-deactivate {
  background: linear-gradient(135deg, #fee2e2, #fecaca);
  color: #991b1b;
}

.payment-badge {
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.payment-paid {
  background: linear-gradient(135deg, #dcfce7, #bbf7d0);
  color: #166534;
}

.payment-pending {
  background: linear-gradient(135deg, #fef3c7, #fde68a);
  color: #92400e;
}

.payment-overdue {
  background: linear-gradient(135deg, #fee2e2, #fecaca);
  color: #991b1b;
}

.action-buttons {
  display: flex;
  align-items: center;
  justify-content: flex-start;
  gap: 8px;
}

.btn-icon-sm {
  width: 36px;
  height: 36px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
  background: #f8fafc;
  color: #475569;
}

button .tooltip {
  visibility: hidden;
  position: absolute;
  bottom: 100%;
  left: 50%;
  transform: translateX(-50%);
  background-color: #333;
  color: white;
  padding: 4px 8px;
  border-radius: 4px;
  white-space: nowrap;
  font-size: 12px;
  z-index: 10;
}

button:hover .tooltip {
  visibility: visible;
}

.btn-icon-sm:hover {
  background: #e2e8f0;
  transform: scale(1.05);
}

.btn-icon-sm.btn-danger {
  background: #fee2e2;
  color: #dc2626;
}

.btn-icon-sm.btn-danger:hover {
  background: #fecaca;
}
.btn-icon-sm.btn-warning {
  background: #fff3cd; 
  color: #ae8406;         
}
.btn-icon-sm.btn-success {
  background: #d1fae5; 
  color: #047857;      
}
.btn-icon-sm.btn-success:hover {
  background: #a7f3d0;  
}
.btn-icon-sm.btn-warning:hover {
  background: #ffe58f;      
}
.btn-icon-sm.btn-view {
  color: #6366f1; 
}

.btn-icon-sm.btn-view:hover {
  background-color: #e0e7ff;
}

.btn-icon-sm.btn-edit {
  color: #3b82f6;
}

.btn-icon-sm.btn-edit:hover {
  background-color: #dbeafe; 
}

.btn-icon-sm.btn-success:hover {
  background: #bbf7d0;
}

.no-data {
  text-align: center;
  padding: 60px 20px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
}

.no-data svg {
  color: #cbd5e1;
}

.no-data h3 {
  font-size: 18px;
  font-weight: 600;
  color: #1e293b;
  margin: 0;
}

.no-data p {
  color: #64748b;
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
  border-radius: 16px;
  width: 100%;
  max-width: 500px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

.modal-header {
  padding: 24px;
  border-bottom: 1px solid #f1f5f9;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
}

.modal-header h3 {
  font-size: 18px;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
}

.modal-close {
  background: none;
  border: none;
  cursor: pointer;
  color: #64748b;
  padding: 4px;
  border-radius: 6px;
  transition: all 0.2s ease;
}

.modal-close:hover {
  background: #e2e8f0;
  color: #1e293b;
}

.modal-body {
  padding: 24px;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.form-group {
  margin-bottom: 20px;
}

.form-label {
  display: block;
  font-weight: 600;
  color: #374151;
  margin-bottom: 6px;
  font-size: 14px;
}

.form-input, .form-select {
  width: 100%;
  padding: 12px;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  font-size: 14px;
  transition: border-color 0.2s ease;
}

.form-input:focus, .form-select:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.modal-footer {
  padding: 24px;
  border-top: 1px solid #f1f5f9;
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  background: #f8fafc;
}

.btn {
  padding: 12px 20px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  border: none;
}

.btn-secondary {
  background: #e2e8f0;
  color: #475569;
}

.btn-secondary:hover {
  background: #cbd5e1;
}

.btn-primary {
  background: linear-gradient(135deg, #1e3a8a, #3b82f6);
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: linear-gradient(135deg, #1e3a8a, #3b82f6);
  transform: translateY(-1px);
}

.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none;
}

.btn-icon-sm:disabled {
  opacity: 0.3;
  cursor: not-allowed;
  transform: none;
}

.btn-icon-sm:disabled:hover {
  background: #f8fafc;
  transform: none;
}

.spinner-small {
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top: 2px solid white;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

.loading {
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 60px;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 3px solid #e2e8f0;
  border-top: 3px solid #667eea;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

@media (max-width: 768px) {
  .filters-row {
    grid-template-columns: 1fr;
  }
  
  .form-row {
    grid-template-columns: 1fr;
  }
  
  .modal {
    margin: 20px;
  }
  
  .tab-navigation {
    width: 100%;
    justify-content: center;
  }
  
  .tab-button {
    flex: 1;
    justify-content: center;
  }
  
  .action-buttons {
    flex-direction: column;
    gap: 4px;
  }
}
</style>