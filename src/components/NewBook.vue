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
            fetchingBook: false,
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

        async getBook(isbn) {
            this.saveInput = {};
            this.fetchingBook = true;

            const res = await this.ajax({
                accion: 'getBook',
                isbn: isbn,
            });

            if (typeof res.data !== 'undefined') {
                this.saveInput = res.data;
            } else {
                this.saveInput = {};
                this.saveInput["isbn"] = isbn;
            }
            this.fetchingBook = false;
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
        <!-- OPCIONES INICIALES -->
        <div v-if="bookNotClicked">
            <h3>Añadir libro por ISBN</h3>
            <div style="display: flex;">
                <input type="text" placeholder="ISBN-10 o ISBN-13" v-model="saveInput['isbn']" class="isbnInput" maxlength="13" size="20" @input="saveInput['isbn'] = numberCheck(saveInput['isbn'])">
                <button type="button" @click="getBook(saveInput['isbn']);bookNotClicked=false;">Añadir libro</button> <!-- VAMOS A ESTO!!!! -->
            </div>  
        </div>

        <form id="saveBookForm" v-if="!bookNotClicked && !fetchingBook">
            <!-- CAMPOS DEL LIBRO -->
            <div>
                <h3>Campos Genéricos</h3>
                <div class="filter">
                    <span style="color: red; font-size:x-small" >* Campos requeridos</span>
                    <div class="filter__input">
                        <label for="isbn">ISBN<span style="color: red" >*</span>:</label>
                        <input type="text" v-model="saveInput['isbn']" name="isbn" maxlength="13" size="13" @input="saveInput['isbn'] = numberCheck(saveInput['isbn'])" required>
                    </div>
                    <div class="filter__input">
                        <label for="title">Título<span style="color: red" >*</span>:</label>
                        <input type="text" v-model="saveInput['title']" name="title" required>
                    </div>
                    <div class="filter__input">
                        <label for="subtitle">Subtítulo:</label>
                        <input type="text" v-model="saveInput['subtitle']" name="subtitle">
                    </div>
                    <div class="filter__input">
                        <label for="author">Autor<span style="color: red" >*</span>:</label>
                        <input type="text" v-model="saveInput['author']" name="author" required>
                    </div>
                    <div class="filter__input">
                        <label for="coauthor">Coautor:</label>
                        <input type="text" v-model="saveInput['coauthor']" name="coauthor" required>
                    </div>
                    <div class="filter__input">
                        <label for="editor">Editorial<span style="color: red" >*</span>:</label>
                        <input type="text" v-model="saveInput['editor']" name="editor" required>
                    </div>
                    <div class="filter__input">
                        <label for="edition">Nº Edición<span style="color: red" >*</span>:</label>
                        <input type="text" v-model="saveInput['edition']" name="edition" min="1" size="2" @input="saveInput['edition'] = numberCheck(saveInput['edition'])" required>
                    </div>
                    <div class="filter__input">
                        <label for="year">Año Publicación<span style="color: red" >*</span>:</label>
                        <input type="text" v-model="saveInput['year']" name="year" min="1" maxlength="4" size="4" @input="saveInput['year'] = numberCheck(saveInput['year'])" required>
                    </div>
                    <div class="filter__input">
                        <label for="pages">Nº Páginas<span style="color: red" >*</span>:</label>
                        <input type="text" v-model="saveInput['pages']" name="pages" min="1" maxlength="4" size="4" @input="saveInput['pages'] = numberCheck(saveInput['pages'])" required>
                    </div>
                    <div class="filter__input">
                        <label for="format">Formato<span style="color: red" >*</span>:</label>
                        <select v-model="saveInput['id_format']" name="format" id="" >
                            <option v-for="room in dataFormat" :value="room.id_format">
                                {{ room.name_format }}
                            </option>
                        </select>
                    </div>
                    <div class="filter__input">
                        <label for="genre">Género<span style="color: red" >*</span>:</label>
                        <select v-model="saveInput['id_genre']" name="genre" id="" >
                            <option v-for="room in dataGenre" :value="room.id_genre">
                                {{ room.name_genre }}
                            </option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- CAMPOS PERSONALES -->
            <div>
                <h3>Campos Personales</h3>
                <div class="filter">
                    <div class="filter__input">
                        <label for="dateBought">Fecha de Compra:</label>
                        <input type="date" v-model="saveInput['dateBought']" name="dateBought">
                    </div>
                    <div class="filter__input">
                        <label for="price">Precio:</label>
                        <input type="text" v-model="saveInput['price']" name="price" maxlength="6" size="6" @input="saveInput['price'] = saveInput['price'].replace(/[^0-9.]/g,'')">
                    </div>
                    <div class="filter__input">
                        <label for="storage">Localización:</label>
                        <select v-model="saveInput['id_storage']" name="storage" id="" >
                            <option v-for="room in dataStorage" :value="room.id_storage">
                                {{ room.name_storage }}
                            </option>
                        </select>
                        <input type="text" v-model="saveInput['shelf']" maxlength="6" size="6">
                    </div>
                    <div class="filter__input">
                        <label for="lent">¿Prestado?</label>
                        <input type="checkbox" v-model="saveInput['lent']" :true-value="1" :false-value="0">
                    </div>
                    <div v-if="saveInput['lent']" class="filter__input">
                        <label for="lent_who">Persona:</label>
                        <input type="text" v-model="saveInput['lent_who']" name="lent_who" >
                    </div>
                    <div v-if="saveInput['lent']" class="filter__input">
                        <label for="lent_who">Fecha:</label>
                        <input type="date" v-model="saveInput['lent_when']" name="lent_when">
                    </div>
                </div>

                <div>
                    <button v-if="bookFound" type="button" @click="this.saveBook(saveInput,false)" class="updateButton" :disabled="!allDataRequired">Guardar Libro</button>
                    <button v-else type="button" @click="this.saveBook(saveInput,true);bookNotClicked=true;" class="updateButton" :disabled="!allDataRequired">Guardar Libro</button>
                </div>
            </div>
        </form>
        <div v-else-if="!bookNotClicked && fetchingBook">
            <h3>⏳ Buscando Libro ⏳</h3>
        </div>
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

    .isbnInput {
        padding: 9px;
        font-size: inherit;
    }

    #saveBookForm {
        flex-direction: row;
        align-items: start;
    }
    @media only screen and (max-width: 650px) {
        #saveBookForm {
            flex-direction: column;
            align-items: center;
        }
    }
</style>