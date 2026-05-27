import { ref, computed, watch, type ComputedRef } from 'vue'

type Theme = 'light' | 'dark'

interface ThemeContext {
  isDarkMode: ComputedRef<boolean>
  toggleTheme: () => void
}

// Module-level singleton state
const theme = ref<Theme>('light')
const isInitialized = ref(false)

const isDarkMode = computed(() => theme.value === 'dark')

const toggleTheme = () => {
  theme.value = theme.value === 'light' ? 'dark' : 'light'
}

// Initialize once on client
if (typeof window !== 'undefined') {
  const savedTheme = localStorage.getItem('theme') as Theme | null
  theme.value = savedTheme || 'light'
  isInitialized.value = true

  // Apply initial theme class immediately
  if (theme.value === 'dark') {
    document.documentElement.classList.add('dark')
  } else {
    document.documentElement.classList.remove('dark')
  }

  watch([theme, isInitialized], ([newTheme, newIsInitialized]) => {
    if (newIsInitialized) {
      localStorage.setItem('theme', newTheme)
      if (newTheme === 'dark') {
        document.documentElement.classList.add('dark')
      } else {
        document.documentElement.classList.remove('dark')
      }
    }
  })
}

export function useTheme(): ThemeContext {
  return {
    isDarkMode,
    toggleTheme,
  }
}
