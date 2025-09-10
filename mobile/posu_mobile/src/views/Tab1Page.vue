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
import { ref, onMounted, computed } from 'vue'
import AppHeader from '@/components/AppHeader.vue'
import {
  IonPage,
  IonHeader,
  IonToolbar,
  IonTitle,
  IonContent,
  IonButton,
  IonCard,
  IonCardContent,
  IonIcon,
  IonBadge,
  alertController,
  toastController
} from '@ionic/vue'
import {
  bluetoothOutline,
  checkmarkCircle,
  closeCircle,
  closeOutline,
  refresh,
  print,
  chevronForward,
  documentText,
  receipt,
  pulse
} from 'ionicons/icons'

const permissions = window.cordova?.plugins?.permissions
const bluetoothSerial = window.bluetoothSerial

const devices = ref([])
const selectedDevice = ref('')
const isScanning = ref(false)
const isConnected = ref(false)
const currentTime = ref('')
const currentDate = ref('')

// Update date and time every second
const updateDateTime = () => {
  const now = new Date()
  currentTime.value = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' })
  currentDate.value = now.toLocaleDateString([], { weekday: 'short', month: 'short', day: 'numeric' })
}

const connectionStatus = computed(() => {
  if (isConnected.value && selectedDevice.value) {
    const device = devices.value.find(d => d.id === selectedDevice.value)
    return {
      connected: true,
      message: `Connected to ${device?.name || 'Unknown Device'}`
    }
  }
  return {
    connected: false,
    message: 'No active connection'
  }
})

// Show toast notification
const showToast = async (message, color = 'primary') => {
  const toast = await toastController.create({
    message,
    duration: 2000,
    color,
    position: 'bottom'
  })
  await toast.present()
}

// Show alert dialog
const showAlert = async (header, message) => {
  const alert = await alertController.create({
    header,
    message,
    buttons: ['OK']
  })
  await alert.present()
}

// Format device ID for display
const formatDeviceId = (id) => {
  if (!id) return 'Unknown ID'
  return id.length > 20 ? id.substring(0, 17) + '...' : id
}

// Request runtime permissions for Android 12+
const requestPermissions = async () => {
  if (!permissions) return true

  const perms = [
    permissions.BLUETOOTH_CONNECT,
    permissions.BLUETOOTH_SCAN,
    permissions.ACCESS_FINE_LOCATION
  ]

  return new Promise((resolve, reject) => {
    permissions.hasPermission(perms, (status) => {
      if (status.hasPermission) {
        resolve(true)
      } else {
        permissions.requestPermissions(perms, (status2) => {
          if (status2.hasPermission) resolve(true)
          else reject('Permission denied')
        }, reject)
      }
    }, reject)
  })
}

// Scan paired devices
const listDevices = async () => {
  if (isScanning.value) return
  
  try {
    isScanning.value = true
    await requestPermissions()
    
    if (!bluetoothSerial) {
      await showAlert('Error', 'Bluetooth plugin not available yet')
      return
    }
    
    bluetoothSerial.list(
      (data) => {
        devices.value = data
        isScanning.value = false
        if (data.length === 0) {
          showToast('No paired devices found', 'warning')
        } else {
          showToast(`Found ${data.length} device${data.length > 1 ? 's' : ''}`, 'success')
        }
      },
      (err) => {
        isScanning.value = false
        showAlert('Scan Error', 'Error listing devices: ' + JSON.stringify(err))
      }
    )
  } catch (err) {
    isScanning.value = false
    showAlert('Permission Error', err.toString())
  }
}

// Connect to device
const connect = (address) => {
  if (selectedDevice.value === address && isConnected.value) return
  
  selectedDevice.value = address
  const device = devices.value.find(d => d.id === address)
  
  bluetoothSerial.connect(
    address,
    () => {
      isConnected.value = true
      showToast(`Connected to ${device?.name || 'device'}`, 'success')
    },
    (err) => {
      isConnected.value = false
      selectedDevice.value = ''
      showAlert('Connection Failed', 'Failed to connect: ' + JSON.stringify(err))
    }
  )
}

// Disconnect from current device
const disconnect = () => {
  if (!selectedDevice.value) return
  
  bluetoothSerial.disconnect(
    () => {
      isConnected.value = false
      selectedDevice.value = ''
      showToast('Disconnected successfully', 'medium')
    },
    (err) => {
      showAlert('Disconnect Error', 'Failed to disconnect: ' + JSON.stringify(err))
    }
  )
}

// Check connection status
const checkConnection = () => {
  if (!selectedDevice.value) {
    showToast('No device selected', 'warning')
    return
  }
  
  bluetoothSerial.isConnected(
    () => {
      isConnected.value = true
      showToast('Connection is active', 'success')
    },
    () => {
      isConnected.value = false
      showToast('Connection lost, please reconnect', 'danger')
    }
  )
}

// Print test page
const printTest = () => {
  if (!selectedDevice.value) {
    showToast('Please connect to a printer first', 'warning')
    return
  }
  
  bluetoothSerial.isConnected(
    () => {
      const ESC = '\x1B'
      const INIT = ESC + '@'          
      const FONT_NORMAL = ESC + '!' + '\x00'
      const CENTER = ESC + 'a' + '\x01'
      const LEFT = ESC + 'a' + '\x00'
      
      const text = INIT + CENTER + FONT_NORMAL + 
                  `*** PRINTER TEST ***\n\n` +
                  LEFT +
                  `Date: ${new Date().toLocaleDateString()}\n` +
                  `Time: ${new Date().toLocaleTimeString()}\n` +
                  `Device: ${devices.value.find(d => d.id === selectedDevice.value)?.name || 'Unknown'}\n\n` +
                  `Test completed successfully!\n\n\n`

      bluetoothSerial.write(text,
        () => showToast('Test page printed successfully!', 'success'),
        (err) => showAlert('Print Error', 'Print failed: ' + JSON.stringify(err))
      )
    },
    () => {
      isConnected.value = false
      showToast('Not connected, please reconnect', 'danger')
    }
  )
}

// Print simple receipt
const printSimple = () => {
  if (!selectedDevice.value) {
    showToast('Please connect to a printer first', 'warning')
    return
  }
  
  bluetoothSerial.isConnected(
    () => {
      const text = `Simple Receipt\n` +
                  `----------------\n` +
                  `Item 1: $10.00\n` +
                  `Item 2: $15.50\n` +
                  `----------------\n` +
                  `Total: $25.50\n\n`

      bluetoothSerial.write(text,
        () => showToast('Receipt printed successfully!', 'success'),
        (err) => showAlert('Print Error', 'Print failed: ' + JSON.stringify(err))
      )
    },
    () => {
      isConnected.value = false
      showToast('Not connected, please reconnect', 'danger')
    }
  )
}

onMounted(() => {
  updateDateTime()
  setInterval(updateDateTime, 1000)
  console.log('Page mounted, BluetoothSerial ready:', !!bluetoothSerial)
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