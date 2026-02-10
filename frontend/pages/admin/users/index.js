import { useState } from 'react';

const apiBase = process.env.NEXT_PUBLIC_API_BASE_URL ?? 'http://localhost:8000';

export default function AdminUsersListPage() {
  const [users, setUsers] = useState([]);
  const [error, setError] = useState('');

  const loadUsers = async () => {
    setError('');
    try {
      const response = await fetch(`${apiBase}/api/v1/users`);
      if (!response.ok) {
        throw new Error(`Request failed with ${response.status}`);
      }
      const data = await response.json();
      setUsers(Array.isArray(data) ? data : data.users ?? []);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Unable to load users.');
    }
  };

  return (
    <main className="container container--start container--full">
      <div className="card card--full">
        <p className="eyebrow">Admin / Users</p>
        <h1>User list</h1>
        <p>Simple list page for <code>GET /api/v1/users</code>.</p>

        <div className="admin-nav">
          <a className="ghost" href="/admin">Admin home</a>
          <a className="ghost" href="/admin/users/create">Create</a>
          <a className="ghost" href="/admin/users/read">Get by id</a>
          <a className="ghost" href="/admin/users/update">Update</a>
          <a className="ghost" href="/admin/users/delete">Delete</a>
        </div>

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
    </main>
  );
}
