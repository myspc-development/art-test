import React, { useState, useEffect } from 'react';

const ORG_FIELDS = [
  { name: 'ead_org_description', type: 'textarea', label: 'Description', required: true },
  { name: 'ead_org_website_url', type: 'url', label: 'Website' },
  { name: 'ead_org_logo_id', type: 'media', label: 'Logo' },
  { name: 'ead_org_banner_id', type: 'media', label: 'Banner' },
  { name: 'ead_org_type', type: 'select', label: 'Organization Type' },
  { name: 'ead_org_size', type: 'text', label: 'Organization Size' },
  { name: 'ead_org_facebook_url', type: 'url', label: 'Facebook URL' },
  { name: 'ead_org_twitter_url', type: 'url', label: 'Twitter URL' },
  { name: 'ead_org_instagram_url', type: 'url', label: 'Instagram URL' },
  { name: 'ead_org_linkedin_url', type: 'url', label: 'LinkedIn URL' },
  { name: 'ead_org_artsy_url', type: 'url', label: 'Artsy URL' },
  { name: 'ead_org_pinterest_url', type: 'url', label: 'Pinterest URL' },
  { name: 'ead_org_youtube_url', type: 'url', label: 'YouTube URL' },
  { name: 'ead_org_primary_contact_name', type: 'text', label: 'Primary Contact Name' },
  { name: 'ead_org_primary_contact_email', type: 'email', label: 'Primary Contact Email', required: true },
  { name: 'ead_org_primary_contact_phone', type: 'text', label: 'Primary Contact Phone' },
  { name: 'ead_org_primary_contact_role', type: 'text', label: 'Primary Contact Role' },
  { name: 'ead_org_street_address', type: 'text', label: 'Street Address' },
  { name: 'ead_org_postal_address', type: 'text', label: 'Postal Address' },
  { name: 'ead_org_venue_address', type: 'text', label: 'Venue Address' },
  { name: 'ead_org_venue_email', type: 'email', label: 'Venue Email' },
  { name: 'ead_org_venue_phone', type: 'text', label: 'Venue Phone' },
  { name: 'ead_org_monday_start_time', type: 'time', label: 'Monday Opening Time' },
  { name: 'ead_org_monday_end_time', type: 'time', label: 'Monday Closing Time' },
  { name: 'ead_org_monday_closed', type: 'checkbox', label: 'Closed on Monday' },
  { name: 'ead_org_tuesday_start_time', type: 'time', label: 'Tuesday Opening Time' },
  { name: 'ead_org_tuesday_end_time', type: 'time', label: 'Tuesday Closing Time' },
  { name: 'ead_org_tuesday_closed', type: 'checkbox', label: 'Closed on Tuesday' },
  { name: 'ead_org_wednesday_start_time', type: 'time', label: 'Wednesday Opening Time' },
  { name: 'ead_org_wednesday_end_time', type: 'time', label: 'Wednesday Closing Time' },
  { name: 'ead_org_wednesday_closed', type: 'checkbox', label: 'Closed on Wednesday' },
  { name: 'ead_org_thursday_start_time', type: 'time', label: 'Thursday Opening Time' },
  { name: 'ead_org_thursday_end_time', type: 'time', label: 'Thursday Closing Time' },
  { name: 'ead_org_thursday_closed', type: 'checkbox', label: 'Closed on Thursday' },
  { name: 'ead_org_friday_start_time', type: 'time', label: 'Friday Opening Time' },
  { name: 'ead_org_friday_end_time', type: 'time', label: 'Friday Closing Time' },
  { name: 'ead_org_friday_closed', type: 'checkbox', label: 'Closed on Friday' },
  { name: 'ead_org_saturday_start_time', type: 'time', label: 'Saturday Opening Time' },
  { name: 'ead_org_saturday_end_time', type: 'time', label: 'Saturday Closing Time' },
  { name: 'ead_org_saturday_closed', type: 'checkbox', label: 'Closed on Saturday' },
  { name: 'ead_org_sunday_start_time', type: 'time', label: 'Sunday Opening Time' },
  { name: 'ead_org_sunday_end_time', type: 'time', label: 'Sunday Closing Time' },
  { name: 'ead_org_sunday_closed', type: 'checkbox', label: 'Closed on Sunday' },
];

const ORG_TYPES = ['gallery','museum','studio','collective','non-profit','commercial-gallery','public-art-space','educational-institution','other'];

