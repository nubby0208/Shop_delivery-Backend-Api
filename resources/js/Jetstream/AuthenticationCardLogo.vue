<template>
    <Link :href="route('home')" :class="'flex justify-center h-16 '">
        <img class="img-fluid rounded-normal" :src="$page.props.appURL+'/images/logo.png'" alt="logo"/>
    </Link>
</template>

<script>
    import { defineComponent } from 'vue'
    import { Link } from '@inertiajs/inertia-vue3';

    export default defineComponent({
        components: {
            Link,
        },
    })
</script>
