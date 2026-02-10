import { useEffect, useMemo, useState } from 'react';

const apiBase = process.env.NEXT_PUBLIC_API_BASE_URL ?? 'http://localhost:8000';

const defaultForm = {
  key: '',
  label: '',
  subject: 'admin',
  object: '/api/v1/admin/*',
  action: 'GET',
  scope: 'api',
  ptype: 'p',
};

export default function AdminPermissions() {
  const [form, setForm] = useState(defaultForm);
  const [permissions, setPermissions] = useState([]);
  const [rules, setRules] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  const [subjectHeader, setSubjectHeader] = useState('admin');

  const headers = useMemo(() => {
    const nextHeaders = {};
    if (subjectHeader.trim()) {
      nextHeaders['X-Subject'] = subjectHeader.trim();
    }
    return nextHeaders;
  }, [subjectHeader]);

  const loadPermissions = async () => {
    setLoading(true);
    setError('');
    try {
      const response = await fetch(`${apiBase}/api/v1/admin/permissions`, {
        headers,
      });
      if (!response.ok) {
        throw new Error(`Request failed with ${response.status}`);
      }
      const data = await response.json();
      setPermissions(data.permissions?.groups ?? []);
      setRules(data.casbin_rules ?? []);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Unable to load permissions.');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadPermissions();
  }, [headers]);

  const handleChange = (event) => {
    const { name, value } = event.target;
    setForm((prev) => ({ ...prev, [name]: value }));
  };

  const handleSubmit = async (event) => {
    event.preventDefault();
    setError('');
    setSuccess('');
    try {
      const response = await fetch(`${apiBase}/api/v1/admin/permissions`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          ...headers,
        },
        body: JSON.stringify({
          ...form,
          action: form.action.toUpperCase(),
          scope: form.scope || 'api',
        }),
      });
      const data = await response.json();
      if (!response.ok) {
        const message = data?.message || 'Unable to save permission.';
        const detail = data?.errors ? JSON.stringify(data.errors) : '';
        throw new Error(detail ? `${message} ${detail}` : message);
      }
      setSuccess('Permission and Casbin policy saved.');
      setForm((prev) => ({
        ...prev,
        key: '',
        label: '',
      }));
      await loadPermissions();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Unable to save permission.');
    }
  };

  return (
    <main className="container container--start">
      <div className="card card--wide">
        <p className="eyebrow">Admin</p>
        <h1>Permission management</h1>
        <p>
          Create permissions and Casbin policy rules used by the admin API. The
          policy entry is stored in the <code>casbin_rule</code> table.
        </p>
        <div className="toolbar">
          <label className="input-group">
            <span>Request subject header (X-Subject)</span>
            <input
              type="text"
              value={subjectHeader}
              onChange={(event) => setSubjectHeader(event.target.value)}
              placeholder="admin"
            />
          </label>
          <button className="primary" type="button" onClick={loadPermissions}>
            Refresh
          </button>
        </div>
        {error ? <p className="notice notice--error">{error}</p> : null}
        {success ? <p className="notice notice--success">{success}</p> : null}
        <section className="grid">
          <form className="panel" onSubmit={handleSubmit}>
            <h2>Create permission</h2>
            <p className="muted">
              Permissions are stored in the <code>permissions</code> table and
              referenced by key.
            </p>
            <label className="input-group">
              <span>Permission key</span>
              <input
                name="key"
                value={form.key}
                onChange={handleChange}
                placeholder="admin.permissions.manage"
                required
              />
            </label>
            <label className="input-group">
              <span>Permission label</span>
              <input
                name="label"
                value={form.label}
                onChange={handleChange}
                placeholder="Manage permissions"
                required
              />
            </label>
            <h3>Casbin policy</h3>
            <label className="input-group">
              <span>Subject</span>
              <input
                name="subject"
                value={form.subject}
                onChange={handleChange}
                placeholder="admin"
                required
              />
            </label>
            <label className="input-group">
              <span>Object (route pattern)</span>
              <input
                name="object"
                value={form.object}
                onChange={handleChange}
                placeholder="/api/v1/admin/*"
                required
              />
            </label>
            <label className="input-group">
              <span>Action (HTTP method or regex)</span>
              <input
                name="action"
                value={form.action}
                onChange={handleChange}
                placeholder="GET|POST"
                required
              />
            </label>
            <label className="input-group">
              <span>Scope</span>
              <input
                name="scope"
                value={form.scope}
                onChange={handleChange}
                placeholder="api"
                required
              />
            </label>
            <label className="input-group">
              <span>Policy type</span>
              <input
                name="ptype"
                value={form.ptype}
                onChange={handleChange}
                placeholder="p"
                required
              />
            </label>
            <button className="primary" type="submit">
              Save permission
            </button>
          </form>
          <div className="panel">
            <h2>Existing permissions</h2>
            {loading ? (
              <p>Loading…</p>
            ) : permissions.length === 0 ? (
              <p className="muted">No permissions found yet.</p>
            ) : (
              <div className="list">
                {permissions.map((group) => (
                  <div key={group.id} className="list-item">
                    <h4>{group.label}</h4>
                    <ul>
                      {group.permissions.map((permission) => (
                        <li key={permission.key}>
                          <strong>{permission.key}</strong>
                          <span>{permission.label}</span>
                        </li>
                      ))}
                    </ul>
                  </div>
                ))}
              </div>
            )}
          </div>
        </section>
        <section className="panel">
          <h2>Casbin rules ({rules.length})</h2>
          {rules.length === 0 ? (
            <p className="muted">No policy rules stored yet.</p>
          ) : (
            <div className="list">
              {rules.map((rule, index) => (
                <div key={`${rule.ptype}-${index}`} className="rule">
                  <code>{rule.policy}</code>
                </div>
              ))}
            </div>
          )}
        </section>
        <div className="actions">
          <a className="ghost" href="/admin">
            Back to admin overview
          </a>
        </div>
      </div>
    </main>
  );
}
