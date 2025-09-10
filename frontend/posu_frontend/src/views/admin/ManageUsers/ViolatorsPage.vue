<template>
<SidebarLayout page-title="Violators Management">
<ViolatorsManagement :currentUserRole="authState.user?.role">

</ViolatorsManagement>
</SidebarLayout>
</template>
<script>
import SidebarLayout from '@/components/SidebarLayout.vue';
import ViolatorsManagement from '@/components/ViolatorsManagement.vue';
import { useAuthStore } from '@/stores/auth';
export default {
	name: 'ViolatorsPage',
	components: {
		SidebarLayout,
		ViolatorsManagement,
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