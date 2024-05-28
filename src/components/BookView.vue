<script>
import axios from 'axios';
export default {
    name: "BookView",
    props: ["user","book"],
    data() {
        return {
            code: "",
            msg: "",
            data: this.book,
            dataStorage: [],
            saveDisabled: true,
            showMsg: false,
            isDisabled: true,
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

    <form class="">
        <!-- INFORMACIÓN PRINCIPAL -->
        <div class="primaryInfo">
            <!-- width="150" height="250" -->
            <!-- width="300" height="500" -->
            <img class="primaryInfo--cover" :src="data.pic" alt="[img]" width="250" height="350"> 
            <div class="primaryInfo__data">
                <div class="primaryInfo__data--aux">
                    <h1 class="primaryInfo__data--title">{{ data.title }}</h1>
                    <h3 v-if="data.subtitle" class="primaryInfo__data--title">{{ data.subtitle }}</h3>
                </div>
                <div class="primaryInfo__data--aux">
                    <p>Por <b>{{ data.author }}</b> <span v-if="data.coauthor">y <b>{{ data.coauthor }}</b></span></p>
                    <p>{{ data.editor }}, {{ data.edition }}º Edición</p>
                </div>
                <div class="primaryInfo__data--aux">
                    <p>Publición original: <i>{{ data.year }}</i></p>
                    <p>Páginas: <i>{{ data.pages }}</i></p>
                    <p>ISBN: <i>{{ data.isbn }}</i></p>
                </div>
                <div class="primaryInfo__data--aux">
                    <p>Formato: <i>{{ data.name_format }}</i></p>
                    <p>Género: <i>{{ data.name_genre }}</i></p>
                </div>
            </div>
        </div>
        
        <!-- CAMPOS EDITABLES -->
        <div class="filter">
            <div>
                <label for="dateBought">Fecha de Compra:</label>
                <input type="date" v-model="data.dateBought" name="dateBought" :disabled="isDisabled">
            </div>
            <div>
                <label for="price">Precio:</label>
                <input type="text" v-model="data.price" name="price" :disabled="isDisabled" maxlength="6" size="6" @input="data.price = data.price.replace(/[^0-9]/g,'')">
            </div>
            <div>
                <label for="storage">Localización:</label>
                <select v-model="data.id_storage" name="storage" id="" :disabled="isDisabled">
                    <option :value="data.id_storage" selected hidden>{{ data.name_storage }}</option>
                    <option v-for="room in dataStorage" :value="room.id_storage">
                        {{ room.name_storage }}
                    </option>
                </select>
                <input type="text" v-model="data.shelf" :disabled="isDisabled" maxlength="6" size="6">
            </div>
            <div>
                <label for="lent">¿Prestado?</label>
                <input type="checkbox" v-model="data.lent" :true-value="1" :false-value="0" :disabled="isDisabled">
                <div v-if="data.lent">
                    <div>
                        <label for="lent_who">Persona:</label>
                        <input type="text" v-model="data.lent_who" name="lent_who" :disabled="isDisabled">
                    </div>
                    <div>
                        <label for="lent_who">Fecha:</label>
                        <input type="date" v-model="data.lent_when" name="lent_when" :disabled="isDisabled">
                    </div>
                </div>
            </div>
        </div>
        <div class="buttonDiv">
                <button type="button" @click="this.isDisabled=!this.isDisabled">Editar</button>
                <button type="button" @click="this.updateBook(user,data)" class="updateButton" :disabled="isDisabled">Guardar Cambios</button>
        </div>
    </form>
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