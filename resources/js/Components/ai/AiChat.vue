<script setup>
import { ref, nextTick, onMounted } from 'vue'
import axios from 'axios'



const props = defineProps({
  endpoint: { type: String, default: '/admin/chat' },
  model: { type: String, default: 'qwen2.5:1.5b-cloud' },
  title: { type: String, default: 'AI Assistant' },
  floating: { type: Boolean, default: true }, // fixed bottom-right widget
})

const messages = ref([]) // { role: 'user' | 'assistant', content: string }
const input = ref('')
const isLoading = ref(false)
const errorMsg = ref('')
const scrollArea = ref(null)
const isOpen = ref(!props.floating) // non-floating mode: always open

const scrollToBottom = async () => {
  await nextTick()
  if (scrollArea.value) {
    scrollArea.value.scrollTop = scrollArea.value.scrollHeight
  }
}

const sendMessage = async () => {
  const text = input.value.trim()
  if (!text || isLoading.value) return

  errorMsg.value = ''
  messages.value.push({ role: 'user', content: text })
  input.value = ''
  isLoading.value = true
  scrollToBottom()

  try {
    // send prior history (excluding the message we just added, backend appends it)
    const history = messages.value.slice(0, -1).map(m => ({
      role: m.role,
      content: m.content,
    }))

    const { data } = await axios.post(props.endpoint, {
      message: text,
      history,
      model: props.model,
    })

    const reply =
      data?.message?.content ??
      data?.content ??
      'Sorry, I didn\'t get a response.'

    messages.value.push({ role: 'assistant', content: reply })
  } catch (err) {
    errorMsg.value =
      err?.response?.data?.message ?? 'Something went wrong. Please try again.'
    messages.value.push({
      role: 'assistant',
      content: '⚠️ ' + errorMsg.value,
      isError: true,
    })
  } finally {
    isLoading.value = false
    scrollToBottom()
  }
}

const handleKeydown = (e) => {
  if (e.key === 'Enter' && !e.shiftKey) {
    e.preventDefault()
    sendMessage()
  }
}

const clearChat = () => {
  messages.value = []
  errorMsg.value = ''
}

const toggleOpen = () => {
  isOpen.value = !isOpen.value
  if (isOpen.value) scrollToBottom()
}

onMounted(scrollToBottom)
</script>

<template>
  <!-- Floating launcher button (only shown in floating mode when closed) -->
  <button
    v-if="floating && !isOpen"
    class="ai-chat__launcher"
    type="button"
    @click="toggleOpen"
    aria-label="Open AI chat"
  >
    <span class="ai-chat__launcher-icon">💬</span>
  </button>

  <div
    class="ai-chat"
    :class="{ 'ai-chat--floating': floating, 'ai-chat--closed': floating && !isOpen }"
  >
    <header class="ai-chat__header">
      <div class="ai-chat__title">
        <span class="ai-chat__dot"></span>
        {{ title }}
      </div>
      <div class="ai-chat__header-actions">
        <button
          class="ai-chat__clear"
          type="button"
          @click="clearChat"
          v-if="messages.length"
        >
          Clear
        </button>
        <button
          v-if="floating"
          class="ai-chat__close"
          type="button"
          @click="toggleOpen"
          aria-label="Close chat"
        >
          ✕
        </button>
      </div>
    </header>

    <div class="ai-chat__body" ref="scrollArea">
      <div v-if="!messages.length" class="ai-chat__empty">
        Ask me anything to get started.
      </div>

      <div
        v-for="(msg, i) in messages"
        :key="i"
        class="ai-chat__row"
        :class="msg.role === 'user' ? 'ai-chat__row--user' : 'ai-chat__row--bot'"
      >
        <div
          class="ai-chat__bubble"
          :class="{
            'ai-chat__bubble--user': msg.role === 'user',
            'ai-chat__bubble--bot': msg.role === 'assistant',
            'ai-chat__bubble--error': msg.isError,
          }"
        >
          {{ msg.content }}
        </div>
      </div>

      <div v-if="isLoading" class="ai-chat__row ai-chat__row--bot">
        <div class="ai-chat__bubble ai-chat__bubble--bot ai-chat__typing">
          <span></span><span></span><span></span>
        </div>
      </div>
    </div>

    <form class="ai-chat__footer" @submit.prevent="sendMessage">
      <textarea
        v-model="input"
        rows="1"
        placeholder="Type a message..."
        @keydown="handleKeydown"
        :disabled="isLoading"
      ></textarea>
      <button type="submit" :disabled="isLoading || !input.trim()">
        Send
      </button>
    </form>
  </div>
