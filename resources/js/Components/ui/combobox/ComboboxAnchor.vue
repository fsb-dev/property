<script setup>
import { reactiveOmit } from "@vueuse/core";
import { ComboboxAnchor, useForwardProps } from "reka-ui";
import { cn } from "@/lib/utils";

const props = defineProps({
  reference: { type: null, required: false },
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
  <ComboboxAnchor v-bind="forwardedProps" :class="cn('relative block w-full', props.class)">
    <slot />
  </ComboboxAnchor>
</template>
