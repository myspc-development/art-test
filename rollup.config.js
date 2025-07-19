const babel = require('@rollup/plugin-babel').default;
const nodeResolve = require('@rollup/plugin-node-resolve').nodeResolve;
const commonjs = require('@rollup/plugin-commonjs');
const typescript = require('@rollup/plugin-typescript');
const postcss = require('rollup-plugin-postcss');

const extensions = ['.js', '.jsx', '.ts', '.tsx'];

function createConfig(input, file, name, globals = {}, external = Object.keys(globals), useTS = false) {
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
      useTS && typescript(),
      commonjs(),
    ].filter(Boolean),
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
    'assets/js/ap-widget-matrix.js',
    'dist/widget-matrix.js',
    'APWidgetMatrix',
    { react: 'React', 'react-dom/client': 'ReactDOM' },
    undefined,
    true
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
  createConfig(
    'src/admin/WidgetEditorApp.jsx',
    'build/widget-editor-ui.js',
    'APWidgetEditorUI',
    { react: 'React', 'react-dom/client': 'ReactDOM', 'react-grid-layout': 'ReactGridLayout' }
  ),
  createConfig(
    'assets/js/DashboardContainer.jsx',
    'assets/js/dashboard-container.js',
    'APDashboardContainer',
    { react: 'React', 'react-dom/client': 'ReactDOM', 'react-grid-layout': 'ReactGridLayout' }
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
