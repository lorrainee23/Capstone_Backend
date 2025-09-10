import { ref, computed } from 'vue'
import { enforcerAPI } from '@/services/api.js'

export function useNotifications() {
  // State
  const notifications = ref([])
  const isLoading = ref(false)
  const isLoadingMore = ref(false)
  const currentPage = ref(1)
  const hasMorePages = ref(true)
  const lastUpdated = ref(null)

  // Filters
  const filters = ref({
    status: 'unread',
    type: null,     
    dateFrom: new Date().toISOString().split('T')[0],
    dateTo: new Date().toISOString().split('T')[0],
    includeDeleted: false
  })

  // Computed
  const unreadCount = computed(() =>
    notifications.value.filter(n => !n.read_at && !n.deleted_at).length
  )

  const totalCount = computed(() =>
    notifications.value.filter(n => !n.deleted_at).length
  )

  const filteredNotifications = computed(() =>
    notifications.value.filter(n => {
      if (!filters.value.includeDeleted && n.deleted_at) return false
      if (filters.value.status === 'unread' && n.read_at) return false
      if (filters.value.status === 'read' && !n.read_at) return false
      if (filters.value.type && n.type !== filters.value.type) return false

      const notificationDate = new Date(n.created_at).toISOString().split('T')[0]
      if (filters.value.dateFrom && notificationDate < filters.value.dateFrom) return false
      if (filters.value.dateTo && notificationDate > filters.value.dateTo) return false

      return true
    })
  )

  // Actions
  const loadNotifications = async (page = 1, append = false) => {
    if (page === 1) isLoading.value = true
    else isLoadingMore.value = true

    try {
      const params = {
        page,
        per_page: 15,
        status: filters.value.status,
        type: filters.value.type,
        date_from: filters.value.dateFrom,
        date_to: filters.value.dateTo,
        include_deleted: filters.value.includeDeleted
      }

      Object.keys(params).forEach(key => {
        if (params[key] === null || params[key] === undefined) delete params[key]
      })

      const response = await enforcerAPI.getNotifications(params)
      const data = response.data.data
      const newNotifications = data.notifications?.data || data.notifications || []

      if (append) notifications.value = [...notifications.value, ...newNotifications]
      else notifications.value = newNotifications

      hasMorePages.value = data.notifications?.next_page_url !== null
      currentPage.value = page
      lastUpdated.value = new Date()
      return { success: true, data: newNotifications }
    } catch (error) {
      console.error('Load notifications error:', error)
      return { success: false, error }
    } finally {
      isLoading.value = false
      isLoadingMore.value = false
    }
  }

  const markAsRead = async (id) => {
    try {
      await enforcerAPI.markNotificationAsRead(id)
      const notif = notifications.value.find(n => n.id === id)
      if (notif) notif.read_at = new Date().toISOString()
      return { success: true }
    } catch (error) {
      console.error(error)
      return { success: false, error }
    }
  }

  const markAllAsRead = async () => {
    try {
      await enforcerAPI.markAllNotificationsAsRead()
      notifications.value.forEach(n => {
        if (!n.read_at && !n.deleted_at) n.read_at = new Date().toISOString()
      })
      return { success: true }
    } catch (error) {
      console.error(error)
      return { success: false, error }
    }
  }

  const hideNotification = async (id) => {
    try {
      await enforcerAPI.hideNotification(id)
      const notif = notifications.value.find(n => n.id === id)
      if (notif) notif.deleted_at = new Date().toISOString()
      return { success: true }
    } catch (error) {
      console.error(error)
      return { success: false, error }
    }
  }

  const restoreNotification = async (id) => {
    try {
      await enforcerAPI.restoreNotification(id)
      const notif = notifications.value.find(n => n.id === id)
      if (notif) notif.deleted_at = null
      return { success: true }
    } catch (error) {
      console.error(error)
      return { success: false, error }
    }
  }


  const setFilter = (key, value) => {
    filters.value[key] = value
    currentPage.value = 1
    hasMorePages.value = true
  }

  const resetFilters = () => {
    filters.value = {
      status: 'unread',
      type: null,
      dateFrom: new Date().toISOString().split('T')[0],
      dateTo: new Date().toISOString().split('T')[0],
      includeDeleted: false
    }
    currentPage.value = 1
    hasMorePages.value = true
  }

  const refreshNotifications = async () => loadNotifications(1, false)
  const loadMore = async () => {
    if (!hasMorePages.value || isLoadingMore.value) return
    return await loadNotifications(currentPage.value + 1, true)
  }

  return {
    notifications,
    isLoading,
    isLoadingMore,
    currentPage,
    hasMorePages,
    lastUpdated,
    filters,
    unreadCount,
    totalCount,
    filteredNotifications,
    loadNotifications,
    markAsRead,
    markAllAsRead,
    hideNotification,
    restoreNotification,
    setFilter,
    resetFilters,
    refreshNotifications,
    loadMore
  }
}
