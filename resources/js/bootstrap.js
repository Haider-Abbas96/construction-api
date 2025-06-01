import axios from 'axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';


window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: 'bd0ca80701632a1c42db', // your Pusher key
    cluster: 'ap2',
    forceTLS: true // use TLS for hosted Pusher (recommended)
});
