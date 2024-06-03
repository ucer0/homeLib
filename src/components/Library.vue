<script>
import axios from 'axios';
import BookView from '@/components/BookView.vue';

export default {
    name: "Library",
    props: ["user"],
    components: {
        BookView,
    },
    data() {
        return {
            code: "",
            msg: "",
            data: [],
            headers: ["Título", "Autor", "Año", "Localización",],
            showMsg: false,
            showTarjetaView: false,
            searchInput: "",
            currentIDSelected: 0,
            // Devuelve la hora en este formato "YYYY-MM-DD HH:mm:ss"
            currentDate: (new Date().toISOString().slice(0,10))+" "+(new Date().toLocaleTimeString()),
            // --- PAGINACIÓN ---
            rowsPerPage: localStorage.getItem("rowsPerPage") ?? 10, // Nº de filas que se enseñan en la tabla
            pageNumber: 0,   // Nº de la página actual
            // ------------------
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
        // Cookie para que guarde el número de registros por página al cambiarlo
        rowsPerPage: {
            handler(val) {
                localStorage.setItem("rowsPerPage",val);
                localStorage.getItem("rowsPerPage");
            },
            deep: true,
        },
        // --- PAGINACIÓN ---
        paginatedData() {
            if (this.paginatedData.length == 0) {
                this.pageNumber = 0;
            }
        }
        // ------------------
    },

    computed: {
        filteredData() {
            // Devuelve todos los datos si no hay filtro
            if (this.searchInput === '') {
                return this.data; 
            } else {
                return this.data.filter((row) =>
                    row['isbn'].toString().includes(this.searchInput) ||
                    row['author'].toString().toLowerCase().includes(this.searchInput.toLowerCase()) ||
                    row['title'].toString().toLowerCase().includes(this.searchInput.toLowerCase()) ||
                    (row['name_storage']+"-"+row['shelf']).toString().toLowerCase().includes(this.searchInput.toLowerCase()) ||
                    row['year'].toString().includes(this.searchInput) 
                );  
            }
        },
        // --- PAGINACIÓN ---
        pageCount(){
            return Math.ceil(this.filteredData.length/this.rowsPerPage);
        },
        paginatedData(){
            const start = this.pageNumber * this.rowsPerPage;
            const end = parseInt(start) + parseInt(this.rowsPerPage);
            return this.filteredData.slice(start, end);
        },
        // ------------------
    },
    
    methods: {

        // --- MÉTODOS CON CONEXIÓN CON BBDD ---
        async ajax(params) {
            const res = await axios.post('ajax.php', params);
            return res.data ? res.data : false;
        },

        async getPersonalLibrary(id, filter=[]) {
            this.data = [];
            this.isLoading = true;
            
            const res = await this.ajax({
                accion: 'getPersonalLibrary',
                id: id,
                filter: filter
            });

            if (typeof res.data !== 'undefined') {
                this.isLoading = false;
                this.data = res.data;
                // this.headers = Object.keys(res.data[0]);
            } else {
                this.isLoading = false;
                this.data = [];
            }
        },
        // ------------------

        // --- PAGINACIÓN ---
        nextPage(){
            this.pageNumber++;
        },
        prevPage(){
            this.pageNumber--;
        },
        // ------------------
    },

    created() {
        this.getPersonalLibrary(this.user);
        if (!localStorage.hasOwnProperty('rowsPerPage')) {
            localStorage.setItem('rowsPerPage', localStorage.getItem("rowsPerPage") ?? 10);   
        }
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
            <!-- TABLA -->
            <div class="">
                <!-- PAGINACIÓN -->
                <div>Mostrando <b>{{ paginatedData.length }}</b> resultados de <b>{{ filteredData.length }}</b></div>
                <div class="pagination">
                    <!-- BUSCADOR -->
                    <div>
                        <input class="searchBox--input" type="text" v-model="searchInput" size="42" placeholder="Busca por ISBN, Título, Autor, año o Estante"/>
                    </div>
                    <!-- -------- -->
                    <div>
                        <button type="button" :disabled="pageNumber === 0" @click="pageNumber = 0"><<</button>
                        <button type="button" :disabled="pageNumber === 0" @click="prevPage"><</button>
                        <span style="padding:10px">Pág. {{ pageNumber+1 }} de {{ pageCount }}</span>
                        <button type="button" :disabled="pageNumber >= pageCount -1" @click="nextPage">></button>
                        <button type="button" :disabled="pageNumber >= pageCount -1" @click="pageNumber = pageCount-1">>></button>
                    </div>
                    <div>
                        <span>Nº de registros por pág. </span>
                        <select v-model="rowsPerPage">
                            <option value="5">5</option>
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                </div>
                <!-- ---------- -->
                <table v-if="filteredData.length != 0">
                    <tr class="tableHeader">
                        <th v-for="row in headers">{{ row }}</th>
                    </tr>
                    <tr v-for="(row,index) in paginatedData" class="tableRow" @click="(currentIDSelected=index),(showTarjetaView=true)">
                        <!-- <td>{{ row.isbn }}</td> -->
                        <td>{{ row.title }}</td>
                        <td>{{ row.author }}</td>
                        <td>{{ row.year }}</td>
                        <td>{{ row.name_storage }}-{{ row.shelf }}</td>
                    </tr>
                </table>   
                <div v-else-if="isLoading">
                    <h3>⌛️ Cargando ⌛️</h3>
                </div>  
                <div v-else>
                    <h3>No se han encontrado datos con este filtro.</h3>
                </div>
            </div>
        </form>
    </div>

    <div v-if="showTarjetaView">
        <div class="floatingForm_bg" @click="showTarjetaView = false"></div>
        <div class=" floatingForm">
            <BookView :user="user" :book="data[currentIDSelected]"/>
        </div>
    </div>
</template>

<style scoped>
    .searchBox--input {
        width: 80%;
    }
    .tableRow:hover {
        cursor: pointer;
        background-color: var(--button1);
    }
</style>
