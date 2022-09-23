import Axios from 'axios'
export default {

    apiRegister(formdata) {
        return Axios.post('/api/theme-builder/register', formdata);
    },
    
    apiLogin(formdata) {
        return Axios.post('/api/theme-builder/login', formdata);
    },
}