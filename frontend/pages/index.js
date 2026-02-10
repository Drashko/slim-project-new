export default function Home() {
  return (
    <main className="container container--full">
      <div className="card card--full">
        <p className="eyebrow">Next.js is ready</p>
        <h1>Welcome to the Slim Project frontend</h1>
        <p>
          This frontend now includes dedicated pages for the public and admin API
          endpoints. Use the links below to see live responses from the Slim
          back end.
        </p>
        <div className="actions">
          <a className="primary" href="/public">
            View public API status
          </a>
          <a className="ghost" href="/admin">
            View admin API status
          </a>
        </div>
      </div>
    </main>
  );
}
