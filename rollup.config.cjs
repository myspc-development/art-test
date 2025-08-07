const babel = require('@rollup/plugin-babel').default;
const nodeResolve = require('@rollup/plugin-node-resolve').nodeResolve;
const commonjs = require('@rollup/plugin-commonjs');
const postcss = require('rollup-plugin-postcss');
const replace = require('@rollup/plugin-replace');

const extensions = ['.js', '.jsx'];

function createDashboardBundle(input, output, globalName) {
  return {
    input,
    output: {
      file: output,
      format: 'iife',
      name: globalName,
      globals: {
        react: 'React',
        'react-dom/client': 'ReactDOM',
        'react-dom': 'ReactDOM',
        'react-grid-layout': 'ReactGridLayout'
      }
    },
    external: ['react', 'react-dom', 'react-dom/client', 'react-grid-layout'],
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
}

module.exports = [
  createDashboardBundle('assets/js/AppDashboard.js', 'dist/app-dashboard.js', 'APDashboardApp'),
  createDashboardBundle('assets/js/react-widgets.js', 'dist/react-widgets.js', 'APReactWidgets'),
  createDashboardBundle('assets/js/DashboardContainer.jsx', 'dist/dashboard-container.js', 'APDashboardContainer')
];