</template>

<style scoped>
.ai-chat {
  display: flex;
  flex-direction: column;
  height: 640px;
  max-height: 80vh;
  width: 100%;
  max-width: 480px;
  border: 1px solid #e2e2e2;
  border-radius: 14px;
  overflow: hidden;
  background: #ffffff;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
}

/* Floating widget: fixed to bottom-right of the viewport, stays put on scroll */
.ai-chat--floating {
  position: fixed;
  right: 24px;
  bottom: 24px;
  z-index: 1000;
  width: 380px;
  height: 560px;
  max-height: calc(100vh - 48px);
}

.ai-chat--closed {
  display: none;
}

@media (max-width: 480px) {
  .ai-chat--floating {
    right: 12px;
    bottom: 12px;
    left: 12px;
    width: auto;
  }
}

/* Round launcher button, fixed bottom-right */
.ai-chat__launcher {
  position: fixed;
  right: 24px;
  bottom: 24px;
  z-index: 1000;
  width: 56px;
  height: 56px;
  border-radius: 50%;
  border: none;
  background: #2563eb;
  color: #fff;
  font-size: 22px;
  cursor: pointer;
  box-shadow: 0 6px 18px rgba(37, 99, 235, 0.35);
  display: flex;
  align-items: center;
  justify-content: center;
  transition: transform 0.15s ease;
}
.ai-chat__launcher:hover {
  transform: scale(1.06);
}

.ai-chat__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 14px 18px;
  border-bottom: 1px solid #eee;
  background: #fafafa;
}

.ai-chat__title {
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 600;
  font-size: 15px;
  color: #1a1a1a;
}

.ai-chat__dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #22c55e;
  display: inline-block;
}

.ai-chat__header-actions {
  display: flex;
  align-items: center;
  gap: 12px;
}

.ai-chat__clear {
  background: none;
  border: none;
  color: #888;
  font-size: 13px;
  cursor: pointer;
}
.ai-chat__clear:hover {
  color: #333;
}

.ai-chat__close {
  background: none;
  border: none;
  color: #888;
  font-size: 15px;
  line-height: 1;
  cursor: pointer;
}
.ai-chat__close:hover {
  color: #333;
}

.ai-chat__body {
  flex: 1;
  overflow-y: auto;
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 10px;
  background: #fcfcfc;
}

.ai-chat__empty {
  margin: auto;
  color: #999;
  font-size: 14px;
}

.ai-chat__row {
  display: flex;
}
.ai-chat__row--user {
  justify-content: flex-end;
}
.ai-chat__row--bot {
  justify-content: flex-start;
}

.ai-chat__bubble {
  max-width: 78%;
  padding: 10px 14px;
  border-radius: 16px;
  font-size: 14.5px;
  line-height: 1.45;
  white-space: pre-wrap;
  word-break: break-word;
}

.ai-chat__bubble--user {
  background: #2563eb;
  color: #fff;
  border-bottom-right-radius: 4px;
}

.ai-chat__bubble--bot {
  background: #eef0f3;
  color: #1a1a1a;
  border-bottom-left-radius: 4px;
}

.ai-chat__bubble--error {
  background: #fee2e2;
  color: #991b1b;
}

.ai-chat__typing {
  display: flex;
  gap: 4px;
  padding: 14px;
}
.ai-chat__typing span {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: #999;
  animation: ai-chat-bounce 1.2s infinite ease-in-out;
}
.ai-chat__typing span:nth-child(2) {
  animation-delay: 0.15s;
}
.ai-chat__typing span:nth-child(3) {
  animation-delay: 0.3s;
}
@keyframes ai-chat-bounce {
  0%, 60%, 100% { transform: translateY(0); opacity: 0.5; }
  30% { transform: translateY(-4px); opacity: 1; }
}

.ai-chat__footer {
  display: flex;
  gap: 8px;
  padding: 12px;
  border-top: 1px solid #eee;
  background: #fff;
}

.ai-chat__footer textarea {
  flex: 1;
  resize: none;
  border: 1px solid #ddd;
  border-radius: 10px;
  padding: 10px 12px;
  font-size: 14px;
  font-family: inherit;
  outline: none;
  max-height: 100px;
}
.ai-chat__footer textarea:focus {
  border-color: #2563eb;
}

.ai-chat__footer button {
  padding: 0 18px;
  border: none;
  border-radius: 10px;
  background: #2563eb;
  color: #fff;
  font-weight: 600;
  font-size: 14px;
  cursor: pointer;
}
.ai-chat__footer button:disabled {
  background: #9db8ee;
  cursor: not-allowed;
}
</style>
