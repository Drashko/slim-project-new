import Link from 'next/link';

export default function AppLogo({ href = '/', className = '', ariaLabel = 'BrightPHP home' }) {
  const classes = ['app-logo', className].filter(Boolean).join(' ');

  return (
    <Link href={href} className={classes} aria-label={ariaLabel}>
      <span className="app-logo__mark" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
          <path d="M5 3h8a4 4 0 0 1 0 8H5Z" />
          <path d="M5 11h7a4 4 0 0 1 0 8H5Z" />
          <path d="M19 3v16" />
        </svg>
      </span>
      <span className="app-logo__text">BrightPHP</span>
    </Link>
  );
}
