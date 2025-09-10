<template>
  <header class="dashboard-header">
        <div class="header-content">
          <h1>Violators Management</h1>
          <p>Manage Violators Information</p>
        </div>
        <button class="refresh-btn" @click="loadingViolators" aria-label="Refresh Dashboard">
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
	<br><br>
 <div class="filters-card">
  <div class="filters-row">
    <div class="filter-group">
      <label class="form-label">Search Name</label>
      <input 
        v-model="violatorFilters.name" 
        type="text" 
        class="form-input" 
        placeholder="Search by First or Last Name"
      />
    </div>
    <div class="filter-group">
      <label class="form-label">Search Address</label>
      <input 
        v-model="violatorFilters.address" 
        type="text" 
        class="form-input"
        placeholder="Filter by Address"
      />
    </div>
    <div class="filter-group">
      <label class="form-label">Search Mobile Number</label>
      <input 
        v-model="violatorFilters.mobile_number" 
        type="text" 
        class="form-input"
        placeholder="Search by Mobile Number"
      />
    </div>
    <div class="filter-group">
      <label class="form-label">Sex</label>
      <select v-model="violatorFilters.gender" class="form-select">
        <option value="">All</option>
        <option :value="true">Male</option>
        <option :value="false">Female</option>
      </select>
    </div>
    <div class="filter-group">
      <label class="form-label">License Type</label>
      <select v-model="violatorFilters.professional" class="form-select">
        <option value="">All</option>
        <option :value="true">Professional</option>
        <option :value="false">Non-Professional</option>
      </select>
    </div>
    <div class="filter-group">
      <label class="form-label">License Number</label>
      <input 
        v-model="violatorFilters.license_number" 
        type="text" 
        class="form-input"
        placeholder="Search by License Number"
      />
    </div>
  </div>
</div>


          <!-- Violators Table -->
          <div class="table-card">
            <div v-if="loadingViolators" class="loading">
              <div class="spinner"></div>
            </div>
            
            <div v-else>
              <table class="table">
                <thead>
                  <tr>
                    <th class="violator">No.</th>
                    <th class="violator">Violator Name</th>
                    <th class="violator">License Number</th>
                    <th class="violator">Email</th>
                    <th class="violator">Mobile Number</th>
                    <th class="violator">Address</th>
                    <th class="violator">Sex</th>
                    <th class="violator">License Type</th>
                    <th class="violator">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="violator in paginatedViolators" :key="violator.id">
                  <td class="violator">
                    {{ violator.id }}
                  </td>
                  <td class="violator">
                    <div class="violator-info">
                      <div class="violator-avatar">
                        {{ getInitials(violator.first_name, violator.last_name) }}
                      </div>
                      <div>
                        <div class="violator-name">{{ violator.first_name }} {{ violator.middle_name }} {{ violator.last_name }}</div>
                      </div>
                    </div>
                  </td>
                  <td class="violator">
                    <div class="vehicle-type">
                      {{ violator.license_number || "N/A" }}
                    </div>
                  </td>
                  <td class="violator">
                    <div class="violator-name">
                      {{ violator.email || "N/A"}}
                    </div>
                  </td>
                  <td class="violator">
                    <div class="violator-name">
                      {{ violator.mobile_number }}
                    </div>
                  </td>
                  <td class="violator">
                    <div class="address-info">
                      {{ violator.barangay }} {{ violator.city }}, {{ violator.province }}
                    </div>
                  </td>
                <td class="violator">
                  <div class="violator-name">
                    {{ violator.gender ? 'Male' : 'Female' }}
                  </div>
                </td>
                  <td class="violator">
                    <div class="violator-name">
                      {{ violator.professional ? 'Professional' : 'Non-Professional' }}
                    </div>
                  </td>
                  <td class="violator">
                    <div class="action-buttons">
                      <button @click="editViolator(violator)" class="btn-icon-sm btn-edit" title="Edit Transaction">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                          <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                          <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                      </button>
                      <span v-if="violator.transactions.some(t => t.status === 'Paid')"
                            class="btn-icon-sm btn-success"
                            title="Paid">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="36" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2l4-4m6 2a9 9 0 11-18 0a9 9 0 0118 0z"/>
                        </svg>
                      </span>
                      <span v-else class="btn-icon-sm btn-warning" title="Pending">
                        <svg xmlns="http://www.w3.org/2000/svg" width="23" height="36" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0a9 9 0 0118 0z"/>
                        </svg>
                      </span>
                    </div>
                  </td>
                  </tr>
                </tbody>
              </table>

          <!-- Pagination for Violators -->
          <div class="pagination-container" v-if="violatorPaginationData.total > 0">
            <div class="pagination-info">
              Showing
              {{ (violatorPaginationData.current_page - 1) * violatorPaginationData.per_page + 1 }}
              to
              {{ Math.min(violatorPaginationData.current_page * violatorPaginationData.per_page, violatorPaginationData.total) }}
              of {{ violatorPaginationData.total }} entries
            </div>

            <div class="pagination-controls">
              <button
                @click="goToViolatorPage(violatorPaginationData.current_page - 1)"
                :disabled="violatorPaginationData.current_page === 1"
                class="pagination-btn"
              >
                Previous
              </button>

              <button
                v-for="page in visiblePages"
                :key="page"
                @click="goToViolatorPage(page)"
                :class="['pagination-number', { active: page === violatorPaginationData.current_page }]">
                {{ page }}
              </button>

              <button
                @click="goToViolatorPage(violatorPaginationData.current_page + 1)"
                :disabled="violatorPaginationData.current_page === violatorPaginationData.last_page"
                class="pagination-btn"
              >
                Next
              </button>
            </div>

            <div class="per-page-selector">
              <label>Show:</label>
              <select v-model="violatorPerPage" @change="changePerPage">
                <option value="15">15</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
              </select>
              <span>per page</span>
            </div>
          </div>
         </div>
      </div>


      <!-- Create/Edit User Modal -->
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
              <input v-model="userForm.email" type="email" class="form-input" required />
            </div>
            <div class="form-group">
              <label class="form-label">Username</label>
              <input v-model="userForm.username" type="text" class="form-input" />
            </div>
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Status *</label>
                <select v-model="userForm.status" class="form-select" required>
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                  <option value="deactivate">Deactivated</option>
                </select>
              </div>
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

      <!-- Edit Violator Modal -->
