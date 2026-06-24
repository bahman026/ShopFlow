<script setup>
import FooterLinkColumn from '@/Components/Footer/FooterLinkColumn.vue';
import FooterContact from '@/Components/Footer/FooterContact.vue';
import FooterSocial from '@/Components/Footer/FooterSocial.vue';
import FooterNewsletter from '@/Components/Footer/FooterNewsletter.vue';
import FooterCopyright from '@/Components/Footer/FooterCopyright.vue';

defineProps({
    about: {
        type: String,
        default: '',
    },
    columns: {
        type: Array,
        default: () => [],
    },
    contact: {
        type: Object,
        default: () => ({}),
    },
    socials: {
        type: Array,
        default: () => [],
    },
    copyright: {
        type: String,
        default: '',
    },
    showNewsletter: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(['subscribe']);
</script>

<template>
    <footer class="mt-12 bg-white">
        <div class="mx-auto max-w-6xl px-4 py-10">
            <p
                v-if="about"
                class="mb-8 max-w-3xl text-sm leading-7 text-gray-600"
            >
                {{ about }}
            </p>

            <div class="grid grid-cols-2 gap-8 md:grid-cols-4">
                <FooterLinkColumn
                    v-for="(column, index) in columns"
                    :key="index"
                    :title="column.title"
                    :links="column.links"
                />

                <FooterContact
                    :title="contact.title"
                    :phone="contact.phone"
                    :email="contact.email"
                    :hours="contact.hours ?? []"
                    :address="contact.address"
                />
            </div>

            <div class="mt-10 grid gap-8 border-t border-gray-200 pt-8 md:grid-cols-2">
                <FooterNewsletter
                    v-if="showNewsletter"
                    @subscribe="emit('subscribe', $event)"
                />

                <FooterSocial :socials="socials" />
            </div>
        </div>

        <FooterCopyright :text="copyright" />
    </footer>
</template>
