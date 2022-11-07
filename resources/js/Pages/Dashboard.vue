<template>
    <app-layout title="Dashboard">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg text-center">
                    <div class="grid">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900">You successfully installed Backend API Code.</h3>
                            <h3></h3>
                            <div v-if="$page.props.storageExist"> </div>
                            <div v-else>
                                <h3 class="text-lg font-medium text-gray-900">Generate storage link. Run below command in terminal or CMD:</h3>
                                <h3 class="text-lg font-medium text-gray-900"><b>php artisan storage:link</b></h3>
                            </div>
                        </div>
                    </div>
                    <div class="ml-4 text-center text-sm text-gray-500 sm:ml-0">
                        <div class="grid">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900">Have you setup flutter web admin panel?</h3>
                                <h3 class="text-lg font-medium text-gray-900"><a href="https://meetmighty.com/codecanyon/document/mighty-delivery/#admin-config" target= "_blank"> <u>Click Here</u></a> For setup flutter web admin</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </app-layout>
</template>

<script>
    import { defineComponent } from 'vue'
    import AppLayout from '@/Layouts/AppLayout.vue'
    // import Welcome from '@/Jetstream/Welcome.vue'

    export default defineComponent({
        components: {
            AppLayout,
            // Welcome,
        },
    })
</script>
