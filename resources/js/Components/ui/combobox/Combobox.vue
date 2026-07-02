<script setup>
import { ComboboxRoot, useForwardPropsEmits } from "reka-ui";

const props = defineProps({
  open: { type: Boolean, required: false },
  defaultOpen: { type: Boolean, required: false },
  modelValue: { type: null, required: false },
  defaultValue: { type: null, required: false },
  multiple: { type: Boolean, required: false },
  by: { type: [String, Function], required: false },
  dir: { type: String, required: false },
  disabled: { type: Boolean, required: false },
  ignoreFilter: { type: Boolean, required: false, default: true },
  openOnFocus: { type: Boolean, required: false, default: true },
  openOnClick: { type: Boolean, required: false, default: true },
  resetSearchTermOnBlur: { type: Boolean, required: false, default: true },
  resetSearchTermOnSelect: { type: Boolean, required: false, default: true },
});
const emits = defineEmits(["update:modelValue", "update:open", "highlight"]);

const forwarded = useForwardPropsEmits(props, emits);
</script>

<template>
  <ComboboxRoot v-bind="forwarded">
    <slot />
  </ComboboxRoot>
</template>
