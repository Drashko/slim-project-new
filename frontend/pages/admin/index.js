const adminMenu = [
  {
    title: 'User list',
    description: 'List all users (GET /api/v1/users)',
    href: '/admin/users',
  },
  {
    title: 'Create user',
    description: 'Create a new user (POST /api/v1/users)',
    href: '/admin/users/create',
  },
  {
    title: 'Get user by id',
    description: 'Read one user (GET /api/v1/users/{id})',
    href: '/admin/users/read',
  },
  {
    title: 'Update user',
    description: 'Update one user (PUT /api/v1/users/{id})',
    href: '/admin/users/update',
  },
  {
    title: 'Delete user',
    description: 'Delete one user (DELETE /api/v1/users/{id})',
    href: '/admin/users/delete',
  },
  {
    title: 'Permissions',
    description: 'Manage Casbin permissions and rules',
    href: '/admin/permissions',
  },
];

export default function AdminHome() {
  return (
    <main className="container container--start container--full">
      <div className="card card--full">
        <p className="eyebrow">Admin Panel</p>
        <h1>Simple admin navigation</h1>
        <p>
          Pick a page for user CRUD operations. Routes are based on
          <code> config/routes.php </code>.
        </p>

        <div className="admin-menu-grid">
          {adminMenu.map((item) => (
            <a key={item.href} className="admin-menu-item" href={item.href}>
              <strong>{item.title}</strong>
              <span className="muted">{item.description}</span>
            </a>
          ))}
        </div>

        <div className="actions">
          <a className="ghost" href="/">
            Back to overview
          </a>
        </div>
      </div>
    </main>
  );
}
