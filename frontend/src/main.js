import { createPinia } from 'pinia';
import { createApp } from 'vue'
import App from './App.vue'

// Import Tailwind CSS directly
import 'tailwindcss/tailwind.css'

const pinia = createPinia();

createApp(App).use(pinia).mount('#app')
