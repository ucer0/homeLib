<script setup>
import { RouterLink, RouterView } from 'vue-router';
const props = defineProps({
  user: String,
})
</script>

<template>
  <div class="menuContainer">
      <h1 class="menuContainer--logo">HomeLib📖</h1>
      <div>
        <RouterLink to="/">Biblioteca</RouterLink>
        <RouterLink to="/add">Añadir Libro</RouterLink>
        <RouterLink to="/account">Cuenta</RouterLink>
      </div>
  </div>
  <RouterView :user="user"/>
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
