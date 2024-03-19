<template>
  <transition name="fade">
    <div
      v-if="show"
      :class="[typeClasses, 'absolute top-0 right-0 m-4 px-4 py-2 rounded-md']"
    >
      {{ message }}
    </div>
  </transition>
</template>

<script>
import { ref, computed, watch } from "vue";

export default {
  props: {
    show: Boolean,
    message: String,
    type: String, // Success or error
  },
  setup(props, { emit }) {
    const timerSeconds = ref(5000);

    const typeClasses = computed(() => {
      return props.type === "success"
        ? "bg-green-500 text-white"
        : "bg-red-500 text-white";
    });

    // Hide the Snackbar after 5 seconds
    watch(() => props.show, (newValue) => {
      // console.log('Show prop changed from', oldValue, 'to', newValue);
      if (newValue) {
        setTimeout(() => {
          // console.log('Timeout executed');
          emit("hide");
        }, timerSeconds.value);
      }
    });

    return {
      typeClasses,
    };
  },
};
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.5s;
}
.fade-enter,
.fade-leave-to {
  opacity: 0;
}
</style>
