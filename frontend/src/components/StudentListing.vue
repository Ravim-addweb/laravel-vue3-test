<template>
  <div class="mb-4">
    <Snackbar
      :show="snackbarShow"
      :message="snackbarMessage"
      :type="snackbarType"
      @hide="disableSnackbar"
    />

    <h2 class="text-lg font-semibold mb-4">Student Listing</h2>

    <!-- Student Popup -->
    <AddStudent v-if="isPopupOpen" :page-size="pageSize" @showSnackabar="showSnackabar" @close="togglePopup" />

    <!-- Pagination -->
    <div class="w-3/4 m-auto relative flex justify-center mt-8">
      <div class="d-flex items-center justify-center">
        <button
          @click="prevPage"
          :disabled="!isPrevPage"
          class="mr-2 px-3 py-1 bg-grey-300 rounded-md hover:bg-grey-300 focus:outline-none focus:bg-grey-300 disabled:opacity-50 disabled:cursor-not-allowed border-2"
        >
          Prev
        </button>
        <span class="text-lg px-1">{{ currentPage }}</span>
        <button
          @click="nextPage"
          :disabled="!isNextPage"
          class="ml-2 px-3 py-1 bg-grey-200 rounded-md hover:bg-grey-300 focus:outline-none focus:bg-grey-300 disabled:opacity-50 disabled:cursor-not-allowed border-2"
        >
          Next
        </button>
      </div>
      <div class="absolute right-0">
        <span class="text-lg"
          >{{ getStartIndex() }} -
          {{ Math.min(getEndIndex(), totalStudents) }} of
          {{ totalStudents }}</span
        >
        <select
          v-model="pageSize"
          @change="changePageSize"
          class="ml-4 px-3 py-1 bg-transparent border-2 rounded-md focus:outline-none"
        >
          <option value="10">10</option>
          <option value="20">20</option>
          <option value="30">30</option>
          <!-- Add more options as needed -->
        </select>
      </div>
    </div>
    
    <div class="flex justify-center">
      <!-- Student Listing Table -->
      <table class="w-3/4 divide-y divide-gray-200 mt-4">
        <thead class="bg-blue-50">
          <tr>
            <th
              scope="col"
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
            >
              Username
            </th>
            <th
              scope="col"
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
            >
              Email
            </th>
            <th
              scope="col"
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
            >
              Actions
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-if="students.length === 0">
            <td class="px-6 py-4 text-left" colspan="3">No data available</td>
          </tr>
          <tr v-for="(student, index) in students" :key="index">
            <td class="px-6 py-4 text-left whitespace-nowrap">
              <div
                v-if="student && student.username"
                class="text-sm text-gray-900"
              >
                {{ student.username }}
              </div>
            </td>
            <td class="px-6 py-4 text-left whitespace-nowrap">
              <div
                v-if="student && student.email"
                class="text-sm text-gray-900"
              >
                {{ student.email }}
              </div>
            </td>
            <td
              class="px-6 py-4 text-left whitespace-nowrap text-sm text-gray-500"
            >
              <button
                @click="deleteStudent(student.id)"
                class="text-red-600 hover:text-red-900"
                :disabled="student.isDelete"
              >
                <span v-if="!student.isDelete">Delete</span>
                <span v-else>
                  <svg
                    class="animate-spin h-5 w-5 mr-3 text-red-600"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                  >
                    <circle
                      class="opacity-25"
                      cx="12"
                      cy="12"
                      r="10"
                      stroke="currentColor"
                      stroke-width="4"
                    ></circle>
                    <path
                      class="opacity-75"
                      fill="currentColor"
                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647zM20 12c0-3.042-1.135-5.824-3-7.938l-3 2.647A7.962 7.962 0 0120 12h4zm-7.777 6.707a7.956 7.956 0 01-1.434-.539l-2.762 2.487A9.966 9.966 0 0012 22c2.564 0 4.904-.956 6.686-2.529l-2.463-2.764zm-1.66-3.388a9.996 9.996 0 00-1.92-1.915l-2.49 2.766a7.97 7.97 0 01-1.434.539l2.762-2.49a9.968 9.968 0 001.082 1.1zM6.553 5.783a9.967 9.967 0 00-1.082-1.1l2.49-2.766a7.97 7.97 0 011.434-.539L5.646 4.244a9.996 9.996 0 00-1.093 1.539zm7.312-2.63A9.967 9.967 0 0012 2c-2.564 0-4.904.956-6.686 2.529l2.463 2.764a7.956 7.956 0 011.434.539l2.762-2.487zm4.787 7.708l3 2.647C21.865 16.824 23 14.042 23 12h-4c0 2.958-1.135 5.742-3.196 7.938z"
                    ></path>
                  </svg>
                </span>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    
  </div>
