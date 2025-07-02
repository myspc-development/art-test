# Tailwind UI Extensions for ArtPulse

Reusable Tailwind UI patterns for advanced interactivity.

---

## 🚪 Modal Window
```html
<div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden" id="ap-modal">
  <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6">
    <h2 class="text-lg font-semibold mb-4">Modal Title</h2>
    <p class="text-sm text-gray-600">Modal content goes here...</p>
    <div class="mt-4 flex justify-end">
      <button class="mr-2 px-4 py-2 rounded bg-gray-200 text-sm">Cancel</button>
      <button class="px-4 py-2 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">Confirm</button>
    </div>
  </div>
</div>
```

---

## 📝 Tabs Interface
```html
<div class="w-full">
  <div class="flex border-b space-x-4">
    <button class="px-4 py-2 font-medium text-sm border-b-2 border-blue-600">Overview</button>
    <button class="px-4 py-2 font-medium text-sm text-gray-500 hover:text-gray-700">Activity</button>
    <button class="px-4 py-2 font-medium text-sm text-gray-500 hover:text-gray-700">Settings</button>
  </div>
  <div class="mt-4">
    <p class="text-sm">Tab content goes here...</p>
  </div>
</div>
```

---

## ▶️ Accordion Panel
```html
<div class="border-b">
  <button class="w-full text-left py-3 px-4 text-sm font-medium flex justify-between items-center">
    What is ArtPulse?
    <span class="transform rotate-0 transition-transform">▼</span>
  </button>
  <div class="px-4 pb-4 text-sm text-gray-600 hidden">
    ArtPulse is a plugin for managing creative events and artists.
  </div>
</div>
```

---

## 📢 Toast Notification
```html
<div class="fixed bottom-4 right-4 z-50 bg-white border border-gray-200 shadow-lg rounded-lg p-4 w-72 hidden" id="ap-toast">
  <div class="flex items-start">
    <span class="text-green-500 text-xl mr-2">✓</span>
    <div>
      <p class="text-sm font-semibold text-gray-800">Success!</p>
      <p class="text-xs text-gray-500">Your RSVP was saved.</p>
    </div>
  </div>
</div>
```

---

## 🔍 Filter Bar with Dropdowns
```html
<div class="flex flex-wrap gap-4 items-end">
  <div>
    <label class="text-sm font-medium text-gray-700">Category</label>
    <select class="block w-48 mt-1 border border-gray-300 rounded-md p-2 text-sm">
      <option>All</option>
      <option>Music</option>
      <option>Visual Arts</option>
    </select>
  </div>
  <div>
    <label class="text-sm font-medium text-gray-700">Keyword</label>
    <input type="text" class="mt-1 block w-64 border border-gray-300 rounded-md p-2 text-sm">
  </div>
  <button class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">Search</button>
</div>
```

---

## 📊 Responsive Table with Pagination
```html
<div class="overflow-x-auto">
  <table class="min-w-full divide-y divide-gray-200 text-sm">
    <thead class="bg-gray-50">
      <tr>
        <th class="px-4 py-2 text-left text-gray-600 font-medium">Name</th>
        <th class="px-4 py-2 text-left text-gray-600 font-medium">Email</th>
        <th class="px-4 py-2 text-left text-gray-600 font-medium">Status</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-gray-100">
      <tr>
        <td class="px-4 py-2">Jane Doe</td>
        <td class="px-4 py-2">jane@example.com</td>
        <td class="px-4 py-2 text-green-600 font-semibold">Attending</td>
      </tr>
    </tbody>
  </table>
  <div class="flex justify-end mt-4 space-x-2">
    <button class="text-sm px-3 py-1 border border-gray-300 rounded">Prev</button>
    <button class="text-sm px-3 py-1 border border-gray-300 rounded bg-gray-100">1</button>
    <button class="text-sm px-3 py-1 border border-gray-300 rounded">Next</button>
  </div>
</div>
```
