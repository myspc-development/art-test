import React from 'react';
import { createRoot } from 'react-dom/client';
import ReactForm from './components/ReactForm.js';

const container = document.getElementById( 'react-form-root' );
if (container) {
	const props = {
		type: container.dataset.type || 'default'
	};
	const root  = createRoot( container );
	root.render( < ReactForm {...props} / > );
}
