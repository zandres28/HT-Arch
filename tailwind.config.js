/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./resources/views/**/*.blade.php",
  ],
  safelist: [
    'grid-rows-5',
    'grid-rows-6',
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}
