<template>
  <header class="dashboard-header">
        <div class="header-content">
          <h1>Vehicles Management</h1>
          <p>Manage Vehicle Information</p>
        </div>
        <button class="refresh-btn" @click="loadVehicles" aria-label="Refresh Dashboard">
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
	<div class="admin-vehicles">
	<br><br>
 <div class="filters-card">
  <div class="filters-row">
    <div class="filter-group">
      <label class="form-label">Search Owner Name</label>
      <input 
        v-model="vehicleFilters.owner_name" 
        type="text" 
        class="form-input" 
        placeholder="Search by Owner Name"
      />
    </div>
    <div class="filter-group">
      <label class="form-label">Search Plate Number</label>
      <input 
        v-model="vehicleFilters.plate_number" 
        type="text" 
        class="form-input"
        placeholder="Search by Plate Number"
      />
    </div>
    <div class="filter-group">
      <label class="form-label">Search Make</label>
      <input 
        v-model="vehicleFilters.make" 
        type="text" 
        class="form-input"
        placeholder="Search by Vehicle Make"
      />
    </div>
    <div class="filter-group">
      <label class="form-label">Vehicle Type</label>
      <select v-model="vehicleFilters.vehicle_type" class="form-select">
        <option value="">All</option>
        <option value="Motorcycle">Motorcycle</option>
        <option value="Motor">Motor</option>
        <option value="Van">Van</option>
        <option value="Bus">Bus</option>
        <option value="Truck">Truck</option>
      </select>
    </div>
    <div class="filter-group">
      <label class="form-label">Search Model</label>
      <input 
        v-model="vehicleFilters.model" 
        type="text" 
        class="form-input"
        placeholder="Search by Model"
      />
    </div>
    <div class="filter-group">
      <label class="form-label">Search Color</label>
      <input 
        v-model="vehicleFilters.color" 
        type="text" 
        class="form-input"
        placeholder="Search by Color"
      />
    </div>
  </div>
</div>
</div>
          <!-- Vehicles Table -->
          <div class="table-card">
            <div v-if="loadingVehicles" class="loading">
              <div class="spinner"></div>
            </div>
            
            <div v-else>
              <table class="table">
                <thead>
                  <tr>
                    <th class="vehicle">No.</th>
						<th class="vehicle">Violator</th>
                    <th class="vehicle">Owner Name</th>
                    <th class="vehicle">Plate Number</th>
                    <th class="vehicle">Make & Model</th>
                    <th class="vehicle">Color</th>
                    <th class="vehicle">Vehicle Type</th>
                    <th class="vehicle">Owner Address</th>
                    <th class="vehicle">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="vehicle in paginatedVehicles" :key="vehicle.id">
                  <td class="vehicle">
                    {{ vehicle.id }}
                  </td>
						<td class="vehicle">
                    <div class="owner-info">
                      <div class="owner-avatar">
                        {{ getInitials(vehicle.owner_first_name, vehicle.owner_last_name) }}
                      </div>
                      <div>
                        <div class="owner-name">{{ vehicle.owner_first_name }} {{ vehicle.owner_middle_name }} {{ vehicle.owner_last_name }}</div>
                      </div>
                    </div>
                  </td>
                  <td class="vehicle">
                    <div class="owner-info">
                      <div class="owner-avatar">
                        {{ getInitials(vehicle.violator?.first_name, vehicle.violator?.last_name) }}
                      </div>
                      <div>
                        <div class="owner-name">{{ vehicle.violator?.first_name }} {{ vehicle.violator?.middle_name }} {{ vehicle.violator?.last_name }}</div>
                      </div>
                    </div>
                  </td>
                  <td class="vehicle">
                    <div class="license-plate">
                      {{ vehicle.plate_number }}
                    </div>
                  </td>
                  <td class="vehicle">
                    <div class="vehicle-details">
                      <div class="make-model">{{ vehicle.make }} {{ vehicle.model }}</div>
                    </div>
                  </td>
                  <td class="vehicle">
                    <div class="vehicle-color">
                      {{ vehicle.color }}
                    </div>
                  </td>
                  <td class="vehicle">
                    <div class="vehicle-type">
                      {{ vehicle.vehicle_type }}
                    </div>
                  </td>
                  <td class="vehicle">
                    <div class="address-info">
                      {{ vehicle.owner_barangay }} {{ vehicle.owner_city }}, {{ vehicle.owner_province }}
                    </div>
                  </td>
                  <td class="vehicle">
                    <div class="action-buttons">
                      <button @click="editVehicle(vehicle)" class="btn-icon-sm btn-edit" title="Edit Vehicle">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                          <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                          <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                      </button>
                      <button @click="viewVehicleDetails(vehicle)" class="btn-icon-sm btn-view" title="View Details">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                          <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                          <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                      </button>
                    </div>
                  </td>
                  </tr>
                </tbody>
              </table>

          <!-- Pagination for Vehicles -->
          <div class="pagination-container" v-if="vehiclePaginationData.total > 0">
            <div class="pagination-info">
              Showing
              {{ (vehiclePaginationData.current_page - 1) * vehiclePaginationData.per_page + 1 }}
              to
              {{ Math.min(vehiclePaginationData.current_page * vehiclePaginationData.per_page, vehiclePaginationData.total) }}
              of {{ vehiclePaginationData.total }} entries
            </div>

            <div class="pagination-controls">
              <button
                @click="goToVehiclePage(vehiclePaginationData.current_page - 1)"
                :disabled="vehiclePaginationData.current_page === 1"
                class="pagination-btn"
              >
                Previous
              </button>

              <button
                v-for="page in visibleVehiclePages"
                :key="page"
                @click="goToVehiclePage(page)"
                :class="['pagination-number', { active: page === vehiclePaginationData.current_page }]">
                {{ page }}
              </button>

              <button
                @click="goToVehiclePage(vehiclePaginationData.current_page + 1)"
                :disabled="vehiclePaginationData.current_page === vehiclePaginationData.last_page"
                class="pagination-btn"
              >
                Next
              </button>
            </div>

            <div class="per-page-selector">
              <label>Show:</label>
              <select v-model="vehiclePerPage" @change="changeVehiclePerPage">
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

      <!-- Edit Vehicle Modal -->
