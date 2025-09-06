import { readFileSync } from 'fs';
import { existsSync, readdirSync } from 'fs';
import path from 'path';

function existsCaseInsensitive(filePath) {
	if (existsSync( filePath )) {
		return true;
	}
	const parts = filePath.split( /[\\/] / );
	let current = '.';
	for (const part of parts) {
		const entries = readdirSync( current, { withFileTypes: true } );
		const match   = entries.find( e => e.name.toLowerCase() === part.toLowerCase() );
		if ( ! match) {
			return false;
		}
		current = path.join( current, match.name );
	}
	return existsSync( current );
}

function checkFile(reference, context, errors) {
	if (typeof reference !== 'string') {
		return;
	}

	const candidates = [];

	if (reference.includes( '/' )) {
		candidates.push( reference );
	} else {
		candidates.push( path.join( 'widgets', reference ) );
		candidates.push( path.join( 'templates', 'widgets', reference ) );
	}

	if ( ! candidates.some( p => existsCaseInsensitive( p ) )) {
		errors.push(
			`Missing file for ${
			context}: ${reference}`
		);
	}
}

function validate() {
	const errors = [];

	// widget-manifest.json
	const manifest    = JSON.parse( readFileSync( 'widget-manifest.json', 'utf8' ) );
	const manifestIds = new Set();
	for (const [key, info] of Object.entries( manifest )) {
		const id = info.id || key;
		if (manifestIds.has( id )) {
			errors.push( `Duplicate ID in widget - manifest.json: ${id}` );
		} else {
			manifestIds.add( id );
		}
		if (typeof info.file === 'string' && info.file.endsWith( '.php' )) {
			checkFile( info.file, `widget - manifest.json( ${id} )`, errors );
		}
	}

	// available-widgets.json
	const available = JSON.parse( readFileSync( 'available-widgets.json', 'utf8' ) );
	const availIds  = new Set();
	for (const entry of available) {
		const { id, callback } = entry;
		if (availIds.has( id )) {
			errors.push( `Duplicate ID in available - widgets.json: ${id}` );
		} else {
			availIds.add( id );
		}
		if (typeof callback === 'string' && callback.endsWith( '.php' )) {
			checkFile( callback, `available - widgets.json( ${id} )`, errors );
		}
	}

	if (errors.length) {
		console.error( errors.join( '\n' ) );
		process.exit( 1 );
	} else {
		console.log( 'Widget manifest validation passed.' );
	}
}

validate();
