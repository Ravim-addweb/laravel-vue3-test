<template>
  <div>
    <!-- Modal toggle -->
    <div class="flex justify-center mt-4">
      <!-- Align button in center -->
      <button
        @click="toggleModal"
        class="text-white inline-flex items-center bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"
      >
        <svg
          class="me-1 -ms-1 w-5 h-5"
          fill="currentColor"
          viewBox="0 0 20 20"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            fill-rule="evenodd"
            d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
            clip-rule="evenodd"
          ></path>
        </svg>
        Add Student
      </button>
    </div>

    <!-- Main modal -->
    <div
      v-if="isModalOpen"
      id="student-modal"
      tabindex="-1"
      aria-hidden="true"
      class="fixed inset-0 overflow-y-auto overflow-x-hidden z-50 flex justify-center items-center bg-black bg-opacity-50"
    >
      <div class="relative p-4 w-full max-w-md">
        <!-- Modal content -->
        <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
          <!-- Modal header -->
          <div
            class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600"
          >
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
              Add New Student
            </h3>
            <button
              @click="toggleModal"
              type="button"
              class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white"
            >
              <svg
                class="w-3 h-3"
                aria-hidden="true"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 14 14"
              >
                <path
                  stroke="currentColor"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"
                />
              </svg>
              <span class="sr-only">Close modal</span>
            </button>
          </div>
          <!-- Modal body -->
          <form @submit.prevent="handleSubmit" class="p-4 md:p-5">
            <div class="grid gap-4 mb-4 grid-cols-2">
              <div class="col-span-2">
                <label
                  for="username"
                  class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"
                  >Username</label
                >
                <input
                  v-model="username"
                  type="text"
                  name="username"
                  id="username"
                  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                  placeholder="Enter your username"
                  required
                />
              </div>
              <div class="col-span-2">
                <label
                  for="email"
                  class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"
                  >Email</label
                >
                <input
                  v-model="email"
                  type="email"
                  name="email"
                  id="email"
                  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                  placeholder="Enter your email"
                  required
                />
              </div>
            </div>
            <div class="col-span-2 text-red-500 mb-2" v-if="showValidationMsg">
              {{ showValidationMsg }}
            </div>
            <button
              type="submit"
              class="text-white inline-flex items-center bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"
            >
              Submit
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref } from "vue";
import { useStudentsStore } from "../stores/studentStore";

export default {
  props: {
    pageSize: {
      type: Number,
      default: 10,
    },
  },
  setup(props, { emit }) {
    const store = useStudentsStore();
    const isModalOpen = ref(false);
    const username = ref("");
    const email = ref("");
    const showValidationMsg = ref();

    const handleSubmit = async () => {
      const response = await store.addStudent(
        {
          username: username.value,
          email: email.value,
        },
        props?.pageSize
      );
      // console.log(response);
      if (
        response &&
        response?.status &&
        (response?.status === 500 || response?.status === 400)
      ) {
        emit("showSnackabar", {
          type: "error",
          message: response?.data?.error,
        });
        // showValidationMsg.value = response?.data?.error;
      } else if (response.status === 201) {
        // console.log("response", response);
        emit("showSnackabar", {
          type: "success",
          message: "Student created successfully!",
        });
      }
      isModalOpen.value = false;
      username.value = "";
      email.value = "";
    };

    const toggleModal = () => {
      isModalOpen.value = !isModalOpen.value;
    };

    return {
      isModalOpen,
      toggleModal,
      username,
      email,
      handleSubmit,
      showValidationMsg,
    };
  },
};
</script>
