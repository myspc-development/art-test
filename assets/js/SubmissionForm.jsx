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
      setTimeout(() => {
        window.location.href = APSubmission.dashboardUrl;
      }, 3000);
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
    <form onSubmit={handleSubmit} className="ap-form-container">
      <div className="ap-form-messages" role="status" aria-live="polite">{message}</div>

      <p>
        <label className="ap-form-label" htmlFor="ap_event_title">Event Title</label>
        <input
          id="ap_event_title"
          className="ap-form-input"
          type="text"
          value={title}
          onChange={e => setTitle(e.target.value)}
          required
        />
      </p>

      <p>
        <label className="ap-form-label" htmlFor="ap_event_date">Date</label>
        <input
          id="ap_event_date"
          className="ap-form-input"
          type="date"
          value={eventDate}
          onChange={e => setEventDate(e.target.value)}
          required
        />
      </p>

      <p>
        <label className="ap-form-label" htmlFor="ap_event_start">Start Date</label>
        <input
          id="ap_event_start"
          className="ap-form-input"
          type="date"
          value={startDate}
          onChange={e => setStartDate(e.target.value)}
          required
        />
      </p>

      <p>
        <label className="ap-form-label" htmlFor="ap_event_end">End Date</label>
        <input
          id="ap_event_end"
          className="ap-form-input"
          type="date"
          value={endDate}
          onChange={e => setEndDate(e.target.value)}
        />
      </p>

      <p>
        <label className="ap-form-label" htmlFor="ap_venue_name">Venue Name</label>
        <input
          id="ap_venue_name"
          className="ap-form-input"
          type="text"
          value={venueName}
          onChange={e => setVenueName(e.target.value)}
        />
      </p>

      <p>
        <label className="ap-form-label" htmlFor="ap_event_street">Street Address</label>
        <input
          id="ap_event_street"
          className="ap-form-input"
          type="text"
          value={streetAddress}
          onChange={e => setStreetAddress(e.target.value)}
        />
      </p>

      <p>
        <label className="ap-form-label" htmlFor="ap_event_country">Country</label>
        <input
          id="ap_event_country"
          className="ap-form-input ap-address-country"
          type="text"
          value={country}
          onChange={e => setCountry(e.target.value)}
          required
        />
      </p>

      <p>
        <label className="ap-form-label" htmlFor="ap_event_state">State/Province</label>
        <input
          id="ap_event_state"
          className="ap-form-input ap-address-state"
          type="text"
          value={stateProv}
          onChange={e => setStateProv(e.target.value)}
        />
      </p>

      <p>
        <label className="ap-form-label" htmlFor="ap_event_city">City</label>
        <input
          id="ap_event_city"
          className="ap-form-input ap-address-city"
          type="text"
          value={city}
          onChange={e => setCity(e.target.value)}
        />
      </p>

      <p>
        <label className="ap-form-label" htmlFor="ap_event_postcode">Postcode</label>
        <input
          id="ap_event_postcode"
          className="ap-form-input ap-address-postcode"
          type="text"
          value={postcode}
          onChange={e => setPostcode(e.target.value)}
        />
      </p>

      <p>
        <label className="ap-form-label" htmlFor="ap_event_location">Location</label>
        <input
          id="ap_event_location"
          className="ap-form-input ap-google-autocomplete"
          type="text"
          value={location}
          onChange={e => setLocation(e.target.value)}
          required
        />
        <input type="hidden" value={addressComponents} readOnly name="address_components" />
      </p>

      <p>
        <label className="ap-form-label" htmlFor="ap_event_organizer">Organizer Name</label>
        <input
          id="ap_event_organizer"
          className="ap-form-input"
          type="text"
          value={organizerName}
          onChange={e => setOrganizerName(e.target.value)}
        />
      </p>

      <p>
        <label className="ap-form-label" htmlFor="ap_event_organizer_email">Organizer Email</label>
        <input
          id="ap_event_organizer_email"
          className="ap-form-input"
          type="email"
          value={organizerEmail}
          onChange={e => setOrganizerEmail(e.target.value)}
        />
      </p>

      <p>
        <label className="ap-form-label" htmlFor="ap_banner">Event Banner</label>
        <input
          id="ap_banner"
          className="ap-form-input"
          type="file"
          accept="image/*"
          onChange={handleBannerChange}
        />
      </p>

      <label className="ap-form-label">
        <input
          type="checkbox"
          className="ap-form-input"
          checked={featured}
          onChange={e => setFeatured(e.target.checked)}
        />
        <span> Request Featured</span>
      </label>

      <p>
        <label className="ap-form-label" htmlFor="ap_images">Images (max 5)</label>
        <input
          id="ap_images"
          className="ap-form-input"
          type="file"
          multiple
          accept="image/*"
          onChange={handleFileChange}
        />
      </p>

      <div className="flex gap-2 flex-wrap">
        {previews.map((src, i) => (
          <img key={i} src={src} alt="" className="w-24 h-24 object-cover rounded border" />
        ))}
      </div>

      <button
        className="ap-form-button"
        type="submit"
        disabled={loading}
      >
        {loading ? 'Submitting...' : 'Submit'}
      </button>
    </form>
  );
}
