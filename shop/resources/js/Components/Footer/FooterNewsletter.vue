<script setup>
import { ref } from 'vue';
import BaseButton from '@/Components/BaseButton.vue';

defineProps({
    title: {
        type: String,
        default: 'خبرنامه',
    },
    description: {
        type: String,
        default: 'برای دریافت جدیدترین‌ها ایمیلتان را وارد کنید',
    },
});

const emit = defineEmits(['subscribe']);

const email = ref('');

const submit = () => {
    if (email.value.trim() === '') {
        return;
    }

    emit('subscribe', email.value);
    email.value = '';
};
</script>

<template>
    <div>
        <p class="mb-2 font-bold text-gray-800">{{ title }}</p>
        <p class="mb-3 text-sm text-gray-600">{{ description }}</p>

        <form
            class="flex gap-2"
            @submit.prevent="submit"
        >
            <input
                v-model="email"
                type="email"
                required
                placeholder="آدرس ایمیل"
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-brand focus:outline-none"
            >
            <BaseButton type="submit">
                ثبت
            </BaseButton>
        </form>
    </div>
</template>
