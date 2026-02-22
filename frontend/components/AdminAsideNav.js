import { useRouter } from 'next/router';

const adminLinks = [
  { href: '/admin', label: 'Admin Home', icon: 'home' },
  { href: '/admin/users', label: 'Users List', icon: 'users' },
  { href: '/admin/users/create', label: 'Create User', icon: 'plus' },
  { href: '/admin/permissions', label: 'Casbin Rules', icon: 'shield' },
];

function NavIcon({ type }) {
  if (type === 'users') {
    return (
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
        <circle cx="9" cy="7" r="4" />
        <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
        <path d="M16 3.13a4 4 0 0 1 0 7.75" />
      </svg>
    );
  }

  if (type === 'plus') {
    return (
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
        <circle cx="12" cy="12" r="10" />
        <path d="M12 8v8" />
        <path d="M8 12h8" />
      </svg>
    );
  }

  if (type === 'shield') {
    return (
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
        <path d="m12 3 7 4v5c0 5-3.2 8.8-7 10-3.8-1.2-7-5-7-10V7l7-4Z" />
        <path d="m9 12 2 2 4-4" />
      </svg>
    );
  }

  return (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
      <path d="M3 11.5 12 4l9 7.5" />
      <path d="M5 10.5V20h14v-9.5" />
    </svg>
  );
}

export default function AdminAsideNav() {
  const router = useRouter();

  return (
    <aside className="admin-aside" aria-label="Admin navigation">
      <p className="eyebrow">Navigation</p>
      <nav className="admin-aside-nav">
        {adminLinks.map((link) => {
          const isActive = router.pathname === link.href;
          return (
            <a key={link.href} href={link.href} className={`admin-aside-link${isActive ? ' admin-aside-link--active' : ''}`} aria-current={isActive ? 'page' : undefined}>
              <span className="admin-aside-link-icon" aria-hidden="true">
                <NavIcon type={link.icon} />
              </span>
              <span>{link.label}</span>
            </a>
          );
        })}
      </nav>
    </aside>
  );
}
