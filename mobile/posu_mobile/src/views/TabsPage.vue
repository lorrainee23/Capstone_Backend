<template>
  <ion-page>
    <ion-tabs>
      <ion-router-outlet></ion-router-outlet>
      <ion-tab-bar slot="bottom" class="wave-tab-bar">
        <!-- Left tabs -->
        <ion-tab-button tab="tab1" href="/tabs/tab1" class="tab-button">
          <div class="tab-content">
            <ion-icon :icon="bluetooth" class="tab-icon" />
            <ion-label class="tab-label">Connect</ion-label>
          </div>
        </ion-tab-button>

        <ion-tab-button tab="tab2" href="/tabs/tab2" class="tab-button">
          <div class="tab-content">
            <ion-icon :icon="timeOutline" class="tab-icon" />
            <ion-label class="tab-label">History</ion-label>
          </div>
        </ion-tab-button>

        <!-- Floating Center Button -->
        <ion-tab-button tab="tab3" href="/tabs/tab3" class="floating-button">
          <div class="floating-circle">
            <ion-icon :icon="documentTextOutline" class="floating-icon" />
          </div>
          <ion-label class="floating-label">Record</ion-label>
        </ion-tab-button>

        <!-- Right tabs -->
        <ion-tab-button tab="tab4" href="/tabs/tab4" class="tab-button">
          <div class="tab-content">
            <ion-icon :icon="notificationsOutline" class="tab-icon" />
            <ion-label class="tab-label">Notifications</ion-label>
          </div>
        </ion-tab-button>

        <ion-tab-button tab="tab5" href="/tabs/tab5" class="tab-button">
          <div class="tab-content">
            <ion-icon :icon="person" class="tab-icon" />
            <ion-label class="tab-label">Profile</ion-label>
          </div>
        </ion-tab-button>
      </ion-tab-bar>
    </ion-tabs>
  </ion-page>
</template>

<script setup lang="ts">
import { IonTabBar, IonTabButton, IonTabs, IonIcon, IonPage, IonRouterOutlet, IonLabel } from '@ionic/vue';
import { bluetooth, timeOutline, documentTextOutline, notificationsOutline, person } from 'ionicons/icons';
</script>

<style scoped>
.wave-tab-bar {
  --background: #ffffff;
  --border: none;
  height: 80px;
  padding: 0;
  box-shadow: 0 -4px 20px rgba(59, 130, 246, 0.1);
  border-radius: 25px 25px 0 0;
  position: relative;
  overflow: visible;
}

.tab-button {
  --color: #94A3B8;
  --color-selected: #3B82F6;
  --background-selected: transparent;
  --background: transparent;
  --ripple-color: transparent;
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
}

.tab-button.tab-selected {
  --color: #3B82F6;
}

.tab-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100%;
  padding: 8px 0;
}

.tab-icon {
  font-size: 24px;
  margin-bottom: 4px;
  transition: all 0.3s ease;
}

.tab-button:hover .tab-icon {
  color: #3B82F6;
  transform: translateY(-2px);
}

.tab-label {
  font-size: 12px;
  font-weight: 500;
  margin: 0;
  text-transform: none;
}

.floating-button {
  --color: #ffffff;
  --color-selected: #ffffff;
  --background: transparent;
  --background-selected: transparent;
  --ripple-color: transparent;
  position: relative;
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: flex-end;
  padding-bottom: 8px;
}

.floating-circle {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  background: linear-gradient(135deg, #3B82F6 0%, #1D4ED8 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  position: absolute;
  top: 4px;
  box-shadow: 0 8px 24px rgba(59, 130, 246, 0.3);
  transition: all 0.3s ease;
  border: 3px solid #ffffff;
}

.floating-circle:hover {
  transform: translateY(-3px) scale(1.05);
  box-shadow: 0 12px 32px rgba(59, 130, 246, 0.4);
}

.floating-icon {
  font-size: 28px;
  color: #ffffff;
}

.floating-label {
  font-size: 12px;
  font-weight: 500;
  margin: 0;
  margin-top: 36px;
  color: #3B82F6;
  text-transform: none;
}

.tab-button.tab-selected .tab-icon {
  color: #3B82F6;
  transform: scale(1.1);
}

.tab-button.tab-selected .tab-label {
  color: #3B82F6;
  font-weight: 600;
}

ion-tab-bar {
  border-radius: 0 !important;
  width: 100% !important;
  margin: 0 !important;
}

ion-page {
  --background: linear-gradient(135deg, #F8FAFF 0%, #EBF4FF 100%);
}
</style>