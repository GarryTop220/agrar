<template>
  <div class="main-container">
    <Menu />
    <div class="main">
      <div class="menu_z">
        <menu_prof />
      </div>
      <history_comp />
    </div>
    <Footer />
  </div>
</template>

<script>
import Menu from '@/components/Menu.vue';
import Footer from '@/components/footer.vue';
import history_comp from "@/components/profile/profil-pages/profil-pages_comp/history.vue";
import menu_prof from "@/components/profile/comp_prof/menu_prof.vue";

export default {
  components: {
    Menu,
    Footer,
    history_comp,
    menu_prof
  },
  data() {
    return {};
  }
};
</script>

<style>
.main {
  margin-top: -100px;
  padding-bottom: 100px;
  display: flex;
  justify-content: center;
  gap: 15px;
}
</style>