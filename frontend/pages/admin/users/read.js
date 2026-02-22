import { useRouter } from 'next/router';
import { useEffect, useState } from 'react';
import AdminAsideNav from '../../../components/AdminAsideNav';

const apiBase = '';

const apiHeaders = {
  'Content-Type': 'application/json',
};

export default function AdminUsersReadPage() {
  const router = useRouter();
  const [userId, setUserId] = useState('');
  const [user, setUser] = useState(null);
  const [error, setError] = useState('');

  useEffect(() => {
    if (!router.isReady) {
      return;
    }

    const queryId = router.query.id;
    if (typeof queryId === 'string' && queryId.trim()) {
      setUserId(queryId.trim());
    }
  }, [router.isReady, router.query.id]);

  const loadUser = async (event) => {
    event.preventDefault();
    setError('');
    setUser(null);

    if (!userId.trim()) {
      setError('User id is required.');
      return;
    }

    try {
      const response = await fetch(`${apiBase}/api/v1/users/${userId.trim()}`, { headers: apiHeaders, credentials: 'include' });
      if (!response.ok) {
        throw new Error(`Request failed with ${response.status}`);
      }
      const data = await response.json();
      setUser(data);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Unable to load user.');
    }
  };

  return (
    <main className="container container--start container--full">
      <div className="admin-layout">
        <div className="card card--full">
          <p className="eyebrow">Admin / Users</p>
          <h1>Get user by id</h1>

          <form className="panel" onSubmit={loadUser}>
            <label className="input-group">
              User id
              <input value={userId} onChange={(event) => setUserId(event.target.value)} required />
            </label>
            <button className="primary" type="submit">Load user</button>
          </form>

          {error ? <p className="notice notice--error">{error}</p> : null}
          {user ? (
            <div className="rule"><code>{JSON.stringify(user, null, 2)}</code></div>
          ) : (
            <p className="muted">No user loaded yet.</p>
          )}
        </div>
        <AdminAsideNav />
      </div>
    </main>
  );
}
