import { useRouter } from 'next/router';

const adminLinks = [
  { href: '/admin', label: 'Admin Home', icon: '🏠' },
  { href: '/admin/users', label: 'Users List', icon: '👥' },
  { href: '/admin/users/create', label: 'Create User', icon: '➕' },
  { href: '/admin/users/read', label: 'Read User', icon: '🔎' },
  { href: '/admin/users/update', label: 'Update User', icon: '✏️' },
  { href: '/admin/users/delete', label: 'Delete User', icon: '🗑️' },
  { href: '/admin/permissions', label: 'Permissions', icon: '🛡️' },
];

export default function AdminAsideNav() {
  const router = useRouter();

  return (
    <aside className="admin-aside" aria-label="Admin navigation">
      <p className="eyebrow">Navigation</p>
      <nav className="admin-aside-nav">
        {adminLinks.map((link) => {
          const isActive = router.pathname === link.href;
          return (
            <a
              key={link.href}
              href={link.href}
              className={`admin-aside-link${isActive ? ' admin-aside-link--active' : ''}`}
              aria-current={isActive ? 'page' : undefined}
            >
              <span aria-hidden="true">{link.icon}</span>
              <span>{link.label}</span>
            </a>
          );
        })}
      </nav>
    </aside>
  );
}
