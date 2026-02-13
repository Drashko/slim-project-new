import { useEffect, useState } from 'react';
import AdminAsideNav from '../../../components/AdminAsideNav';

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

  useEffect(() => {
    loadUsers();
  }, []);

  return (
    <main className="container container--start container--full">
      <div className="admin-layout">
        <div className="card card--full">
          <p className="eyebrow">Admin / Users</p>
          <h1>User list</h1>
          <p>Списък с потребители + действия за детайли, промяна и изтриване.</p>

          <section className="panel">
            <div className="actions">
              <a className="primary" href="/admin/users/create">Създай потребител</a>
              <button type="button" className="ghost" onClick={loadUsers}>Опресни списъка</button>
            </div>
            {error ? <p className="notice notice--error">{error}</p> : null}
            <div className="list">
              {users.length === 0 ? (
                <p className="muted">Няма заредени потребители.</p>
              ) : (
                users.map((user, index) => {
                  const id = user.id ?? user.uuid ?? index;
                  return (
                    <div className="list-item" key={id}>
                      <h4>{user.name ?? user.email ?? `User ${id}`}</h4>
                      <p className="muted">ID: <code>{String(id)}</code></p>
                      {user.email ? <p>Email: {user.email}</p> : null}
                      <div className="actions">
                        <a className="ghost" href={`/admin/users/read?id=${encodeURIComponent(String(id))}`}>Детайли</a>
                        <a className="ghost" href={`/admin/users/update?id=${encodeURIComponent(String(id))}`}>Промяна</a>
                        <a className="ghost" href={`/admin/users/delete?id=${encodeURIComponent(String(id))}`}>Изтриване</a>
                      </div>
                    </div>
                  );
                })
              )}
            </div>
          </section>
        </div>
        <AdminAsideNav />
      </div>
    </main>
  );
}
