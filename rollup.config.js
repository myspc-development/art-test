const babel = require('@rollup/plugin-babel').default;
const nodeResolve = require('@rollup/plugin-node-resolve').nodeResolve;
const commonjs = require('@rollup/plugin-commonjs');
const postcss = require('rollup-plugin-postcss');

const extensions = ['.js', '.jsx'];

function createConfig(input, file, name, globals = {}, external = Object.keys(globals)) {
  return {
    input,
    output: {
      file,
      format: 'iife',
      name,
      globals,
    },
    external,
    plugins: [
      nodeResolve({ extensions }),
      babel({ babelHelpers: 'bundled', extensions }),
      commonjs(),
    ],
  };
}

const configs = [
  createConfig('assets/js/OrganizationSubmissionForm.jsx', 'assets/js/ap-org-submission.js', 'APOrgSubmission', { react: 'React' }),
  createConfig('assets/js/SidebarMenu.jsx', 'assets/js/sidebar-menu.js', 'APSidebarMenu', { react: 'React' }),
  createConfig('assets/js/dashboard.jsx', 'assets/js/ap-dashboard.js', 'APDashboard', {
    react: 'React',
    'react-dom': 'ReactDOM',
    'assets/js/SidebarMenu.jsx': 'APSidebarMenu',
    'assets/js/rolesMenus.js': 'rolesMenus'
  }, ['react', 'react-dom', 'assets/js/SidebarMenu.jsx', 'assets/js/rolesMenus.js']),
  createConfig(
    'assets/js/admin-dashboard-widgets-editor.jsx',
    'assets/js/admin-dashboard-widgets-editor.js',
    'APDashboardWidgetsEditor',
    { react: 'React', 'react-dom/client': 'ReactDOM' }
  ),
  createConfig(
    'src/index.js',
    'dist/react-form.js',
    'APReactForm',
    { react: 'React', 'react-dom/client': 'ReactDOM' }
  ),
  createConfig(
    'assets/js/AppDashboard.js',
    'assets/js/app-dashboard.js',
    'APDashboardApp',
    { react: 'React', 'react-dom': 'ReactDOM', 'chart.js/auto': 'Chart' }
  ),
  createConfig(
    'assets/react/RoleMatrix.jsx',
    'dist/role-matrix.js',
    'APRoleMatrix',
    { react: 'React', 'react-dom': 'ReactDOM' }
  ),
  createConfig(
    'assets/js/react-widgets.js',
    'assets/js/react-widgets.bundle.js',
    'APReactWidgets',
    { react: 'React', 'react-dom/client': 'ReactDOM' }
  ),
  createConfig(
    'assets/js/ap-org-roles.js',
    'assets/js/ap-org-roles.bundle.js',
    'APOrgRoles',
    {
      '@wordpress/element': 'wp.element',
      '@wordpress/api-fetch': 'wp.apiFetch'
    }
  ),
];

configs.push({
  input: 'assets/css/main.css',
  output: {
    file: 'dist/bundle.css',
    format: 'es'
  },
  plugins: [
    postcss({
      extract: true,
      minimize: true,
      plugins: []
    })
  ]
});

module.exports = configs;
