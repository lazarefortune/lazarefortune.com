const Encore = require('@symfony/webpack-encore');
const webpack = require('webpack');

if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')
    .configureDevServerOptions(options => {
        options.host = '0.0.0.0';
        options.allowedHosts = 'all';
    })
    .addEntry('app', './assets/app.js')
    .addEntry('studio', './assets/studio/index.jsx')
    .enablePostCssLoader()
    .splitEntryChunks()
    .enableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = '3.23';
    })
    .enableReactPreset()
    .addPlugin(new webpack.ProvidePlugin({
        React: 'react',
    }))
;

module.exports = Encore.getWebpackConfig();
