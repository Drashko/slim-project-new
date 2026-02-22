import Link from 'next/link';
import AppLogo from './AppLogo';

const navItems = [
  { href: '/', label: 'Home' },
  { href: '/about', label: 'About' },
  { href: '/contact', label: 'Contact' },
  { href: '/login', label: 'Login' },
  { href: '/register', label: 'Register' },
];

export default function PublicNav() {
  return (
    <header className="public-nav-wrapper">
      <nav className="public-nav" aria-label="Public pages">
        <AppLogo className="public-nav__logo" />
        <div className="public-nav__links">
          {navItems.map((item) => (
            <Link key={item.href} href={item.href} className="public-nav__link">
              {item.label}
            </Link>
          ))}
        </div>
      </nav>
    </header>
  );
}
