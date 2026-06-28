/** @type {import('tailwindcss').Config} */
export default {
  content: ['./index.html', './src/**/*.{ts,tsx}'],
  theme: {
    extend: {
      colors: {
        surface: '#f6f7f9',
        ink: '#17202a',
        muted: '#667085',
        line: '#d9dee7',
        brand: '#0f766e',
        accent: '#b45309'
      },
      boxShadow: {
        soft: '0 12px 32px rgba(16, 24, 40, 0.08)'
      }
    },
  },
  plugins: [],
};
