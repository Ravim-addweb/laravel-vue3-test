import axios from 'axios';
import { defineStore } from 'pinia';

export const useStudentsStore = defineStore('studentStore', {
  state: () => ({
    students: [],
    isPrevPage: false,
    isNextPage: true,
    totalStudents: 0
  }),
  actions: {
    async fetchStudents(page, per_page) {
      try {
        const response = await axios.get(`${process.env.VUE_APP_API_ENDPOINT}/api/students?page=${page}&per_page=${per_page}`);
        this.isPrevPage = response?.data?.prev_page_url ? true : false;
        this.isNextPage = response?.data?.next_page_url ? true : false;
        this.totalStudents = response?.data?.total;
        this.students = response.data.data; // Assuming the response contains an array of students
        return response.data.data;
      } catch (error) {
        console.error('Error fetching students:', error);
      }
    },

    async addStudent(student, pageSize) {
      try {
        const response = await axios.post(`${process.env.VUE_APP_API_ENDPOINT}/api/students`, student);
        // console.log('response', response.data);
        if (this.students.length < pageSize) {
          this.students.push(response.data);
        }
        return response;
      } catch (error) {
        console.error('Error adding student:', error);
        return error?.response;
      }
    },

    async deleteStudent(id) {
      try {
        const response = await axios.delete(`${process.env.VUE_APP_API_ENDPOINT}/api/students/${id}`);
        if (response?.status === 200) {
          return response;
        }
      } catch (error) {
        console.error('Error deleting student:', error);
      }
    },
  },
});
