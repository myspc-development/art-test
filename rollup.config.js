const babel = require('@rollup/plugin-babel').default;
const nodeResolve = require('@rollup/plugin-node-resolve').nodeResolve;

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
    plugins: [nodeResolve({ extensions }), babel({ babelHelpers: 'bundled', extensions })],
  };
}

module.exports = [
  createConfig('assets/js/OrganizationSubmissionForm.jsx', 'assets/js/ap-org-submission.js', 'APOrgSubmission', { react: 'React' }),
  createConfig('assets/js/SidebarMenu.jsx', 'assets/js/sidebar-menu.js', 'APSidebarMenu', { react: 'React', 'lucide-react': 'lucideReact' }),
  createConfig('assets/js/dashboard.jsx', 'assets/js/ap-dashboard.js', 'APDashboard', {
    react: 'React',
    'react-dom': 'ReactDOM',
    'assets/js/SidebarMenu.jsx': 'APSidebarMenu',
    'assets/js/rolesMenus.js': 'rolesMenus',
    'lucide-react': 'lucideReact'
  }, ['react', 'react-dom', 'assets/js/SidebarMenu.jsx', 'assets/js/rolesMenus.js', 'lucide-react']),
];
