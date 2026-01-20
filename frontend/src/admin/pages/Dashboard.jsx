import React from "react";
import DashboardCards from "../components/DashboardCards.jsx";

const highlights = [
  {
    title: "Active campaign reach",
    value: "2.4M",
    detail: "monthly impressions in network",
  },
  {
    title: "Average listing time",
    value: "18 min",
    detail: "from upload to approval",
  },
  {
    title: "Verified sellers",
    value: "96%",
    detail: "trusted profiles and businesses",
  },
];

const featureCards = [
  {
    title: "Smart audience targeting",
    description:
      "Use interest, location, and behavior signals to reach the right buyers without guesswork.",
    icon: "🎯",
  },
  {
    title: "Creative studio",
    description:
      "Start from ready-made ad layouts, auto-resize assets, and keep brand colors consistent.",
    icon: "🎨",
  },
  {
    title: "Real-time performance",
    description:
      "Track clicks, calls, and saves live with alerts that keep your team in the loop.",
    icon: "📊",
  },
  {
    title: "Budget guardrails",
    description:
      "Set daily caps, guard against overspend, and pause campaigns instantly from mobile.",
    icon: "🛡️",
  },
];

const quickActions = [
  {
    title: "Post a new listing",
    detail: "Launch a product or service ad in under 5 minutes.",
    action: "Start listing",
  },
  {
    title: "Create a promo bundle",
    detail: "Combine ads across channels for a seasonal push.",
    action: "Build bundle",
  },
  {
    title: "Invite your team",
    detail: "Share roles and approvals with agency or sales teams.",
    action: "Add collaborators",
  },
];

const categories = [
  "Real estate",
  "Auto & transport",
  "Jobs",
  "Services",
  "Electronics",
  "Home & living",
  "Events",
  "Retail",
];

const Dashboard = () => (
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
                  <h5 className="fw-semibold mb-3">Today at a glance</h5>
                  <ul className="list-unstyled m-0">
                    <li className="d-flex justify-content-between align-items-center mb-3">
                      <div>
                        <p className="mb-0 fw-semibold">New leads</p>
                        <small className="text-muted">Across 6 active campaigns</small>
                      </div>
                      <span className="fs-4 fw-bold text-primary">126</span>
                    </li>
                    <li className="d-flex justify-content-between align-items-center mb-3">
                      <div>
                        <p className="mb-0 fw-semibold">Click-through rate</p>
                        <small className="text-muted">Last 24 hours</small>
                      </div>
                      <span className="fs-4 fw-bold text-success">4.8%</span>
                    </li>
                    <li className="d-flex justify-content-between align-items-center">
                      <div>
                        <p className="mb-0 fw-semibold">Top channel</p>
                        <small className="text-muted">Mobile feed placements</small>
                      </div>
                      <span className="fs-5 fw-bold text-dark">Social + Search</span>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section className="py-5">
        <div className="container">
          <div className="row g-4">
            {highlights.map((item) => (
              <div className="col-md-4" key={item.title}>
                <div className="card h-100 shadow-sm">
                  <div className="card-body">
                    <p className="text-uppercase text-muted fw-semibold small mb-2">{item.title}</p>
                    <h3 className="fw-bold mb-1">{item.value}</h3>
                    <p className="text-muted mb-0">{item.detail}</p>
                  </div>
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>

      <section className="py-4">
        <div className="container">
          <div className="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-4 gap-3">
            <div>
              <h2 className="fw-bold mb-2">Built for high-performing ads</h2>
              <p className="text-muted mb-0">
                Everything you need to launch, optimize, and scale listings for any marketplace category.
              </p>
            </div>
            <button className="btn btn-primary btn-lg">Explore capabilities</button>
          </div>
          <div className="row g-4">
            {featureCards.map((card) => (
              <div className="col-md-6 col-lg-3" key={card.title}>
                <div className="card h-100 shadow-sm border-0">
                  <div className="card-body">
                    <div className="feature-icon mb-3" aria-hidden="true">
                      {card.icon}
                    </div>
                    <h5 className="fw-semibold">{card.title}</h5>
                    <p className="text-muted mb-0">{card.description}</p>
                  </div>
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>

      <section className="py-5">
        <div className="container">
          <div className="card border-0 shadow-sm p-4 p-lg-5">
            <div className="row align-items-center gy-4">
              <div className="col-lg-6">
                <h3 className="fw-bold mb-3">Popular ad categories</h3>
                <p className="text-muted">
                  Jump-start new listings using category templates that already perform well on Slim Ads.
                </p>
                <div className="d-flex flex-wrap gap-2">
                  {categories.map((category) => (
                    <span className="category-pill" key={category}>
                      {category}
                    </span>
                  ))}
                </div>
              </div>
              <div className="col-lg-6">
                <div className="row g-3">
                  {quickActions.map((action) => (
                    <div className="col-md-6" key={action.title}>
                      <div className="card h-100 shadow-sm border-0">
                        <div className="card-body">
                          <h6 className="fw-semibold mb-2">{action.title}</h6>
                          <p className="text-muted small mb-3">{action.detail}</p>
                          <button className="btn btn-outline-primary btn-sm fw-semibold">
                            {action.action}
                          </button>
                        </div>
                      </div>
                    </div>
                  ))}
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section className="py-4">
        <div className="container">
          <DashboardCards />
        </div>
      </section>
    </main>
  </div>
);

export default Dashboard;
