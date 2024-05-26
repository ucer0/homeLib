<script setup>
import { RouterLink, RouterView } from 'vue-router';
</script>

<template>
  <div class="menuContainer">
      <h1 class="menuContainer--logo">HomeLib📖</h1>
      <div>
        <!-- Poder hacer que se visiten otros perfiles? -->
        <!-- <RouterLink to="/_$POST['id_user']">Biblioteca</RouterLink> -->
        <RouterLink to="/">Biblioteca</RouterLink>
        <RouterLink to="/add">Añadir Libro</RouterLink>
        <RouterLink to="/account">Cuenta</RouterLink>
        <!-- <RouterLink to="/settings">Ajustes</RouterLink> -->
      </div>
  </div>
  <RouterView />
</template>

<style scoped>

#app {
  font-family: Avenir, Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-align: center;
  color: #2c3e50;
  margin: 0 auto;
}

.menuContainer {
  display: flex;
  flex-direction: column;
  text-align: center;
  align-items: center;
  justify-content: center;
}

</style>
