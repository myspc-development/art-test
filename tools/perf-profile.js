import puppeteer from 'puppeteer';

async function profile() {
	const browser = await puppeteer.launch( { args: ['--no-sandbox', '--disable-setuid-sandbox'] } );
	const page    = await browser.newPage();
	const url     = process.env.PERF_URL || `file:// ${process.cwd()}/offline.html`;
	await page.goto( url, { waitUntil: 'networkidle0' } );
	const client  = await page.target().createCDPSession();
	const metrics = await client.send( 'Performance.getMetrics' );
	const result  = {};
	metrics.metrics.forEach( m => { result[m.name] = m.value; } );
	console.log( 'Performance metrics', result );
	await browser.close();
}

profile().catch( err => { console.error( err ); process.exit( 1 ); } );
