export default function Home() {
  return (
    <main className="container">
      <div className="card">
        <p className="eyebrow">Next.js is ready</p>
        <h1>Welcome to the Slim Project frontend</h1>
        <p>
          This frontend is powered by Next.js. Start editing{' '}
          <code>frontend/pages/index.js</code> to customize the experience.
        </p>
        <div className="actions">
          <a className="primary" href="https://nextjs.org/docs" target="_blank" rel="noreferrer">
            Read the docs
          </a>
          <a className="ghost" href="https://nextjs.org/learn" target="_blank" rel="noreferrer">
            Learn Next.js
          </a>
        </div>
      </div>
    </main>
  );
}
