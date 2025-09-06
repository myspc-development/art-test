const cache = new Map();

/**
 * Lightweight wrapper for the WordPress REST API.
 * Automatically prefixes requests with the REST root and injects nonces.
 * Supports simple sessionStorage caching.
 */
export async function apiFetch(path, { method = 'GET', headers = {}, body, cacheKey, ttlMs } = {}) {
	const root = (window.ARTPULSE_BOOT ? .restRoot || '').replace( /\/$/, '' );
	const url  = root + path;
	const key  = cacheKey || url;
	const now  = Date.now();

	if (method === 'GET' && ttlMs) {
		const hit = cache.get( key ) || JSON.parse( sessionStorage.getItem( key ) || 'null' );
		if (hit && now - hit.time < ttlMs) {
			return hit.data;
		}
	}

	const res = await fetch(
		url,
		{
			method,
			credentials: 'same-origin',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': window.ARTPULSE_BOOT ? .restNonce || '',
				...headers,
			},
			body: body ? JSON.stringify( body ) : undefined,
		}
	);

	if (res.status === 401) {
		window.location.href = '/login';
		throw new Error( 'unauthorised' );
	}

	const data = await res.json().catch( () => null );

	if (method === 'GET' && ttlMs) {
		const record = { time: now, data };
		cache.set( key, record );
		try {
			sessionStorage.setItem( key, JSON.stringify( record ) );
		} catch (e) {
			/* sessionStorage might be full */
		}
	}
	return data;
}

export function assertRole(role) {
	const roles = window.ARTPULSE_BOOT ? .currentUser ? .roles || [];
	if ( ! roles.includes( role )) {
		throw new Error( 'forbidden' );
	}
}

export const __ = (key) => window.ARTPULSE_BOOT ? .i18n ? .[key] || key;

// Basic event bus
const bus         = document.createElement( 'div' );
export const on   = (event, handler) => bus.addEventListener( event, handler );
export const emit = (event, detail = {}) => bus.dispatchEvent( new CustomEvent( event, { detail } ) );

export const debounce = (fn, wait = 200) => {
	let t;
	return (...args) => {
		clearTimeout( t );
		t             = setTimeout( () => fn( ...args ), wait );
	};
};

export const throttle = (fn, limit = 200) => {
	let inThrottle;
	return (...args) => {
		if ( ! inThrottle) {
			fn( ...args );
			inThrottle = true;
			setTimeout( () => (inThrottle = false), limit );
		}
	};
};

export const formatDate   = (d) => new Date( d ).toLocaleDateString();
export const formatNumber = (n) => new Intl.NumberFormat().format( n );
