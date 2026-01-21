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

const Dashboard = ({ copy = defaultCopy }) => (
  <div className="app-shell min-vh-100 admin-dashboard">
    <main className="page-content">
      <section className="py-4">
        <div className="container">
          <DashboardCards copy={copy.overview} />
        </div>
      </section>
    </main>
  </div>
);

export default Dashboard;
