const CRITICAL_SUITES = ['Rest', 'Payment', 'Reporting'];

function CoverageBar({ value = 0 }) {
  const pct = Math.round(value);
  return (
    <div className="coverage-bar">
      <div className="coverage-fill" style={{ width: pct + '%' }}></div>
    </div>
  );
}

function SuiteTile({ name, data }) {
  const status = data.status || 'unknown';
  const tests = data.tests || {};
  const total = tests.total ?? (tests.passed || 0) + (tests.failed || 0) + (tests.skipped || 0);
  const passed = tests.passed || 0;
  const failed = tests.failed || 0;
  const coverage = data.coverage || 0;
  const artifacts = data.artifacts || {};

  const incomplete = status !== 'complete' && status !== 'passed';
  const isCriticalIncomplete = CRITICAL_SUITES.includes(name) && incomplete;

  return (
    <div className={`suite-tile${isCriticalIncomplete ? ' critical' : ''}`}>
      <h3>{name}</h3>
      <p>Status: {status}</p>
      <p>Passed: {passed} / {total} ({failed} failed)</p>
      <CoverageBar value={coverage} />
      <div className="artifacts">
        {Object.entries(artifacts).map(([key, url]) => (
          <a key={key} href={url}>{key}</a>
        ))}
      </div>
    </div>
  );
}

function App() {
  const [summary, setSummary] = React.useState(null);
  const [error, setError] = React.useState(null);

  React.useEffect(() => {
    fetch('/build/test-summary.json')
      .then((res) => res.json())
      .then(setSummary)
      .catch(setError);
  }, []);

  if (error) {
    return <div className="error">Failed to load summary.</div>;
  }

  if (!summary) {
    return <div>Loading...</div>;
  }

  const suites = Array.isArray(summary)
    ? summary
    : Object.entries(summary).map(([name, data]) => ({ name, ...data }));

  return (
    <div className="suite-grid">
      {suites.map((suite) => (
        <SuiteTile key={suite.name || suite.suite || suite.id} name={suite.name || suite.suite} data={suite} />
      ))}
    </div>
  );
}

ReactDOM.createRoot(document.getElementById('qa-root')).render(<App />);
