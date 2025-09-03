<template>
  <SidebarLayout>
    <div class="archives-page">
      <div class="archives-header">
        <h1 class="page-title">Archives Management</h1>
        <p class="page-subtitle">Restore or permanently delete archived records.</p>
      </div>

      <div class="archives-content">
        <div class="tabs-container">
          <button
            @click="switchTab('users')"
            :class="{ active: activeTab === 'users' }"
            class="tab-button"
          >
            Users <span class="tab-count">{{ totalStats.users }}</span>
          </button>
          <button
            @click="switchTab('violations')"
            :class="{ active: activeTab === 'violations' }"
            class="tab-button"
          >
            Violations <span class="tab-count">{{ totalStats.violations }}</span>
          </button>
          <button
            @click="switchTab('violators')"
            :class="{ active: activeTab === 'violators' }"
            class="tab-button"
          >
            Violators <span class="tab-count">{{ totalStats.violators }}</span>
          </button>
        </div>

        <div class="search-container">
           <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
           </svg>
           <input type="text" :placeholder="`Search in ${activeTab}...`" v-model="searchQuery" class="search-input">
        </div>

        <div v-if="loading" class="state-container">
          <p>Loading...</p>
        </div>
        <div v-else-if="error" class="state-container">
          <p>{{ error }}</p>
          <button @click="retryLoad" class="retry-button">Try Again</button>
        </div>
        <div v-else-if="isCurrentTabEmpty" class="state-container">
           <p>No archived {{ activeTab }} found.</p>
        </div>

        <div v-else class="table-wrapper">
          <table class="data-table">
            <template v-if="activeTab === 'users'">
              <thead>
                <tr>
                  <th>User</th>
                  <th>Role</th>
                  <th>Archived Date</th>
                  <th class="actions-header">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="user in filteredUsers" :key="user.id">
                  <td>
                    <div class="user-info">
                      <div class="user-avatar">{{ getUserInitials(user.first_name, user.last_name) }}</div>
                      <div>
                        <div class="text-primary">{{ user.first_name }} {{ user.last_name }}</div>
                        <div class="text-secondary">{{ user.email }}</div>
                      </div>
                    </div>
                  </td>
                  <td>{{ user.role }}</td>
                  <td>{{ formattedDate(user.deleted_at) }}</td>
                  <td>
                    <div class="action-buttons">
                       <button @click="restoreItem(user.id, 'user')" class="btn-restore" :disabled="isProcessing(user.id, 'user')">Restore</button>
                       <button @click="forceDeleteItem(user.id, 'user')" class="btn-delete" :disabled="isProcessing(user.id, 'user')">Delete</button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </template>

            <template v-if="activeTab === 'violations'">
               <thead>
                <tr>
                  <th>Violation</th>
                  <th>Fine</th>
                  <th>Archived Date</th>
                  <th class="actions-header">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="violation in filteredViolations" :key="violation.id">
                   <td>
                      <div>
                        <div class="text-primary">{{ violation.name }}</div>
                        <div class="text-secondary">{{ violation.description }}</div>
                      </div>
                   </td>
                   <td>₱{{ formatCurrency(violation.fine_amount) }}</td>
                   <td>{{ formattedDate(violation.deleted_at) }}</td>
                   <td>
                      <div class="action-buttons">
                        <button @click="restoreItem(violation.id, 'violation')" class="btn-restore" :disabled="isProcessing(violation.id, 'violation')">Restore</button>
                        <button @click="forceDeleteItem(violation.id, 'violation')" class="btn-delete" :disabled="isProcessing(violation.id, 'violation')">Delete</button>
                      </div>
                   </td>
                </tr>
              </tbody>
            </template>
            
            <template v-if="activeTab === 'violators'">
               <thead>
                <tr>
                  <th>Violator</th>
                  <th>License No.</th>
                  <th>Archived Date</th>
                  <th class="actions-header">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="violator in filteredViolators" :key="violator.id">
                   <td>
                      <div>
                        <div class="text-primary">{{ violator.first_name }} {{ violator.last_name }}</div>
                        <div class="text-secondary">{{ violator.city }}, {{ violator.province }}</div>
                      </div>
                   </td>
                   <td>{{ violator.license_number }}</td>
                   <td>{{ formattedDate(violator.deleted_at) }}</td>
                   <td>
                      <div class="action-buttons">
                        <button @click="restoreItem(violator.id, 'violator')" class="btn-restore" :disabled="isProcessing(violator.id, 'violator')">Restore</button>
                        <button @click="forceDeleteItem(violator.id, 'violator')" class="btn-delete" :disabled="isProcessing(violator.id, 'violator')">Delete</button>
                      </div>
                   </td>
                </tr>
              </tbody>
            </template>
          </table>
        </div>
      </div>
    </div>
  </SidebarLayout>
