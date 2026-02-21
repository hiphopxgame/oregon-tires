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
        accent: '#F59E0B',
        'brand-light': '#007030',
      }
    }
  },
  plugins: [],
}
