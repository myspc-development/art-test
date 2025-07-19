import React, { useState, useEffect } from 'react';
import WidgetList from './WidgetList';
import RoleSelector from './RoleSelector';
import SaveButton from './SaveButton';
import PreviewPane from './PreviewPane';
import { loadLayout, saveLayout } from './utils/storage';
import { fetchWidgets, fetchRoles } from './utils/api';

export default function App() {
  const [widgets, setWidgets] = useState([]);
  const [layout, setLayout] = useState([]);
  const [roles, setRoles] = useState([]);
  const [selectedRole, setSelectedRole] = useState('');
  const [showPreview, setShowPreview] = useState(false);

  useEffect(() => {
    fetchRoles().then(setRoles).catch(() => {});
    fetchWidgets().then(setWidgets).catch(() => {});
  }, []);

  useEffect(() => {
    if (!selectedRole) return;
    loadLayout(selectedRole).then(l => setLayout(Array.isArray(l) ? l : []));
  }, [selectedRole]);

  const handleSave = () => {
    saveLayout(selectedRole, layout);
  };

  return (
    <div>
      <h2>Widget Editor</h2>
      <RoleSelector roles={roles} value={selectedRole} onChange={setSelectedRole} />
      <WidgetList widgets={widgets} layout={layout} setLayout={setLayout} />
      <SaveButton onClick={handleSave} disabled={!selectedRole} />
      <button type="button" onClick={() => setShowPreview(!showPreview)}>
        {showPreview ? 'Hide Preview' : 'Preview'}
      </button>
      {showPreview && (
        <PreviewPane layout={layout} widgets={widgets} />
      )}
    </div>
  );
}
