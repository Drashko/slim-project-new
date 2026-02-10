const userApiRoutes = [
  {
    method: 'GET',
    path: '/api/v1/users',
    description: 'List all users',
    action: 'Open users console',
    href: '/admin/users',
  },
  {
    method: 'GET',
    path: '/api/v1/users/{id}',
    description: 'Get a single user by id',
    action: 'Find user by id',
    href: '/admin/users#get-user',
  },
  {
    method: 'POST',
    path: '/api/v1/users',
    description: 'Create a new user',
    action: 'Create user',
    href: '/admin/users#create-user',
  },
  {
    method: 'PUT',
    path: '/api/v1/users/{id}',
    description: 'Update user by id',
    action: 'Update user',
    href: '/admin/users#update-user',
  },
  {
    method: 'DELETE',
    path: '/api/v1/users/{id}',
    description: 'Delete user by id',
    action: 'Delete user',
    href: '/admin/users#delete-user',
  },
];

export default function AdminHome() {
  return (
    <main className="container container--start container--full">
      <div className="card card--full">
        <p className="eyebrow">Admin API</p>
        <h1>Admin routes menu</h1>
        <p>
          These links are generated from the existing user routes in
          <code> config/routes.php </code>.
        </p>

        <section className="panel">
          <h2>User endpoints</h2>
          <div className="list">
            {userApiRoutes.map((route) => (
              <div key={`${route.method}-${route.path}`} className="list-item">
                <p>
                  <strong>{route.method}</strong> <code>{route.path}</code>
                </p>
                <p className="muted">{route.description}</p>
                <div className="actions">
                  <a className="primary" href={route.href}>
                    {route.action}
                  </a>
                </div>
              </div>
            ))}
          </div>
        </section>

        <div className="actions">
          <a className="ghost" href="/">
            Back to overview
          </a>
        </div>
      </div>
    </main>
  );
}