<div v-if="showEditViolatorModal" class="modal-overlay" @click="closeEditViolatorModal">
  <div class="modal" @click.stop>
    <div class="modal-header">
      <h3>{{ showEditViolatorModal}} Edit Violator </h3>
      <button @click="closeEditViolatorModal" class="modal-close">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <line x1="18" y1="6" x2="6" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          <line x1="6" y1="6" x2="18" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </button>
    </div>

    <form @submit.prevent="saveViolator" class="modal-body">

      <!-- Violator Info Section -->
      <h4 class="section-label">Violator Info</h4>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">First Name *</label>
          <input v-model="violatorForm.first_name" type="text" class="form-input" required />
        </div>
        <div class="form-group">
          <label class="form-label">Last Name *</label>
          <input v-model="violatorForm.last_name" type="text" class="form-input" required />
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Middle Name</label>
        <input v-model="violatorForm.middle_name" type="text" class="form-input" />
      </div>

      <div class="form-group">
        <label class="form-label">License Number</label>
        <input v-model="violatorForm.license_number" type="text" class="form-input" />
      </div>
      <div class="form-group">
  <label class="form-label">License Type</label>
  <select v-model="violatorForm.professional" class="form-select" required>
    <option value="">Select Type</option>
    <option :value="1">Professional</option>
    <option :value="0">Non Professional</option>
  </select>
</div>
      <div class="form-group">
        <label class="form-label">Mobile Number</label>
        <input v-model="violatorForm.mobile_number" type="text" class="form-input" />
      </div>
      <div class="form-group">
  <label class="form-label">Sex</label>
  <select v-model="violatorForm.gender" class="form-select" required>
    <option value="">Select Sex</option>
    <option :value="1">Male</option>
    <option :value="0">Female</option>
  </select>
</div>

      <!-- Violator Address Section -->
      <h4 class="section-label">Violator Address</h4>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Barangay</label>
          <input v-model="violatorForm.barangay" type="text" class="form-input" />
        </div>
        <div class="form-group">
          <label class="form-label">City</label>
          <input v-model="violatorForm.city" type="text" class="form-input" />
        </div>
        <div class="form-group">
          <label class="form-label">Province</label>
          <input v-model="violatorForm.province" type="text" class="form-input" />
        </div>
      </div>

       <div class="form-group">
              <label class="form-label">Password *</label>
              <input v-model="violatorForm.password" type="password" class="form-input" />
            </div>
          
      <div class="modal-footer">
        <button @click="closeEditViolatorModal" type="button" class="btn btn-secondary">Cancel</button>
        <button type="submit" class="btn btn-primary" :disabled="savingViolator">
          <span v-if="savingViolator" class="spinner-small"></span>
          {{ savingViolator ? (showEditViolatorModal ? 'Updating...' : 'Saving...') : (showEditViolatorModal ? 'Update Violator' : 'Create Violator') }}
        </button>
      </div>

    </form>
  </div>
