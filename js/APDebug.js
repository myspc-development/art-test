const isEnabled = () => {
	if (typeof process !== 'undefined' && process.env) {
		if (process.env.NODE_ENV === 'production') {
			return false;
		}
		return process.env.AP_DEBUG === '1' || process.env.AP_DEBUG === 'true';
	}
	if (typeof window !== 'undefined') {
		try {
			return (
			window.localStorage.getItem( 'AP_DEBUG' ) === '1' ||
			window.localStorage.getItem( 'AP_DEBUG' ) === 'true'
			);
		} catch {
			return false;
		}
	}
	return false;
};

const APDebug = {
	log: (...args) => {
		if (isEnabled()) {
			console.log( ...args );
		}
	},
	group: (...args) => {
		if (isEnabled() && console.group) {
			console.group( ...args );
		}
	},
	groupEnd: () => {
		if (isEnabled() && console.groupEnd) {
			console.groupEnd();
		}
	}
};

module.exports         = APDebug;
module.exports.default = APDebug;
