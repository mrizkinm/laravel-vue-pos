<template>
    <form @submit.prevent="submit" class="space-y-6">
        <div>
            <InputLabel for="name" value="Supplier Name" />
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
            <InputLabel for="company" value="Company Name" />
            <TextInput
                id="company"
                type="text"
                class="mt-1 block w-full"
                v-model="form.company"
                required
            />
            <InputError class="mt-2" :message="form.errors.company" />
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
        </div>

        <div>
            <InputLabel for="state" value="State" />
            <TextInput
                id="state"
                type="text"
                class="mt-1 block w-full"
                v-model="form.state"
            />
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
        </div>

        <div>
            <InputLabel for="tax_number" value="Tax Number" />
            <TextInput
                id="tax_number"
                type="text"
                class="mt-1 block w-full"
                v-model="form.tax_number"
            />
            <InputError class="mt-2" :message="form.errors.tax_number" />
        </div>

        <div>
            <InputLabel for="bank_name" value="Bank Name" />
            <TextInput
                id="bank_name"
                type="text"
                class="mt-1 block w-full"
                v-model="form.bank_name"
            />
            <InputError class="mt-2" :message="form.errors.bank_name" />
        </div>

        <div>
            <InputLabel for="bank_account" value="Bank Account Number" />
            <TextInput
                id="bank_account"
                type="text"
                class="mt-1 block w-full"
                v-model="form.bank_account"
            />
            <InputError class="mt-2" :message="form.errors.bank_account" />
        </div>

        <div>
            <InputLabel for="notes" value="Notes" />
            <textarea
                id="notes"
                v-model="form.notes"
                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                rows="3"
            ></textarea>
            <InputError class="mt-2" :message="form.errors.notes" />
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
import Checkbox from '../Checkbox.vue';

const props = defineProps({
    supplier: {
        type: Object,
        default: () => ({
            id: null,
            name: '',
            company: '',
            email: '',
            phone: '',
            address: '',
            tax_number: '',
            bank_name: '',
            bank_account: '',
            notes: '',
            is_active: true,
            city: '',
            state: '',
            postal_code: '',
            country: ''
        })
    },
    submitLabel: {
        type: String,
        default: 'Save Supplier'
    }
});

const emit = defineEmits(['cancel', 'submitted']);

const form = useForm({
    name: props.supplier?.name,
    company: props.supplier?.company,
    email: props.supplier?.email,
    phone: props.supplier?.phone,
    address: props.supplier?.address,
    tax_number: props.supplier?.tax_number,
    bank_name: props.supplier?.bank_name,
    bank_account: props.supplier?.bank_account,
    notes: props.supplier?.notes,
    is_active: props.supplier?.is_active,
    city: props.supplier?.city,
    state: props.supplier?.state,
    postal_code: props.supplier?.postal_code,
    country: props.supplier?.country
});

const submit = () => {
    if (props.supplier?.id) {
        form.put(route('suppliers.update', props.supplier.id), {
            onSuccess: () => emit('submitted')
        });
    } else {
        form.post(route('suppliers.store'), {
            onSuccess: () => emit('submitted')
        });
    }
};
</script> 