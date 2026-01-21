import React from "react";
import DashboardCards from "../components/DashboardCards.jsx";

const defaultCopy = {
  hero: {
    badge: "Ads Command Center",
    title: "Launch smarter ads that reach buyers right when they are ready.",
    lead:
      "Slim Ads keeps your listings, promo campaigns, and analytics in one place—optimized for speed, clarity, and measurable conversions.",
    actions: {
      primary: "Create campaign",
      secondary: "View demo workspace",
    },
  },
  today: {
    title: "Today at a glance",
    items: [
      {
        title: "New leads",
        detail: "Across 6 active campaigns",
        value: "126",
        valueClass: "text-primary",
      },
      {
        title: "Click-through rate",
        detail: "Last 24 hours",
        value: "4.8%",
        valueClass: "text-success",
      },
      {
        title: "Top channel",
        detail: "Mobile feed placements",
        value: "Social + Search",
        valueClass: "text-dark",
      },
    ],
  },
  highlights: [
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
  ],
  feature: {
    title: "Built for high-performing ads",
    description:
      "Everything you need to launch, optimize, and scale listings for any marketplace category.",
    cta: "Explore capabilities",
    cards: [
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
    ],
  },
  categories: {
    title: "Popular ad categories",
    description:
      "Jump-start new listings using category templates that already perform well on Slim Ads.",
    items: [
      "Real estate",
      "Auto & transport",
      "Jobs",
      "Services",
      "Electronics",
      "Home & living",
      "Events",
      "Retail",
    ],
  },
  quickActions: {
    items: [
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
    ],
  },
  overview: undefined,
};

const Dashboard = ({ copy = defaultCopy, showOnlyOverview = false }) => (
  <div className="app-shell min-vh-100 admin-dashboard">
    <main className="page-content">
      {showOnlyOverview ? (
        <section className="py-4">
          <div className="container">
            <DashboardCards copy={copy.overview} />
          </div>
        </section>
      ) : (
        <>
      <section className="hero-banner py-5">
        <div className="container">
          <div className="row align-items-center gy-4">
            <div className="col-lg-7 text-center text-lg-start">
              <span className="badge bg-light text-primary fw-semibold mb-3">
                {copy.hero?.badge ?? defaultCopy.hero.badge}
              </span>
              <h1 className="display-5 fw-bold mb-3">
                {copy.hero?.title ?? defaultCopy.hero.title}
              </h1>
              <p className="lead mb-4">
                {copy.hero?.lead ?? defaultCopy.hero.lead}
              </p>
              <div className="d-flex flex-wrap gap-3 justify-content-center justify-content-lg-start">
                <button className="btn btn-light btn-lg fw-semibold">
                  {copy.hero?.actions?.primary ?? defaultCopy.hero.actions.primary}
                </button>
                <button className="btn btn-outline-light btn-lg fw-semibold">
                  {copy.hero?.actions?.secondary ?? defaultCopy.hero.actions.secondary}
                </button>
              </div>
            </div>
            <div className="col-lg-5">
              <div className="card shadow-lg border-0">
                <div className="card-body p-4">
                  <h5 className="fw-semibold mb-3">
                    {copy.today?.title ?? defaultCopy.today.title}
                  </h5>
                  <ul className="list-unstyled m-0">
                    {(copy.today?.items ?? defaultCopy.today.items).map((item, index) => (
                      <li
                        className={`d-flex justify-content-between align-items-center${index < 2 ? " mb-3" : ""}`}
                        key={item.title}
                      >
                        <div>
                          <p className="mb-0 fw-semibold">{item.title}</p>
                          <small className="text-muted">{item.detail}</small>
                        </div>
                        <span className={`fs-4 fw-bold ${item.valueClass ?? "text-primary"}`}>
                          {item.value}
                        </span>
                      </li>
                    ))}
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
            {(copy.highlights ?? defaultCopy.highlights).map((item) => (
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
              <h2 className="fw-bold mb-2">
                {copy.feature?.title ?? defaultCopy.feature.title}
              </h2>
              <p className="text-muted mb-0">
                {copy.feature?.description ?? defaultCopy.feature.description}
              </p>
            </div>
            <button className="btn btn-primary btn-lg">
              {copy.feature?.cta ?? defaultCopy.feature.cta}
            </button>
          </div>
          <div className="row g-4">
            {(copy.feature?.cards ?? defaultCopy.feature.cards).map((card) => (
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
                <h3 className="fw-bold mb-3">
                  {copy.categories?.title ?? defaultCopy.categories.title}
                </h3>
                <p className="text-muted">
                  {copy.categories?.description ?? defaultCopy.categories.description}
                </p>
                <div className="d-flex flex-wrap gap-2">
                  {(copy.categories?.items ?? defaultCopy.categories.items).map((category) => (
                    <span className="category-pill" key={category}>
                      {category}
                    </span>
                  ))}
                </div>
              </div>
              <div className="col-lg-6">
                <div className="row g-3">
                  {(copy.quickActions?.items ?? defaultCopy.quickActions.items).map((action) => (
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
          <DashboardCards copy={copy.overview} />
        </div>
      </section>
        </>
      )}
    </main>
  </div>
);

export default Dashboard;
