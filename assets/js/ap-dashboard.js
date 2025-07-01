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
      { label: "My Events", icon: "Calendar", section: "events" }
    ],
    organization: [
      { label: "Org Dashboard", icon: "Building2", section: "dashboard" },
      { label: "Edit Org Profile", icon: "Edit", section: "profile" },
      { label: "Members", icon: "Users", section: "members" },
      { label: "Add Member", icon: "UserPlus", section: "add-member" },
      { label: "Org Events", icon: "Calendar", section: "events" },
      { label: "Add Event", icon: "Plus", section: "add-event" }
    ]
  };

  function SidebarMenu({ role, activeSection, setActiveSection }) {
    const menu = rolesMenus[role] || [];
    return createElement(
      'nav',
      { className: 'w-full md:w-60 bg-white shadow-md py-6 px-3 rounded-2xl' },
      createElement(
        'ul',
        { className: 'space-y-2' },
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
                className: 'w-full flex items-center gap-2 px-3 py-2 rounded-xl text-left ' + (active ? 'bg-indigo-100 font-medium' : 'hover:bg-indigo-50')
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
      { className: 'ap-dashboard-react flex gap-6' },
      createElement(SidebarMenu, { role, activeSection, setActiveSection }),
      createElement(
        'div',
        { className: 'flex-1' },
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
