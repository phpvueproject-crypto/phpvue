<template>
    <div v-if="!pageLoading" class="auth-page">
        <router-view></router-view>
    </div>
</template>

<script>
import {mapState} from 'vuex';

export default {
    computed: {
        ...mapState({
            pageLoading: state => state.pageLoading
        })
    },
    created() {
        document.body.style.overflow = 'auto';
        document.body.className = 'hold-transition login-page';
    }
}
</script>

<style lang="scss" scoped>
.auth-page{
    position:   relative;
    min-height: 100vh;
    text-align: center;
}
</style>
