import { useState } from 'react';

const apiBase = process.env.NEXT_PUBLIC_API_BASE_URL ?? 'http://localhost:8000';

const initialForm = {
  name: '',
  email: '',
  password: '',
};

export default function AdminUsersCreatePage() {
  const [form, setForm] = useState(initialForm);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');

  const submit = async (event) => {
    event.preventDefault();
    setError('');
    setSuccess('');

    try {
      const response = await fetch(`${apiBase}/api/v1/users`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(form),
      });

      const data = await response.json().catch(() => null);
      if (!response.ok) {
        throw new Error(data?.message ?? `Request failed with ${response.status}`);
      }

      setSuccess('User created successfully.');
      setForm(initialForm);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Unable to create user.');
    }
  };

  return (
    <main className="container container--start container--full">
      <div className="card card--full">
        <p className="eyebrow">Admin / Users</p>
        <h1>Create user</h1>
        <p>Simple create page for <code>POST /api/v1/users</code>.</p>

        <div className="admin-nav">
          <a className="ghost" href="/admin">Admin home</a>
          <a className="ghost" href="/admin/users">List</a>
          <a className="ghost" href="/admin/users/read">Get by id</a>
          <a className="ghost" href="/admin/users/update">Update</a>
          <a className="ghost" href="/admin/users/delete">Delete</a>
        </div>

        <form className="panel" onSubmit={submit}>
          <label className="input-group">
            Name
            <input value={form.name} onChange={(event) => setForm((prev) => ({ ...prev, name: event.target.value }))} required />
          </label>
          <label className="input-group">
            Email
            <input type="email" value={form.email} onChange={(event) => setForm((prev) => ({ ...prev, email: event.target.value }))} required />
          </label>
          <label className="input-group">
            Password
            <input type="password" value={form.password} onChange={(event) => setForm((prev) => ({ ...prev, password: event.target.value }))} required />
          </label>
          <button className="primary" type="submit">Create user</button>
        </form>

        {error ? <p className="notice notice--error">{error}</p> : null}
        {success ? <p className="notice notice--success">{success}</p> : null}
      </div>
    </main>
  );
}
