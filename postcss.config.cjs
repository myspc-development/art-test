const purgecss = require('@fullhuman/postcss-purgecss');

module.exports = {
  plugins: [
    require('postcss-import'),
    require('postcss-nested'),
    require('autoprefixer'),
    require('cssnano')(), // Minify CSS
    purgecss({
      content: [
        './templates/**/*.php',
        './src/**/*.js',
        './includes/**/*.php',
      ],
      safelist: ['wp-block', /^ap-/], // keep necessary class prefixes
      defaultExtractor: content => content.match(/[\w-/:]+(?<!:)/g) || [],
    }),
  ],
};
