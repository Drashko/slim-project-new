import { useEffect, useState } from 'react';

const apiBase = process.env.NEXT_PUBLIC_API_BASE_URL ?? 'http://localhost:8080';

export default function PublicHome() {
  const [payload, setPayload] = useState(null);
  const [error, setError] = useState('');

  useEffect(() => {
    let isMounted = true;

    const load = async () => {
      try {
        const response = await fetch(`${apiBase}/api/v1`);
        if (!response.ok) {
          throw new Error(`Request failed with ${response.status}`);
        }
        const data = await response.json();
        if (isMounted) {
          setPayload(data);
        }
      } catch (err) {
        if (isMounted) {
          setError(err instanceof Error ? err.message : 'Unable to load data.');
        }
      }
    };

    load();

    return () => {
      isMounted = false;
    };
  }, []);

  return (
    <main className="container">
      <div className="card">
        <p className="eyebrow">Public endpoint</p>
        <h1>HomeEndpoint response</h1>
        <p>
          This page calls <code>{`${apiBase}/api/v1`}</code> to confirm the
          public API is available.
        </p>
        {error ? (
          <p>Unable to reach the API: {error}</p>
        ) : payload ? (
          <div>
            <p>Status: {payload.status}</p>
            <p>Message: {payload.message}</p>
          </div>
        ) : (
          <p>Loading response…</p>
        )}
        <div className="actions">
          <a className="ghost" href="/">
            Back to overview
          </a>
          <a className="primary" href="/admin">
            View admin API status
          </a>
        </div>
      </div>
    </main>
  );
}
