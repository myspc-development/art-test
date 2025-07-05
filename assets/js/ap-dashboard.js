(function(){
  const { createElement, render, useState } = wp.element;
  const rolesMenus = {
    user: [
      { label: "Dashboard", icon: "Home", section: "dashboard" },
      { label: "My Profile", icon: "User", section: "profile" },
      { label: "Events", icon: "Calendar", section: "events" },
      { label: "Favorites", icon: "Heart", section: "favorites" }
    ],
    artist: [
      { label: "Artist Dashboard", icon: "Palette", section: "dashboard" },
      { label: "Edit Profile", icon: "User", section: "profile" },
      { label: "My Artworks", icon: "Image", section: "artworks" },
      { label: "Add Artwork", icon: "Plus", section: "add-artwork" },
      { label: "My Events", icon: "Calendar", section: "events" },
      { label: "My Collections", icon: "Folder", section: "collections" }
    ],
    organization: [
      { label: "Org Dashboard", icon: "Building2", section: "dashboard" },
      { label: "Edit Org Profile", icon: "Edit", section: "profile" },
      { label: "Members", icon: "Users", section: "members" },
      { label: "Add Member", icon: "UserPlus", section: "add-member" },
      { label: "Org Events", icon: "Calendar", section: "events" },
      { label: "Add Event", icon: "Plus", section: "add-event" },
      { label: "My Collections", icon: "Folder", section: "collections" }
    ],
    curator: [
      { label: "Curator Dashboard", icon: "LayoutGrid", section: "dashboard" },
      { label: "My Collections", icon: "Folder", section: "collections" }
    ]
  };

  function SidebarMenu({ role, activeSection, setActiveSection }) {
    const menu = rolesMenus[role] || [];
    return createElement(
      'nav',
      { className: 'ap-dashboard-sidebar' },
      createElement(
        'ul',
        null,
        menu.map(function(item){
          const active = item.section === activeSection;
          return createElement(
            'li',
            { key: item.section },
            createElement(
              'button',
              {
                type: 'button',
                onClick: function(){ setActiveSection(item.section); },
                className: 'ap-sidebar-link ' + (active ? 'active' : '')
              },
              item.label
            )
          );
        })
      )
    );
  }

  function Dashboard({ role }) {
    const [activeSection, setActiveSection] = useState((rolesMenus[role] && rolesMenus[role][0] ? rolesMenus[role][0].section : ''));
    return createElement(
      'div',
      { className: 'ap-dashboard-wrapper' },
      createElement(SidebarMenu, { role, activeSection, setActiveSection }),
      createElement(
        'div',
        { className: 'ap-dashboard-main' },
        (rolesMenus[role] || []).map(function(item){
          return createElement(
            'section',
            {
              key: item.section,
              style: { display: activeSection === item.section ? 'block' : 'none' }
            },
            createElement('div', { id: 'ap-' + item.section })
          );
        })
      )
    );
  }

  document.addEventListener('DOMContentLoaded', function(){
    const root = document.getElementById('ap-dashboard-root');
    if (root) {
      render(createElement(Dashboard, { role: APDashboard.role }), root);
    }
  });
})();
