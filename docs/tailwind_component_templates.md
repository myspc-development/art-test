# Tailwind Component Templates for ArtPulse

Reusable Tailwind-styled components for the ArtPulse plugin. See the
[Tailwind Design Guide](tailwind-design-guide.md) for overall layout and
button conventions.

---

## ✅ Event Card
```html
<div class="bg-white rounded-xl shadow hover:shadow-lg transition p-4 flex flex-col gap-2">
  <img src="{{ event_image_url }}" alt="{{ event_title }}" class="w-full h-48 object-cover rounded-md">
  <div>
    <h3 class="text-lg font-semibold text-gray-900">{{ event_title }}</h3>
    <p class="text-sm text-gray-600">{{ event_venue }} — {{ event_date }}</p>
  </div>
  <div class="mt-auto flex justify-between items-center">
    <button class="bg-blue-600 text-white text-sm px-4 py-2 rounded hover:bg-blue-700">RSVP</button>
    <button class="text-yellow-500 hover:text-yellow-600 text-xl">★</button>
  </div>
</div>
```

---

## 🧑‍🎤 Artist Profile Card
```html
<div class="bg-white rounded-lg shadow p-4 text-center">
  <img src="{{ avatar_url }}" class="w-24 h-24 mx-auto rounded-full object-cover mb-2" />
  <h3 class="text-lg font-medium text-gray-900">{{ artist_name }}</h3>
  <p class="text-sm text-gray-500">{{ genre }}</p>
  <a href="{{ profile_url }}" class="text-blue-600 text-sm hover:underline mt-2 inline-block">View Profile</a>
</div>
```

---

## 🗓️ Calendar Day Cell
```html
<div class="bg-gray-50 border border-gray-200 rounded p-2 h-40 overflow-auto">
  <p class="text-xs text-gray-500">{{ day_label }}</p>
  {% for event in events %}
    <div class="mt-1 p-1 bg-white rounded hover:bg-blue-50 text-xs text-gray-800 cursor-pointer">
      {{ event.title }}
    </div>
  {% endfor %}
</div>
```

---

## 📊 Dashboard Widget
```html
<div class="bg-white shadow-sm rounded-lg p-4">
  <h4 class="text-sm text-gray-500 font-medium mb-1">{{ label }}</h4>
  <div class="text-2xl font-bold text-gray-900">{{ value }}</div>
</div>
```

---

## ✍️ Submission Form
```html
<form class="bg-white shadow rounded-lg p-6 space-y-4">
  <div>
    <label class="block text-sm font-medium text-gray-700">Event Title</label>
    <input type="text" name="title" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500">
  </div>
  <div>
    <label class="block text-sm font-medium text-gray-700">Date</label>
    <input type="date" name="date" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
  </div>
  <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Submit Event</button>
</form>
```
