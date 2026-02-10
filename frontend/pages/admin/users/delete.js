import { useState } from 'react';

const apiBase = process.env.NEXT_PUBLIC_API_BASE_URL ?? 'http://localhost:8000';

export default function AdminUsersDeletePage() {
  const [userId, setUserId] = useState('');
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');

  const submit = async (event) => {
    event.preventDefault();
    setError('');
    setSuccess('');

    if (!userId.trim()) {
      setError('User id is required.');
      return;
    }

    try {
      const response = await fetch(`${apiBase}/api/v1/users/${userId.trim()}`, {
        method: 'DELETE',
      });

      const data = await response.json().catch(() => null);
      if (!response.ok) {
        throw new Error(data?.message ?? `Request failed with ${response.status}`);
      }

      setSuccess('User delete request completed.');
      setUserId('');
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Unable to delete user.');
    }
  };

  return (
    <main className="container container--start container--full">
      <div className="card card--full">
        <p className="eyebrow">Admin / Users</p>
        <h1>Delete user</h1>
        <p>Simple delete page for <code>DELETE /api/v1/users/{'{id}'}</code>.</p>

        <div className="admin-nav">
          <a className="ghost" href="/admin">Admin home</a>
          <a className="ghost" href="/admin/users">List</a>
          <a className="ghost" href="/admin/users/create">Create</a>
          <a className="ghost" href="/admin/users/read">Get by id</a>
          <a className="ghost" href="/admin/users/update">Update</a>
        </div>

        <form className="panel" onSubmit={submit}>
          <label className="input-group">
            User id
            <input value={userId} onChange={(event) => setUserId(event.target.value)} required />
          </label>
          <button className="primary" type="submit">Delete user</button>
        </form>

        {error ? <p className="notice notice--error">{error}</p> : null}
        {success ? <p className="notice notice--success">{success}</p> : null}
      </div>
    </main>
  );
}
