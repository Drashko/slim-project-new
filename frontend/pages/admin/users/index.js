import AdminAsideNav from '../../../components/AdminAsideNav';
import { useState } from 'react';

const apiBase = process.env.NEXT_PUBLIC_API_BASE_URL ?? 'http://localhost:8000';

const apiHeaders = {
  'Content-Type': 'application/json',
  'X-Subject': process.env.NEXT_PUBLIC_API_SUBJECT ?? 'user:1',
  'X-Scope': process.env.NEXT_PUBLIC_API_SCOPE ?? 'api',
};

export default function AdminUsersListPage() {
  const [users, setUsers] = useState([]);
  const [error, setError] = useState('');

  const loadUsers = async () => {
    setError('');
    try {
      const response = await fetch(`${apiBase}/api/v1/users`, {
        headers: apiHeaders,
      });

      const data = await response.json().catch(() => null);
      if (!response.ok) {
        throw new Error(data?.message ?? `Request failed with ${response.status}`);
      }

      setUsers(Array.isArray(data) ? data : data.users ?? []);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Unable to load users.');
    }
  };

  return (
    <main className="container container--start container--full">
      <div className="admin-layout">
        <div className="card card--full">
        <p className="eyebrow">Admin / Users</p>
        <h1>User list</h1>
        <p>Simple list page for <code>GET /api/v1/users</code>.</p>

        <section className="panel">
          <div className="actions">
            <button type="button" className="primary" onClick={loadUsers}>Load users</button>
          </div>
          {error ? <p className="notice notice--error">{error}</p> : null}
          <div className="list">
            {users.length === 0 ? (
              <p className="muted">No users loaded yet.</p>
            ) : (
              users.map((user, index) => (
                <div className="rule" key={user.id ?? index}>
                  <code>{JSON.stringify(user, null, 2)}</code>
                </div>
              ))
            )}
          </div>
        </section>
        </div>
        <AdminAsideNav />
      </div>
    </main>
  );
}