</div>
</div>
</template>
<script>
import { ref, onMounted, computed } from 'vue'
import { adminAPI } from '@/services/api'
import Swal from 'sweetalert2'

export default {
  name: 'ViolatorsManagement',
  components: {
  },
  setup() {
    const loading = ref(true)
    const loadingViolators = ref(true)
    const saving = ref(false)
    const activeTab = ref('users')
    const violators = ref([])
    const showViolatorDetailsModal = ref(false)
    const selectedViolator = ref(null)
    const showEditViolatorModal = ref(false);
    const editingViolator = ref(null);

    const violatorForm = ref({
      id:'',
      first_name: '',
      middle_name: '',
      last_name: '',
      license_number: '',
      mobile_number: '',
      barangay: '',
      city: '',
      province: '',
      password: '',
    });

    const savingViolator = ref(false);

    const error = ref('')
    
    const violatorFilters = ref({
    name: '',
    address: '',
    mobile_number: '',
    gender: '',
    professional: '',
    license_number: ''
  })
    
    const violatorPaginationData = ref({
      current_page: 1,
      last_page: 1,
      per_page: 15,
      total: 0,
      from: 0,
      to: 0
    })

    const violatorPerPage = ref(15)
    
    const visibleViolatorPages = computed(() => {
      const current = violatorPaginationData.value.current_page;
      const last = violatorPaginationData.value.last_page;
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
    })
    

    const editViolator = (violator) => {
  editingViolator.value = violator;

  violatorForm.value = {
    id: violator.id,
    first_name: violator.first_name,
    middle_name: violator.middle_name,
    last_name: violator.last_name,
    email: violator.email,
    mobile_number: violator.mobile_number,
    gender: violator.gender ? 1 : 0,
    license_number: violator.license_number,
    professional: violator.professional ? 1 : 0,
    barangay: violator.barangay,
    city: violator.city,
    province: violator.province,
  };

  showEditViolatorModal.value = true;
};

const closeEditViolatorModal = () => {
  showEditViolatorModal.value = false;
  editingViolator.value = null;
  violatorForm.value = {
    id:'',
    first_name: '',
    middle_name: '',
    last_name: '',
    license_number: '',
    mobile_number: '',
    status: '',
    barangay: '',
    city: '',
    province: ''
  };
};

const saveViolator = async () => {
  savingViolator.value = true;
  try {
    const payload = { ...violatorForm.value };
    const response = await adminAPI.updateViolator(payload);

    if (response.data.status === 'success') {
      await loadViolators(violatorPaginationData.value.current_page);
      closeEditViolatorModal();
      Swal.fire({
        icon: 'success',
        title: 'Updated',
        text: 'Violator updated successfully',
        timer: 1500,
        showConfirmButton: true
      });
    }
  } catch (err) {
    console.error(err);
    Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to update violator' });
  } finally {
    savingViolator.value = false;
  }
};
    
    const loadViolators = async (page = 1) => {
  loadingViolators.value = true;
  try {
    const params = {
      page,
      per_page: violatorPerPage.value,
      ...violatorFilters.value
    };
    const response = await adminAPI.getViolators(params);
    
    if (response.data.status === "success") {
      violators.value = response.data.data.data;
      violatorPaginationData.value = {
        current_page: response.data.data.current_page,
        last_page: response.data.data.last_page,
        per_page: response.data.data.per_page,
        total: response.data.data.total,
        from: response.data.data.from,
        to: response.data.data.to
      };
    }
  } catch (err) {
    console.error(err);
  } finally {
    loadingViolators.value = false;
  }
};

const filteredViolators = computed(() => {
  let base = Array.isArray(violators.value) ? violators.value : []
  let filtered = base.filter(v => !!v)

  if (violatorFilters.value.name) {
    const search = violatorFilters.value.name.toLowerCase()
    filtered = filtered.filter(v => {
      const fn = v.first_name?.toLowerCase() || ''
      const ln = v.last_name?.toLowerCase() || ''
      return fn.includes(search) || ln.includes(search)
    })
  }

  if (violatorFilters.value.address) {
    const addr = violatorFilters.value.address.toLowerCase()
    filtered = filtered.filter(v => {
      const fullAddr = `${v.barangay || ''} ${v.city || ''} ${v.province || ''}`.toLowerCase()
      return fullAddr.includes(addr)
    })
  }

  if (violatorFilters.value.mobile_number) {
    const mobile = violatorFilters.value.mobile_number.toLowerCase()
    filtered = filtered.filter(v => 
      v.mobile_number?.toLowerCase().includes(mobile)
    )
  }

  if (violatorFilters.value.gender !== '') {
    filtered = filtered.filter(v => String(v.gender) === String(violatorFilters.value.gender))
  }

  if (violatorFilters.value.professional !== '') {
    filtered = filtered.filter(v => String(v.professional) === String(violatorFilters.value.professional))
  }

  if (violatorFilters.value.license_number) {
    const lic = violatorFilters.value.license_number.toLowerCase()
    filtered = filtered.filter(v => v.license_number?.toLowerCase().includes(lic))
  }

  return filtered
})

const paginatedViolators = computed(() => {
  return filteredViolators.value; 
});
    

    const viewViolatorDetails = (violator) => {
      selectedViolator.value = violator;
      showViolatorDetailsModal.value = true;
    };

    const closeViolatorDetailsModal = () => {
      showViolatorDetailsModal.value = false;
      selectedViolator.value = null;
    };


    const archiveViolator = async (violator) => {
  const result = await Swal.fire({
    title: 'Archive Violator?',
    html: `Are you sure you want to archive <strong>${violator.first_name} ${violator.last_name}</strong>?<br><br>
           Archived violators can be restored later from the Archives page.`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Yes, archive',
    cancelButtonText: 'Cancel',
    confirmButtonColor: '#dc2626',
    iconColor:'#dc2626'
  });

  if (result.isConfirmed) {
    try {
      await adminAPI.archiveViolator(violator.id);
      await loadViolators(violatorPaginationData.value.current_page);

      Swal.fire({
        title: 'Archived',
        text: 'Violator has been archived successfully.',
        icon: 'success',
        timer: 1500,
        showConfirmButton: true
      });
    } catch (error) {
      console.error('Failed to archive violator:', error);
      Swal.fire({
        title: 'Archive failed',
        text: 'Could not archive violator. Please try again.',
        icon: 'error'
      });
    }
  }
};


const goToViolatorPage = async (page) => {
  if (page < 1 || page > violatorPaginationData.value.last_page) return
  
  try {
    loadingViolators.value = true
    const params = {
      page: page,
      per_page: violatorPerPage.value,
      ...violatorFilters.value
    }
    
    const response = await adminAPI.getViolators(params)
    
    if (response.data.status === 'success') {
      violators.value = response.data.data.data
      violatorPaginationData.value = {
        current_page: response.data.data.current_page,
        last_page: response.data.data.last_page,
        per_page: response.data.data.per_page,
        total: response.data.data.total,
        from: response.data.data.from,
        to: response.data.data.to
      }
    }
  } catch (error) {
    console.error('Failed to load violators page:', error)
  } finally {
    loadingViolators.value = false
  }
}

const changeViolatorPerPage = async () => {
  violatorPaginationData.value.current_page = 1
  await goToViolatorPage(1)
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
    
    const getAttemptClass = (count) => {
      if (!count || count <= 1) return 'attempt-first'
      if (count === 2) return 'attempt-second'
      if (count >= 3) return 'attempt-third'
      return ''
    }

    const formatAttempt = (count) => {
      if (!count || count === 0) return "0 Attempt"
      if (count === 1) return "1st Attempt"
      if (count === 2) return "2nd Attempt"
      if (count === 3) return "3rd Attempt"
      return `${count}th Attempt`
    }
     
    
    
    const formatDate = (dateString) => {
      if (!dateString) return null
      return new Date(dateString).toLocaleDateString('en-PH', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      })
    }
    
    const formatCurrency = (amount) => {
  if (amount == null || isNaN(amount)) return "0.00";
  return new Intl.NumberFormat("en-PH", {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(parseFloat(amount));
};

    
    const getInitials = (firstName, lastName) => {
      return `${firstName?.charAt(0) || ''}${lastName?.charAt(0) || ''}`.toUpperCase()
    }
    
    onMounted(() => {
      loadViolators()
    })
    
    return {loading,loadingViolators,saving,activeTab,violators,paginatedViolators,violatorFilters,violatorPaginationData,violatorPerPage,visibleViolatorPages,saveViolator,error,viewViolatorDetails,closeViolatorDetailsModal,showViolatorDetailsModal,selectedViolator,archiveViolator,goToViolatorPage,changeViolatorPerPage,getAttemptClass,formatAttempt,formatDate,formatDateTime,formatCurrency,getInitials,editViolator,showEditViolatorModal,violatorForm,closeEditViolatorModal,savingViolator,
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