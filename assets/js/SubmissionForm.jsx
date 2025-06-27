import React, { useState, useEffect, useRef } from 'react';

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
  const [progresses, setProgresses] = useState([]);
  const [order, setOrder] = useState([]);
  const [dragIndex, setDragIndex] = useState(null);
  const [loading, setLoading] = useState(false);
  const [message, setMessage] = useState('');
  const [organizerName, setOrganizerName] = useState('');
  const [organizerEmail, setOrganizerEmail] = useState('');
  const [featured, setFeatured] = useState(false);
  const [rsvpEnabled, setRsvpEnabled] = useState(false);
  const [rsvpLimit, setRsvpLimit] = useState('');
  const [waitlistEnabled, setWaitlistEnabled] = useState(false);
  const orderRef = useRef(null);

  const handleFileChange = (e) => {
    const files = Array.from(e.target.files).slice(0, 5);
    setImages(files);
    setPreviews(files.map(file => URL.createObjectURL(file)));
    setOrder(files.map((_, i) => i));
    setProgresses(files.map(() => 0));
  };

  const handleBannerChange = (e) => {
    setBanner(e.target.files[0] || null);
  };

  useEffect(() => {
    setAddressComponents(JSON.stringify({ country, state: stateProv, city }));
  }, [country, stateProv, city]);

  useEffect(() => {
    if (orderRef.current) {
      orderRef.current.value = order.join(',');
    }
  }, [order]);

  const handleDragStart = (i) => setDragIndex(i);
  const handleDrop = (i) => {
    if (dragIndex === null || dragIndex === i) return;
    const imgs = [...images];
    const prevs = [...previews];
    const progs = [...progresses];
    const ord = [...order];
    const [f] = imgs.splice(dragIndex, 1);
    const [p] = prevs.splice(dragIndex, 1);
    const [g] = progs.splice(dragIndex, 1);
    const [o] = ord.splice(dragIndex, 1);
    imgs.splice(i, 0, f);
    prevs.splice(i, 0, p);
    progs.splice(i, 0, g);
    ord.splice(i, 0, o);
    setImages(imgs);
    setPreviews(prevs);
    setProgresses(progs);
    setOrder(ord);
    setDragIndex(null);
  };

  const uploadMedia = (file, index) => {
    return new Promise((resolve, reject) => {
      const formData = new FormData();
      formData.append('file', file);

      const xhr = new XMLHttpRequest();
      xhr.open('POST', APSubmission.mediaEndpoint);
      xhr.setRequestHeader('X-WP-Nonce', APSubmission.nonce);

      xhr.upload.addEventListener('progress', (e) => {
        if (e.lengthComputable) {
          const percent = Math.round((e.loaded / e.total) * 100);
          setProgresses((prev) => {
            const copy = [...prev];
            copy[index] = percent;
            return copy;
          });
        }
      });

      xhr.onload = () => {
        let json = {};
        try { json = JSON.parse(xhr.responseText); } catch (_) {}
        if (xhr.status >= 200 && xhr.status < 300) {
          resolve(json.id);
        } else {
          reject(new Error(json.message || 'Upload failed'));
        }
      };

      xhr.onerror = () => reject(new Error('Upload failed'));

      xhr.send(formData);
    });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setMessage('');

    try {
      const imageIds = [];
      for (let i = 0; i < images.length; i++) {
        setMessage(`Uploading image ${i + 1} of ${images.length}`);
        const id = await uploadMedia(images[i], i);
        imageIds.push(id);
      }
      let bannerId = null;
      if (banner) {
        setMessage('Uploading banner');
        bannerId = await uploadMedia(banner, images.length);
      }

      setMessage('Submitting form');

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
          event_rsvp_enabled: rsvpEnabled ? '1' : '0',
          event_rsvp_limit: rsvpLimit,
          event_waitlist_enabled: waitlistEnabled ? '1' : '0',
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
      setRsvpEnabled(false);
      setRsvpLimit('');
      setWaitlistEnabled(false);
      setImages([]);
      setPreviews([]);
      setProgresses([]);
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
          className="ap-input"
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
          className="ap-input"
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
          className="ap-input"
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
          className="ap-input"
          type="date"
          value={endDate}
          onChange={e => setEndDate(e.target.value)}
        />
      </p>

      <p>
        <label className="ap-form-label" htmlFor="ap_venue_name">Venue Name</label>
        <input
          id="ap_venue_name"
          className="ap-input"
          type="text"
          value={venueName}
          onChange={e => setVenueName(e.target.value)}
        />
      </p>

      <p>
        <label className="ap-form-label" htmlFor="ap_event_street">Street Address</label>
        <input
          id="ap_event_street"
          className="ap-input"
          type="text"
          value={streetAddress}
          onChange={e => setStreetAddress(e.target.value)}
        />
      </p>

      <p>
        <label className="ap-form-label" htmlFor="ap_event_country">Country</label>
        <input
          id="ap_event_country"
          className="ap-input ap-address-country"
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
          className="ap-input ap-address-state"
          type="text"
          value={stateProv}
          onChange={e => setStateProv(e.target.value)}
        />
      </p>

      <p>
        <label className="ap-form-label" htmlFor="ap_event_city">City</label>
        <input
          id="ap_event_city"
          className="ap-input ap-address-city"
          type="text"
          value={city}
          onChange={e => setCity(e.target.value)}
        />
      </p>

      <p>
        <label className="ap-form-label" htmlFor="ap_event_postcode">Postcode</label>
        <input
          id="ap_event_postcode"
          className="ap-input ap-address-postcode"
          type="text"
          value={postcode}
          onChange={e => setPostcode(e.target.value)}
        />
      </p>

      <p>
        <label className="ap-form-label" htmlFor="ap_event_location">Location</label>
        <input
          id="ap_event_location"
          className="ap-input ap-google-autocomplete"
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
          className="ap-input"
          type="text"
          value={organizerName}
          onChange={e => setOrganizerName(e.target.value)}
        />
      </p>

      <p>
        <label className="ap-form-label" htmlFor="ap_event_organizer_email">Organizer Email</label>
        <input
          id="ap_event_organizer_email"
          className="ap-input"
          type="email"
          value={organizerEmail}
          onChange={e => setOrganizerEmail(e.target.value)}
        />
      </p>

      <p>
        <label className="ap-form-label" htmlFor="ap_banner">Event Banner</label>
        <input
          id="ap_banner"
          className="ap-input"
          type="file"
          accept="image/*"
          onChange={handleBannerChange}
        />
      </p>
      <label className="ap-form-label">
        <input
          type="checkbox"
          className="ap-input"
          checked={rsvpEnabled}
          onChange={e => setRsvpEnabled(e.target.checked)}
        />
        <span> Enable RSVP</span>
      </label>

      <p>
        <label className="ap-form-label" htmlFor="ap_rsvp_limit">RSVP Limit</label>
        <input
          id="ap_rsvp_limit"
          className="ap-input"
          type="number"
          value={rsvpLimit}
          onChange={e => setRsvpLimit(e.target.value)}
        />
      </p>

      <label className="ap-form-label">
        <input
          type="checkbox"
          className="ap-input"
          checked={waitlistEnabled}
          onChange={e => setWaitlistEnabled(e.target.checked)}
        />
        <span> Enable Waitlist</span>
      </label>

      <label className="ap-form-label">
        <input
          type="checkbox"
          className="ap-input"
          checked={featured}
          onChange={e => setFeatured(e.target.checked)}
        />
        <span> Request Featured</span>
      </label>

      <p>
        <label className="ap-form-label" htmlFor="ap_images">Images (max 5)</label>
        <input
          id="ap_images"
          className="ap-input"
          type="file"
          multiple
          accept="image/*"
          onChange={handleFileChange}
        />
        <input type="hidden" name="image_order" ref={orderRef} readOnly />
      </p>

      <div className="flex gap-2 flex-wrap">
        {previews.map((src, i) => (
          <div key={i} className="w-24 text-center">
            <img
              src={src}
              alt=""
              className="w-24 h-24 object-cover rounded border"
              draggable
              onDragStart={() => handleDragStart(i)}
              onDragOver={e => e.preventDefault()}
              onDrop={() => handleDrop(i)}
            />
            <progress
              className="ap-upload-progress w-full"
              value={progresses[i] || 0}
              max="100"
            />
          </div>
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
