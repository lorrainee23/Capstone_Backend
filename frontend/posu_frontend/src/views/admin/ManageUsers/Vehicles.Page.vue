<template>
<SidebarLayout page-title="Vehicles Management">
<VehiclesManagement :currentUserRole="authState.user?.role">

</VehiclesManagement>
</SidebarLayout>
</template>
<script>
import SidebarLayout from '@/components/SidebarLayout.vue';
import VehiclesManagement from '@/components/VehiclesManagement.vue';
import { useAuthStore } from '@/stores/auth';
export default {
	name: 'VehiclesPage',
	components: {
		SidebarLayout,
		VehiclesManagement,
	},
	setup() {
		const authStore = useAuthStore()

		authStore.initAuth()

		return {
			authState: authStore.state
		}
	}
}
</script>