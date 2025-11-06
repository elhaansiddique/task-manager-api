<template>
    <div>
        <h3>{{ isEditing ? 'Edit Task' : 'Create Task' }}</h3>
        <form @submit.prevent="submitForm">
            <div>
                <label for="title">Title:</label>
                <input type="text" id="title" v-model="form.title" required>
            </div>
            <div>
                <label for="description">Description:</label>
                <textarea id="description" v-model="form.description"></textarea>
            </div>
            <div>
                <label for="assigned_to">Assigned To:</label>
                <select id="assigned_to" v-model="form.assigned_to" required>
                    <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                </select>
            </div>
            <button type="submit">{{ isEditing ? 'Update' : 'Create' }}</button>
            <button @click="$emit('close')">Cancel</button>
        </form>
    </div>
</template>

<script>
export default {
    props: {
        task: {
            type: Object,
            default: null,
        },
    },
    data() {
        return {
            form: {
                title: '',
                description: '',
                assigned_to: null,
            },
            users: [],
        };
    },
    computed: {
        isEditing() {
            return !!this.task;
        },
    },
    watch: {
        task: {
            handler(newTask) {
                if (newTask) {
                    this.form.title = newTask.title;
                    this.form.description = newTask.description;
                    this.form.assigned_to = newTask.assigned_to;
                }
            },
            immediate: true,
        },
    },
    async created() {
        await this.fetchUsers();
    },
    methods: {
        async fetchUsers() {
            const response = await fetch('/api/users', {
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
                    'Accept': 'application/json',
                },
            });
            this.users = await response.json();
        },
        async submitForm() {
            const method = this.isEditing ? 'PUT' : 'POST';
            const url = this.isEditing ? `/api/tasks/${this.task.id}` : '/api/tasks';

            const response = await fetch(url, {
                method,
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(this.form),
            });

            const data = await response.json();

            if (this.isEditing) {
                this.$emit('task-updated', data);
            } else {
                this.$emit('task-created', data);
            }
        },
    },
};
</script>
