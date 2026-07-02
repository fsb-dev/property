<script setup>
import { reactiveOmit } from "@vueuse/core";
import { ComboboxInput, useForwardProps } from "reka-ui";
import { cn } from "@/lib/utils";

const props = defineProps({
  modelValue: { type: String, required: false },
  displayValue: { type: Function, required: false },
  autoFocus: { type: Boolean, required: false },
  disabled: { type: Boolean, required: false },
  asChild: { type: Boolean, required: false },
  as: { type: null, required: false },
  class: {
    type: [Boolean, null, String, Object, Array],
    required: false,
    skipCheck: true,
  },
});
const emits = defineEmits(["update:modelValue"]);

const delegatedProps = reactiveOmit(props, "class");
const forwardedProps = useForwardProps(delegatedProps);
</script>

<template>
  <ComboboxInput
    v-bind="forwardedProps"
    :model-value="modelValue"
    @update:model-value="emits('update:modelValue', $event)"
    :class="
      cn(
        'flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
        props.class,
      )
    "
  />
</template>
