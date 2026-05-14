<script setup>
import { onMounted, ref } from 'vue';

defineProps({
    modelValue: String,
});

defineEmits(['update:modelValue']);

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        ref="input"
        class="rounded-2xl border-slate-300 bg-white text-slate-900 placeholder:text-slate-400 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
        :value="modelValue"
        placeholder="Escribe aqui"
        @input="$emit('update:modelValue', $event.target.value)"
    >
</template>
