import postcssImport from 'postcss-import';
import postcssNested from 'postcss-nested';
import autoprefixer from 'autoprefixer';
import cssnano from 'cssnano';
import purgecss from '@fullhuman/postcss-purgecss';

export default {
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
