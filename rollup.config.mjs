import babel from '@rollup/plugin-babel';
import { nodeResolve } from '@rollup/plugin-node-resolve';
import commonjs from '@rollup/plugin-commonjs';
import postcss from 'rollup-plugin-postcss';
import replace from '@rollup/plugin-replace';

const extensions = ['.js', '.jsx'];

export default {
  input: 'assets/js/app-dashboard.jsx',
  output: {
    file: 'dist/app-dashboard.js',
    format: 'iife',
    name: 'APDashboardApp',
    globals: {
      react: 'React',
      'react-dom/client': 'ReactDOM',
      'react-dom': 'ReactDOM',
      'react-grid-layout': 'ReactGridLayout',
      'chart.js/auto': 'Chart'
    },
    exports: 'none'
  },
  external: ['react', 'react-dom', 'react-dom/client', 'react-grid-layout', 'chart.js/auto'],
  plugins: [
    nodeResolve({ extensions }),
    commonjs(),
    postcss({
      inject: true,
      minimize: true
    }),
    babel({
      babelHelpers: 'bundled',
      extensions,
      presets: ['@babel/preset-env', '@babel/preset-react'],
      exclude: 'node_modules/**'
    }),
    replace({
      'process.env.NODE_ENV': JSON.stringify(process.env.NODE_ENV || 'production'),
      preventAssignment: true
    })
  ]
};

