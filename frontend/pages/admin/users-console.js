import { useState } from 'react';

const apiBase = process.env.NEXT_PUBLIC_API_BASE_URL ?? 'http://localhost:8000';

const createInitialForm = {
  name: '',
  email: '',
  password: '',
};

const updateInitialForm = {
  name: '',
  email: '',
};

export default function AdminUsersPage() {
  const [users, setUsers] = useState([]);
  const [listError, setListError] = useState('');

  const [lookupId, setLookupId] = useState('');
  const [selectedUser, setSelectedUser] = useState(null);
  const [lookupError, setLookupError] = useState('');

  const [createForm, setCreateForm] = useState(createInitialForm);
  const [createMessage, setCreateMessage] = useState('');
  const [createError, setCreateError] = useState('');

  const [updateId, setUpdateId] = useState('');
  const [updateForm, setUpdateForm] = useState(updateInitialForm);
  const [updateMessage, setUpdateMessage] = useState('');
  const [updateError, setUpdateError] = useState('');

  const [deleteId, setDeleteId] = useState('');
  const [deleteMessage, setDeleteMessage] = useState('');
  const [deleteError, setDeleteError] = useState('');

  const listUsers = async () => {
    setListError('');
    try {
      const response = await fetch(`${apiBase}/api/v1/users`, { credentials: 'include' });
      if (!response.ok) {
        throw new Error(`Request failed with ${response.status}`);
      }
      const data = await response.json();
      setUsers(Array.isArray(data) ? data : data.users ?? []);
    } catch (error) {
      setListError(error instanceof Error ? error.message : 'Unable to list users.');
    }
  };

  const getUser = async () => {
    setLookupError('');
    setSelectedUser(null);
    if (!lookupId.trim()) {
      setLookupError('User id is required.');
      return;
    }

    try {
      const response = await fetch(`${apiBase}/api/v1/users/${lookupId.trim()}`, { credentials: 'include' });
      if (!response.ok) {
        throw new Error(`Request failed with ${response.status}`);
      }
      const data = await response.json();
      setSelectedUser(data);
    } catch (error) {
      setLookupError(error instanceof Error ? error.message : 'Unable to load user.');
    }
  };

  const createUser = async (event) => {
    event.preventDefault();
    setCreateError('');
    setCreateMessage('');

    try {
      const response = await fetch(`${apiBase}/api/v1/users`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(createForm),
        credentials: 'include',
      });

      const data = await response.json().catch(() => null);

      if (!response.ok) {
        throw new Error(data?.message ?? `Request failed with ${response.status}`);
      }

      setCreateMessage('User created successfully.');
      setCreateForm(createInitialForm);
      listUsers();
    } catch (error) {
      setCreateError(error instanceof Error ? error.message : 'Unable to create user.');
    }
  };

  const updateUser = async (event) => {
    event.preventDefault();
    setUpdateError('');
    setUpdateMessage('');

    if (!updateId.trim()) {
      setUpdateError('User id is required.');
      return;
    }

    try {
      const response = await fetch(`${apiBase}/api/v1/users/${updateId.trim()}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(updateForm),
        credentials: 'include',
      });

      const data = await response.json().catch(() => null);

      if (!response.ok) {
        throw new Error(data?.message ?? `Request failed with ${response.status}`);
      }

      setUpdateMessage('User update request completed.');
      listUsers();
    } catch (error) {
      setUpdateError(error instanceof Error ? error.message : 'Unable to update user.');
    }
  };

  const deleteUser = async (event) => {
    event.preventDefault();
    setDeleteError('');
    setDeleteMessage('');

    if (!deleteId.trim()) {
      setDeleteError('User id is required.');
      return;
    }

    try {
      const response = await fetch(`${apiBase}/api/v1/users/${deleteId.trim()}`, {
        method: 'DELETE',
        credentials: 'include',
      });

      const data = await response.json().catch(() => null);

      if (!response.ok) {
        throw new Error(data?.message ?? `Request failed with ${response.status}`);
      }

      setDeleteMessage('User delete request completed.');
      setDeleteId('');
      listUsers();
    } catch (error) {
      setDeleteError(error instanceof Error ? error.message : 'Unable to delete user.');
    }
  };

  return (
    <main className="container container--start container--full">
      <div className="card card--full">
        <p className="eyebrow">Admin API</p>
        <h1>Users route console</h1>
        <p>
          Use this page to test existing routes from <code>config/routes.php</code>.
        </p>

        <section className="grid">
          <div className="panel">
            <h2>GET /api/v1/users</h2>
            <button type="button" className="primary" onClick={listUsers}>
              Load users
            </button>
            {listError ? <p className="notice notice--error">{listError}</p> : null}
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
          </div>

          <div className="panel" id="get-user">
            <h2>GET /api/v1/users/{'{id}'}</h2>
            <div className="toolbar">
              <label className="input-group">
                User id
                <input value={lookupId} onChange={(event) => setLookupId(event.target.value)} />
              </label>
              <button type="button" className="primary" onClick={getUser}>
                Load user
              </button>
            </div>
            {lookupError ? <p className="notice notice--error">{lookupError}</p> : null}
            {selectedUser ? (
              <div className="rule">
                <code>{JSON.stringify(selectedUser, null, 2)}</code>
              </div>
            ) : (
              <p className="muted">No user loaded yet.</p>
            )}
          </div>
        </section>

        <section className="grid">
          <form className="panel" id="create-user" onSubmit={createUser}>
            <h2>POST /api/v1/users</h2>
            <label className="input-group">
              Name
              <input
                name="name"
                value={createForm.name}
                onChange={(event) =>
                  setCreateForm((prev) => ({ ...prev, name: event.target.value }))
                }
              />
            </label>
            <label className="input-group">
              Email
              <input
                name="email"
                type="email"
                value={createForm.email}
                onChange={(event) =>
                  setCreateForm((prev) => ({ ...prev, email: event.target.value }))
                }
              />
            </label>
            <label className="input-group">
              Password
              <input
                name="password"
                type="password"
                value={createForm.password}
                onChange={(event) =>
                  setCreateForm((prev) => ({ ...prev, password: event.target.value }))
                }
              />
            </label>
            <button className="primary" type="submit">
              Create user
            </button>
            {createError ? <p className="notice notice--error">{createError}</p> : null}
            {createMessage ? <p className="notice notice--success">{createMessage}</p> : null}
          </form>

          <form className="panel" id="update-user" onSubmit={updateUser}>
            <h2>PUT /api/v1/users/{'{id}'}</h2>
            <label className="input-group">
              User id
              <input value={updateId} onChange={(event) => setUpdateId(event.target.value)} />
            </label>
            <label className="input-group">
              Name
              <input
                value={updateForm.name}
                onChange={(event) =>
                  setUpdateForm((prev) => ({ ...prev, name: event.target.value }))
                }
              />
            </label>
            <label className="input-group">
              Email
              <input
                type="email"
                value={updateForm.email}
                onChange={(event) =>
                  setUpdateForm((prev) => ({ ...prev, email: event.target.value }))
                }
              />
            </label>
            <button className="primary" type="submit">
              Update user
            </button>
            {updateError ? <p className="notice notice--error">{updateError}</p> : null}
            {updateMessage ? <p className="notice notice--success">{updateMessage}</p> : null}
          </form>
        </section>

        <form className="panel" id="delete-user" onSubmit={deleteUser}>
          <h2>DELETE /api/v1/users/{'{id}'}</h2>
          <div className="toolbar">
            <label className="input-group">
              User id
              <input value={deleteId} onChange={(event) => setDeleteId(event.target.value)} />
            </label>
            <button className="primary" type="submit">
              Delete user
            </button>
          </div>
          {deleteError ? <p className="notice notice--error">{deleteError}</p> : null}
          {deleteMessage ? <p className="notice notice--success">{deleteMessage}</p> : null}
        </form>

        <div className="actions">
          <a className="ghost" href="/admin">
            Back to routes menu
          </a>
        </div>
      </div>
    </main>
  );
}
