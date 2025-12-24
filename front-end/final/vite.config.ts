// import path from 'path'

// import { defineConfig } from 'vite'
// import vue from '@vitejs/plugin-vue'

// // https://vitejs.dev/config/
// export default defineConfig({
//   base: '',
//   plugins: [vue(),],
//   resolve: {
//     alias: {
//       '@': fileURLToPath(new URL('./src',import.meta.url))
//     }
//   },
//   build: {
//     chunkSizeWarningLimit: 160000,
//   },
// })

import vue from '@vitejs/plugin-vue'

export default {
  plugins: [vue()],
  resolve: {
    alias: {
      '@': '/src'
    }
  }
}


