import { useEffect, useState } from 'react';
import AdminAsideNav from '../../../components/AdminAsideNav';

// Use same-origin BFF routes (Next.js) which proxy to the Slim API server-to-server.
const apiBase = '';

const apiHeaders = {
  'Content-Type': 'application/json',
};

export default function AdminUsersListPage() {
  const [users, setUsers] = useState([]);
  const [error, setError] = useState('');

  const loadUsers = async () => {
    setError('');
    try {
      const response = await fetch(`${apiBase}/api/v1/users`, {
        headers: apiHeaders,
        credentials: 'include',
      });

      console.log(response);

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
            {users.length === 0 ? (
              <p className="muted">Няма заредени потребители.</p>
            ) : (
              <div className="users-table-wrap">
                <table className="users-table">
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Име</th>
                      <th>Email</th>
                      <th>Действия</th>
                    </tr>
                  </thead>
                  <tbody>
                    {users.map((user, index) => {
                      const id = user.id ?? user.uuid ?? index;
                      return (
                        <tr key={id}>
                          <td><code>{String(id)}</code></td>
                          <td>{user.name ?? '—'}</td>
                          <td>{user.email ?? '—'}</td>
                          <td>
                            <div className="users-table-actions">
                              <a className="ghost" href={`/admin/users/read?id=${encodeURIComponent(String(id))}`}>Детайли</a>
                              <a className="ghost" href={`/admin/users/update?id=${encodeURIComponent(String(id))}`}>Промяна</a>
                              <a className="ghost" href={`/admin/users/delete?id=${encodeURIComponent(String(id))}`}>Изтриване</a>
                            </div>
                          </td>
                        </tr>
                      );
                    })}
                  </tbody>
                </table>
              </div>
            )}
          </section>
        </div>
        <AdminAsideNav />
      </div>
    </main>
  );
}
