const purgecss = require('@fullhuman/postcss-purgecss')({
  content: [
    './**/*.php',
    './assets/js/**/*.js',
    './assets/js/**/*.jsx',
  ],
  safelist: [/^wp-/, /^ap-/, /^admin-/],
  defaultExtractor: content => content.match(/[\w-/:]+(?<!:)/g) || []
});

module.exports = {
  plugins: [
    require('postcss-import'),
    require('postcss-nested'),
    require('autoprefixer'),
    require('cssnano')({
      preset: 'default',
    }),
    ...(process.env.NODE_ENV === 'production' ? [purgecss] : [])
  ]
};
