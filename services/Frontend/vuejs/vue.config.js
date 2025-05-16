const { defineConfig } = require('@vue/cli-service')

module.exports = defineConfig({
  transpileDependencies: true,
  lintOnSave: false // Temporarily disable linting on save to avoid initial setup issues
}) 