</template>

<script>
import { ref, onMounted, computed } from "vue";
import AddStudent from "./AddStudent.vue";
import Snackbar from "./SnackbarComponent.vue";
import { useStudentsStore } from "../stores/studentStore";

export default {
  components: {
    AddStudent,
    Snackbar,
  },
  setup() {
    const isPopupOpen = ref(true);
    const studentsStore = useStudentsStore();
    let students = ref([]);
    const currentPage = ref(1);
    const startIndex = ref(1);
    const pageSize = ref(10); // Number of items per page
    const isPrevPage = ref(studentsStore.isPrevPage);
    const isNextPage = ref(studentsStore.isNextPage);
    const totalStudents = ref(studentsStore.totalStudents);
    const snackbarShow = ref(false);
    const snackbarMessage = ref("");
    const snackbarType = ref("");

    const setData = () => {
      students.value = studentsStore.students;
      isPrevPage.value = studentsStore.isPrevPage;
      isNextPage.value = studentsStore.isNextPage;
      totalStudents.value = studentsStore.totalStudents;
    };

    const fetchStudents = async () => {
      await studentsStore.fetchStudents(currentPage.value, pageSize.value);
      setData();
    };

    onMounted(fetchStudents);

    // Calculate the paginated students based on current page
    const paginatedStudents = computed(() => {
      const startIndex = (currentPage.value - 1) * pageSize.value;
      return students.value.slice(startIndex, startIndex + pageSize.value);
    });

    const deleteStudent = async (id) => {
      const studentIndex = studentsStore.students.findIndex(
        (student) => student.id === id
      );
      if (studentIndex !== -1) {
        studentsStore.students[studentIndex].isDelete = true;
      }
      setTimeout(async () => {
        const response = await studentsStore.deleteStudent(id);
        if (response?.status === 200) {
          snackbarShow.value = true;
          snackbarType.value = "success";
          snackbarMessage.value = response?.data?.message;
          if (studentIndex !== -1 && studentsStore.students[studentIndex]) {
            studentsStore.students[studentIndex].isDelete = false;
            studentsStore.students.splice(studentIndex, 1);
          }
          if (studentsStore.students.length < 1) {
            await prevPage();
          } else {
            await fetchStudents();
          }
        }
      }, 2000);
    };

    const togglePopup = () => {
      isPopupOpen.value = !isPopupOpen.value;
    };

    const prevPage = async () => {
      if (currentPage.value > 1) {
        currentPage.value = currentPage.value - 1;
        await fetchStudents(); // Fetch students for the previous page
      }
    };

    const nextPage = async () => {
      const nextPageNumber = currentPage.value + 1;
      const response = await studentsStore.fetchStudents(
        nextPageNumber,
        pageSize.value
      );
      if (response && response?.length >= 1) {
        currentPage.value = nextPageNumber;
        setData();
      }
    };

    const getStartIndex = () => {
      return (currentPage.value - 1) * pageSize.value + 1;
    };

    const getEndIndex = () => {
      return currentPage.value * pageSize.value;
    };

    const changePageSize = async (event) => {
      const newSize = parseInt(event.target.value);
      pageSize.value = newSize;
      await fetchStudents();
    };

    const disableSnackbar = () => {
      snackbarShow.value = false;
      snackbarType.value = "";
      snackbarMessage.value = "";
    };

    const showSnackabar = (val) => {
      snackbarShow.value = true;
      snackbarType.value = val?.type;
      snackbarMessage.value = val?.message;
    }

    return {
      isPopupOpen,
      togglePopup,
      students,
      deleteStudent,
      currentPage,
      paginatedStudents,
      prevPage,
      nextPage,
      pageSize,
      isPrevPage,
      isNextPage,
      totalStudents,
      startIndex,
      getStartIndex,
      getEndIndex,
      changePageSize,
      snackbarShow,
      snackbarMessage,
      snackbarType,
      disableSnackbar,
      showSnackabar
    };
  },
};
</script>
