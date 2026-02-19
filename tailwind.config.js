/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './public_html/**/*.html',
    './public_html/**/*.js',
  ],
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        brand: '#0D3618',
        accent: '#FFFE03',
        'brand-light': '#007030',
      }
    }
  },
  plugins: [],
}
