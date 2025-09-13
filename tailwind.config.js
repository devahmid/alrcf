/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./src/**/*.{html,ts}",
  ],
  theme: {
    extend: {
      fontFamily: {
        'sans': ['Inter', 'sans-serif'],
      },
      colors: {
        'royal-blue': '#4169E1',
        'light-grey': '#D3D3D3',
      },
    },
  },
  plugins: [],
}

