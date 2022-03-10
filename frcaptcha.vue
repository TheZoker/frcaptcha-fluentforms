<template>
    <el-form-item :label="item.settings.label">
        <img style="max-width: 60px;" :src="plugin_url+'img/frcaptcha-placeholder.png'" alt="frcaptcha-placeholder">
    </el-form-item>
</template>

<script>
export default {
    name: 'frcaptcha',
    props: ['item'],
    data() {
        return {
            plugin_url: window.FluentFormApp.plugin_public_url
        }
    }
}
</script>
