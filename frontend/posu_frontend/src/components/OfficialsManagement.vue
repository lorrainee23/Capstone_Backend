<template>
  <header class="dashboard-header">
        <div class="header-content">
          <h1>Officials Management</h1>
          <p>Manage Government Officials</p>
        </div>
        <button class="refresh-btn" @click="loadUsers" aria-label="Refresh Dashboard">
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
  <div class="admin-users">
  <div>
    <!-- User Actions -->
    <div class="content-header">
      <button @click="showCreateModal = true" class="btn btn-primary">
        Add New User
      </button>
    </div>

    <!-- User Filters -->
        <div class="filters-card">
          <div class="filters-row">
            <div class="filter-group">
              <label class="form-label">Role</label>
              <select v-model="userFilters.role" class="form-select">
                <option value="">All Roles</option>
                <option 
                  v-for="role in allowedRoles" 
                  :key="role" 
                  :value="role"
                >
                  {{ role.charAt(0).toUpperCase() + role.slice(1) }}
                </option>
              </select>
            </div>
            <div class="filter-group">
              <label class="form-label">Status</label>
              <select v-model="userFilters.status" class="form-select">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="deactivate">Deactivated</option>
              </select>
            </div>
            <div class="filter-group">
              <label class="form-label">Search</label>
              <input 
                v-model="userFilters.search" 
                type="text" 
                class="form-input" 
                placeholder="Search by name or email..."
              />
            </div>
          </div>
        </div>

        <!-- Users Table -->
        <div class="table-card">
          <div v-if="loading" class="loading">
            <div class="spinner"></div>
          </div>
          
          <div v-else>
            <table class="table">
              <thead>
                <tr>
                  <th>No.</th>
                  <th>User</th>
                  <th>Username</th>
                  <th>Role</th>
                  <th>Office</th>
                  <th>Status</th>
                  <th>Created</th>
                  <th>Last Login</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(user, index) in filteredUsers" :key="user.id">
                  <td>
                    <div class="user-name">
                      {{ index + 1  }}
                    </div>
                  </td>
                  <td>
                    <div class="user-info">
                      <div class="user-avatar">
                        <img 
                        v-if="user.image" 
                        :src="`http://127.0.0.1:8000/storage/${user.image}`" 
                        alt="avatar" 
                        class="avatar-img"
                      />
                      <span v-else>
                        {{ getInitials(user.first_name, user.last_name) }}
                      </span>
                      </div>
                      <div>
                        <div class="user-name">{{ user.first_name }} {{ user.middle_name }} {{ user.last_name }}</div>
                        <div class="user-email">{{ user.email }}</div>
                      </div>
                    </div>
                  </td>
                  <td>
                    <div class="user-name">
                      {{ user.username }}
                    </div>
                  </td>
                  <td>
                    <span class="role-badge" :class="`role-${user.user_type?.toLowerCase()}`">
                      {{ user.user_type }}
                    </span>
                  </td>
                  <td>
                    <div class="user-name">
                      {{ user.office }}
                    </div>
                  </td>
                  <td>
                    <span class="status-badge" :class="`status-${user.status?.toLowerCase()}`">
                      {{ user.status }}
                    </span>
                  </td>
                  <td>{{ formatDateTime(user.created_at) }}</td>
                  <td>{{ formatDateTime(user.last_login_at) || 'Never' }}</td>
                  <td>
                    <div class="action-buttons">
                      <button @click="editUser(user)" class="btn-icon-sm btn-edit" title="Edit">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                          <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                          <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                      </button>
                      <button 
                      @click="toggleUserStatus(user)" 
                      class="btn-icon-sm"
                      :class="user.status === 'active' ? 'btn-danger' : 'btn-success'"
                      :title="user.status === 'active' ? 'Deactivate' : 'Activate'"
                    >
                      <svg v-if="user.status === 'active'" width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                        <line x1="4.93" y1="4.93" x2="19.07" y2="19.07" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                      </svg>
                      <svg v-else width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <polygon points="5,3 19,12 5,21" fill="currentColor"/>
                      </svg>
                    </button>
                      <button @click="deleteUser(user)" class="btn-icon-sm btn-danger" title="Archive">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                          <path d="M21 8v13H3V8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                          <path d="M23 3H1v5h22V3z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                          <path d="M10 12h4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
            
            <div v-if="filteredUsers.length === 0" class="no-data">
              <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <circle cx="8.5" cy="7" r="4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
              <h3>No users found</h3>
              <p>No users match your current filters.</p>
            </div>
          </div>
        </div>
      </div>
    <!-- Create/Edit Modal -->
      <div v-if="showCreateModal || showEditModal" class="modal-overlay" @click="closeModals">
        <div class="modal" @click.stop>
          <div class="modal-header">
            <h3>{{ showEditModal ? 'Edit User' : 'Create New User' }}</h3>
            <button @click="closeModals" class="modal-close">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <line x1="18" y1="6" x2="6" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <line x1="6" y1="6" x2="18" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </button>
          </div>
          <!-- Updated modal form section -->
          <form @submit.prevent="saveUser" class="modal-body">
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">First Name *</label>
                <input v-model="userForm.first_name" type="text" class="form-input" required />
              </div>
              <div class="form-group">
                <label class="form-label">Last Name *</label>
                <input v-model="userForm.last_name" type="text" class="form-input" required />
              </div>
            </div>
            
            <div class="form-group">
              <label class="form-label">Middle Name</label>
              <input v-model="userForm.middle_name" type="text" class="form-input" />
            </div>
            
            <div class="form-group">
              <label class="form-label">Email *</label>
              <input v-model="userForm.email" type="email" class="form-input" />
            </div>
            
            <div class="form-group">
              <label class="form-label">Username *</label>
              <input v-model="userForm.username" type="text" class="form-input" required />
            </div>

            <!-- Conditional Role Selection -->
             <div v-if="normalizedRole !== 'admin'" class="form-group">
              <label class="form-label">Role *</label>
              <select v-model="userForm.user_type" class="form-select" required>
                <option value="">Select Role</option>
                <option v-if="normalizedRole === 'head'" value="deputy">Deputy</option>
                <option v-if="['head','deputy'].includes(normalizedRole)" value="admin">Admin</option>
                <option value="enforcer">Enforcer</option>
              </select>
            </div>

            <div class="form-group">
              <label class="form-label">Office *</label>
              <input v-model="userForm.office" type="text" class="form-input" required />
            </div>
            
            <div class="form-group">
              <label class="form-label">Status *</label>
              <select v-model="userForm.status" class="form-select" required>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="deactivate">Deactivated</option>
              </select>
            </div>
            
            <div class="form-group">
              <label class="form-label">Password *</label>
              <input v-model="userForm.password" type="password" class="form-input" />
            </div>

            <div class="modal-footer">
              <button @click="closeModals" type="button" class="btn btn-secondary">Cancel</button>
              <button type="submit" class="btn btn-primary" :disabled="saving">
                <span v-if="saving" class="spinner-small"></span>
                {{ saving ? 'Saving...' : (showEditModal ? 'Update User' : 'Create User') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
</template>

<script>
import { ref, computed, onMounted } from "vue"
import { adminAPI } from "@/services/api"
import Swal from "sweetalert2"

export default {
  name: "OfficialsManagement",
  props: {
    currentUserRole: { type: String, required: true }
  },
  setup(props) {
    const loading = ref(true)
    const saving = ref(false)
    const users = ref([])
    const showCreateModal = ref(false)
    const showEditModal = ref(false)
    const editingUser = ref(null)

    const userForm = ref({
      first_name: "",
      last_name: "",
      middle_name: "",
      email: "",
      username: "",
      status: "active",
      office: "",
      password: "",
      user_type: ""
    })

    const userFilters = ref({ role: "", status: "", search: "" })

    const normalizedRole = computed(() => props.currentUserRole?.toLowerCase() || "")

    const allowedRoles = computed(() => {
      if (normalizedRole.value === "admin") return ["enforcer"]
      if (normalizedRole.value === "deputy") return ["admin", "enforcer"]
      if (normalizedRole.value === "head") return ["deputy", "admin", "enforcer"]
      return []
    })

    const loadUsers = async () => {
      loading.value = true
      try {
        const res = await adminAPI.getUsers()
        users.value = res.data.data.data
      } finally {
        loading.value = false
      }
    }

    const filteredUsers = computed(() => {
      let filtered = [...users.value]
      if (userFilters.value.role)
        filtered = filtered.filter(u => u.user_type === userFilters.value.role)
      if (userFilters.value.status)
        filtered = filtered.filter(u => u.status === userFilters.value.status)
      if (userFilters.value.search) {
        const s = userFilters.value.search.toLowerCase()
        filtered = filtered.filter(u => 
          u.first_name.toLowerCase().includes(s) || 
          u.last_name.toLowerCase().includes(s) || 
          u.email.toLowerCase().includes(s)
        )
      }
      return filtered
    })

    const saveUser = async () => {
    saving.value = true
    try {
      let payload = { ...userForm.value }

      if (normalizedRole.value === "admin") {
        payload.user_type = "enforcer"
      } else if (normalizedRole.value === "deputy") {
        if (!userForm.value.user_type) {
          Swal.fire("Error", "Please select a role", "error")
          saving.value = false
          return
        }
        payload.user_type = userForm.value.user_type.toLowerCase()
      } else if (normalizedRole.value === "head") {
        if (!userForm.value.user_type) {
          Swal.fire("Error", "Please select a role", "error")
          saving.value = false
          return
        }
        payload.user_type = userForm.value.user_type.toLowerCase()
      }

      let res
      if (showEditModal.value) {
        res = await adminAPI.updateUser(editingUser.value.user_type, editingUser.value.id, payload)
      } else {
        res = await adminAPI.createUser(payload)
      }

      if (res.data.status === "success") {
        await loadUsers()
        closeModals()
        Swal.fire("Success", `User ${showEditModal.value ? 'updated' : 'created'} successfully!`, "success")
      }
    } catch (e) {
      console.error("Error saving user:", e)
      const errorMessage = e.response?.data?.message
      Swal.fire("Error", errorMessage, "error")
    } finally {
      saving.value = false
    }
      }

    const editUser = user => {
      editingUser.value = user
      userForm.value = { ...user, password: "" }
      showEditModal.value = true
    }

    const toggleUserStatus = async (user) => {
    const newStatus = user.status === 'active' ? 'deactivate' : 'active';

      await adminAPI.changeUserStatus({
        id: user.id,
        status: newStatus,
        user_type: user.user_type,
      });
      await loadUsers();
      Swal.fire('Success', `User status ${newStatus === 'active' ? 'activated' : 'deactivated'}!`, 'success');
  };


    const deleteUser = async (user) => {
    const result = await Swal.fire({
      title: 'Are you sure?',
      text: `Do you really want to delete ${user.first_name} ${user.last_name}?`,
      icon: 'warning',
      showCancelButton: true,
      iconColor: '#d33',
      confirmButtonColor: '#d33',
      confirmButtonText: 'Yes, archive!',
      cancelButtonText: 'Cancel'
    });

    if (result.isConfirmed) {
      try {
        await adminAPI.archiveUser(user.user_type, user.id);
        Swal.fire("Success", `User deleted successfully!`, "success");
        await loadUsers();
      } catch (e) {
        console.error(e);
        Swal.fire("Error", "Failed to archive user.", "error");
      }
    }
  };

    const closeModals = () => {
      showCreateModal.value = false
      showEditModal.value = false
      editingUser.value = null
      saving.value = false
      
      userForm.value = {
        first_name: "",
        last_name: "",
        middle_name: "",
        email: "",
        username: "",
        office: "",
        status: "active",
        password: "",
        user_type: "",
      }
    }

    const getInitials = (firstName, lastName) => {
      return `${firstName?.charAt(0) || ''}${lastName?.charAt(0) || ''}`.toUpperCase()
    }

    const formatDateTime = (dateString) => {
      if (!dateString) return null
      return new Date(dateString).toLocaleString('en-PH', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
    }

    onMounted(loadUsers)

    return {
      loading,
      saving,
      users,
      userForm,
      userFilters,
      allowedRoles,
      filteredUsers,
      showCreateModal,
      showEditModal,
      saveUser,
      editUser,
      toggleUserStatus,
      deleteUser,
      closeModals,
      getInitials,
      formatDateTime,
      normalizedRole
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
  width: 45px;
  height: 45px;
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
  font-weight: 600;
  color: #1e293b;
  font-size: 13px;
}

.violator-info, .officer-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.violator-license {
  font-size: 0.8rem;
  color: #6c757d;
}

.officer-name {
  font-weight: 600;
  color: #1e293b;
  font-size: 14px;
}

.officer-badge {
  font-size: 12px;
  color: #64748b;
  font-family: "Courier New", monospace;
  margin-top: 2px;
}

.repeat-offender {
  display: inline-flex;
  align-items: center;
  padding: 6px 12px;
  border-radius: 16px;
  font-size: 11px;
  font-weight: 600;
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
  font-size: 13px;
  color: #475569;
  max-width: 150px;
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
  border-color: #cbd5e1;
  transform: translateY(-1px);
}

.pagination-number.active {
  background: linear-gradient(135deg, #667eea, #764ba2);
  border-color: #667eea;
  color: white;
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
.role-deputy {
  background: linear-gradient(135deg, #ede9fe, #ddd6fe);
  color: #6b21a8; /* bold violet text */
}

.role-admin {
  background: linear-gradient(135deg, #ffedd5, #fed7aa);
  color: #c2410c; 
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
}</style>