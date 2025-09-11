<template>
  <SidebarLayout>
    <div class="archives-page">
      <div class="archives-header">
        <h1 class="page-title">Archives Management</h1>
        <p class="page-subtitle">Restore archived users based on your access level.</p>
      </div>

      <div class="archives-content">
        <div class="tabs-container">
          <button
            v-for="userType in manageableUserTypes"
            :key="userType"
            @click="switchTab(userType)"
            :class="{ active: activeTab === userType }"
            class="tab-button"
          >
            {{ formatUserType(userType) }}s 
            <span class="tab-count">{{ getTabCount(userType) }}</span>
          </button>
        </div>

        <div class="search-container">
           <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
           </svg>
           <input type="text" :placeholder="`Search archived ${formatUserType(activeTab)}s...`" v-model="searchQuery" class="search-input">
        </div>

        <div v-if="loading" class="state-container">
          <p>Loading...</p>
        </div>
        <div v-else-if="error" class="state-container">
          <p>{{ error }}</p>
          <button @click="retryLoad" class="retry-button">Try Again</button>
        </div>
        <div v-else-if="filteredUsers.length === 0" class="state-container">
           <p>No archived {{ formatUserType(activeTab) }}s found.</p>
        </div>

        <div v-else class="table-wrapper">
          <table class="data-table">
            <thead>
              <tr>
                <th>{{ formatUserType(activeTab) }}</th>
                <th>Email</th>
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
                      <div class="text-secondary">
                        <span class="user-type-badge" :class="getUserTypeBadgeClass(user.user_type || activeTab)">
                          {{ formatUserType(user.user_type || activeTab) }}
                        </span>
                      </div>
                    </div>
                  </div>
                </td>
                <td class="text-secondary">{{ user.email }}</td>
                <td class="text-secondary">{{ formattedDate(user.deleted_at) }}</td>
                <td>
                  <div class="action-buttons">
                     <button 
                       @click="restoreUser(user.user_type || activeTab, user.id)" 
                       class="btn-restore" 
                       :disabled="isProcessing(`user-${user.user_type || activeTab}-${user.id}`)">
                       <svg v-if="isProcessing(`user-${user.user_type || activeTab}-${user.id}`)" class="animate-spin h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24">
                         <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                         <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                       </svg>
                       Restore
                     </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </SidebarLayout>
</template>

<script>
import { ref, onMounted, computed } from 'vue';
import SidebarLayout from '@/components/SidebarLayout.vue';
import { adminAPI } from '@/services/api';
import { useAuthStore } from '@/stores/auth';
import Swal from 'sweetalert2';

