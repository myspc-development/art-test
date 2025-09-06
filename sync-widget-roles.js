# ! / usr / bin / env node
/**
 * Synchronize widget role definitions across PHP, JSON and React component files.
 *
 * Validates roles across sources, patches React components with a roles export,
 * and updates available-widgets.json with missing definitions.
 *
 * Usage: node sync-widget-roles.js [--dry-run]
 */

import fs from 'fs';
import path from 'path';
import { execSync } from 'child_process';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath( import.meta.url );
const __dirname  = path.dirname( __filename );
const ROOT       = __dirname;
const JSON_FILE  = path.join( ROOT, 'available-widgets.json' );
const PHP_CONFIG = path.join( ROOT, 'config', 'dashboard-widgets.php' );
const REACT_DIR  = path.join( ROOT, 'assets', 'js', 'widgets' );

const dryRun = process.argv.includes( '--dry-run' );

function loadPhpWidgets() {
	try {
		const cmd    = `php - r "echo json_encode(include '${PHP_CONFIG}');"`;
		const output = execSync( cmd, { encoding: 'utf8' } );
		return JSON.parse( output );
	} catch (err) {
		console.error( 'Failed to load PHP widget definitions:', err.message );
		return {};
	}
}

function loadJsonWidgets() {
	try {
		return JSON.parse( fs.readFileSync( JSON_FILE, 'utf8' ) );
	} catch (err) {
		console.error( 'Failed to read available-widgets.json:', err.message );
		return [];
	}
}

function saveJsonWidgets(data) {
	if (dryRun) {
		return;
	}
	fs.writeFileSync( JSON_FILE, JSON.stringify( data, null, 4 ) + '\n' );
}

function findReactFile(widget) {
	const cb = widget.callback || '';
	if (cb.endsWith( '.jsx' ) || cb.endsWith( '.tsx' )) {
		const direct = path.resolve( ROOT, cb );
		if (fs.existsSync( direct )) {
			return direct;
		}
		const alt = path.join( REACT_DIR, cb );
		if (fs.existsSync( alt )) {
			return alt;
		}
	}
	const guess = path.join( REACT_DIR, `${widget.id.replace( /(^|_)([a-z])/g, (_, p1, p2) => p2.toUpperCase() )}Widget.jsx` );
	return fs.existsSync( guess ) ? guess : null;
}

function parseRoleArray(str) {
	return str
	.replace( /^[^[]*\[/, '[' )
	.replace( /\][^\]]*$/, ']' )
	.replace( /[\s"']/g, '' )
	.slice( 1, -1 )
	.split( ',' )
	.filter( Boolean );
}

function arraysEqual(a, b) {
	const sa = [...a].sort().join( ',' );
	const sb = [...b].sort().join( ',' );
	return sa === sb;
}

function patchReactFile(file, roles) {
	const content     = fs.readFileSync( file, 'utf8' );
	const rolesStr    = `[${roles.map( r => `'${r}'` ).join( ', ' )}]`;
	const isClass     = /class\s+[A-Za-z0-9_]+\s+extends\s+/s.test( content );
	const exportRegex = /export\s+const\s+roles\s*=\s*\[[^\]]*\]\s*;?/;
	const staticRegex = /static\s+roles\s*=\s*\[[^\]]*\]\s*;?/;

	let newContent = content;
	let changed    = false;

	if (isClass) {
		if (staticRegex.test( content )) {
			const existing = parseRoleArray( content.match( staticRegex )[0] );
			if ( ! arraysEqual( existing, roles )) {
				newContent = content.replace( staticRegex, `static roles = ${rolesStr};` );
				changed = true;
			}
		} else {
			newContent = content.replace( /(class\s+[A-Za-z0-9_]+\s+extends[^\{]*{)/, `$1\n  static roles = ${rolesStr};` );
			changed = true;
		}
	} else {
		if (exportRegex.test( content )) {
			const existing = parseRoleArray( content.match( exportRegex )[0] );
			if ( ! arraysEqual( existing, roles )) {
				newContent = content.replace( exportRegex, `export const roles = ${rolesStr};` );
				changed = true;
			}
		} else {
			const defaultIdx = content.lastIndexOf( 'export default' );
			if (defaultIdx !== -1) {
				newContent = content.slice( 0, defaultIdx ) + `export const roles = ${rolesStr};\n\n` + content.slice( defaultIdx );
			} else {
				newContent = content + `\nexport const roles = ${rolesStr};\n`;
			}
			changed = true;
		}
	}

	if (changed) {
		const rel = path.relative( ROOT, file );
		if (dryRun) {
			console.log( `Would patch roles in ${rel}` );
		} else {
			fs.writeFileSync( file, newContent );
			console.log( `Patched roles in ${rel}` );
		}
	}
}

function main() {
	const phpWidgets  = loadPhpWidgets();
	const jsonWidgets = loadJsonWidgets();
	const jsonMap     = new Map( jsonWidgets.map( w => [w.id, w] ) );
	let jsonChanged   = false;

	for (const [id, cfg] of Object.entries( phpWidgets )) {
		const label = cfg.label || cfg.title || '';
		if (label && /legacy|deprecated/i.test( label )) {
			continue;
		}
		const roles = Array.isArray( cfg.roles ) ? cfg.roles.map( r => r.toLowerCase() ) : [];

		let jsonEntry = jsonMap.get( id );
		if ( ! jsonEntry) {
			jsonEntry = { id };
			jsonWidgets.push( jsonEntry );
			jsonMap.set( id, jsonEntry );
			console.log( `Widget ${id} missing from available - widgets.json` );
		}

		if ( ! arraysEqual( jsonEntry.allowed_roles || [], roles )) {
			jsonEntry.allowed_roles = roles;
			jsonChanged             = true;
			console.log(
				`Updated roles for ${
				id} in available - widgets.json`
			);
		}
		if (label) {
			jsonEntry.title = label;
		}
		if (cfg.description) {
			jsonEntry.description = cfg.description;
		}
		if (cfg.category) {
			jsonEntry.category = cfg.category;
		}

		const reactFile = findReactFile( jsonEntry );
		if ( ! reactFile || ! roles.length) {
			continue;
		}
		patchReactFile( reactFile, roles );
	}

	if (jsonChanged) {
		jsonWidgets.sort( (a, b) => a.id.localeCompare( b.id ) );
		saveJsonWidgets( jsonWidgets );
	}
}

main();
