<h2 class="ap-card__title">Admin Dashboard Widgets Editor – Help Guide</h2>
<h3>👋 Overview</h3>
<p>This tool allows administrators to customize the WordPress Dashboard for different types of users — such as members, artists, or organizations. You can reorder, show or hide widgets, and preview layouts per role.</p>

<h3>🔄 Role Selector</h3>
<p><strong>What it does:</strong> Lets you switch between user roles to customize their unique dashboard layout.</p>
<p><strong>How to use:</strong></p>
<ul>
<li>Use the dropdown to select a role (e.g., artist, member, organization).</li>
<li>The widget layout editor and preview will update to reflect that role's configuration.</li>
</ul>

<h3>🧩 Editing Widgets</h3>
<p><strong>What you can do:</strong></p>
<ul>
<li>Drag-and-drop widgets to rearrange.</li>
<li>Hide or show widgets with the 👁 toggle.</li>
<li>Add widgets not yet in the layout using the ➕ Add Widget panel.</li>
</ul>
<p><strong>How to save:</strong> Click the 💾 Save Layout button to store your changes for the selected role.</p>

<h3>👁 Live Preview</h3>
<p>As you customize the layout, a live preview is shown below the editor. It reflects:</p>
<ul>
<li>Current order</li>
<li>Visibility state</li>
<li>Actual widget output</li>
</ul>
<p>This helps you confirm what users will see when they log in.</p>

<h3>📝 Export / 📥 Import Layout</h3>
<p>Use this to back up or migrate dashboard layouts between sites.</p>
<ul>
<li><strong>Export:</strong> Copy the layout JSON.</li>
<li><strong>Import:</strong> Paste saved layout JSON and click Import Layout.</li>
</ul>
<p><strong>Layout format example:</strong></p>
<pre><code>[
  { "id": "welcome_widget", "visible": true },
  { "id": "sales_summary", "visible": false }
]
</code></pre>

<h3>🔄 Reset Layout</h3>
<p>Click the Reset Layout button to revert the selected role's dashboard to its default state.</p>
<p><strong>⚠️ Note:</strong> This only resets layout for the selected role — user customizations (if allowed) remain untouched.</p>

<h3>🔒 Access &amp; Permissions</h3>
<ul>
<li>Only users with the <code>manage_options</code> capability (typically administrators) can edit layouts.</li>
<li>End users (members, artists, etc.) will only see the dashboard, not the layout tools.</li>
</ul>

<h3>🛠 Advanced Notes</h3>
<ul>
<li>Layouts are stored per role using <code>wp_options</code>.</li>
<li>Fallback priority: user layout → role layout → default layout.</li>
<li>You can register widgets using <code>DashboardWidgetRegistry::register(...)</code>.</li>
</ul>
