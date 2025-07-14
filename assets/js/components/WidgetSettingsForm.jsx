import React from "react";

export default function WidgetSettingsForm({ schema, values, onChange }) {
  return (
    <form className="space-y-4 p-4 bg-white rounded-xl shadow">
      {schema.map(({ key, label, type = "text" }) => {
        const value = values[key];

        if (type === "checkbox") {
          return (
            <label key={key} className="flex items-center space-x-2">
              <input
                type="checkbox"
                checked={!!value}
                onChange={(e) => onChange(key, e.target.checked)}
                className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
              />
              <span className="text-sm text-gray-700">{label}</span>
            </label>
          );
        }

        if (type === "number") {
          return (
            <div key={key}>
              <label className="block text-sm font-medium text-gray-700 mb-1">
                {label}
              </label>
              <input
                type="number"
                value={value}
                onChange={(e) =>
                  onChange(key, e.target.value === "" ? "" : parseFloat(e.target.value))
                }
                className="block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              />
            </div>
          );
        }

        // Default to text input
        return (
          <div key={key}>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              {label}
            </label>
            <input
              type="text"
              value={value}
              onChange={(e) => onChange(key, e.target.value)}
              className="block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            />
          </div>
        );
      })}
    </form>
  );
}
