const { __ } = wp.i18n;

export default function DashboardNavbar({ userRole, onLogout }) {
  return (
    <nav className="bg-white border-b shadow-sm px-4 py-2 flex justify-between items-center">
      <div className="text-lg font-bold">{__('ArtPulse Dashboard', 'artpulse')}</div>
      <div className="flex gap-4 items-center">
        {userRole === 'artist' && <a href="#/artist" className="text-blue-600">{__('Artist', 'artpulse')}</a>}
        {userRole === 'organization' && <a href="#/org" className="text-blue-600">{__('Organization', 'artpulse')}</a>}
        {userRole === 'member' && <a href="#/member" className="text-blue-600">{__('Member', 'artpulse')}</a>}
        <button onClick={onLogout} className="bg-red-500 text-white px-3 py-1 rounded">{__('Logout', 'artpulse')}</button>
      </div>
    </nav>
  );
}
