<template>
  <ion-page>
    <AppHeader title="POSU Enforcer"/>
    <ion-content :fullscreen="true" class="ion-padding custom-content">
      <ion-header collapse="condense">
        <ion-toolbar>
          <ion-title size="large">Printer Manager</ion-title>
        </ion-toolbar>
      </ion-header>

      <!-- Connection Status Card -->
      <ion-card class="status-card">
        <ion-card-content>
          <div class="status-content">
            <div class="status-info">
              <ion-icon 
                :icon="connectionStatus.connected ? checkmarkCircle : closeCircle" 
                :class="['status-icon', connectionStatus.connected ? 'connected' : 'disconnected']">
              </ion-icon>
              <div>
                <h3>{{ connectionStatus.connected ? 'Connected' : 'Disconnected' }}</h3>
                <p>{{ connectionStatus.message }}</p>
              </div>
            </div>
            <ion-button 
              v-if="connectionStatus.connected" 
              fill="clear" 
              @click="disconnect"
              class="disconnect-btn"
            >
              <ion-icon :icon="closeOutline"></ion-icon>
            </ion-button>
          </div>
        </ion-card-content>
      </ion-card>

      <!-- Scanner Section -->
      <div class="section-header">
        <h2>Available Devices</h2>
        <ion-button 
          fill="clear" 
          @click="listDevices" 
          :disabled="isScanning"
          class="scan-btn"
        >
          <ion-icon 
            :icon="refresh" 
            :class="{ 'rotating': isScanning }"
          ></ion-icon>
          {{ isScanning ? 'Scanning...' : 'Scan' }}
        </ion-button>
      </div>

      <!-- Devices List -->
      <div v-if="devices.length > 0" class="devices-container">
        <ion-card 
          v-for="device in devices" 
          :key="device.id" 
          class="device-card"
          :class="{ 'selected': selectedDevice === device.id }"
          button
          @click="connect(device.id)"
        >
          <ion-card-content>
            <div class="device-content">
              <div class="device-info">
                <ion-icon :icon="print" class="device-icon"></ion-icon>
                <div>
                  <h3>{{ device.name || 'Unknown Device' }}</h3>
                  <p>{{ formatDeviceId(device.id) }}</p>
                </div>
              </div>
              <div class="device-actions">
                <ion-badge 
                  v-if="selectedDevice === device.id && connectionStatus.connected"
                  color="success"
                >
                  Connected
                </ion-badge>
                <ion-icon 
                  :icon="chevronForward" 
                  class="connect-icon"
                ></ion-icon>
              </div>
            </div>
          </ion-card-content>
        </ion-card>
      </div>

      <!-- Empty State -->
      <div v-else-if="!isScanning" class="empty-state">
        <ion-icon :icon="bluetoothOutline" class="empty-icon"></ion-icon>
        <h3>No Devices Found</h3>
        <p>Tap scan to search for paired Bluetooth devices</p>
      </div>

      <!-- Print Actions -->
      <div class="actions-section" v-if="connectionStatus.connected">
        <h2>Print Options</h2>
        <div class="action-buttons">
          <ion-button 
            expand="block" 
            color="primary" 
            @click="printTest"
            class="action-btn"
          >
            <ion-icon :icon="documentText" slot="start"></ion-icon>
            Print Test Page
          </ion-button>
          
          <ion-button 
            expand="block" 
            color="secondary" 
            @click="printSimple"
            class="action-btn"
          >
            <ion-icon :icon="receipt" slot="start"></ion-icon>
            Print Simple Receipt
          </ion-button>
          
          <ion-button 
            expand="block" 
            fill="outline" 
            color="medium" 
            @click="checkConnection"
            class="action-btn"
          >
            <ion-icon :icon="pulse" slot="start"></ion-icon>
            Test Connection
          </ion-button>
        </div>
      </div>
    </ion-content>
  </ion-page>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import AppHeader from '@/components/AppHeader.vue'
import { bluetoothService } from '@/services/bluetooth.js'
import {
  IonPage, IonHeader, IonToolbar, IonTitle, IonContent,
  IonButton, IonCard, IonCardContent, IonIcon, IonBadge,
  toastController, alertController
} from '@ionic/vue'
import {
  bluetoothOutline, checkmarkCircle, closeCircle, closeOutline,
  refresh, print, chevronForward, documentText, receipt, pulse
} from 'ionicons/icons'

const devices = ref([])
const selectedDevice = ref('')
const isConnected = ref(false)
const isScanning = ref(false)

// Date & Time
const currentTime = ref('')
const currentDate = ref('')
const updateDateTime = () => {
  const now = new Date()
  currentTime.value = now.toLocaleTimeString()
  currentDate.value = now.toLocaleDateString()
}

// Computed connection status
const connectionStatus = computed(() => {
  if (isConnected.value && selectedDevice.value) {
    const device = devices.value.find(d => d.id === selectedDevice.value)
    return { connected: true, message: `Connected to ${device?.name || 'Unknown Device'}` }
  }
  return { connected: false, message: 'No active connection' }
})

// Toast & Alert helpers
const showToast = async (msg, color='primary') => {
  const toast = await toastController.create({ message: msg, duration: 2000, color, position:'bottom' })
  await toast.present()
}

const showAlert = async (header, msg) => {
  const alert = await alertController.create({ header, message: msg, buttons:['OK'] })
  await alert.present()
}

// Format device ID
const formatDeviceId = id => id?.length > 20 ? id.substring(0,17)+'...' : id

