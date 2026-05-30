const { useEffect, useState } = React;
  const h = React.createElement;

  function Dashboard() {
    const [rows, setRows] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    async function load() {
      setLoading(true);
      setError(null);

      try {
        console.log("Before calling...")
        const res = await fetch('/api/analytics/visits-per-hour');
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        console.log("Calling OK")
        setRows(await res.json());
      } catch (e) {
        setError(e.message);
      } finally {
        setLoading(false);
      }
    }

    useEffect(() => { load(); }, []);

    return h('main', null,
      h('div', { className: 'top' },
        h('h2', null, 'Visits per hour'),
        h('button', { onClick: load, disabled: loading }, loading ? 'Loading...' : 'Refresh')
      ),

      error && h('p', { className: 'error' }, error),

      rows.length === 0 && !loading
        ? h('p', { className: 'muted' }, 'No visits registered yet.')
        : h('table', null,
            h('thead', null,
              h('tr', null,
                h('th', null, 'Hour'),
                h('th', null, 'Visits')
              )
            ),
            h('tbody', null,
              rows.map(row =>
                h('tr', { key: row.hour },
                  h('td', null, row.hour),
                  h('td', null, row.visits)
                )
              )
            )
          )
    );
  }

ReactDOM.createRoot(document.getElementById('root')).render(h(Dashboard));