<div v-if="showEditVehicleModal" class="modal-overlay" @click="closeEditVehicleModal">
  <div class="modal" @click.stop>
    <div class="modal-header">
      <h3>Edit Vehicle</h3>
      <button @click="closeEditVehicleModal" class="modal-close">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <line x1="18" y1="6" x2="6" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          <line x1="6" y1="6" x2="18" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </button>
    </div>

    <form @submit.prevent="saveVehicle" class="modal-body">

      <!-- Owner Info Section -->
      <h4 class="section-label">Owner Information</h4>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">First Name *</label>
          <input v-model="vehicleForm.owner_first_name" type="text" class="form-input" required />
        </div>
        <div class="form-group">
          <label class="form-label">Last Name *</label>
          <input v-model="vehicleForm.owner_last_name" type="text" class="form-input" required />
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Middle Name</label>
        <input v-model="vehicleForm.owner_middle_name" type="text" class="form-input" />
      </div>

      <!-- Owner Address Section -->
      <h4 class="section-label">Owner Address</h4>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Barangay</label>
          <input v-model="vehicleForm.owner_barangay" type="text" class="form-input" />
        </div>
        <div class="form-group">
          <label class="form-label">City</label>
          <input v-model="vehicleForm.owner_city" type="text" class="form-input" />
        </div>
        <div class="form-group">
          <label class="form-label">Province</label>
          <input v-model="vehicleForm.owner_province" type="text" class="form-input" />
        </div>
      </div>

      <!-- Vehicle Info Section -->
      <h4 class="section-label">Vehicle Information</h4>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Plate Number *</label>
          <input v-model="vehicleForm.plate_number" type="text" class="form-input" required />
        </div>
        <div class="form-group">
          <label class="form-label">Make *</label>
          <input v-model="vehicleForm.make" type="text" class="form-input" required />
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Model *</label>
          <input v-model="vehicleForm.model" type="text" class="form-input" required />
        </div>
        <div class="form-group">
          <label class="form-label">Color *</label>
          <input v-model="vehicleForm.color" type="text" class="form-input" required />
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Vehicle Type *</label>
        <select v-model="vehicleForm.vehicle_type" class="form-select" required>
          <option value="">Select Vehicle Type</option>
          <option value="Motorcycle">Motorcycle</option>
          <option value="Motor">Motor</option>
          <option value="Van">Van</option>
          <option value="Bus">Bus</option>
          <option value="Truck">Truck</option>
        </select>
      </div>
          
      <div class="modal-footer">
        <button @click="closeEditVehicleModal" type="button" class="btn btn-secondary">Cancel</button>
        <button type="submit" class="btn btn-primary" :disabled="savingVehicle">
          <span v-if="savingVehicle" class="spinner-small"></span>
          {{ savingVehicle ? 'Updating...' : 'Update Vehicle' }}
        </button>
      </div>

    </form>
  </div>