export default function OrganizationSubmissionForm() {
  const [title, setTitle] = useState('');
  const [images, setImages] = useState([]);
  const [logo, setLogo] = useState(null);
  const [banner, setBanner] = useState(null);
  const [addressComponents, setAddressComponents] = useState('');
  const [country, setCountry] = useState('');
  const [stateProv, setStateProv] = useState('');
  const [city, setCity] = useState('');
  const [previews, setPreviews] = useState([]);
  const [loading, setLoading] = useState(false);
  const [message, setMessage] = useState('');

  const handleFileChange = (e) => {
    const files = Array.from(e.target.files).slice(0, 5);
    setImages(files);
    setPreviews(files.map(file => URL.createObjectURL(file)));
  };

  const handleLogoChange = (e) => {
    setLogo(e.target.files[0] || null);
  };

  const handleBannerChange = (e) => {
    setBanner(e.target.files[0] || null);
  };

  useEffect(() => {
    setAddressComponents(JSON.stringify({
      country,
      state: stateProv,
      city
    }));
  }, [country, stateProv, city]);

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

      let logoId = null;
      if (logo) logoId = await uploadMedia(logo);
      let bannerId = null;
      if (banner) bannerId = await uploadMedia(banner);

      const payload = { post_type: 'artpulse_org', title };
      const fd = new FormData(e.target);
      fd.delete('title');
      fd.delete('images[]');
      fd.delete('ead_org_logo_id');
      fd.delete('ead_org_banner_id');
      for (const [key, value] of fd.entries()) {
        payload[key] = value;
      }
      document.querySelectorAll('form input[type="checkbox"]').forEach(cb => {
        if (!fd.has(cb.name)) payload[cb.name] = '0';
      });
      payload.image_ids = imageIds;
      if (logoId) payload.ead_org_logo_id = logoId;
      if (bannerId) payload.ead_org_banner_id = bannerId;
      if (addressComponents) payload.address_components = addressComponents;

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
      setImages([]);
      setPreviews([]);
      setLogo(null);
      setBanner(null);
      setCountry('');
      setStateProv('');
      setCity('');
      setAddressComponents('');
    } catch (err) {
      console.error(err);
      setMessage(`Error: ${err.message}`);
    } finally {
      setLoading(false);
    }
  };

  return (
    <form onSubmit={handleSubmit} className="p-4 max-w-xl mx-auto rounded-xl shadow bg-white space-y-4" encType="multipart/form-data">
      <h2 className="text-xl font-bold">Submit Organization</h2>

      <input
        className="w-full p-2 border rounded"
        type="text"
        placeholder="Organization Name"
        value={title}
        onChange={e => setTitle(e.target.value)}
        required
      />

      <input
        className="w-full p-2 border rounded ap-address-country"
        type="text"
        placeholder="Country"
        value={country}
        onChange={e => setCountry(e.target.value)}
        required
      />

      <input
        className="w-full p-2 border rounded ap-address-state"
        type="text"
        placeholder="State/Province"
        value={stateProv}
        onChange={e => setStateProv(e.target.value)}
      />

      <input
        className="w-full p-2 border rounded ap-address-city"
        type="text"
        placeholder="City"
        value={city}
        onChange={e => setCity(e.target.value)}
      />

      {ORG_FIELDS.map(field => (
        <div key={field.name} className="w-full">
          <label className="block text-sm" htmlFor={field.name}>{field.label}</label>
          {field.type === 'textarea' && (
            <textarea id={field.name} name={field.name} className="w-full p-2 border rounded" required={field.required}></textarea>
          )}
          {field.type === 'checkbox' && (
            <input id={field.name} type="checkbox" name={field.name} value="1" />
          )}
          {field.type === 'select' && field.name === 'ead_org_type' && (
            <select id={field.name} name={field.name} className="w-full p-2 border rounded">
              <option value="">Select</option>
              {ORG_TYPES.map(t => (
                <option key={t} value={t}>{t.replace('-', ' ')}</option>
              ))}
            </select>
          )}
          {field.type === 'media' && (
            <input
              id={field.name}
              type="file"
              name={field.name}
              accept="image/*"
              onChange={field.name === 'ead_org_logo_id' ? handleLogoChange : handleBannerChange}
            />
          )}
          {['textarea','checkbox','select','media'].indexOf(field.type) === -1 && (
            <input id={field.name} type={field.type} name={field.name} className="w-full p-2 border rounded" required={field.required} />
          )}
        </div>
      ))}

      <input
        className="w-full"
        type="file"
        multiple
        accept="image/*"
        onChange={handleFileChange}
      />
      <input type="hidden" value={addressComponents} readOnly name="address_components" />

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
