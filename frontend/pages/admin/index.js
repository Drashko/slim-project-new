import AdminAsideNav from '../../components/AdminAsideNav';

export default function AdminHome() {
  return (
    <main className="container container--start container--full">
      <div className="admin-layout">
        <div className="card card--full">
        <p className="eyebrow">Admin Panel</p>
        <h1>Admin dashboard</h1>
        <p>Use the menu on the left to move through admin pages.</p>

        <div className="actions">
          <a className="ghost" href="/">
            Back to overview
          </a>
        </div>
        </div>
        <AdminAsideNav />
      </div>
    </main>
  );
}
