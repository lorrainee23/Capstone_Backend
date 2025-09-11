// Simple offline queue for violation records
// Stores items in localStorage and tries to sync when online

const STORAGE_KEY = "offline_violation_queue";

const readQueue = () => {
	try {
		const raw = localStorage.getItem(STORAGE_KEY);
		return raw ? JSON.parse(raw) : [];
	} catch (e) {
		return [];
	}
};

const writeQueue = (items) => {
	localStorage.setItem(STORAGE_KEY, JSON.stringify(items));
};

export const offlineQueue = {
	getAll() {
		return readQueue();
	},

	getCount() {
		return readQueue().length;
	},

	push(item) {
		const items = readQueue();
		const newItem = { id: Date.now() + Math.random(), createdAt: Date.now(), item };
		items.push(newItem);
		writeQueue(items);
		console.log('Offline queue: Added item', newItem);
		console.log('Offline queue: Total items', items.length);
	},

	removeById(id) {
		const items = readQueue().filter((q) => q.id !== id);
		writeQueue(items);
	},

	/**
	 * Flush queue using provided async sender. Sender receives queued.item
	 */
	async flush(sender) {
		const items = readQueue();
		for (const q of items) {
			try {
				await sender(q.item);
				this.removeById(q.id);
			} catch (e) {
				break;
			}
		}
	},
};


