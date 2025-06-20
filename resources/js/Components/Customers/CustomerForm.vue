<template>
    <form @submit.prevent="submit" class="space-y-6">
        <div>
            <InputLabel for="name" value="Customer Name" />
            <TextInput
                id="name"
                type="text"
                class="mt-1 block w-full"
                v-model="form.name"
                required
                autofocus
            />
            <InputError class="mt-2" :message="form.errors.name" />
        </div>

        <div>
            <InputLabel for="email" value="Email" />
            <TextInput
                id="email"
                type="email"
                class="mt-1 block w-full"
                v-model="form.email"
            />
            <InputError class="mt-2" :message="form.errors.email" />
        </div>

        <div>
            <InputLabel for="phone" value="Phone Number" />
            <TextInput
                id="phone"
                type="tel"
                class="mt-1 block w-full"
                v-model="form.phone"
                required
            />
            <InputError class="mt-2" :message="form.errors.phone" />
        </div>

        <div>
            <InputLabel for="address" value="Address" />
            <textarea
                id="address"
                v-model="form.address"
                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                rows="3"
            ></textarea>
            <InputError class="mt-2" :message="form.errors.address" />
        </div>

        <div>
            <InputLabel for="city" value="City" />
            <TextInput
                id="city"
                type="text"
                class="mt-1 block w-full"
                v-model="form.city"
            />
            <InputError class="mt-2" :message="form.errors.city" />
        </div>

        <div>
            <InputLabel for="state" value="State" />
            <TextInput
                id="state"
                type="text"
                class="mt-1 block w-full"
                v-model="form.state"
            />
            <InputError class="mt-2" :message="form.errors.state" />
        </div>

        <div>
            <InputLabel for="postal_code" value="Postal Code" />
            <TextInput
                id="postal_code"
                type="text"
                class="mt-1 block w-full"
                v-model="form.postal_code"
            />
        </div>

        <div>
            <InputLabel for="country" value="Country" />
            <TextInput
                id="country"
                type="text"
                class="mt-1 block w-full"
                v-model="form.country"
            />
            <InputError class="mt-2" :message="form.errors.country" />
        </div>

        <div>
            <InputLabel for="is_active" value="Active" />
            <Checkbox name="is_active" v-model:checked="form.is_active" />
            <InputError class="mt-2" :message="form.errors.is_active" />
        </div>

        <div class="flex justify-end space-x-4">
            <PrimaryButton :disabled="form.processing">{{ submitLabel }}</PrimaryButton>
            <SecondaryButton type="button" @click="$emit('cancel')">Cancel</SecondaryButton>
        </div>
    </form>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Checkbox from '@/Components/Checkbox.vue';

const props = defineProps({
    customer: {
        type: Object,
        default: () => ({
            id: null,
            name: '',
            email: '',
            phone: '',
            address: '',
            city: '',
            state: '',
            postal_code: '',
            country: '',
            is_active: true
        })
    },
    submitLabel: {
        type: String,
        default: 'Save Customer'
    }
});

const emit = defineEmits(['cancel', 'submitted']);

const form = useForm({
    name: props.customer?.name,
    email: props.customer?.email,
    phone: props.customer?.phone,
    address: props.customer?.address,
    city: props.customer?.city,
    state: props.customer?.state,
    postal_code: props.customer?.postal_code,
    country: props.customer?.country,
    is_active: props.customer?.is_active
});

const submit = () => {
    if (props.customer?.id) {
        form.put(route('customers.update', props.customer.id), {
            onSuccess: () => emit('submitted')
        });
    } else {
        form.post(route('customers.store'), {
            onSuccess: () => emit('submitted')
        });
    }
};
</script> 