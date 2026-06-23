import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

// ✅ Pusher debug mode on
Pusher.logToConsole = true;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT,
    wssPort: import.meta.env.VITE_REVERB_PORT,
    forceTLS: false,
    enabledTransports: ['ws', 'wss'],
});

// ✅ Connection debug
window.Echo.connector.pusher.connection.bind('connected', () => {
    console.log('✅ Reverb connected:', window.Echo.connector.pusher.connection.socket_id);
});

window.Echo.connector.pusher.connection.bind('error', (err) => {
    console.log('❌ Reverb error:', err);
});

window.Echo.connector.pusher.connection.bind('disconnected', () => {
    console.log('⚠️ Reverb disconnected');
});

window.Echo.connector.pusher.connection.bind('state_change', (states) => {
    console.log('🔄 State:', states.previous, '→', states.current);
});
