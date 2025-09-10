<template>
  <ion-header class="ion-no-border">
    <ion-toolbar class="custom-header">
      <ion-title class="header-title">
        <div class="title-content">
          <span>{{ title }}</span>
        </div>
      </ion-title>
      <div slot="end" class="datetime-display">
        <div class="time">{{ currentTime }}</div>
        <div class="date">{{ currentDate }}</div>
      </div>
    </ion-toolbar>
  </ion-header>
</template>

<script setup>
import { IonHeader, IonToolbar, IonTitle } from "@ionic/vue"
import { ref, onMounted } from "vue"

defineProps({
  title: { type: String, required: true }
})

const currentTime = ref("")
const currentDate = ref("")

const updateDateTime = () => {
  const now = new Date()
  currentTime.value = now.toLocaleTimeString([], {
    hour: "2-digit",
    minute: "2-digit",
    second: "2-digit",
  })
  currentDate.value = now.toLocaleDateString([], {
    weekday: "short",
    month: "short",
    day: "numeric",
  })
}

onMounted(() => {
  updateDateTime()
  setInterval(updateDateTime, 1000)
})
</script>

<style scoped>
/* Header Styles - Optimized for POCO M6 PRO */
.custom-header {
  --background: linear-gradient(135deg, #1e40af, #3b82f6);
  --color: white;
  box-shadow: 0 2px 16px rgba(30, 64, 175, 0.15);
  --min-height: 80px;
  --padding-top: 35px;
  --padding-bottom: 15px;
}

.datetime-display {
  text-align: right;
  color: white;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  margin-right: 12px;
}

.time {
  font-size: 1.1rem;
  font-weight: 600;
  margin-bottom: 2px;
}

.date {
  font-size: 0.75rem;
  opacity: 0.9;
}

.header-title {
  font-weight: 600;
  color: white;
}

.title-content {
  display: flex;
  align-items: center;
  gap: 8px;
  color: white;
}
</style>