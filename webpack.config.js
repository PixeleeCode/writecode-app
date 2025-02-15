const Encore = require("@symfony/webpack-encore");

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || "dev");
}

Encore
    // directory where compiled assets will be stored
    .setOutputPath("public/build/")
    // public path used by the web server to access the output path
    .setPublicPath("/build")
    // only needed for CDN's or sub-directory deploy
    // .setManifestKeyPrefix('build/')

    /*
     * ENTRY CONFIG
     *
     * Each entry will result in one JavaScript file (e.g. app.js)
     * and one CSS file (e.g. app.scss) if your JavaScript imports CSS.
     */
    .addEntry("js", "./assets/app.js")
    .addStyleEntry("scss", "./assets/scss/app.scss")
    .addStyleEntry("course", "./assets/scss/course.scss")
    .addStyleEntry("github", "./assets/scss/markdown/github.scss")

    // enables the Symfony UX Stimulus bridge (used in assets/bootstrap.js)
    .enableStimulusBridge("./assets/controllers.json")

    // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
    .splitEntryChunks()

    // will require an extra script tag for runtime.js
    // but, you probably want this, unless you're building a single-page app
    .enableSingleRuntimeChunk()

    /*
     * FEATURE CONFIG
     *
     * Enable & configure other features below. For a full
     * list of features, see:
     * https://symfony.com/doc/current/frontend.html#adding-more-features
     */
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    // enables hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())

    .configureImageRule({
        // tell Webpack it should consider inlining
        type: "asset",
        // maxSize: 4 * 1024, // 4 kb - the default is 8kb
    })

    .configureFontRule({
        type: "asset",
        // maxSize: 4 * 1024
    })

    .configureBabel((config) => {
        config.plugins.push("@babel/plugin-proposal-class-properties");
    })

    // enables @babel/preset-env polyfills
    .configureBabelPresetEnv((config) => {
        // eslint-disable-next-line no-param-reassign
        config.useBuiltIns = "usage";
        // eslint-disable-next-line no-param-reassign
        config.corejs = 3;
    })

    // enable PostCSS
    .enablePostCssLoader((options) => {
        // eslint-disable-next-line no-param-reassign
        options.config = {
            path: "./postcss.config.js",
        };
    })

    // enables Sass/SCSS support
    .enableSassLoader(() => {}, {
        resolveUrlLoader: false,
    });

// uncomment if you use TypeScript
// .enableTypeScriptLoader()

// uncomment if you use React
// .enableReactPreset()

// uncomment to get integrity="..." attributes on your script & link tags
// requires WebpackEncoreBundle 1.4 or higher
// .enableIntegrityHashes(Encore.isProduction())

// uncomment if you're having problems with a jQuery plugin
// .autoProvidejQuery()

module.exports = Encore.getWebpackConfig();
