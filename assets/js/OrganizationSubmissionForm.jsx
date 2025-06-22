import React, { useState } from 'react';

export default function OrganizationSubmissionForm() {
  const [title, setTitle] = useState('');
  const [description, setDescription] = useState('');
  const [website, setWebsite] = useState('');
  const [email, setEmail] = useState('');
  const [images, setImages] = useState([]);
  const [previews, setPreviews] = useState([]);
  const [loading, setLoading] = useState(false);
  const [message, setMessage] = useState('');

  const handleFileChange = (e) => {
    const files = Array.from(e.target.files).slice(0, 5);
    setImages(files);
    setPreviews(files.map(file => URL.createObjectURL(file)));
  };

  const uploadMedia = async (file) => {
    const formData = new FormData();
    formData.append('file', file);

    const res = await fetch(APSubmission.mediaEndpoint, {
      method: 'POST',
      headers: {
        'X-WP-Nonce': APSubmission.nonce
      },
      body: formData
    });

    const json = await res.json();
    if (!res.ok) throw new Error(json.message || 'Upload failed');
    return json.id;
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setMessage('');

    try {
      const imageIds = [];
      for (const file of images) {
        const id = await uploadMedia(file);
        imageIds.push(id);
      }

      const payload = { post_type: 'artpulse_org', title };
      const fd = new FormData(e.target);
      fd.delete('title');
      fd.delete('images[]');
      for (const [key, value] of fd.entries()) {
        payload[key] = value;
      }
      document.querySelectorAll('form input[type="checkbox"]').forEach(cb => {
        if (!fd.has(cb.name)) payload[cb.name] = '0';
      });
      payload.image_ids = imageIds;

      const res = await fetch(APSubmission.endpoint, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': APSubmission.nonce
        },
        body: JSON.stringify(payload)
      });

      const json = await res.json();
      if (!res.ok) throw new Error(json.message || 'Submission failed');

      setMessage('Submission successful!');
      setTitle('');
      setDescription('');
      setWebsite('');
      setEmail('');
      setImages([]);
      setPreviews([]);
    } catch (err) {
      console.error(err);
      setMessage(`Error: ${err.message}`);
    } finally {
      setLoading(false);
    }
  };

  return (
    <form onSubmit={handleSubmit} className="p-4 max-w-xl mx-auto rounded-xl shadow bg-white space-y-4">
      <h2 className="text-xl font-bold">Submit Organization</h2>

      <input
        className="w-full p-2 border rounded"
        type="text"
        placeholder="Organization Name"
        value={title}
        onChange={e => setTitle(e.target.value)}
        required
      />

      <textarea
        className="w-full p-2 border rounded"
        placeholder="Description"
        value={description}
        onChange={e => setDescription(e.target.value)}
        required
      />

      <input
        className="w-full p-2 border rounded"
        type="text"
        placeholder="Website"
        value={website}
        onChange={e => setWebsite(e.target.value)}
      />

      <input
        className="w-full p-2 border rounded"
        type="email"
        placeholder="Email"
        value={email}
        onChange={e => setEmail(e.target.value)}
        required
      />

      <input
        className="w-full"
        type="file"
        multiple
        accept="image/*"
        onChange={handleFileChange}
      />

      <div className="flex gap-2 flex-wrap">
        {previews.map((src, i) => (
          <img key={i} src={src} alt="" className="w-24 h-24 object-cover rounded border" />
        ))}
      </div>

      <button
        className="bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700"
        type="submit"
        disabled={loading}
      >
        {loading ? 'Submitting...' : 'Submit'}
      </button>

      {message && <p className="text-sm text-center text-gray-700 mt-2">{message}</p>}
    </form>
  );
}
