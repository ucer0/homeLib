<script>
import axios from 'axios';
export default {
    name: "Account",
    props: ["user"],
    data() {
        return {
            code: "",
            msg: "",
            csv: "",
            backup: "",
            dataUser: {},
            saveInput: {
                password: "",
                password2: ""
            },
            showMsg: false,
            isDisabled: true,
            isImportDisabled: true,
            // Devuelve la hora en este formato "YYYY-MM-DD HH:mm:ss"
            currentDate: (new Date().toISOString().slice(0,10)),
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
        async ajax(params) {
            const res = await axios.post('ajax.php', params);
            return res.data ? res.data : false;
        },

        async getUser() {
            this.dataUser = [];
            
            const res = await this.ajax({
                accion: 'getUser',
                id: this.user
            });

            if (typeof res.data !== 'undefined') {
                this.dataUser = res.data;
            } else {
                this.dataUser = [];
            }
        },

        async updateUser(arr) {
            const res = await this.ajax({
                accion: 'updateUser',
                id: this.user,
                data: this.dataUser,
                dataPwd: arr["password"]
            });
            this.code = res.code;
            this.msg = res.msg;
            this.showMsg = true;

            if (this.code == "SU0100") {
                this.isDisabled = true;
            }
        },

        async getDownloadableLibrary() {
            this.dataUser = [];
            
            const res = await this.ajax({
                accion: 'getDownloadableLibrary',
                id: this.user
            });

            if (typeof res.data !== 'undefined') {
                this.csv = res.data;
            } else {
                this.csv = "";
            }
        },

        async exportBackup() {
            this.dataUser = [];
            
            const res = await this.ajax({
                accion: 'exportBackup',
                id: this.user
            });

            if (typeof res.data !== 'undefined') {
                this.backup = res.data;
            } else {
                this.backup = "";
            }
        },

        async importBackup(arr) {
            if (confirm("Importar esta copia de seguridad sobreescribirá los datos actuales\n¿Quiere continuar?")) {
                const res = await this.ajax({
                    accion: 'importBackup',
                    id: this.user,
                    data: arr
                });

                this.code = res.code;
                this.msg = res.msg;
                this.showMsg = true; 
            } else {
                this.isImportDisabled = true;
            }
        },
        // ----------------------
        
        isDataValid() {
            if (this.saveInput['password']==this.saveInput['password2'] 
                && this.saveInput['password'].length >=8
                && this.dataUser.name_user.length > 4) {
                return true;
            } else {
                return false;
            }
        },

        formatDate() {
            var options = {year: 'numeric', month: 'long', day: 'numeric' };
            return new Date(this.dataUser.signupDate).toLocaleDateString('es-ES',options);
        },

        getFile() {
            let file = document.getElementById("importFile").files[0];
            if (/(\.csv)$/i.exec(file.name)) {
                let reader = new FileReader();
                reader.readAsText(file);
                reader.onload = () => {
                    this.csvImport = reader.result;
                    this.isImportDisabled = false;
                };
            } else {
                alert("Archivo Inválido");
                this.csvImport = false;
                this.isImportDisabled = true;
            }
        }
    },

    created() {
        this.getUser();
        this.getDownloadableLibrary();
        this.exportBackup();
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
            <div>
                <h3>Datos de la Cuenta</h3>
                <p>Cuenta creada el {{ formatDate() }}</p>
                <div class="filter" >
                    <div class="filter__input">
                        <label for="name_user">Usuario:</label>
                        <input type="text" v-model="dataUser.name_user" name="name_user" :disabled="isDisabled" maxlength="20" @input="dataUser.name_user = dataUser.name_user.replace(/[^0-9a-z]/g,'')">
                    </div>
                    <div class="filter__input">
                        <label for="mail">Mail:</label>
                        <input type="mail" v-model="dataUser.mail" name="mail" :disabled="isDisabled">
                    </div>
                    <div v-if="!isDisabled" class="filter__input">
                        <label for="pass">Contraseña:</label>
                        <input type="password" v-model="saveInput['password']" name="pass" :disabled="isDisabled">
                    </div>
                    <div v-if="!isDisabled" class="filter__input">
                        <label for="pass">Repetir:</label>
                        <input type="password" v-model="saveInput['password2']" name="pass" :disabled="isDisabled">
                    </div>
                </div>
                <div class="buttonDiv">
                    <button type="button" @click="isDisabled=!isDisabled">Editar Datos</button>
                    <button type="button" @click="updateUser(saveInput)" class="updateButton" :disabled="isDisabled || !isDataValid()">Guardar Cambios</button>
                </div>
            </div>

            <div>
                <h3>Exportar Biblioteca Personal</h3>
                <p>Para tener toda tu biblioteca de forma local</p>
                <div>
                    <a type="button" :download="'homeLib_'+currentDate+'.csv'" :href="'data:text/csv;base64,'+csv" class="updateButton" >Exportar Datos</a>
                </div>
            </div>

            <div>
                <h3>Copias de Seguridad</h3>
                <p>Importación y exportación de copia de seguridad personal</p>
                <div class="backupDiv">
                    <a type="button" :download="'backup_'+currentDate+'.csv'" :href="'data:text/csv;base64,'+backup" >Exportar Copia</a>
                    <div>
                        <label for="import">Archivo a importar:</label>
                        <input type="file" name="import" id="importFile" accept=".csv" @change="getFile()">
                    </div>
                    <button type="button" @click="importBackup(this.csvImport)" :disabled="isImportDisabled">Importar Copia</button>
                </div>
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

    .backupDiv {
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .buttonDiv {
        display: flex;
        justify-content: center;
    }
</style>