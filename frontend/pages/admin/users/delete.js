import { useRouter } from 'next/router';
import { useEffect, useState } from 'react';
import AdminAsideNav from '../../../components/AdminAsideNav';

const apiBase = '';

const apiHeaders = {
  'Content-Type': 'application/json',
};

export default function AdminUsersDeletePage() {
  const router = useRouter();
  const [userId, setUserId] = useState('');
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');

  useEffect(() => {
    if (!router.isReady) {
      return;
    }

    const queryId = router.query.id;
    if (typeof queryId === 'string' && queryId.trim()) {
      setUserId(queryId.trim());
    }
  }, [router.isReady, router.query.id]);

  const submit = async (event) => {
    event.preventDefault();
    setError('');
    setSuccess('');

    if (!userId.trim()) {
      setError('User id is required.');
      return;
    }

    try {
      const response = await fetch(`${apiBase}/api/v1/admin/users/${userId.trim()}`, {
        method: 'DELETE',
        headers: apiHeaders,
        credentials: 'include',
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
      <div className="admin-layout">
        <div className="card card--full">
          <p className="eyebrow">Admin / Users</p>
          <h1>Delete user</h1>

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
        <AdminAsideNav />
      </div>
    </main>
  );
}