</div>

      <!-- Vehicle Details Modal -->
<div v-if="showVehicleDetailsModal" class="modal-overlay" @click="closeVehicleDetailsModal">
  <div class="modal" @click.stop>
    <div class="modal-header">
      <h3>Vehicle Details</h3>
      <button @click="closeVehicleDetailsModal" class="modal-close">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <line x1="18" y1="6" x2="6" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          <line x1="6" y1="6" x2="18" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </button>
    </div>

    <div class="vehicle-details" v-if="selectedVehicle">
		<!-- Owner Info Section -->
      <div class="detail-section">
        <h4>Violator Information</h4>
        <div class="detail-grid">
          <div class="detail-item">
            <label>Full Name</label>
            <span>{{ selectedVehicle.violator?.first_name }} {{ selectedVehicle.violator?.middle_name }} {{ selectedVehicle.violator?.last_name }}</span>
          </div>
          <div class="detail-item">
            <label>Address</label>
            <span>{{ selectedVehicle.violator?.barangay }} {{ selectedVehicle.violator?.city }}, {{ selectedVehicle.violator?.province }}</span>
          </div>
        </div>
      </div>
      <!-- Owner Info Section -->
      <div class="detail-section">
        <h4>Owner Information</h4>
        <div class="detail-grid">
          <div class="detail-item">
            <label>Full Name</label>
            <span>{{ selectedVehicle.owner_first_name }} {{ selectedVehicle.owner_middle_name }} {{ selectedVehicle.owner_last_name }}</span>
          </div>
          <div class="detail-item">
            <label>Address</label>
            <span>{{ selectedVehicle.owner_barangay }} {{ selectedVehicle.owner_city }}, {{ selectedVehicle.owner_province }}</span>
          </div>
        </div>
      </div>

      <!-- Vehicle Info Section -->
      <div class="detail-section">
        <h4>Vehicle Information</h4>
        <div class="detail-grid">
          <div class="detail-item">
            <label>Plate Number</label>
            <span class="license-plate">{{ selectedVehicle.plate_number }}</span>
          </div>
          <div class="detail-item">
            <label>Make</label>
            <span>{{ selectedVehicle.make }}</span>
          </div>
          <div class="detail-item">
            <label>Model</label>
            <span>{{ selectedVehicle.model }}</span>
          </div>
          <div class="detail-item">
            <label>Color</label>
            <span>{{ selectedVehicle.color }}</span>
          </div>
          <div class="detail-item">
            <label>Vehicle Type</label>
            <span>{{ selectedVehicle.vehicle_type }}</span>
          </div>
          <div class="detail-item">
            <label>Date Registered</label>
            <span>{{ formatDate(selectedVehicle.created_at) }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
</template>

<script>
import { ref, onMounted, computed } from 'vue'
import { adminAPI } from '@/services/api'
import Swal from 'sweetalert2'

export default {
  name: 'VehiclesManagement',
  components: {
  },
  setup() {
    const loading = ref(true)
    const loadingVehicles = ref(true)
    const saving = ref(false)
    const vehicles = ref([])
    const showVehicleDetailsModal = ref(false)
    const selectedVehicle = ref(null)
    const showEditVehicleModal = ref(false)
    const editingVehicle = ref(null)

    const vehicleForm = ref({
      id:'',
      owner_first_name: '',
      owner_middle_name: '',
      owner_last_name: '',
      owner_barangay: '',
      owner_city: '',
      owner_province: '',
      plate_number: '',
      make: '',
      model: '',
      color: '',
      vehicle_type: '',
    })

    const savingVehicle = ref(false)

    const error = ref('')
    
    const vehicleFilters = ref({
      owner_name: '',
      plate_number: '',
      make: '',
      vehicle_type: '',
      model: '',
      color: ''
    })
    
    const vehiclePaginationData = ref({
      current_page: 1,
      last_page: 1,
      per_page: 15,
      total: 0,
      from: 0,
      to: 0
    })

    const vehiclePerPage = ref(15)
    
    const visibleVehiclePages = computed(() => {
      const current = vehiclePaginationData.value.current_page
      const last = vehiclePaginationData.value.last_page
      const pages = []

      let start = Math.max(1, current - 2)
      let end = Math.min(last, current + 2)
      if (end - start < 4) {
        if (start === 1) {
          end = Math.min(last, start + 4)
        } else if (end === last) {
          start = Math.max(1, end - 4)
        }
      }

      for (let i = start; i <= end; i++) {
        pages.push(i)
      }

      return pages
    })
    

    const editVehicle = (vehicle) => {
      editingVehicle.value = vehicle

      vehicleForm.value = {
        id: vehicle.id,
        owner_first_name: vehicle.owner_first_name,
        owner_middle_name: vehicle.owner_middle_name,
        owner_last_name: vehicle.owner_last_name,
        owner_barangay: vehicle.owner_barangay,
        owner_city: vehicle.owner_city,
        owner_province: vehicle.owner_province,
        plate_number: vehicle.plate_number,
        make: vehicle.make,
        model: vehicle.model,
        color: vehicle.color,
        vehicle_type: vehicle.vehicle_type,
      }

      showEditVehicleModal.value = true
    }

    const closeEditVehicleModal = () => {
      showEditVehicleModal.value = false
      editingVehicle.value = null
      vehicleForm.value = {
        id:'',
        owner_first_name: '',
        owner_middle_name: '',
        owner_last_name: '',
        owner_barangay: '',
        owner_city: '',
        owner_province: '',
        plate_number: '',
        make: '',
        model: '',
        color: '',
        vehicle_type: '',
      }
    }

    const saveVehicle = async () => {
      savingVehicle.value = true
      try {
        const payload = { ...vehicleForm.value }
        const response = await adminAPI.updateVehicle(editingVehicle.value.id,payload)

        if (response.data.status === 'success') {
          await loadVehicles(vehiclePaginationData.value.current_page)
          closeEditVehicleModal()
          Swal.fire({
            icon: 'success',
            title: 'Updated',
            text: 'Vehicle updated successfully',
            timer: 1500,
            showConfirmButton: true
          })
        }
      } catch (err) {
        console.error(err)
        Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to update vehicle' })
      } finally {
        savingVehicle.value = false
      }
    }
    
    const loadVehicles = async (page = 1) => {
      loadingVehicles.value = true
      try {
        const params = {
          page,
          per_page: vehiclePerPage.value,
          ...vehicleFilters.value
        }
        const response = await adminAPI.getVehicles(params)
        
        if (response.data.status === "success") {
          vehicles.value = response.data.data.data
          vehiclePaginationData.value = {
            current_page: response.data.data.current_page,
            last_page: response.data.data.last_page,
            per_page: response.data.data.per_page,
            total: response.data.data.total,
            from: response.data.data.from,
            to: response.data.data.to
          }
        }
      } catch (err) {
        console.error(err)
      } finally {
        loadingVehicles.value = false
      }
    }

    const filteredVehicles = computed(() => {
      let base = Array.isArray(vehicles.value) ? vehicles.value : []
      let filtered = base.filter(v => !!v)

      if (vehicleFilters.value.owner_name) {
        const search = vehicleFilters.value.owner_name.toLowerCase()
        filtered = filtered.filter(v => {
          const fn = v.owner_first_name?.toLowerCase() || ''
          const ln = v.owner_last_name?.toLowerCase() || ''
          const mn = v.owner_middle_name?.toLowerCase() || ''
          return fn.includes(search) || ln.includes(search) || mn.includes(search)
        })
      }

      if (vehicleFilters.value.plate_number) {
        const plate = vehicleFilters.value.plate_number.toLowerCase()
        filtered = filtered.filter(v => 
          v.plate_number?.toLowerCase().includes(plate)
        )
      }

      if (vehicleFilters.value.make) {
        const make = vehicleFilters.value.make.toLowerCase()
        filtered = filtered.filter(v => 
          v.make?.toLowerCase().includes(make)
        )
      }

      if (vehicleFilters.value.vehicle_type) {
        filtered = filtered.filter(v => v.vehicle_type === vehicleFilters.value.vehicle_type)
      }

      if (vehicleFilters.value.model) {
        const model = vehicleFilters.value.model.toLowerCase()
        filtered = filtered.filter(v => 
          v.model?.toLowerCase().includes(model)
        )
      }

      if (vehicleFilters.value.color) {
        const color = vehicleFilters.value.color.toLowerCase()
        filtered = filtered.filter(v => 
          v.color?.toLowerCase().includes(color)
        )
      }

      return filtered
    })

    const paginatedVehicles = computed(() => {
      return filteredVehicles.value
    })

    const viewVehicleDetails = (vehicle) => {
      selectedVehicle.value = vehicle
      showVehicleDetailsModal.value = true
    }

    const closeVehicleDetailsModal = () => {
      showVehicleDetailsModal.value = false
      selectedVehicle.value = null
    }

    const goToVehiclePage = async (page) => {
      if (page < 1 || page > vehiclePaginationData.value.last_page) return
      
      try {
        loadingVehicles.value = true
        const params = {
          page: page,
          per_page: vehiclePerPage.value,
          ...vehicleFilters.value
        }
        
        const response = await adminAPI.getVehicles(params)
        
        if (response.data.status === 'success') {
          vehicles.value = response.data.data.data
          vehiclePaginationData.value = {
            current_page: response.data.data.current_page,
            last_page: response.data.data.last_page,
            per_page: response.data.data.per_page,
            total: response.data.data.total,
            from: response.data.data.from,
            to: response.data.data.to
          }
        }
      } catch (error) {
        console.error('Failed to load vehicles page:', error)
      } finally {
        loadingVehicles.value = false
      }
    }

    const changeVehiclePerPage = async () => {
      vehiclePaginationData.value.current_page = 1
      await goToVehiclePage(1)
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
      loadVehicles()
    })
    
    return {
      loading,
      loadingVehicles,
      saving,
      vehicles,
      paginatedVehicles,
      vehicleFilters,
      vehiclePaginationData,
      vehiclePerPage,
      visibleVehiclePages,
      saveVehicle,
      error,
      viewVehicleDetails,
      closeVehicleDetailsModal,
      showVehicleDetailsModal,
      selectedVehicle,
      goToVehiclePage,
      changeVehiclePerPage,
      formatDate,
      formatDateTime,
      getInitials,
      editVehicle,
      showEditVehicleModal,
      vehicleForm,
      closeEditVehicleModal,
      savingVehicle,
      loadVehicles
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

.vehicle-details {
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

.section-label {
  font-size: 16px;
  font-weight: 600;
  color: #1f2937;
  margin: 20px 0 10px 0;
  padding-bottom: 8px;
  border-bottom: 1px solid #e5e7eb;
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

.owner-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.owner-avatar {
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

.owner-name {
  font-weight: 600;
  color: #1e293b;
  font-size: 14px;
}

.license-plate {
  background: #cbcbcc;
  color: white;
  padding: 4px 8px;
  border-radius: 4px;
  font-family: 'Courier New', monospace;
  font-weight: 700;
  font-size: 15px;
  letter-spacing: 1px;
}

.vehicle-details {
  display: flex;
  flex-direction: column;
}

.make-model {
  font-weight: 600;
  color: #1e293b;
  font-size: 14px;
}

.vehicle-color {
  font-weight: 500;
  color: #374151;
  font-size: 14px;
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

.address-info {
  font-size: 13px;
  color: #475569;
  max-width: 150px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

/* Table Styles */
.table-card {
  background: white;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  border: 1px solid rgba(0, 0, 0, 0.05);
}

.table {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
}

.table th {
  background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
  padding: 20px 16px;
  text-align: left;
  font-weight: 700;
  color: #1e293b;
  font-size: 13px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  border-bottom: 2px solid #e2e8f0;
}

.table td {
  padding: 20px 16px;
  border-bottom: 1px solid #f1f5f9;
  vertical-align: middle;
}

.table tbody tr:hover {
  background: #f8fafc;
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

.per-page-selector {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  color: #64748b;
  font-weight: 500;
}

.per-page-selector select {
  padding: 6px 10px;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  font-size: 14px;
  background: white;
  color: #475569;
  font-weight: 500;
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
  max-width: 600px;
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
  box-sizing: border-box;
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
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: white;
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
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
  
  .action-buttons {
    flex-direction: column;
    gap: 4px;
  }
}
</style>