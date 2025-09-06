import { readdirSync, readFileSync, statSync } from 'fs';
import { join } from 'path';

const ALLOWED = new Set(
	[
	'AI', 'AJAX', 'ADMIN', 'BLOCKS', 'CLI', 'COMMUNITY', 'CORE', 'CRM', 'DB',
	'ENGAGEMENT', 'FRONTEND', 'INTEGRATION', 'MONETIZATION', 'PAYMENT',
	'PERSONALIZATION', 'REPORTING', 'REST', 'SAFETY', 'SEARCH', 'SMOKE',
	'SUPPORT', 'UNIT', 'WIDGETS', 'WPUNIT', 'PHPUNIT', 'ENVIRONMENT'
	]
);

function getPhpFiles(dir) {
	const entries = readdirSync( dir, { withFileTypes: true } );
	const files   = [];
	for (const entry of entries) {
		const full = join( dir, entry.name );
		if (entry.isDirectory()) {
			files.push( ...getPhpFiles( full ) );
		} else if (entry.isFile() && entry.name.endsWith( '.php' )) {
			files.push( full );
		}
	}
	return files;
}

const files   = getPhpFiles( 'tests' ).filter( f => f.endsWith( 'Test.php' ) );
const invalid = [];

for (const file of files) {
	const content = readFileSync( file, 'utf8' );
	const match   = content.match( /@group\s+([A-Z0-9_]+)/i );
	if ( ! match) {
		invalid.push( `${file}: missing @group` );
		continue;
	}
	const group = match[1].toUpperCase();
	if ( ! ALLOWED.has( group )) {
		invalid.push( `${file}: invalid group ${group}` );
	}
}

if (invalid.length) {
	console.error( 'Invalid @group annotations:\n' + invalid.join( '\n' ) );
	process.exit( 1 );
}
