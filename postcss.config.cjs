// postcss.config.cjs
const postcssImport = require('postcss-import');
const postcssNested = require('postcss-nested');
const autoprefixer = require('autoprefixer');
const cssnano = require('cssnano');
const purgecss = require('@fullhuman/postcss-purgecss').default;

module.exports = {
  plugins: [
    postcssImport,
    postcssNested,
    autoprefixer,
    cssnano(), // Minify CSS
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
