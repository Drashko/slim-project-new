import AdminAsideNav from '../../components/AdminAsideNav';
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

function parsePolicy(policy = '') {
  const parts = String(policy).split(',').map((item) => item.trim());
  const [ptype = 'p', subject = '', object = '', action = '', scope = 'api'] = parts;

  return {
    ptype,
    subject,
    object,
    action,
    scope,
  };
}

export default function AdminPermissions() {
  const [form, setForm] = useState(defaultForm);
  const [rules, setRules] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  const [subjectHeader, setSubjectHeader] = useState('admin');
  const [selectedRuleIndex, setSelectedRuleIndex] = useState(null);

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
      const data = await response.json().catch(() => null);
      if (!response.ok) {
        const message = data?.message || 'Unable to save rule.';
        const detail = data?.errors ? JSON.stringify(data.errors) : '';
        throw new Error(detail ? `${message} ${detail}` : message);
      }
      setSuccess('Casbin rule saved successfully.');
      setForm((prev) => ({ ...prev, key: '', label: '' }));
      await loadPermissions();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Unable to save rule.');
    }
  };

  const fillForEdit = (rule) => {
    const parsed = parsePolicy(rule.policy);
    setForm((prev) => ({
      ...prev,
      ...parsed,
    }));
    setSuccess('Правилото е заредено във формата за промяна.');
  };

  const showDetails = (index) => {
    setSelectedRuleIndex((current) => (current === index ? null : index));
  };

  const deleteRule = async (rule) => {
    setError('');
    setSuccess('');

    const parsed = parsePolicy(rule.policy);

    try {
      const response = await fetch(`${apiBase}/api/v1/admin/permissions`, {
        method: 'DELETE',
        headers: {
          'Content-Type': 'application/json',
          ...headers,
        },
        body: JSON.stringify(parsed),
      });

      const data = await response.json().catch(() => null);
      if (!response.ok) {
        throw new Error(data?.message ?? `Request failed with ${response.status}`);
      }

      setSuccess('Casbin rule deleted successfully.');
      await loadPermissions();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Unable to delete rule.');
    }
  };

  const selectedRule = selectedRuleIndex === null ? null : rules[selectedRuleIndex] ?? null;

  return (
    <main className="container container--start container--full">
      <div className="admin-layout">
        <div className="card card--wide card--full">
          <p className="eyebrow">Admin / Casbin</p>
          <h1>Casbin rule management</h1>
          <p>Списък с правила и CRUD действия: детайли, създаване, промяна и изтриване.</p>

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
            <a className="ghost" href="#create-rule-form">Създай правило</a>
          </div>

          {error ? <p className="notice notice--error">{error}</p> : null}
          {success ? <p className="notice notice--success">{success}</p> : null}

          <section className="panel">
            <h2>Casbin rules ({rules.length})</h2>
            {loading ? (
              <p>Loading…</p>
            ) : rules.length === 0 ? (
              <p className="muted">No policy rules stored yet.</p>
            ) : (
              <div className="list">
                {rules.map((rule, index) => (
                  <div key={`${rule.ptype}-${index}`} className="list-item">
                    <h4>{rule.ptype ?? 'p'} rule #{index + 1}</h4>
                    <code>{rule.policy}</code>
                    <div className="actions">
                      <button type="button" className="ghost" onClick={() => showDetails(index)}>
                        Детайли
                      </button>
                      <button type="button" className="ghost" onClick={() => fillForEdit(rule)}>
                        Промяна
                      </button>
                      <button type="button" className="ghost" onClick={() => deleteRule(rule)}>
                        Изтриване
                      </button>
                    </div>
                  </div>
                ))}
              </div>
            )}
          </section>

          {selectedRule ? (
            <section className="panel">
              <h2>Rule details</h2>
              <div className="rule">
                <code>{JSON.stringify(selectedRule, null, 2)}</code>
              </div>
            </section>
          ) : null}

          <form id="create-rule-form" className="panel" onSubmit={handleSubmit}>
            <h2>Create / update rule</h2>
            <label className="input-group">
              <span>Permission key (optional)</span>
              <input
                name="key"
                value={form.key}
                onChange={handleChange}
                placeholder="admin.permissions.manage"
              />
            </label>
            <label className="input-group">
              <span>Permission label (optional)</span>
              <input
                name="label"
                value={form.label}
                onChange={handleChange}
                placeholder="Manage permissions"
              />
            </label>
            <label className="input-group">
              <span>Subject</span>
              <input name="subject" value={form.subject} onChange={handleChange} required />
            </label>
            <label className="input-group">
              <span>Object</span>
              <input name="object" value={form.object} onChange={handleChange} required />
            </label>
            <label className="input-group">
              <span>Action</span>
              <input name="action" value={form.action} onChange={handleChange} required />
            </label>
            <label className="input-group">
              <span>Scope</span>
              <input name="scope" value={form.scope} onChange={handleChange} required />
            </label>
            <label className="input-group">
              <span>Policy type</span>
              <input name="ptype" value={form.ptype} onChange={handleChange} required />
            </label>
            <button className="primary" type="submit">Запази правило</button>
          </form>
        </div>
        <AdminAsideNav />
      </div>
    </main>
  );
}
