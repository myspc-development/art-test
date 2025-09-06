module.exports = function analyze(input) {
	const errors = [];

	// Accept JSON strings or objects
	let data = input;
	if (typeof input === 'string') {
		try {
			data = JSON.parse( input );
		} catch (e) {
			errors.push( 'Invalid JSON' );
		}
	}

	if (typeof data !== 'object' || data === null || Array.isArray( data )) {
		errors.push( 'Input must be an object' );
	} else {
		if (typeof data.name !== 'string' || data.name.trim() === '') {
			errors.push( 'name is required' );
		}
		if (typeof data.value !== 'number') {
			errors.push( 'value must be a number' );
		}
	}

	return {
		valid: errors.length === 0,
		errors,
		data: errors.length === 0 ? data : undefined,
	};
};
