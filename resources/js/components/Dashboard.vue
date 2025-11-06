<template>
    <div class="min-h-screen bg-gray-100 p-6">
        <div class="max-w-4xl mx-auto">
            <!-- Manager Interface -->
            <div v-if="user.role === 'manager'">
                <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
                    <h2 class="text-2xl font-bold mb-6">Create New Task</h2>
                    <form @submit.prevent="createTask" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                            <input
                                v-model="newTask.title"
                                type="text"
                                required
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                                placeholder="Enter task title"
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea
                                v-model="newTask.description"
                                rows="3"
                                required
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                                placeholder="Enter task description"
                            ></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Assign To Employee</label>
                            <select
                                v-model="newTask.assigned_to"
                                required
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                            >
                                <option value="">Select an employee</option>
                                <option v-for="employee in employees" :key="employee.id" :value="employee.id">
                                    {{ employee.name }}
                                </option>
                            </select>
                        </div>
                        <button
                            type="submit"
                            class="w-full bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600 transition-colors"
                        >
                            Create Task
                        </button>
                    </form>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-xl font-semibold mb-4">All Tasks</h3>
                    <div class="space-y-4">
                        <div v-for="task in tasks" :key="task.id" class="border rounded-lg p-4">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="font-medium">{{ task.title }}</h4>
                                    <p class="text-gray-600 mt-1">{{ task.description }}</p>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500">
                                            Assigned to: <span class="font-medium">{{ task.assignee?.name }}</span>
                                        </p>
                                        <p class="text-sm">
                                            Status:
                                            <span :class="{
                                                'px-2 py-1 rounded-full text-xs font-medium': true,
                                                'bg-yellow-100 text-yellow-800': task.status === 'pending',
                                                'bg-blue-100 text-blue-800': task.status === 'in_progress',
                                                'bg-green-100 text-green-800': task.status === 'completed'
                                            }">
                                                {{ task.status }}
                                            </span>
                                        </p>
                                    </div>
                                </div>
                                <div class="flex space-x-2">
                                    <button
                                        @click="deleteTask(task.id)"
                                        class="text-red-600 hover:text-red-800"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Employee Interface -->
            <div v-else-if="user.role === 'employee'">
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h2 class="text-2xl font-bold mb-6">My Tasks</h2>
                    <div class="space-y-4">
                        <div v-for="task in tasks" :key="task.id" class="border rounded-lg p-4">
                            <h4 class="font-medium">{{ task.title }}</h4>
                            <p class="text-gray-600 mt-1">{{ task.description }}</p>
                            <div class="mt-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Update Status</label>
                                <select
                                    v-model="task.status"
                                    @change="updateTaskStatus(task)"
                                    class="w-full sm:w-auto px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                                >
                                    <option value="pending">Pending</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="completed">Completed</option>
                                </select>
                            </div>
                        </div>
                        <div v-if="tasks.length === 0" class="text-center text-gray-500 py-4">
                            No tasks assigned to you yet.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Task Modal -->
        <div v-if="editingTask" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-lg w-full">
                <div class="p-6">
                    <h3 class="text-lg font-medium mb-4">Edit Task</h3>
                    <task-form
                        :task="editingTask"
                        :users="users"
                        @task-updated="handleTaskUpdated"
                        @close="editingTask = null">
                    </task-form>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import TaskForm from './TaskForm.vue';

export default {
    components: {
        TaskForm,
    },
    data() {
        return {
            user: {},             // Current user info
            tasks: [],           // List of tasks
            users: [],          // All users
            employees: [],      // List of employees (for managers)
            newTask: {          // New task form data
                title: '',
                description: '',
                assigned_to: ''
            },
            showCreateTaskForm: false,
            editingTask: null,
        }
    },
    async created() {
        if (!localStorage.getItem('auth_token')) {
            this.$router.push('/');
            return;
        }
        await this.fetchUser();
        await this.fetchTasks();
        if (this.user.role === 'manager') {
            await this.fetchEmployees();
        }
    },
    methods: {
        async fetchUser() {
            try {
                const response = await fetch('/api/user', {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
                        'Accept': 'application/json',
                    },
                });

                if (!response.ok) throw new Error('Failed to fetch user');
                this.user = await response.json();
            } catch (error) {
                console.error('Error fetching user:', error);
                this.logout();
            }
        },
        async fetchTasks() {
            try {
                const response = await fetch('/api/tasks', {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
                        'Accept': 'application/json',
                    },
                });

                if (!response.ok) throw new Error('Failed to fetch tasks');
                const data = await response.json();
                this.tasks = data.tasks || [];
                this.users = data.users || [];
            } catch (error) {
                console.error('Error fetching tasks:', error);
            }
        },
        async fetchEmployees() {
            try {
                const response = await fetch('/api/users', {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
                        'Accept': 'application/json',
                    },
                });

                if (!response.ok) throw new Error('Failed to fetch employees');
                this.employees = await response.json();
            } catch (error) {
                console.error('Error fetching employees:', error);
            }
        },
        logout() {
            localStorage.removeItem('auth_token');
            this.$router.push('/');
        },
        async createTask() {
            try {
                const response = await fetch('/api/tasks', {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(this.newTask),
                });

                if (!response.ok) throw new Error('Failed to create task');

                const task = await response.json();
                this.tasks.unshift(task);

                // Reset form
                this.newTask = {
                    title: '',
                    description: '',
                    assigned_to: ''
                };

                alert('Task created successfully');
            } catch (error) {
                console.error('Error creating task:', error);
                alert('Failed to create task');
            }
        },
        handleTaskCreated(newTask) {
            this.tasks.push(newTask);
            this.showCreateTaskForm = false;
        },
        handleTaskUpdated(updatedTask) {
            const index = this.tasks.findIndex(task => task.id === updatedTask.id);
            if (index !== -1) {
                this.tasks.splice(index, 1, updatedTask);
            }
            this.editingTask = null;
        },
        editTask(task) {
            this.editingTask = { ...task };
        },
        async updateTaskStatus(task) {
            try {
                const response = await fetch(`/api/tasks/${task.id}`, {
                    method: 'PUT',
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ status: task.status }),
                });

                if (!response.ok) throw new Error('Failed to update task status');
                const updatedTask = await response.json();
                this.handleTaskUpdated(updatedTask);
            } catch (error) {
                console.error('Error updating task status:', error);
                // Revert the status change in the UI
                const originalTask = this.tasks.find(t => t.id === task.id);
                if (originalTask) {
                    task.status = originalTask.status;
                }
                alert('Failed to update task status');
            }
        },
        async deleteTask(taskId) {
            if (!confirm('Are you sure you want to delete this task?')) return;

            try {
                const response = await fetch(`/api/tasks/${taskId}`, {
                    method: 'DELETE',
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
                        'Accept': 'application/json',
                    },
                });

                if (!response.ok) throw new Error('Failed to delete task');

                this.tasks = this.tasks.filter(task => task.id !== taskId);
            } catch (error) {
                console.error('Error deleting task:', error);
                alert('Failed to delete task');
            }
        },
    },
};
</script>
