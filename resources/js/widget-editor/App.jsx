import React, { useState, useEffect } from 'react';
import WidgetList from './WidgetList';
import RoleSelector from './RoleSelector';
import { loadLayout, saveLayout } from './utils/storage';
import { fetchWidgets, fetchRoles } from './utils/api';

export default function App() {
  const [widgets, setWidgets] = useState([]);
  const [layout, setLayout] = useState([]);
  const [roles, setRoles] = useState([]);
  const [selectedRole, setSelectedRole] = useState('');

  useEffect(() => {
    fetchRoles().then(setRoles).catch(() => {});
    fetchWidgets().then(setWidgets).catch(() => {});
  }, []);

  useEffect(() => {
    if (selectedRole) {
      const stored = loadLayout(selectedRole);
      if (stored) setLayout(stored);
    }
  }, [selectedRole]);

  const handleSave = () => {
    saveLayout(selectedRole, layout);
  };

  return (
    <div>
      <h2>Widget Editor</h2>
      <RoleSelector roles={roles} value={selectedRole} onChange={setSelectedRole} />
      <WidgetList widgets={widgets} layout={layout} setLayout={setLayout} />
      <button onClick={handleSave}>💾 Save</button>
    </div>
  );
}
