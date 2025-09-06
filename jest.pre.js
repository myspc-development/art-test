if (typeof window === 'undefined') {
	global.window = {};
}
global.window.localStorage = {
	getItem: jest.fn(),
	setItem: jest.fn(),
	removeItem: jest.fn(),
};
