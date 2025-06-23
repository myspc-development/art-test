import React, { useState, useEffect } from 'react';

export default function SubmissionForm() {
  const [title, setTitle] = useState('');
  const [eventDate, setEventDate] = useState('');
  const [startDate, setStartDate] = useState('');
  const [endDate, setEndDate] = useState('');
  const [venueName, setVenueName] = useState('');
  const [location, setLocation] = useState('');
  const [streetAddress, setStreetAddress] = useState('');
  const [city, setCity] = useState('');
  const [stateProv, setStateProv] = useState('');
  const [country, setCountry] = useState('');
  const [postcode, setPostcode] = useState('');
  const [addressComponents, setAddressComponents] = useState('');
  const [images, setImages] = useState([]);
  const [banner, setBanner] = useState(null);
  const [previews, setPreviews] = useState([]);
  const [loading, setLoading] = useState(false);
  const [message, setMessage] = useState('');
  const [organizerName, setOrganizerName] = useState('');
  const [organizerEmail, setOrganizerEmail] = useState('');
  const [featured, setFeatured] = useState(false);

  const handleFileChange = (e) => {
    const files = Array.from(e.target.files).slice(0, 5);
    setImages(files);
    setPreviews(files.map(file => URL.createObjectURL(file)));
  };

  const handleBannerChange = (e) => {
    setBanner(e.target.files[0] || null);
  };

  useEffect(() => {
    setAddressComponents(JSON.stringify({ country, state: stateProv, city }));
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
      let bannerId = null;
      if (banner) bannerId = await uploadMedia(banner);

      const res = await fetch(APSubmission.endpoint, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': APSubmission.nonce
        },
        body: JSON.stringify({
          post_type: 'artpulse_event',
          title,
          event_date: eventDate,
          event_start_date: startDate,
          event_end_date: endDate,
          venue_name: venueName,
          event_location: location,
          event_street_address: streetAddress,
          event_city: city,
          event_state: stateProv,
          event_country: country,
          event_postcode: postcode,
          event_organizer_name: organizerName,
          event_organizer_email: organizerEmail,
          event_featured: featured ? '1' : '0',
          image_ids: imageIds,
          address_components: addressComponents,
          ...(bannerId ? { event_banner_id: bannerId } : {})
        })
      });

      const json = await res.json();
      if (!res.ok) throw new Error(json.message || 'Submission failed');

      setMessage('Submission successful!');
      setTitle('');
      setEventDate('');
      setStartDate('');
      setEndDate('');
      setVenueName('');
      setLocation('');
      setStreetAddress('');
      setCity('');
      setStateProv('');
      setCountry('');
      setPostcode('');
      setOrganizerName('');
      setOrganizerEmail('');
      setFeatured(false);
      setImages([]);
      setPreviews([]);
      setBanner(null);
    } catch (err) {
      console.error(err);
      setMessage(`Error: ${err.message}`);
    } finally {
      setLoading(false);
    }
  };

  return (
    <form onSubmit={handleSubmit} className="p-4 max-w-xl mx-auto rounded-xl shadow bg-white space-y-4">
      <h2 className="text-xl font-bold">Submit New Event</h2>

      <input
        className="w-full p-2 border rounded"
        type="text"
        placeholder="Event Title"
        value={title}
        onChange={e => setTitle(e.target.value)}
        required
      />

      <input
        className="w-full p-2 border rounded"
        type="date"
        value={eventDate}
        onChange={e => setEventDate(e.target.value)}
        required
      />

      <input
        className="w-full p-2 border rounded"
        type="date"
        placeholder="Start Date"
        value={startDate}
        onChange={e => setStartDate(e.target.value)}
        required
      />

      <input
        className="w-full p-2 border rounded"
        type="date"
        placeholder="End Date"
        value={endDate}
        onChange={e => setEndDate(e.target.value)}
      />

      <input
        className="w-full p-2 border rounded"
        type="text"
        placeholder="Venue Name"
        value={venueName}
        onChange={e => setVenueName(e.target.value)}
      />

      <input
        className="w-full p-2 border rounded"
        type="text"
        placeholder="Street Address"
        value={streetAddress}
        onChange={e => setStreetAddress(e.target.value)}
      />

      <input
        className="w-full p-2 border rounded"
        type="text"
        placeholder="Country"
        value={country}
        onChange={e => setCountry(e.target.value)}
        required
      />

      <input
        className="w-full p-2 border rounded"
        type="text"
        placeholder="State/Province"
        value={stateProv}
        onChange={e => setStateProv(e.target.value)}
      />

      <input
        className="w-full p-2 border rounded"
        type="text"
        placeholder="City"
        value={city}
        onChange={e => setCity(e.target.value)}
      />

      <input
        className="w-full p-2 border rounded"
        type="text"
        placeholder="Postcode"
        value={postcode}
        onChange={e => setPostcode(e.target.value)}
      />

      <input
        className="w-full p-2 border rounded ap-google-autocomplete"
        type="text"
        placeholder="Location"
        value={location}
        onChange={e => setLocation(e.target.value)}
        required
      />
      <input type="hidden" value={addressComponents} readOnly name="address_components" />

      <input
        className="w-full p-2 border rounded"
        type="text"
        placeholder="Organizer Name"
        value={organizerName}
        onChange={e => setOrganizerName(e.target.value)}
      />

      <input
        className="w-full p-2 border rounded"
        type="email"
        placeholder="Organizer Email"
        value={organizerEmail}
        onChange={e => setOrganizerEmail(e.target.value)}
      />

      <input
        className="w-full"
        type="file"
        accept="image/*"
        onChange={handleBannerChange}
      />

      <label className="flex items-center gap-2">
        <input
          type="checkbox"
          checked={featured}
          onChange={e => setFeatured(e.target.checked)}
        />
        <span>Request Featured</span>
      </label>

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
