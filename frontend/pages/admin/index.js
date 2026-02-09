import { useEffect, useState } from 'react';

const apiBase = process.env.NEXT_PUBLIC_API_BASE_URL ?? 'http://localhost:8080';

export default function AdminHome() {
  const [payload, setPayload] = useState(null);
  const [error, setError] = useState('');

  useEffect(() => {
    let isMounted = true;

    const load = async () => {
      try {
        const response = await fetch(`${apiBase}/api/v1/admin`);
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
        <p className="eyebrow">Admin endpoint</p>
        <h1>HomeAdminEndpoint response</h1>
        <p>
          This page calls <code>{`${apiBase}/api/v1/admin`}</code> to validate
          the admin API is available.
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
          <a className="primary" href="/admin/permissions">
            Manage permissions
          </a>
          <a className="primary" href="/public">
            View public API status
          </a>
        </div>
      </div>
    </main>
  );
}
