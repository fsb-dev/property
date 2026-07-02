<script setup>
import { reactiveOmit } from "@vueuse/core";
import { ComboboxEmpty, useForwardProps } from "reka-ui";
import { cn } from "@/lib/utils";

const props = defineProps({
  asChild: { type: Boolean, required: false },
  as: { type: null, required: false },
  class: {
    type: [Boolean, null, String, Object, Array],
    required: false,
    skipCheck: true,
  },
});

const delegatedProps = reactiveOmit(props, "class");
const forwardedProps = useForwardProps(delegatedProps);
</script>

<template>
  <ComboboxEmpty v-bind="forwardedProps" :class="cn('py-4 text-center text-xs text-muted-foreground', props.class)">
    <slot />
  </ComboboxEmpty>
</template>
