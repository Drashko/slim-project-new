export default function Home() {
  return (
    <main className="container container--full">
      <div className="card card--full">
        <p className="eyebrow">Frontend</p>
        <h1>Welcome to the Slim Project frontend</h1>
        <p>
          This is the default home page at <code>http://localhost:3000</code>.
        </p>

        <div className="actions">
          <a className="primary" href="/admin">
            View admin API status
          </a>
        </div>
      </div>
    </main>
  );
}
