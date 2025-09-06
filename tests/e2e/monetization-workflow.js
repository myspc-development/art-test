const puppeteer = require( 'puppeteer' );

(async() => {
	const baseUrl  = process.env.BASE_URL || 'http://localhost:8000';
	const username = process.env.WP_USER || 'admin';
	const password = process.env.WP_PASS || 'password';

	const browser = await puppeteer.launch( { headless: 'new' } );
	const page    = await browser.newPage();
	page.setDefaultTimeout( 10000 );

	try {
		console.log( 'Opening login page...' );
		await page.goto( `${baseUrl} / wp - login.php` );
		await page.type( '#user_login', username );
		await page.type( '#user_pass', password );
		await Promise.all(
			[
			page.click( '#wp-submit' ),
			page.waitForNavigation( { waitUntil: 'networkidle0' } )
			]
		);
		console.log( 'Logged in' );

		console.log( 'Navigating to sample artist profile...' );
		await page.goto( `${baseUrl} / artists / sample - artist / ` );
		await page.waitForSelector( '.ap-tip-button' );
		console.log( 'Tip button located' );

		await page.click( '.ap-tip-button' );
		await page.waitForSelector( '.ap-tip-modal' );
		console.log( 'Tip modal opened' );
	} catch (err) {
		console.error( 'Workflow failed:', err );
		process.exitCode = 1;
	} finally {
		await browser.close();
	}
})();
