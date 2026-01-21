import React, { useEffect, useMemo, useState } from "react";

const useOverviewCounts = () => {
  const [counts, setCounts] = useState({ users: null, roles: null, ads: null });
  const [status, setStatus] = useState({ loading: true, error: null });

  useEffect(() => {
    let active = true;

    const loadCounts = async () => {
      try {
        const response = await fetch("/api/admin/overview");
        if (!response.ok) {
          throw new Error(`Request failed: ${response.status}`);
        }
        const data = await response.json();
        if (active) {
          setCounts({
            users: data.users ?? null,
            roles: data.roles ?? null,
            ads: data.ads ?? null,
          });
          setStatus({ loading: false, error: null });
        }
      } catch (error) {
        if (active) {
          setStatus({ loading: false, error: "Unable to load overview metrics." });
        }
      }
    };

    loadCounts();

    return () => {
      active = false;
    };
  }, []);

  return { counts, ...status };
};

const OverviewCard = ({ title, count, description, link, linkLabel, isLoading, error }) => (
  <div className="card h-100 shadow-sm border-0">
    <div className="card-body d-flex flex-column">
      <p className="text-uppercase text-muted fw-semibold small mb-2">{title}</p>
      <h3 className="fw-bold mb-1">{isLoading ? "—" : count}</h3>
      <p className="text-muted mb-4">{error ?? description}</p>
      <a className="btn btn-primary btn-sm fw-semibold mt-auto" href={link}>
        {linkLabel}
      </a>
    </div>
  </div>
);

const UsersOverviewCard = ({ count, isLoading, error }) => (
  <OverviewCard
    title="Users"
    count={count}
    description="Active accounts with access to the platform."
    link="/admin/users"
    linkLabel="View users list"
    isLoading={isLoading}
    error={error}
  />
);

const RolesOverviewCard = ({ count, isLoading, error }) => (
  <OverviewCard
    title="Roles"
    count={count}
    description="Roles that control permissions and access levels."
    link="/admin/roles"
    linkLabel="View roles list"
    isLoading={isLoading}
    error={error}
  />
);

const AdsOverviewCard = ({ count, isLoading, error }) => (
  <OverviewCard
    title="Ads"
    count={count}
    description="Live ads running across active campaigns."
    link="/admin/ads"
    linkLabel="View ads list"
    isLoading={isLoading}
    error={error}
  />
);

const App = () => {
  const { counts, loading, error } = useOverviewCounts();
  const sharedError = useMemo(() => (error ? error : null), [error]);

  return (
    <div className="app-shell min-vh-100">
      <main className="page-content">
        <section className="hero-banner py-5">
          <div className="container">
            <div className="row align-items-center gy-4">
              <div className="col-lg-7 text-center text-lg-start">
                <span className="badge bg-light text-primary fw-semibold mb-3">
                  Ads Command Center
                </span>
                <h1 className="display-5 fw-bold mb-3">
                  Launch smarter ads that reach buyers right when they are ready.
                </h1>
                <p className="lead mb-4">
                  Slim Ads keeps your listings, promo campaigns, and analytics in one place—optimized for
                  speed, clarity, and measurable conversions.
                </p>
                <div className="d-flex flex-wrap gap-3 justify-content-center justify-content-lg-start">
                  <button className="btn btn-light btn-lg fw-semibold">Create campaign</button>
                  <button className="btn btn-outline-light btn-lg fw-semibold">View demo workspace</button>
                </div>
              </div>
              <div className="col-lg-5">
                <div className="card shadow-lg border-0">
                  <div className="card-body p-4">
                    <h5 className="fw-semibold mb-3">Overview at a glance</h5>
                    <p className="text-muted mb-0">
                      Quickly jump to the most important admin lists for users, roles, and ads.
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>

        <section className="py-5">
          <div className="container">
            <div className="row g-4">
              <div className="col-md-4">
                <UsersOverviewCard count={counts.users} isLoading={loading} error={sharedError} />
              </div>
              <div className="col-md-4">
                <RolesOverviewCard count={counts.roles} isLoading={loading} error={sharedError} />
              </div>
              <div className="col-md-4">
                <AdsOverviewCard count={counts.ads} isLoading={loading} error={sharedError} />
              </div>
            </div>
          </div>
        </section>
      </main>
    </div>
  );
};

export default App;
