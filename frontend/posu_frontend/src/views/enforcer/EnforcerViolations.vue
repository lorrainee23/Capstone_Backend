<template>
    <SidebarLayout page-title="Record Violations">
        <div class="enforcer-violations">
            <!-- Header -->
            <div class="page-header">
                <div class="header-left">
                    <h2>Record New Violation</h2>
                    <p>Issue traffic violations and manage violator records</p>
                </div>
            </div>

            <!-- Violation Form -->
            <div class="violation-form-card">
                <div class="card-header">
                    <h3>Violation Details</h3>
                </div>
                <div class="card-body">
                    <form
                        @submit.prevent="recordViolation"
                        class="violation-form"
                    >
                        <div v-if="error" class="alert alert-error">
                            {{ error }}
                        </div>
                        <div v-if="success" class="alert alert-success">
                            {{ success }}
                        </div>

                        <!-- Violator Information -->
                        <div class="form-section">
                            <h4>Violator Information</h4>
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label"
                                        >First Name *</label
                                    >
                                    <input
                                        v-model="violationForm.first_name"
                                        type="text"
                                        class="form-input"
                                        placeholder="Enter first name..."
                                        required
                                    />
                                </div>
                                <div class="form-group">
                                    <label class="form-label"
                                        >Middle Name</label
                                    >
                                    <input
                                        v-model="violationForm.middle_name"
                                        type="text"
                                        class="form-input"
                                        placeholder="Enter middle name..."
                                    />
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label"
                                        >Last Name *</label
                                    >
                                    <input
                                        v-model="violationForm.last_name"
                                        type="text"
                                        class="form-input"
                                        placeholder="Enter last name..."
                                        required
                                    />
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Email</label>
                                    <input
                                        v-model="violationForm.email"
                                        type="email"
                                        class="form-input"
                                        placeholder="Enter email address..."
                                    />
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label"
                                        >Mobile Number *</label
                                    >
                                    <input
                                        v-model="violationForm.mobile_number"
                                        type="text"
                                        class="form-input"
                                        placeholder="09XXXXXXXXX"
                                        maxlength="11"
                                        required
                                    />
                                </div>
                                <div class="form-group">
                                    <label class="form-label"
                                        >License Number *</label
                                    >
                                    <input
                                        v-model="violationForm.license_number"
                                        type="text"
                                        class="form-input"
                                        placeholder="Enter license number..."
                                        maxlength="16"
                                        required
                                    />
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label"
                                        >Plate Number *</label
                                    >
                                    <input
                                        v-model="violationForm.plate_number"
                                        type="text"
                                        class="form-input"
                                        placeholder="ABC-1234"
                                        maxlength="8"
                                        required
                                    />
                                </div>
                                <div class="form-group">
                                    <label class="form-label"
                                        >Vehicle Model *</label
                                    >
                                    <input
                                        v-model="violationForm.model"
                                        type="text"
                                        class="form-input"
                                        placeholder="Ex: CLICK125"
                                        required
                                    />
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Address *</label>
                                    <input
                                        v-model="violationForm.address"
                                        type="text"
                                        class="form-input"
                                        placeholder="Enter violator's address..."
                                        required
                                    />
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Gender *</label>
                                    <select
                                        v-model.number="violationForm.gender"
                                        class="form-select"
                                        required
                                    >
                                        <option value="">Select Gender</option>
                                        <option :value="1">Male</option>
                                        <option :value="0">Female</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Violation Details -->
                        <div class="form-section">
                            <h4>Violation Information</h4>
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label"
                                        >Violation Type *</label
                                    >
                                    <select
                                        v-model="violationForm.violations_id"
                                        class="form-select"
                                        required
                                    >
                                        <option value="">
                                            Select Violation
                                        </option>
                                        <option
                                            v-for="violation in violationTypes"
                                            :key="violation.id"
                                            :value="violation.id"
                                        >
                                            {{ violation.name }} - ₱{{
                                                formatCurrency(
                                                    violation.fine_amount
                                                )
                                            }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Vehicle Type moved here instead of Date & Time -->
                                <div class="form-group">
                                    <label class="form-label"
                                        >Vehicle Type *</label
                                    >
                                    <select
                                        v-model="violationForm.vehicle_type"
                                        class="form-select"
                                        required
                                    >
                                        <option value="">
                                            Select Vehicle Type
                                        </option>
                                        <option value="Motor">Motor</option>
                                        <option value="Van">Van</option>
                                        <option value="Motorcycle">
                                            Motorcycle
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Details -->
                        <div class="form-section">
                            <h4>Additional Information</h4>
                            <div class="form-group">
                                <label class="form-label">Location *</label>
                                <input
                                    v-model="violationForm.location"
                                    type="text"
                                    class="form-input"
                                    placeholder="Enter violation location..."
                                    required
                                />
                            </div>

                            <!-- Replace Remarks with ID Photo -->
                            <div class="form-group">
                                <label class="form-label"
                                    >Upload ID Photo</label
                                >
                                <input
                                    type="file"
                                    class="form-input"
                                    accept="image/*"
                                    @change="handleFileUpload"
                                />
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="form-actions">
                            <button
                                @click="resetForm"
                                type="button"
                                class="btn btn-secondary"
                            >
                                Reset Form
                            </button>
                            <button
                                type="submit"
                                class="btn btn-primary"
                                :disabled="recording"
                            >
                                <span
                                    v-if="recording"
                                    class="spinner-small"
                                ></span>
                                {{
                                    recording
                                        ? "Recording..."
                                        : "Record Violation"
                                }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Recent Violations -->
            <div class="recent-violations">
                <div class="card-header">
                    <h3>Recent Violations</h3>
                    <router-link
                        to="/enforcer/transactions"
                        class="btn btn-primary btn-sm"
                    >
                        View All Transactions
                    </router-link>
                </div>
                <div class="violations-list">
                    <div v-if="recentViolations.length === 0" class="no-data">
                        <div class="no-data-icon">📋</div>
                        <p>No violations recorded yet</p>
                    </div>
                    <div v-else>
                        <div
                            v-for="violation in recentViolations"
                            :key="violation.id"
                            class="violation-item"
                        >
                            <div class="violation-info">
                                <div class="violation-header-row">
                                    <div class="left">
                                        <span class="field-label"
                                            >Violation:</span
                                        >
                                        <span class="field-value">{{
                                            violation.violation?.name
                                        }}</span>
                                    </div>
                                    <div class="right">
                                        <span class="field-value amount"
                                            >₱{{
                                                formatCurrency(
                                                    violation.fine_amount
                                                )
                                            }}</span
                                        >
                                        <span
                                            class="status-badge"
                                            :class="`status-${violation.status?.toLowerCase()}`"
                                        >
                                            {{ violation.status }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Violator -->
                                <div class="violation-row">
                                    <span class="field-label">Violator:</span>
                                    <span class="field-value">
                                        {{ violation.violator?.first_name }}
                                        {{ violation.violator?.last_name }}
                                    </span>
                                </div>

                                <!-- Location -->
                                <div class="violation-row">
                                    <span class="field-label">Location:</span>
                                    <span class="field-value">{{
                                        violation.location
                                    }}</span>
                                </div>

                                <!-- Date & Time -->
                                <div class="violation-row">
                                    <span class="field-label"
                                        >Date & Time:</span
                                    >
                                    <span class="field-value">{{
                                        formatDateTime(violation.date_time)
                                    }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </SidebarLayout>
</template>

<script>
import { ref, onMounted } from "vue";
import SidebarLayout from "@/components/SidebarLayout.vue";
import { enforcerAPI } from "@/services/api";
import Swal from "sweetalert2";

export default {
    name: "EnforcerViolations",
    components: { SidebarLayout },
    setup() {
        const recording = ref(false);
        const error = ref("");
        const success = ref("");
        const violationTypes = ref([]);
        const recentViolations = ref([]);

        const violationForm = ref({
            first_name: "",
            middle_name: "",
            last_name: "",
            email: "",
            mobile_number: "",
            license_number: "",
            plate_number: "",
            model: "",
            address: "",
            gender: "",
            violations_id: "",
            location: "",
            vehicle_type: "",
        });

        const idPhoto = ref(null);

        const handleFileUpload = (event) => {
            idPhoto.value = event.target.files[0];
        };

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

        const loadRecentViolations = async () => {
            try {
                const { data } = await enforcerAPI.getTransactions();
                if (data.status === "success") {
                    const violations = data.data?.data ?? [];
                    recentViolations.value = violations.slice(0, 5);
                }
            } catch (error) {
                console.error("Failed to load recent violations:", error);
            }
        };

        const recordViolation = async () => {
            try {
                recording.value = true;
                error.value = "";
                success.value = "";

                const formData = new FormData();

                for (const key in violationForm.value) {
                    formData.append(key, violationForm.value[key]);
                }

                if (idPhoto.value) {
                    formData.append("id_photo", idPhoto.value);
                }
                console.log("Uploading:", idPhoto.value);
                console.log(
                    "Type:",
                    idPhoto.value.type,
                    "Size:",
                    idPhoto.value.size
                );
                const response = await enforcerAPI.recordViolation(formData);

                if (response.data.success) {
                    Swal.fire({
                        icon: "success",
                        title: "Success!",
                        text: "Violation recorded successfully!",
                        confirmButtonColor: "#3085d6",
                    });
                    resetForm();
                    await loadRecentViolations();
                } else {
                    error.value =
                        response.data.message || "Failed to record violation";
                }
            } catch (err) {
                console.error("Validation Errors:", err.response?.data?.errors);
                error.value =
                    err.response?.data?.message || "Failed to record violation";
            } finally {
                recording.value = false;
            }
        };

        const resetForm = () => {
            violationForm.value = {
                first_name: "",
                middle_name: "",
                last_name: "",
                email: "",
                mobile_number: "",
                license_number: "",
                plate_number: "",
                model: "",
                address: "",
                gender: "",
                violations_id: "",
                location: "",
                vehicle_type: "",
                id_photo: null,
            };
            error.value = "";
            success.value = "";
        };

        const formatCurrency = (amount) => {
            return new Intl.NumberFormat("en-PH").format(amount);
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

        const setDefaultDateTime = () => {
            const now = new Date();
            const localDateTime = new Date(
                now.getTime() - now.getTimezoneOffset() * 60000
            );
            violationForm.value.date_time = localDateTime
                .toISOString()
                .slice(0, 16);
        };

        onMounted(() => {
            loadViolationTypes();
            loadRecentViolations();
            setDefaultDateTime();
        });

        return {
            recording,
            error,
            success,
            violationTypes,
            recentViolations,
            violationForm,
            recordViolation,
            resetForm,
            formatCurrency,
            formatDateTime,
            handleFileUpload,
        };
    },
};
</script>

<style scoped>
.enforcer-violations {
    max-width: 1200px;
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

.violation-form-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    margin-bottom: 32px;
    overflow: hidden;
}

.card-header {
    padding: 24px;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-header h3 {
    font-size: 18px;
    font-weight: 600;
    color: #1f2937;
    margin: 0;
}

.card-body {
    padding: 24px;
}

.form-section {
    margin-bottom: 32px;
    padding-bottom: 24px;
    border-bottom: 1px solid #f3f4f6;
}

.form-section:last-of-type {
    border-bottom: none;
}

.form-section h4 {
    font-size: 16px;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 16px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.search-container {
    position: relative;
}

.search-results {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #e5e7eb;
    border-top: none;
    border-radius: 0 0 8px 8px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    z-index: 10;
    max-height: 200px;
    overflow-y: auto;
}

.search-result-item {
    padding: 12px 16px;
    cursor: pointer;
    border-bottom: 1px solid #f3f4f6;
    transition: background-color 0.2s ease;
}

.search-result-item:hover {
    background: #f9fafb;
}

.search-result-item:last-child {
    border-bottom: none;
}

.violator-info .violator-name {
    font-weight: 500;
    color: #1f2937;
}

.violator-info .violator-details {
    font-size: 12px;
    color: #6b7280;
}

.selected-violator {
    margin-top: 16px;
}

.violator-card {
    background: #f9fafb;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    padding: 16px;
    display: flex;
    align-items: center;
    gap: 16px;
    position: relative;
}

.violator-avatar {
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, #1e3a8a, #3b82f6);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 16px;
}

.violator-card .violator-details {
    flex: 1;
}

.violator-card .violator-name {
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 4px;
}

.violator-card .violator-info {
    font-size: 14px;
    color: #6b7280;
    margin-bottom: 2px;
}

.violator-card .violator-address {
    font-size: 12px;
    color: #9ca3af;
}

.btn-clear {
    position: absolute;
    top: 8px;
    right: 8px;
    background: #fee2e2;
    color: #dc2626;
    border: none;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    transition: background-color 0.2s ease;
}

.btn-clear:hover {
    background: #fecaca;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 24px;
    padding-top: 24px;
    border-top: 1px solid #e5e7eb;
}

.recent-violations {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.violations-list {
    padding: 24px;
}

.violation-item {
    padding: 16px 0;
    border-bottom: 1px solid #f3f4f6;
}

.violation-item:last-child {
    border-bottom: none;
}

.violation-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 8px;
}

.violation-type {
    font-weight: 600;
    color: #1f2937;
    flex: 1;
}

.violation-amount {
    font-weight: 600;
    color: #059669;
}

.violation-details .violator-name {
    font-weight: 500;
    color: #1f2937;
    margin-bottom: 4px;
}

.violation-meta {
    font-size: 14px;
    color: #6b7280;
}

.btn-sm {
    padding: 8px 16px;
    font-size: 14px;
}

.no-data {
    text-align: center;
    padding: 40px;
}

.no-data-icon {
    font-size: 48px;
    margin-bottom: 16px;
}

.no-data p {
    color: #6b7280;
    margin: 0;
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
.violation-header-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.violation-header-row .left {
    display: flex;
    gap: 6px;
    align-items: center;
}

.violation-header-row .right {
    display: flex;
    gap: 10px;
    align-items: center;
}

.field-label {
    font-weight: bold;
    color: #374151;
    min-width: 90px;
}

.field-value {
    font-weight: bold;
    color: #4b5563;
}

.amount {
    font-weight: 600;
    color: #059669;
}

.status-badge {
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 8px;
    font-size: 12px;
    text-transform: capitalize;
}

.status-paid {
    background: #d1fae5;
    color: #065f46;
}

.status-pending {
    background: #fef3c7;
    color: #92400e;
}

.status-unpaid {
    background: #fee2e2;
    color: #991b1b;
}

.violation-row {
    display: flex;
    gap: 8px;
    margin-bottom: 4px;
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }

    .form-actions {
        flex-direction: column;
    }

    .violation-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }
}
</style>