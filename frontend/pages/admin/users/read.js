import { useState } from 'react';

const apiBase = process.env.NEXT_PUBLIC_API_BASE_URL ?? 'http://localhost:8000';

export default function AdminUsersReadPage() {
  const [userId, setUserId] = useState('');
  const [user, setUser] = useState(null);
  const [error, setError] = useState('');

  const loadUser = async (event) => {
    event.preventDefault();
    setError('');
    setUser(null);

    if (!userId.trim()) {
      setError('User id is required.');
      return;
    }

    try {
      const response = await fetch(`${apiBase}/api/v1/users/${userId.trim()}`);
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
      <div className="card card--full">
        <p className="eyebrow">Admin / Users</p>
        <h1>Get user by id</h1>
        <p>Simple read page for <code>GET /api/v1/users/{'{id}'}</code>.</p>

        <div className="admin-nav">
          <a className="ghost" href="/admin">Admin home</a>
          <a className="ghost" href="/admin/users">List</a>
          <a className="ghost" href="/admin/users/create">Create</a>
          <a className="ghost" href="/admin/users/update">Update</a>
          <a className="ghost" href="/admin/users/delete">Delete</a>
        </div>

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
    </main>
  );
}
