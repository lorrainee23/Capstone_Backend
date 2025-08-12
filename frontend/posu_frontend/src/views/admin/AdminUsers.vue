<template>
  <SidebarLayout page-title="User Management">
    <div class="admin-users">
      <!-- Header Actions -->
      <div class="page-header">
        <div class="header-left">
          <h2>System Users</h2>
          <p>Manage administrators and enforcers</p>
        </div>
        <div class="header-right">
          <button @click="showCreateModal = true" class="btn btn-primary">
            <span class="btn-icon">➕</span>
            Add New User
          </button>
        </div>
      </div>

      <!-- Filters -->
      <div class="filters-card">
        <div class="filters-row">
          <div class="filter-group">
            <label class="form-label">Role</label>
            <select v-model="filters.role" class="form-select">
              <option value="">All Roles</option>
              <option value="Admin">Admin</option>
              <option value="Enforcer">Enforcer</option>
            </select>
          </div>
          <div class="filter-group">
            <label class="form-label">Status</label>
            <select v-model="filters.status" class="form-select">
              <option value="">All Status</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
              <option value="deactivate">Deactivated</option>
            </select>
          </div>
          <div class="filter-group">
            <label class="form-label">Search</label>
            <input 
              v-model="filters.search" 
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
                <th>User</th>
                <th>Role</th>
                <th>Status</th>
                <th>Created</th>
                <th>Last Login</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="user in filteredUsers" :key="user.id">
                <td>
                  <div class="user-info">
                    <div class="user-avatar">
                      {{ getInitials(user.first_name, user.last_name) }}
                    </div>
                    <div>
                      <div class="user-name">{{ user.first_name }} {{ user.last_name }}</div>
                      <div class="user-email">{{ user.email }}</div>
                    </div>
                  </div>
                </td>
                <td>
                  <span class="role-badge" :class="`role-${user.role?.toLowerCase()}`">
                    {{ user.role }}
                  </span>
                </td>
                <td>
                  <span class="status-badge" :class="`status-${user.status?.toLowerCase()}`">
                    {{ user.status }}
                  </span>
                </td>
                <td>{{ formatDate(user.created_at) }}</td>
                <td>{{ formatDate(user.last_login_at) || 'Never' }}</td>
                <td>
                  <div class="action-buttons">
                    <button @click="editUser(user)" class="btn-icon-sm" title="Edit">
                      ✏️
                    </button>
                    <button 
                      @click="toggleUserStatus(user)" 
                      class="btn-icon-sm"
                      :class="user.status === 'active' ? 'btn-warning' : 'btn-success'"
                      :title="user.status === 'active' ? 'Deactivate' : 'Activate'"
                    >
                      {{ user.status === 'active' ? '⏸️' : '▶️' }}
                    </button>
                    <button @click="deleteUser(user)" class="btn-icon-sm btn-danger" title="Delete">
                      🗑️
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
          
          <div v-if="filteredUsers.length === 0" class="no-data">
            <div class="no-data-icon">👥</div>
            <h3>No users found</h3>
            <p>No users match your current filters.</p>
          </div>
        </div>
      </div>

      <!-- Create/Edit User Modal -->
      <div v-if="showCreateModal || showEditModal" class="modal-overlay" @click="closeModals">
        <div class="modal" @click.stop>
          <div class="modal-header">
            <h3>{{ showEditModal ? 'Edit User' : 'Create New User' }}</h3>
            <button @click="closeModals" class="modal-close">✕</button>
          </div>
          
          <form @submit.prevent="saveUser" class="modal-body">
            <div v-if="error" class="alert alert-error">{{ error }}</div>
            
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
              <input v-model="userForm.email" type="email" class="form-input" required />
            </div>
            
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Role *</label>
                <select v-model="userForm.role" class="form-select" required>
                  <option value="">Select Role</option>
                  <option value="Admin">Admin</option>
                  <option value="Enforcer">Enforcer</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">Status *</label>
                <select v-model="userForm.status" class="form-select" required>
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                  <option value="deactivate">Deactivated</option>
                </select>
              </div>
            </div>
            
            <div v-if="!showEditModal" class="form-group">
              <label class="form-label">Password *</label>
              <input v-model="userForm.password" type="password" class="form-input" :required="!showEditModal" />
            </div>
          </form>
          
          <div class="modal-footer">
            <button @click="closeModals" type="button" class="btn btn-secondary">Cancel</button>
            <button @click="saveUser" type="submit" class="btn btn-primary" :disabled="saving">
              <span v-if="saving" class="spinner-small"></span>
              {{ saving ? 'Saving...' : (showEditModal ? 'Update User' : 'Create User') }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </SidebarLayout>
</template>

<script>
import { ref, onMounted, computed } from 'vue'
import SidebarLayout from '@/components/SidebarLayout.vue'
import { adminAPI } from '@/services/api'

export default {
  name: 'AdminUsers',
  components: {
    SidebarLayout
  },
  setup() {
    const loading = ref(true)
    const saving = ref(false)
    const users = ref([])
    const showCreateModal = ref(false)
    const showEditModal = ref(false)
    const error = ref('')
    
    const filters = ref({
      role: '',
      status: '',
      search: ''
    })
    
    const userForm = ref({
      first_name: '',
      middle_name: '',
      last_name: '',
      email: '',
      role: '',
      status: 'active',
      password: ''
    })
    
    const editingUser = ref(null)
    
    const filteredUsers = computed(() => {
      let filtered = users.value
      
      if (filters.value.role) {
        filtered = filtered.filter(user => user.role === filters.value.role)
      }
      
      if (filters.value.status) {
        filtered = filtered.filter(user => user.status === filters.value.status)
      }
      
      if (filters.value.search) {
        const search = filters.value.search.toLowerCase()
        filtered = filtered.filter(user => 
          user.first_name.toLowerCase().includes(search) ||
          user.last_name.toLowerCase().includes(search) ||
          user.email.toLowerCase().includes(search)
        )
      }
      
      return filtered
    })
    
    const loadUsers = async () => {
      try {
        loading.value = true
        const response = await adminAPI.getUsers()
        
        if (response.data.status === 'success') {
          users.value = response.data.data || []
        }
      } catch (error) {
        console.error('Failed to load users:', error)
      } finally {
        loading.value = false
      }
    }
    
    const saveUser = async () => {
      try {
        saving.value = true
        error.value = ''
        
        let response
        if (showEditModal.value && editingUser.value) {
          response = await adminAPI.updateUser(editingUser.value.id, userForm.value)
        } else {
          response = await adminAPI.createUser(userForm.value)
        }
        
        if (response.data.success) {
          await loadUsers()
          closeModals()
        } else {
          error.value = response.data.message || 'Failed to save user'
        }
      } catch (err) {
        error.value = err.response?.data?.message || 'Failed to save user'
      } finally {
        saving.value = false
      }
    }
    
    const editUser = (user) => {
      editingUser.value = user
      userForm.value = {
        first_name: user.first_name,
        middle_name: user.middle_name || '',
        last_name: user.last_name,
        email: user.email,
        role: user.role,
        status: user.status,
        password: ''
      }
      showEditModal.value = true
    }
    
    const toggleUserStatus = async (user) => {
      try {
        const newStatus = user.status === 'active' ? 'inactive' : 'active'
        await adminAPI.updateUser(user.id, { status: newStatus })
        await loadUsers()
      } catch (error) {
        console.error('Failed to update user status:', error)
      }
    }
    
    const deleteUser = async (user) => {
      if (confirm(`Are you sure you want to delete ${user.first_name} ${user.last_name}?`)) {
        try {
          await adminAPI.deleteUser(user.id)
          await loadUsers()
        } catch (error) {
          console.error('Failed to delete user:', error)
        }
      }
    }
    
    const closeModals = () => {
      showCreateModal.value = false
      showEditModal.value = false
      editingUser.value = null
      error.value = ''
      userForm.value = {
        first_name: '',
        middle_name: '',
        last_name: '',
        email: '',
        role: '',
        status: 'active',
        password: ''
      }
    }
    
    const formatDate = (dateString) => {
      if (!dateString) return null
      return new Date(dateString).toLocaleDateString('en-PH', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      })
    }
    
    const getInitials = (firstName, lastName) => {
      return `${firstName?.charAt(0) || ''}${lastName?.charAt(0) || ''}`.toUpperCase()
    }
    
    onMounted(() => {
      loadUsers()
    })
    
    return {
      loading,
      saving,
      users,
      filteredUsers,
      filters,
      showCreateModal,
      showEditModal,
      userForm,
      error,
      saveUser,
      editUser,
      toggleUserStatus,
      deleteUser,
      closeModals,
      formatDate,
      getInitials
    }
  }
}
</script>

