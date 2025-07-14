document.addEventListener('DOMContentLoaded', async () => {
  const res = await fetch(AP_UpdateData.endpoint, {
    headers: { 'X-WP-Nonce': AP_UpdateData.nonce }
  });
  const json = await res.json();
  document.getElementById('ap-update-output').innerText = JSON.stringify(json, null, 2);
});
