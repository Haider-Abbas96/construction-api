import './bootstrap';


const conversationId = 23; // Make sure this matches your test

window.Echo.private(`chat.${conversationId}`)
    .listen('.message.sent', (e) => {
        console.log('📥 Message received:', e.message.body);
        alert(`New message: ${e.message.body}`);
    });