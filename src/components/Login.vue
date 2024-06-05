<script>
import axios from 'axios';
import UserView from '@/views/UserView.vue';

export default {
    name: "Login",
    components: {
        UserView,
    },
    data() {
        return {
            code: "",
            msg: "",
            data: [],
            saveInput: [],
            showMsg: false,
        }
    },

    watch: {
        // Hace que el mensaje solo aparezca en pantalla durante X tiempo
        showMsg: {
            handler() {
                setTimeout(() => {
                    this.showMsg = false;
                }, 3000);
            },
            deep: true,
        },
    },

    computed: {
        getUser() {
            if (sessionStorage.getItem("uuid") == atob(localStorage.getItem("uuid"))) {
                return sessionStorage.getItem("user");
            } else {
                return false;
            }
        }
    },

    methods: {

        // --- MÉTODOS CON CONEXIÓN CON BBDD ---
        // Son muchos pero prefiero tenerlos separados y ordenaditos antes que uno kilométrico
        async ajax(params) {
            const res = await axios.post('ajax.php', params);
            return res.data ? res.data : false;
        },

        async login(arr) {
            this.dataStorage = [];
            
            const res = await this.ajax({
                accion: 'login',
                user: arr['user'],
                pass: arr['pass']
            });

            this.code = res.code;
            this.msg = res.msg;
            this.showMsg = true;

            if (this.code == "SU0100") {
                sessionStorage.setItem("user",res.data["id_user"]);
                sessionStorage.setItem("uuid",res.data["_uuid"]);
                localStorage.setItem("uuid",btoa(res.data["_uuid"]));
                this.$router.go();
            }
        }, 

    },

    created() {
    }
}
</script>

<template >
    <component v-if="!getUser">
        <Transition name="fade">
            <div v-if="showMsg" class="msgContainer">
                <div class="msg">
                    <b>{{ msg }}</b>
                </div>
            </div>
        </Transition>

        <form class="loginContainer">
            <!-- CAMPOS EDITABLES -->
            <h1 class="menuContainer--logo">HomeLib📖</h1>
            <div class="filter">
                <div class="filter__input">
                    <label for="user">Usuario</label>
                    <input type="text" v-model="saveInput['user']" name="user" required>
                </div>
                <div class="filter__input">
                    <label for="pass">Contraseña</label>
                    <input type="password" v-model="saveInput['pass']" name="pass" required>
                </div>
                <div>
                    <button type="button" @click="login(saveInput)" class="updateButton">Log In</button>
                </div>
            </div>
        </form>
    </component>
    
    <component v-else>
        <UserView :user="getUser"/>
    </component>
</template>

<style>
    .loginContainer {
        height: 80vh;
        display: flex;
        justify-content: center;
        align-items: center;
    }
</style>