<style scoped>
.admin-users {
  max-width: 1400px;
  margin: 0 auto;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 32px;
}

.header-left h2 {
  font-size: 28px;
  font-weight: 700;
  color: #1f2937;
  margin: 0 0 4px 0;
}

.header-left p {
  color: #6b7280;
  margin: 0;
}

.btn-icon {
  margin-right: 8px;
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

.user-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.user-avatar {
  width: 40px;
  height: 40px;
  background: linear-gradient(135deg, #1e3a8a, #3b82f6);
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 14px;
}

.user-name {
  font-weight: 500;
  color: #1f2937;
}

.user-email {
  font-size: 12px;
  color: #6b7280;
}

.role-badge {
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 500;
  text-transform: uppercase;
}

.role-admin {
  background: #fef2f2;
  color: #991b1b;
}

.role-enforcer {
  background: #eff6ff;
  color: #1e40af;
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

.btn-icon-sm.btn-danger {
  background: #fee2e2;
  color: #dc2626;
}

.btn-icon-sm.btn-danger:hover {
  background: #fecaca;
}

.btn-icon-sm.btn-warning {
  background: #fef3c7;
  color: #d97706;
}

.btn-icon-sm.btn-warning:hover {
  background: #fde68a;
}

.btn-icon-sm.btn-success {
  background: #d1fae5;
  color: #059669;
}

.btn-icon-sm.btn-success:hover {
  background: #a7f3d0;
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
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.modal-footer {
  padding: 24px;
  border-top: 1px solid #e5e7eb;
  display: flex;
  justify-content: flex-end;
  gap: 12px;
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
  .page-header {
    flex-direction: column;
    align-items: stretch;
    gap: 16px;
  }
  
  .filters-row {
    grid-template-columns: 1fr;
  }
  
  .form-row {
    grid-template-columns: 1fr;
  }
  
  .modal {
    margin: 20px;
  }
}
</style>
