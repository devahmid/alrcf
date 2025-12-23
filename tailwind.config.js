/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./src/**/*.{html,ts}",
  ],
  theme: {
    extend: {
      fontFamily: {
        'sans': ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
        'display': ['Outfit', 'Inter', 'sans-serif'],
      },
      colors: {
        primary: {
          light: '#34495e',
          DEFAULT: '#2c3e50',
          dark: '#1a252f',
        },
        secondary: {
          light: '#5dade2',
          DEFAULT: '#3498db',
          dark: '#2980b9',
        },
        accent: {
          light: '#ea6153',
          DEFAULT: '#e74c3c',
          dark: '#c0392b',
        },
        brand: {
          blue: '#4169E1', // Royal Blue
          light: '#F0F4F8',
        }
      },
      backgroundImage: {
        'gradient-primary': 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
        'gradient-secondary': 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
      },
      boxShadow: {
        'soft': '0 10px 30px rgba(0,0,0,0.05)',
        'medium': '0 15px 40px rgba(0,0,0,0.1)',
        'hard': '0 20px 50px rgba(0,0,0,0.15)',
      },
      animation: {
        'fade-in-up': 'fadeInUp 0.8s ease-out forwards',
        'fade-in-left': 'fadeInLeft 0.8s ease-out forwards',
        'float': 'float 3s ease-in-out infinite',
      },
      keyframes: {
        fadeInUp: {
          '0%': { opacity: '0', transform: 'translateY(20px)' },
          '100%': { opacity: '1', transform: 'translateY(0)' },
        },
        fadeInLeft: {
          '0%': { opacity: '0', transform: 'translateX(-20px)' },
          '100%': { opacity: '1', transform: 'translateX(0)' },
        },
        float: {
          '0%, 100%': { transform: 'translateY(0)' },
          '50%': { transform: 'translateY(-10px)' },
        }
      }
    },
  },
  plugins: [],
}