</template>

<script>
import { ref, onMounted, watch, computed } from 'vue';
import SidebarLayout from '@/components/SidebarLayout.vue';
import { adminAPI } from '@/services/api';
import Swal from 'sweetalert2';

export default {
  name: 'ArchivesPage',
  components: { SidebarLayout },
  setup() {
    const activeTab = ref('users');
    const archivedUsers = ref([]);
    const archivedViolations = ref([]);
    const archivedViolators = ref([]);
    const loading = ref(false);
    const error = ref(null);
    const processingItems = ref(new Set());
    const searchQuery = ref('');

    const totalStats = computed(() => ({
      users: archivedUsers.value.length,
      violations: archivedViolations.value.length,
      violators: archivedViolators.value.length
    }));

    const isCurrentTabEmpty = computed(() => {
      if (activeTab.value === 'users') return filteredUsers.value.length === 0;
      if (activeTab.value === 'violations') return filteredViolations.value.length === 0;
      if (activeTab.value === 'violators') return filteredViolators.value.length === 0;
      return true;
    });

    // Filtering
    const filteredUsers = computed(() => {
      if (!searchQuery.value) return archivedUsers.value;
      const query = searchQuery.value.toLowerCase();
      return archivedUsers.value.filter(u =>
        `${u.first_name} ${u.last_name}`.toLowerCase().includes(query) ||
        u.email.toLowerCase().includes(query) ||
        u.role.toLowerCase().includes(query)
      );
    });

    const filteredViolations = computed(() => {
      if (!searchQuery.value) return archivedViolations.value;
      const query = searchQuery.value.toLowerCase();
      return archivedViolations.value.filter(v =>
        v.name.toLowerCase().includes(query) ||
        v.description.toLowerCase().includes(query)
      );
    });

    const filteredViolators = computed(() => {
      if (!searchQuery.value) return archivedViolators.value;
      const query = searchQuery.value.toLowerCase();
      return archivedViolators.value.filter(v =>
        `${v.first_name} ${v.middle_name} ${v.last_name}`.toLowerCase().includes(query) ||
        v.license_number.toLowerCase().includes(query) ||
        `${v.barangay} ${v.city} ${v.province}`.toLowerCase().includes(query)
      );
    });

    // API
    const fetchAllArchivedData = async () => {
      loading.value = true;
      error.value = null;
      try {
        const [usersRes, violationsRes, violatorsRes] = await Promise.all([
          adminAPI.getArchivedUsers().catch(() => ({ data: { data: { data: [] } } })),
          adminAPI.getArchivedViolations().catch(() => ({ data: { data: [] } })),
          adminAPI.getArchivedViolators().catch(() => ({ data: { data: [] } }))
        ]);
        archivedUsers.value = usersRes.data.data.data || [];
        archivedViolations.value = violationsRes.data.data || [];
        archivedViolators.value = violatorsRes.data.data || [];
      } catch (err) {
        error.value = 'Failed to load archived data. Please try again.';
        console.error(err);
      } finally {
        loading.value = false;
      }
    };

    const fetchArchivedData = async () => {
      if (archivedUsers.value.length === 0 && archivedViolations.value.length === 0 && archivedViolators.value.length === 0) {
        await fetchAllArchivedData();
        return;
      }
      loading.value = true;
      error.value = null;
      try {
        if (activeTab.value === 'users' && archivedUsers.value.length === 0) {
          const res = await adminAPI.getArchivedUsers();
          archivedUsers.value = res.data.data.data || [];
        } else if (activeTab.value === 'violations' && archivedViolations.value.length === 0) {
          const res = await adminAPI.getArchivedViolations();
          archivedViolations.value = res.data.data || [];
        } else if (activeTab.value === 'violators' && archivedViolators.value.length === 0) {
          const res = await adminAPI.getArchivedViolators();
          archivedViolators.value = res.data.data || [];
        }
      } catch (err) {
        error.value = 'Failed to load archived data. Please try again.';
        console.error(err);
      } finally {
        loading.value = false;
      }
    };

    const switchTab = (tabName) => {
      if (activeTab.value !== tabName) {
        activeTab.value = tabName;
        searchQuery.value = '';
      }
    };

    const retryLoad = () => fetchAllArchivedData();

    const restoreItem = async (id, type) => {
      const itemKey = `${type}-${id}`;
      const result = await Swal.fire({
        title: `Restore this ${type}?`,
        text: `This ${type} will be restored and become active again.`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, restore it!',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#6b7280'
      });
      if (!result.isConfirmed) return;

      processingItems.value.add(itemKey);
      try {
        if (type === 'user') {
          await adminAPI.restoreUser(id);
          archivedUsers.value = archivedUsers.value.filter(u => u.id !== id);
        } else if (type === 'violation') {
          await adminAPI.restoreViolation(id);
          archivedViolations.value = archivedViolations.value.filter(v => v.id !== id);
        } else if (type === 'violator') {
          await adminAPI.restoreViolator(id);
          archivedViolators.value = archivedViolators.value.filter(v => v.id !== id);
        }
        Swal.fire({ title: 'Restored!', icon: 'success', timer: 1500, showConfirmButton: true });
      } catch (err) {
        Swal.fire({ title: 'Error!', text: 'Failed to restore.', icon: 'error' });
      } finally {
        processingItems.value.delete(itemKey);
      }
    };

    const forceDeleteItem = async (id, type) => {
      const itemKey = `${type}-${id}`;
      const result = await Swal.fire({
        title: 'Are you sure?',
        html: `This action is <strong>irreversible</strong> and will permanently delete the ${type}.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete!',
        confirmButtonColor: '#ef4444',
        cancelButtonText: 'Cancel',
        cancelButtonColor: '#6b7280'
      });
      if (!result.isConfirmed) return;

      processingItems.value.add(itemKey);
      try {
        if (type === 'user') {
          await adminAPI.forceDeleteUser(id);
          archivedUsers.value = archivedUsers.value.filter(u => u.id !== id);
        } else if (type === 'violation') {
          await adminAPI.forceDeleteViolation(id);
          archivedViolations.value = archivedViolations.value.filter(v => v.id !== id);
        } else if (type === 'violator') {
          await adminAPI.forceDeleteViolator(id);
          archivedViolators.value = archivedViolators.value.filter(v => v.id !== id);
        }
        Swal.fire({ title: 'Deleted!', icon: 'success', timer: 1500, showConfirmButton: true });
      } catch (err) {
        Swal.fire({ title: 'Error!', text: 'Failed to delete.', icon: 'error' });
      } finally {
        processingItems.value.delete(itemKey);
      }
    };

    const isProcessing = (id, type) => processingItems.value.has(`${type}-${id}`);
const formattedDate = date => {
  if (!date) return 'N/A';
  return new Date(date).toLocaleString('en-PH', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    hour12: true
  });
};

    const formatCurrency = amount => new Intl.NumberFormat('en-PH').format(amount);
    const getUserInitials = (first, last) => `${first?.charAt(0)||''}${last?.charAt(0)||''}`.toUpperCase();

    onMounted(fetchAllArchivedData);
    watch(activeTab, fetchArchivedData);

    return {
      activeTab, archivedUsers, archivedViolations, archivedViolators,
      loading, error, searchQuery, totalStats, isCurrentTabEmpty,
      filteredUsers, filteredViolations, filteredViolators,
      switchTab, retryLoad, restoreItem, forceDeleteItem, isProcessing,
      formattedDate, formatCurrency, getUserInitials
    };
  }
};
</script>

<style scoped>
.archives-page {
  min-height: 100vh;
  width: 100%;
  padding: 1.5rem;
  background-color: #f3f4f6;
}
.archives-header {
  padding: 2rem;
  margin-bottom: 1.5rem;
  border-radius: 12px;
  background: linear-gradient(135deg, #1e3a8a, #3b82f6);
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.page-title {
  font-size: 2rem;
  font-weight: bold;
  color: #ffffff;
  margin: 0 0 0.5rem 0;
}

.page-subtitle {
  font-size: 1rem;
  font-weight: 300;
  color: #a7c5f4;
  margin: 0;
}

/* Content Area */
.archives-content {
  background-color: #ffffff;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
}

/* Tabs */
.tabs-container {
  display: flex;
  gap: 0.5rem;
  border-bottom: 1px solid #e5e7eb;
  margin-bottom: 1.5rem;
}

.tab-button {
  padding: 0.75rem 1rem;
  border: none;
  background: none;
  cursor: pointer;
  font-size: 1rem;
  font-weight: 500;
  color: #6b7280;
  border-bottom: 2px solid transparent;
  transition: all 0.2s ease-in-out;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.tab-button:hover {
  color: #1e3a8a;
}

.tab-button.active {
  color: #1e3a8a;
  font-weight: 600;
  border-bottom-color: #3b82f6;
}

.tab-count {
  background-color: #eef2ff;
  color: #4338ca;
  font-size: 0.75rem;
  font-weight: 600;
  padding: 0.125rem 0.5rem;
  border-radius: 9999px;
}

/* Search */
.search-container {
    position: relative;
    margin-bottom: 1.5rem;
}

.search-input {
    width: 100%;
    padding: 0.75rem 1rem 0.75rem 2.5rem;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 0.9rem;
    color: #374151;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.search-input:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.3);
}

.search-icon {
    position: absolute;
    left: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    width: 1.25rem;
    height: 1.25rem;
    color: #9ca3af;
}

/* Loading/Error/Empty States */
.state-container {
  text-align: center;
  padding: 3rem 1rem;
  color: #6b7280;
}

.retry-button {
  background-color: #3b82f6;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 6px;
  cursor: pointer;
  margin-top: 1rem;
}

/* Table Styling */
.table-wrapper {
  overflow-x: auto;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
  color: #374151;
}

.data-table th, .data-table td {
  padding: 1rem;
  text-align: left;
  border-bottom: 1px solid #e5e7eb;
}

.data-table th {
  font-size: 0.75rem;
  font-weight: 600;
  color: #4b5563;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.data-table tr:last-child td {
    border-bottom: none;
}

.data-table tr:hover {
    background-color: #f9fafb;
}

.actions-header {
  display: flex;
  justify-content: flex-end;
}
/* Cell Content */
.text-primary {
  font-weight: 500;
  color: #111827;
}

.text-secondary {
  font-size: 0.875rem;
  color: #6b7280;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.user-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background-color: #e0e7ff;
  color: #4338ca;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 0.875rem;
}

/* Action Buttons */
.action-buttons {
  display: flex;
  gap: 0.5rem;
  justify-content: flex-end;
}

.action-buttons button {
  border: 1px solid #d1d5db;
  background-color: #ffffff;
  color: #374151;
  padding: 0.375rem 0.75rem;
  border-radius: 6px;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s, color 0.2s;
}

.action-buttons button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.action-buttons button.btn-restore:hover:not(:disabled) {
  background-color: #dbeafe;
  border-color: #bfdbfe;
  color: #1e40af;
}

.action-buttons button.btn-delete:hover:not(:disabled) {
  background-color: #fee2e2;
  border-color: #fecaca;
  color: #b91c1c;
}

/* SweetAlert2 Minimalist Theme */
:global(.minimal-swal) {
  font-family: inherit;
  border-radius: 12px !important;
}

:global(.minimal-swal .swal2-title) {
  color: #111827 !important;
}

:global(.minimal-swal .swal2-html-container) {
  color: #4b5563 !important;
}
</style>