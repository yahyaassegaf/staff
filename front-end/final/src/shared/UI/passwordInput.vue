<!-- <template>
    <input :type="inputType" v-model="inputValue" :name="name" :id="id" :placeholder="placeholder"
        class="form-control form-control-lg" :required="required">
    <a href="javascript:void(0);" @click="changeInputType" class="show-password-button text-muted" type="button"
        id="button-addon2"><i class=" align-middle"
            :class="inputType === 'text' ? 'ri-eye-line' : 'ri-eye-off-line'"></i></a>
</template>

<script setup lang="ts">
import { ref, watch, defineProps, defineEmits } from 'vue';


const props = defineProps<{
    initialValue?: string;
    name?: string;
    id?: string;
    placeholder?: string;
    required?: boolean;
}>();


const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();

const inputType = ref('password');
const inputValue = ref(props.initialValue || '');

const changeInputType = () => {
    inputType.value = inputType.value === 'text' ? 'password' : 'text';
};

watch(inputValue, () => {
    emit('update:modelValue', inputValue.value);
});

</script> -->
<template>
  <div class="position-relative">
    <input
      :type="inputType"
      :value="inputValue"
      @input="onInput"
      :name="name"
      :id="id"
      :placeholder="placeholder"
      class="form-control form-control-lg"
      :required="required"
    />
    <a href="javascript:void(0);" @click.prevent="changeInputType" class="show-password-button text-muted" type="button">
      <i class="align-middle" :class="inputType === 'text' ? 'ri-eye-line' : 'ri-eye-off-line'"></i>
    </a>
  </div>
</template>

<script setup lang="ts">
    
import { ref, watch } from 'vue';

const props = defineProps<{
  modelValue?: string | null;
  name?: string;
  id?: string;
  placeholder?: string;
  required?: boolean;
}>();

const emit = defineEmits<{
  (e: 'update:modelValue', value: string): void;
}>();

const inputType = ref<'password' | 'text'>('password');
// internal value yang dipakai input; inisialisasi dari modelValue
const inputValue = ref(props.modelValue ?? '');

// Jika parent mengganti modelValue dari luar, sinkronkan ke internal
watch(
  () => props.modelValue,
  (v) => {
    // hanya update jika berbeda supaya tidak loop
    if ((v ?? '') !== inputValue.value) {
      inputValue.value = v ?? '';
    }
  }
);

// Emit update saat input berubah
function onInput(e: Event) {
  const val = (e.target as HTMLInputElement).value;
  inputValue.value = val;
  emit('update:modelValue', val);
  // debug sementara:

}

const changeInputType = () => {
  inputType.value = inputType.value === 'text' ? 'password' : 'text';
};
</script>

