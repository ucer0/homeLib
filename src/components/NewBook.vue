<script>
import axios from 'axios';
export default {
    name: "NewBook",
    props: ["user"],
    data() {
        return {
            code: "",
            msg: "",
            data: {},
            dataStorage: [],
            dataFormat: [],
            dataGenre: [],
            saveInput: {},
            saveDisabled: true,
            showMsg: false,
            bookNotClicked: true,
            bookFound: false,
            isbn: "",
            allDataRequired: false,
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

        saveInput: {
            handler() {
                if (!this.saveInput['isbn'] || !this.saveInput['title'] || !this.saveInput['author'] || !this.saveInput['editor'] || !this.saveInput['edition']
                    || !this.saveInput['year'] || !this.saveInput['pages'] || !this.saveInput['id_format'] || !this.saveInput['id_genre']
                ) {
                    this.allDataRequired = false;
                } else {
                    this.allDataRequired = true;
                }
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

        async saveBook(arr,isNew) {
            const res = await this.ajax({
                accion: 'saveBook',
                id: this.user,
                data: arr,
                isNew: isNew
            });
            this.code = res.code;
            this.msg = res.msg;
            this.showMsg = true;

            if (this.code == "SU0100") {
                this.saveInput = {};
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

        async getFormat() {
            this.dataFormat = [];
            
            const res = await this.ajax({
                accion: 'getFormat',
            });

            if (typeof res.data !== 'undefined') {
                this.dataFormat = res.data;
            } else {
                this.dataFormat = [];
            }
        },

        async getGenre() {
            this.dataGenre = [];
            
            const res = await this.ajax({
                accion: 'getGenre',
            });

            if (typeof res.data !== 'undefined') {
                this.dataGenre = res.data;
            } else {
                this.dataGenre = [];
            }
        },

        // -------------------------------------

        numberCheck(char) {
            return char.replace(/[^0-9]/g,'');
        }
    },

    created() {
        this.getStorage();
        this.getFormat();
        this.getGenre();
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
        <form id="saveBookForm">
            <div v-if="bookNotClicked">
                <h3>Añadir libro por ISBN</h3>
                <div>
                    <input type="text" :value="isbn" placeholder="ISBN-10 o ISBN-13">
                    <button type="button" @click="bookNotClicked=false;">Añadir por ISBN</button>
                </div>  
                <h3>Añadir desde cero</h3>
                <button type="button" @click="bookNotClicked=false;">Añadir libro desde cero</button>
            </div>

            <!-- CAMPOS DEL LIBRO -->
            <h3 v-if="!bookNotClicked">Campos Genéricos</h3>
            <div class="filter" v-if="!bookNotClicked">
                <span style="color: red; font-size:x-small" >* Campos requeridos</span>
                <div>
                    <label for="isbn">ISBN<span style="color: red" >*</span>:</label>
                    <input type="text" v-model="saveInput['isbn']" name="isbn" maxlength="13" size="13" @input="saveInput['isbn'] = numberCheck(saveInput['isbn'])" required>
                </div>
                <div>
                    <label for="title">Título<span style="color: red" >*</span>:</label>
                    <input type="text" v-model="saveInput['title']" name="title" required>
                </div>
                <div>
                    <label for="subtitle">Subtítulo:</label>
                    <input type="text" v-model="saveInput['subtitle']" name="subtitle">
                </div>
                <div>
                    <label for="author">Autor<span style="color: red" >*</span>:</label>
                    <input type="text" v-model="saveInput['author']" name="author" required>
                </div>
                <div>
                    <label for="coauthor">Coautor:</label>
                    <input type="text" v-model="saveInput['coauthor']" name="coauthor" required>
                </div>
                <div>
                    <label for="editor">Editorial<span style="color: red" >*</span>:</label>
                    <input type="text" v-model="saveInput['editor']" name="editor" required>
                </div>
                <div>
                    <label for="edition">Nº Edición<span style="color: red" >*</span>:</label>
                    <input type="text" v-model="saveInput['edition']" name="edition" min="1" size="2" @input="saveInput['edition'] = numberCheck(saveInput['edition'])" required>
                </div>
                <div>
                    <label for="year">Año Publicación Original<span style="color: red" >*</span>:</label>
                    <input type="text" v-model="saveInput['year']" name="year" min="1" maxlength="4" size="4" @input="saveInput['year'] = numberCheck(saveInput['year'])" required>
                </div>
                <div>
                    <label for="pages">Nº Páginas<span style="color: red" >*</span>:</label>
                    <input type="text" v-model="saveInput['pages']" name="pages" min="1" maxlength="4" size="4" @input="saveInput['pages'] = numberCheck(saveInput['pages'])" required>
                </div>
                <div>
                    <label for="format">Formato<span style="color: red" >*</span>:</label>
                    <select v-model="saveInput['id_format']" name="format" id="" >
                        <option v-for="room in dataFormat" :value="room.id_format">
                            {{ room.name_format }}
                        </option>
                    </select>
                </div>
                <div>
                    <label for="genre">Género<span style="color: red" >*</span>:</label>
                    <select v-model="saveInput['id_genre']" name="genre" id="" >
                        <option v-for="room in dataGenre" :value="room.id_genre">
                            {{ room.name_genre }}
                        </option>
                    </select>
                </div>
            </div>
            
            <!-- CAMPOS PERSONALES -->
            <h3 v-if="!bookNotClicked">Campos Personales</h3>
            <div class="filter" v-if="!bookNotClicked">
                <div>
                    <label for="dateBought">Fecha de Compra:</label>
                    <input type="date" v-model="saveInput['dateBought']" name="dateBought">
                </div>
                <div>
                    <label for="price">Precio:</label>
                    <input type="text" v-model="saveInput['price']" name="price" maxlength="6" size="6" @input="saveInput['price'] = saveInput['price'].replace(/[^0-9.]/g,'')">
                </div>
                <div>
                    <label for="storage">Localización:</label>
                    <select v-model="saveInput['id_storage']" name="storage" id="" >
                        <option v-for="room in dataStorage" :value="room.id_storage">
                            {{ room.name_storage }}
                        </option>
                    </select>
                    <input type="text" v-model="saveInput['shelf']" maxlength="6" size="6">
                </div>
                <div>
                    <label for="lent">¿Prestado?</label>
                    <input type="checkbox" v-model="saveInput['lent']" :true-value="1" :false-value="0">
                    <div v-if="saveInput['lent']">
                        <div>
                            <label for="lent_who">Persona:</label>
                            <input type="text" v-model="saveInput['lent_who']" name="lent_who" >
                        </div>
                        <div>
                            <label for="lent_who">Fecha:</label>
                            <input type="date" v-model="saveInput['lent_when']" name="lent_when">
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="!bookNotClicked">
                <button v-if="bookFound" type="button" @click="this.saveBook(saveInput,false)" class="updateButton" :disabled="!allDataRequired">Guardar Libro</button>
                <button v-else type="button" @click="this.saveBook(saveInput,true)" class="updateButton" :disabled="!allDataRequired">Guardar Libro</button>
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