export default {
  name: 'ArchivesPage',
  components: { SidebarLayout },
  setup() {
    const { state: authState } = useAuthStore();
    
    const activeTab = ref('');
    const archivedUsers = ref([]);
    const loading = ref(false);
    const error = ref(null);
    const processingItems = ref(new Set());
    const searchQuery = ref('');
    const manageableUserTypes = ref([]);

    const getTabCount = (userType) => {
      return archivedUsers.value.filter(user => 
        (user.user_type || activeTab.value) === userType
      ).length;
    };

    // Filtering
    const filteredUsers = computed(() => {
      let users = archivedUsers.value.filter(user => 
        (user.user_type || activeTab.value) === activeTab.value
      );
      
      // Apply search filter
      if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        users = users.filter(u =>
          `${u.first_name} ${u.last_name}`.toLowerCase().includes(query) ||
          u.email.toLowerCase().includes(query)
        );
      }
      
      return users;
    });

    // Get manageable user types based on current user
    const getManageableUserTypes = () => {
    const userRole = authState.user?.role;

    switch (userRole) {
      case 'Head':
        return ['deputy', 'admin', 'enforcer'];
      case 'Deputy':
        return ['admin', 'enforcer'];
      case 'Admin':
        return ['enforcer'];
      default:
        return [];
    }
  };

    // API Methods
    const fetchArchivedUsers = async (userType = 'all') => {
      loading.value = true;
      error.value = null;
      try {
        const params = {
          user_type: userType,
          search: searchQuery.value
        };
        const res = await adminAPI.getArchivedUsers(params);
        archivedUsers.value = res.data.data || [];
      } catch (err) {
        error.value = 'Failed to load archived users. Please try again.';
        console.error(err);
      } finally {
        loading.value = false;
      }
    };

    const switchTab = (userType) => {
      if (activeTab.value !== userType) {
        activeTab.value = userType;
        searchQuery.value = '';
      }
    };

    const retryLoad = () => fetchArchivedUsers();

    const restoreUser = async (userType, id) => {
      const itemKey = `user-${userType}-${id}`;
      const result = await Swal.fire({
        title: `Restore this ${formatUserType(userType)}?`,
        text: `This ${formatUserType(userType)} will be restored and become active again.`,
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
        await adminAPI.restoreUser(userType, id);
        archivedUsers.value = archivedUsers.value.filter(u => !(u.id === id && (u.user_type || activeTab.value) === userType));
        Swal.fire({ 
          title: 'Restored!', 
          text: `${formatUserType(userType)} has been restored successfully.`,
          icon: 'success', 
          timer: 1500, 
          showConfirmButton: true 
        });
      } catch (err) {
        const errorMessage = err.response?.data?.message || 'Failed to restore user.';
        Swal.fire({ 
          title: 'Error!', 
          text: errorMessage, 
          icon: 'error' 
        });
        console.error(err);
      } finally {
        processingItems.value.delete(itemKey);
      }
    };

    const isProcessing = (itemKey) => processingItems.value.has(itemKey);

    // Utility Methods
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

    const getUserInitials = (first, last) => `${first?.charAt(0)||''}${last?.charAt(0)||''}`.toUpperCase();
    
    const formatUserType = (userType) => {
      const typeMap = {
        'super_admin': 'Super Admin',
        'admin': 'Admin',
        'enforcer': 'Enforcer',
        'deputy': 'Deputy'
      };
      return typeMap[userType] || userType;
    };

    const getUserTypeBadgeClass = (userType) => {
      const classMap = {
        'head': 'badge-super-admin',
        'admin': 'badge-admin',
        'enforcer': 'badge-enforcer',
        'deputy': 'badge-deputy'
      };
      return classMap[userType] || 'badge-default';
    };

    // Initialize
    onMounted(() => {
      manageableUserTypes.value = getManageableUserTypes();
      if (manageableUserTypes.value.length > 0) {
        activeTab.value = manageableUserTypes.value[0];
        fetchArchivedUsers();
      }
    });

    return {
      activeTab, archivedUsers, manageableUserTypes,
      loading, error, searchQuery,
      filteredUsers, getTabCount,
      switchTab, retryLoad, restoreUser, isProcessing,
      formattedDate, getUserInitials, formatUserType, getUserTypeBadgeClass
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
  flex-wrap: wrap;
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
  min-width: 1.5rem;
  text-align: center;
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

/* User Type Badges */
.user-type-badge {
  font-size: 0.75rem;
  font-weight: 600;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.badge-super-admin {
  background-color: #fef3c7;
  color: #92400e;
}

.badge-admin {
  background-color: #dbeafe;
  color: #1e40af;
}

.badge-enforcer {
  background-color: #d1fae5;
  color: #065f46;
}

.badge-deputy {
  background-color: #e0e7ff;
  color: #4338ca;
}

.badge-default {
  background-color: #f3f4f6;
  color: #374151;
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
  transition: background-color 0.2s;
}

.retry-button:hover {
  background-color: #2563eb;
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
  background-color: #f9fafb;
}

.data-table tr:last-child td {
  border-bottom: none;
}

.data-table tbody tr:hover {
  background-color: #f9fafb;
}

.actions-header {
  text-align: right;
}

/* Cell Content */
.text-primary {
  font-weight: 500;
  color: #111827;
  margin-bottom: 0.25rem;
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
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background-color: #e0e7ff;
  color: #4338ca;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 0.875rem;
  flex-shrink: 0;
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
  padding: 0.5rem 1rem;
  border-radius: 6px;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  min-width: 80px;
  justify-content: center;
}

.action-buttons button:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.action-buttons button.btn-restore:hover:not(:disabled) {
  background-color: #dbeafe;
  border-color: #bfdbfe;
  color: #1e40af;
}

/* Loading spinner */
.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

/* Responsive Design */
@media (max-width: 768px) {
  .archives-page {
    padding: 1rem;
  }
  
  .archives-header {
    padding: 1.5rem;
  }
  
  .page-title {
    font-size: 1.5rem;
  }
  
  .data-table {
    font-size: 0.875rem;
  }
  
  .data-table th, .data-table td {
    padding: 0.75rem;
  }
  
  .user-info {
    gap: 0.5rem;
  }
  
  .user-avatar {
    width: 32px;
    height: 32px;
    font-size: 0.75rem;
  }
}
</style>