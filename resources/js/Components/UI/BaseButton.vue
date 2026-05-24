<script setup>
import { computed } from "vue";

/**
 * Props:
 * - variant : menentukan warna tombol
 * - size    : ukuran tombol
 * - disabled: status disable
 * - type    : tipe button html
 */
const props = defineProps({
    variant: {
        type: String,
        default: "primary",
    },

    size: {
        type: String,
        default: "md",
    },

    disabled: {
        type: Boolean,
        default: false,
    },

    type: {
        type: String,
        default: "button",
    },
});

/**
 * Emit event custom click
 */
const emit = defineEmits(["click"]);

/**
 * Dynamic bootstrap class
 */
const buttonClass = computed(() => {
    return [
        "btn",

        // variant bootstrap
        `btn-${props.variant}`,

        // size bootstrap
        {
            "btn-sm": props.size === "sm",
            "btn-lg": props.size === "lg",
        },
    ];
});

/**
 * Handle click event
 */
const handleClick = (event) => {
    if (!props.disabled) {
        emit("click", event);
    }
};
</script>

<template>
    <button
        :type="type"
        :class="buttonClass"
        :disabled="disabled"
        @click="handleClick"
    >
        <!-- Slot agar isi button fleksibel -->
        <slot />
    </button>
</template>
