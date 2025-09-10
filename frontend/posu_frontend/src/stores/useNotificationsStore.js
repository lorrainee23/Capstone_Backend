import { reactive } from 'vue'
import { adminAPI, violatorAPI } from '@/services/api'

const state = reactive({
  notifications: [],
  loading: false,
})

export const useNotificationsStore = () => {
  const fetch = async (role) => {
    state.loading = true
    try {
      let res
      if (['Head', 'Deputy', 'Admin'].includes(role)) {
        res = await adminAPI.getNotifications()
      } else if (role === 'Violator') {
        res = await violatorAPI.getNotifications()
      }

      const data = res.data.data?.data || res.data.data || []
      state.notifications = data.map(n => ({
        id: n.id,
        title: n.title,
        message: n.message,
        read_at: n.read_at,
        created_at: n.created_at,
        read: !!n.read_at,
      }))
    } catch (err) {
      console.error('Fetch notifications error:', err)
    } finally {
      state.loading = false
    }
  }

  const markAsRead = (id) => {
    const notif = state.notifications.find(n => n.id === id)
    if (notif) {
      notif.read = true
      notif.read_at = new Date().toISOString()
    }
  }

  const markAllAsRead = () => {
    state.notifications.forEach(n => {
      n.read = true
      n.read_at = new Date().toISOString()
    })
  }

  const unreadCount = () => state.notifications.filter(n => !n.read).length

  return {
    state,
    fetch,
    markAsRead,
    markAllAsRead,
    unreadCount,
  }
}
