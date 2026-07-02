<script setup>
import { reactiveOmit } from "@vueuse/core";
import { ComboboxContent, ComboboxPortal, ComboboxViewport } from "reka-ui";
import { cn } from "@/lib/utils";

defineOptions({ inheritAttrs: false });

const props = defineProps({
  position: { type: String, required: false, default: "popper" },
  side: { type: null, required: false },
  sideOffset: { type: Number, required: false, default: 4 },
  align: { type: null, required: false },
  class: {
    type: [Boolean, null, String, Object, Array],
    required: false,
    skipCheck: true,
  },
});

const delegatedProps = reactiveOmit(props, "class");
</script>

<template>
  <ComboboxPortal>
    <ComboboxContent
      v-bind="{ ...delegatedProps, ...$attrs }"
      :class="
        cn(
          'relative z-50 min-w-[--reka-combobox-trigger-width] overflow-hidden rounded-md border bg-popover text-popover-foreground shadow-md data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95 data-[side=bottom]:slide-in-from-top-2 data-[side=top]:slide-in-from-bottom-2',
          props.class,
        )
      "
    >
      <ComboboxViewport class="max-h-64 overflow-y-auto p-1">
        <slot />
      </ComboboxViewport>
    </ComboboxContent>
  </ComboboxPortal>
</template>
