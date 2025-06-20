<template>
    <div>
        <VCalendarDatePicker
            v-model="localValue"
            :masks="masks"
            :attributes="attributes"
            :is-range="range"
            :placeholder="placeholder"
            class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
            @input="updateValue"
        />
    </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import { DatePicker as VCalendarDatePicker } from 'v-calendar';
import 'v-calendar/dist/style.css';

const props = defineProps({
    modelValue: {
        type: [Date, Array, null],
        default: null
    },
    range: {
        type: Boolean,
        default: false
    },
    placeholder: {
        type: String,
        default: 'Select date'
    }
});

const emit = defineEmits(['update:modelValue']);

const localValue = ref(props.modelValue);

const masks = {
    input: 'MMM DD, YYYY'
};

const attributes = computed(() => [{
    key: 'today',
    highlight: true,
    dates: new Date()
}]);

watch(() => props.modelValue, (newValue) => {
    localValue.value = newValue;
});

const updateValue = (value) => {
    emit('update:modelValue', value);
};
</script> 