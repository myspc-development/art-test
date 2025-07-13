export default function DashboardNavbar({ userRole, onLogout }) {
  return (
    <nav className="bg-white border-b shadow-sm px-4 py-2 flex justify-between items-center">
      <div className="text-lg font-bold">ArtPulse Dashboard</div>
      <div className="flex gap-4 items-center">
        {userRole === 'artist' && <a href="#/artist" className="text-blue-600">Artist</a>}
        {userRole === 'organization' && <a href="#/org" className="text-blue-600">Organization</a>}
        {userRole === 'member' && <a href="#/member" className="text-blue-600">Member</a>}
        <button onClick={onLogout} className="bg-red-500 text-white px-3 py-1 rounded">Logout</button>
      </div>
    </nav>
  );
}