// Actions
const listDevices = async () => {
  if (isScanning.value) return
  isScanning.value = true
  try {
    await bluetoothService.listDevices()
    devices.value = [...bluetoothService.devices]
    showToast(devices.value.length ? `Found ${devices.value.length} device(s)` : 'No paired devices', devices.value.length ? 'success' : 'warning')
  } catch (err) {
    showAlert('Scan Error', JSON.stringify(err))
  } finally { isScanning.value = false }
}

const connect = async (address) => {
  try {
    await bluetoothService.connect(address)
    selectedDevice.value = address
    isConnected.value = true
    showToast('Connected successfully', 'success')
  } catch (err) {
    selectedDevice.value = ''
    isConnected.value = false
    showAlert('Connection Error', JSON.stringify(err))
  }
}

const disconnect = async () => {
  try {
    await bluetoothService.disconnect()
    selectedDevice.value = ''
    isConnected.value = false
    showToast('Disconnected', 'medium')
  } catch (err) {
    showAlert('Disconnect Error', JSON.stringify(err))
  }
}

const checkConnection = async () => {
  const status = await bluetoothService.checkConnection()
  isConnected.value = status
  showToast(status ? 'Connection active' : 'Connection lost', status ? 'success' : 'danger')
}

const printTest = async () => {
  if (!isConnected.value) return showToast('Connect to printer first','warning')

  const ESC = '\x1B'
  const INIT = ESC+'@'
  const CENTER = ESC+'a'+'\x01'
  const LEFT = ESC+'a'+'\x00'
  const FONT_NORMAL = ESC+'!'+ '\x00'
  const text = INIT + CENTER + FONT_NORMAL +
               '*** PRINTER TEST ***\n\n' +
               LEFT + `Date: ${currentDate.value}\nTime: ${currentTime.value}\n\nTest completed successfully!\n\n\n`

  try { await bluetoothService.printText(text); showToast('Test printed', 'success') }
  catch(err){ showAlert('Print Error', JSON.stringify(err)) }
}

const printSimple = async () => {
  if (!isConnected.value) return showToast('Connect to printer first','warning')
  const text = `Simple Receipt\n----------------\nItem 1: $10.00\nItem 2: $15.50\n----------------\nTotal: $25.50\n\n`
  try { await bluetoothService.printText(text); showToast('Receipt printed', 'success') }
  catch(err){ showAlert('Print Error', JSON.stringify(err)) }
}

onMounted(() => {
  updateDateTime()
  setInterval(updateDateTime, 1000)
})
</script>

<style scoped>

/* Content Styles */
.custom-content {
  --background: linear-gradient(135deg, #f8fafc, #e2e8f0);
}

.status-card {
  margin-bottom: 24px;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.status-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.status-info {
  display: flex;
  align-items: center;
  gap: 16px;
}

.status-icon {
  font-size: 2rem;
}

.status-icon.connected {
  color: var(--ion-color-success);
}

.status-icon.disconnected {
  color: var(--ion-color-medium);
}

.status-info h3 {
  margin: 0;
  font-size: 1.1rem;
  font-weight: 600;
}

.status-info p {
  margin: 4px 0 0 0;
  color: var(--ion-color-medium);
  font-size: 0.9rem;
}

.disconnect-btn {
  --color: var(--ion-color-danger);
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin: 24px 0 16px 0;
}

.section-header h2 {
  margin: 0;
  font-size: 1.3rem;
  font-weight: 600;
  color: #1e40af;
}

.scan-btn {
  --color: #1e40af;
  --background: rgba(30, 64, 175, 0.1);
  --background-activated: rgba(30, 64, 175, 0.2);
  backdrop-filter: blur(10px);
  border-radius: 8px;
}

.rotating {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.devices-container {
  margin-bottom: 32px;
}

.device-card {
  margin-bottom: 12px;
  transition: all 0.3s ease;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.device-card.selected {
  background: rgba(30, 64, 175, 0.1);
  border: 2px solid rgba(30, 64, 175, 0.8);
  box-shadow: 0 4px 20px rgba(30, 64, 175, 0.2);
}

.device-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.device-info {
  display: flex;
  align-items: center;
  gap: 16px;
  flex: 1;
}

.device-icon {
  font-size: 1.5rem;
  color: #1e40af;
}

.device-info h3 {
  margin: 0;
  font-size: 1rem;
  font-weight: 600;
}

.device-info p {
  margin: 4px 0 0 0;
  color: var(--ion-color-medium);
  font-size: 0.85rem;
  font-family: monospace;
}

.device-actions {
  display: flex;
  align-items: center;
  gap: 12px;
}

.connect-icon {
  color: var(--ion-color-medium);
  font-size: 1.2rem;
}

.empty-state {
  text-align: center;
  padding: 48px 16px;
  color: #1e40af;
  background: rgba(255, 255, 255, 0.8);
  border-radius: 16px;
  backdrop-filter: blur(10px);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
}

.empty-icon {
  font-size: 4rem;
  margin-bottom: 16px;
  color: #1e40af;
  opacity: 0.7;
}

.empty-state h3 {
  margin: 0 0 8px 0;
  font-weight: 600;
  color: #1e40af;
}

.empty-state p {
  margin: 0;
  font-size: 0.9rem;
  color: #64748b;
}

.actions-section {
  margin-top: 32px;
  padding-top: 24px;
  border-top: 1px solid rgba(30, 64, 175, 0.2);
}

.actions-section h2 {
  margin: 0 0 20px 0;
  font-size: 1.3rem;
  font-weight: 600;
  color: #1e40af;
}

.action-buttons {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.action-btn {
  height: 56px;
  font-weight: 600;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}
</style>