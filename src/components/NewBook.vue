<script>
import axios from 'axios';
export default {
    name: "NewBook",
    // props: ["user","book"],
    data() {
        return {
            code: "",
            msg: "",
            data: {},
            saveInput: {},
            saveDisabled: true,
            showMsg: false,
            bookNotClicked: true,
            isbn: "",
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
        }
    },

    methods: {

        // --- MÉTODOS CON CONEXIÓN CON BBDD ---
        // Son muchos pero prefiero tenerlos separados y ordenaditos antes que uno kilométrico
        async ajax(params) {
            const res = await axios.post('ajax.php', params);
            return res.data ? res.data : false;
        },

        async getStorage() {
            this.dataStorage = [];
            
            const res = await this.ajax({
                accion: 'getStorage',
            });

            if (typeof res.data !== 'undefined') {
                this.dataStorage = res.data;
            } else {
                this.dataStorage = [];
            }
        },

        async updateBook(id,arr) {
            const res = await this.ajax({
                accion: 'updateBook',
                id: id,
                data: arr
            });
            this.code = res.code;
            this.msg = res.msg;
            this.showMsg = true;
        }

    },

    created() {
        this.getStorage();
    }
}
</script>

<template>
    <Transition name="fade">
        <div v-if="showMsg" class="msgContainer">
            <div class="msg">
                <b>{{ msg }}</b>
            </div>
        </div>
    </Transition>

    <div class="container"> 
        <form>
            <div v-if="bookNotClicked">
                <h3>Añadir libro por ISBN</h3>
                <div>
                    <input type="text" :value="isbn" placeholder="ISBN-10 o ISBN-13">
                    <button type="button" @click="bookNotClicked=false;">Añadir por ISBN</button>
                </div>  
                <h3>Añadir desde cero</h3>
                <button type="button" @click="bookNotClicked=false;">Añadir libro desde cero</button>
            </div>

            
        </form>
    </div>
</template>

<style>
    p {
        margin: 10px auto;
    }

    input:disabled {
        color: black;
        /* background: var(--bg);
        border: none;
        font-size: inherit; */
    }

    .primaryInfo {
        display: flex;
        flex-direction: row;
    }
    .primaryInfo--cover {
        border-radius: 5px;
    }

    .primaryInfo__data {
        display: flex;
        flex-direction: column;
    }
    .primaryInfo__data--aux{
        padding-top: 0px;
    }
    .primaryInfo__data--title {
        margin: 0 auto;
    }

    .buttonDiv {
        display: flex;
        justify-content: center;
    }

    .inputLimit {
        width: 6ch;
    }
</style>