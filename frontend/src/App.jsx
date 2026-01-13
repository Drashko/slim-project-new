import React, { useMemo, useState } from "react";
import PageHeader from "./components/PageHeader";
import { categories, latestListings, marketStats, marketplaceListings, tips } from "./data/classifieds";

const AccentBadge = ({ tone = "primary", children }) => (
  <span className={`badge rounded-pill bg-${tone} bg-opacity-10 text-${tone} fw-semibold`}>{children}</span>
);

const ListingCard = ({ listing, compact = false }) => (
  <div className={`card listing-card h-100 shadow-sm ${compact ? "compact" : ""}`}>
    <div className="card-body d-flex flex-column gap-3">
      <div className="d-flex align-items-start justify-content-between">
        <span className="text-muted small">{listing.category}</span>
        <span className="listing-price fw-semibold">{listing.price}</span>
      </div>
      <div>
        <h3 className="h5 mb-1">{listing.title}</h3>
        <p className="text-muted mb-0 small">{listing.description}</p>
      </div>
      <div className="d-flex align-items-center justify-content-between text-secondary small">
        <span className="d-inline-flex align-items-center gap-2">
          <span className="location-dot" aria-hidden="true"></span>
          {listing.location}
        </span>
        <span>{listing.posted}</span>
      </div>
    </div>
  </div>
);

const CategoryPill = ({ name }) => (
  <span className="category-pill">{name}</span>
);

const TipCard = ({ tip }) => (
  <div className="border rounded-3 p-3 bg-white shadow-sm h-100">
    <h4 className="h6 mb-1">{tip.title}</h4>
    <p className="text-muted small mb-0">{tip.detail}</p>
  </div>
);

const StatCard = ({ stat }) => (
  <div className="d-flex flex-column p-3 rounded-3 shadow-sm bg-white h-100">
    <span className="text-muted small mb-1">{stat.label}</span>
    <span className={`h5 mb-0 text-${stat.accent}`}>{stat.value}</span>
  </div>
);

const priceRanges = [
  { id: "any", label: "Всички цени", check: () => true },
  { id: "0-300", label: "До 300 лв", check: (price) => price <= 300 },
  { id: "300-1000", label: "300 - 1000 лв", check: (price) => price > 300 && price <= 1000 },
  { id: "1000+", label: "Над 1000 лв", check: (price) => price > 1000 },
];

const App = () => {
  const [filters, setFilters] = useState({
    category: "",
    location: "",
    condition: "",
    itemType: "",
    price: "any",
    hasDelivery: false,
    tags: [],
  });

  const filterLists = useMemo(() => {
    const locations = [...new Set(marketplaceListings.map((item) => item.location))].sort();
    const conditions = [...new Set(marketplaceListings.map((item) => item.condition))];
    const itemTypes = [...new Set(marketplaceListings.map((item) => item.itemType))];
    const tags = [...new Set(marketplaceListings.flatMap((item) => item.tags))].sort();

    return { locations, conditions, itemTypes, tags };
  }, []);

  const filteredListings = useMemo(() => {
    return marketplaceListings.filter((listing) => {
      if (filters.category && listing.category !== filters.category) return false;
      if (filters.location && listing.location !== filters.location) return false;
      if (filters.condition && listing.condition !== filters.condition) return false;
      if (filters.itemType && listing.itemType !== filters.itemType) return false;

      const priceCheck = priceRanges.find((p) => p.id === filters.price) ?? priceRanges[0];
      if (!priceCheck.check(listing.priceValue)) return false;

      if (filters.hasDelivery && !listing.hasDelivery) return false;

      if (filters.tags.length > 0) {
        const hasAllTags = filters.tags.every((tag) => listing.tags.includes(tag));
        if (!hasAllTags) return false;
      }

      return true;
    });
  }, [filters]);

  const handleFilterChange = (key, value) => {
    setFilters((prev) => ({ ...prev, [key]: value }));
  };

  const toggleTag = (tag) => {
    setFilters((prev) => {
      const exists = prev.tags.includes(tag);
      return { ...prev, tags: exists ? prev.tags.filter((t) => t !== tag) : [...prev.tags, tag] };
    });
  };

  const resetFilters = () => {
    setFilters({
      category: "",
      location: "",
      condition: "",
      itemType: "",
      price: "any",
      hasDelivery: false,
      tags: [],
    });
  };

  return (
    <div className="app-shell bg-body-tertiary min-vh-100">
      <main className="container py-4 page-content">
        <PageHeader
          title="Бързи и ясни обяви"
          subtitle="Публикувайте, намерете и договаряйте в реално време с хора от цяла България."
          actions={
            <div className="d-flex gap-2">
              <button type="button" className="btn btn-primary">
                Публикувай обява
              </button>
              <button type="button" className="btn btn-outline-primary">
                Търсене на обява
              </button>
            </div>
          }
        />

        <div className="hero-banner rounded-4 p-4 p-md-5 mb-4 shadow-sm">
          <div className="row align-items-center g-4">
            <div className="col-lg-8">
              <div className="d-inline-flex align-items-center gap-2 bg-white bg-opacity-75 px-3 py-2 rounded-pill mb-3">
                <AccentBadge tone="success">Нови обяви</AccentBadge>
                <span className="small text-secondary">Всеки ден стотици публикувани предложения</span>
              </div>
              <h2 className="h3 mb-2">Намерете всичко необходимо на едно място</h2>
              <p className="lead text-muted mb-4">
                От техника и мебели до услуги и имоти – Slim Обяви събира продавачи и купувачи с прозрачни профили и бързи съобщения.
              </p>
              <div className="d-flex flex-wrap gap-2">
                {categories.slice(0, 4).map((category) => (
                  <CategoryPill key={category} name={category} />
                ))}
              </div>
            </div>
            <div className="col-lg-4">
              <div className="bg-white rounded-4 p-3 shadow-sm h-100 d-flex flex-column gap-3">
                <div className="d-flex align-items-center justify-content-between">
                  <span className="fw-semibold">Какво искате да публикувате?</span>
                  <AccentBadge tone="primary">Обява</AccentBadge>
                </div>
                <div className="d-grid gap-2">
                  <input type="text" className="form-control form-control-lg" placeholder="Заглавие на обявата" />
                  <div className="d-flex gap-2">
                    <input type="text" className="form-control" placeholder="Локация" />
                    <input type="text" className="form-control" placeholder="Цена" />
                  </div>
                  <button type="button" className="btn btn-primary w-100">
                    Започни публикуване
                  </button>
                </div>
                <p className="text-muted small mb-0">Без такси за публикуване. Обновявайте обявата си веднага.</p>
              </div>
            </div>
          </div>
        </div>

        <div className="row g-4 mb-4">
          {marketStats.map((stat) => (
            <div className="col-md-4" key={stat.label}>
              <StatCard stat={stat} />
            </div>
          ))}
        </div>

        <div className="row g-4">
          <div className="col-lg-4">
            <aside className="filter-card card shadow-sm position-sticky">
              <div className="card-body">
                <div className="d-flex align-items-center justify-content-between mb-3">
                  <div>
                    <h3 className="h6 mb-1">Филтри</h3>
                    <p className="text-muted small mb-0">Намерете точните обяви</p>
                  </div>
                  <button type="button" className="btn btn-link text-decoration-none p-0" onClick={resetFilters}>
                    Нулирай
                  </button>
                </div>

                <div className="filter-section">
                  <p className="text-muted small mb-2">Категория</p>
                  <div className="d-flex flex-wrap gap-2">
                    {categories.map((category) => (
                      <button
                        type="button"
                        key={category}
                        className={`btn btn-sm filter-pill ${filters.category === category ? "active" : ""}`}
                        onClick={() => handleFilterChange("category", filters.category === category ? "" : category)}
                      >
                        {category}
                      </button>
                    ))}
                  </div>
                </div>

                <div className="filter-section">
                  <p className="text-muted small mb-2">Локация</p>
                  <div className="d-grid gap-2">
                    {filterLists.locations.map((location) => (
                      <div className="form-check" key={location}>
                        <input
                          className="form-check-input"
                          type="radio"
                          name="location"
                          id={`loc-${location}`}
                          checked={filters.location === location}
                          onChange={() => handleFilterChange("location", location)}
                        />
                        <label className="form-check-label" htmlFor={`loc-${location}`}>
                          {location}
                        </label>
                      </div>
                    ))}
                    <button
                      type="button"
                      className="btn btn-sm btn-outline-secondary align-self-start"
                      onClick={() => handleFilterChange("location", "")}
                    >
                      Без значение
                    </button>
                  </div>
                </div>

                <div className="filter-section">
                  <p className="text-muted small mb-2">Цена</p>
                  <div className="d-grid gap-2">
                    {priceRanges.map((range) => (
                      <div className="form-check" key={range.id}>
                        <input
                          className="form-check-input"
                          type="radio"
                          name="price"
                          id={`price-${range.id}`}
                          checked={filters.price === range.id}
                          onChange={() => handleFilterChange("price", range.id)}
                        />
                        <label className="form-check-label" htmlFor={`price-${range.id}`}>
                          {range.label}
                        </label>
                      </div>
                    ))}
                  </div>
                </div>

                <div className="filter-section">
                  <p className="text-muted small mb-2">Състояние и тип</p>
                  <div className="row g-2">
                    <div className="col-6">
                      <select
                        className="form-select form-select-sm"
                        value={filters.condition}
                        onChange={(e) => handleFilterChange("condition", e.target.value)}
                      >
                        <option value="">Всички</option>
                        {filterLists.conditions.map((condition) => (
                          <option key={condition} value={condition}>
                            {condition}
                          </option>
                        ))}
                      </select>
                    </div>
                    <div className="col-12">
                      <select
                        className="form-select form-select-sm"
                        value={filters.itemType}
                        onChange={(e) => handleFilterChange("itemType", e.target.value)}
                      >
                        <option value="">Всички типове</option>
                        {filterLists.itemTypes.map((itemType) => (
                          <option key={itemType} value={itemType}>
                            {itemType}
                          </option>
                        ))}
                      </select>
                    </div>
                  </div>
                </div>

                <div className="filter-section">
                  <div className="form-check">
                    <input
                      className="form-check-input"
                      type="checkbox"
                      id="with-delivery"
                      checked={filters.hasDelivery}
                      onChange={(e) => handleFilterChange("hasDelivery", e.target.checked)}
                    />
                    <label className="form-check-label" htmlFor="with-delivery">
                      Само с доставка
                    </label>
                  </div>
                </div>

                <div className="filter-section">
                  <p className="text-muted small mb-2">Тагове</p>
                  <div className="d-flex flex-wrap gap-2">
                    {filterLists.tags.map((tag) => {
                      const active = filters.tags.includes(tag);
                      return (
                        <button
                          type="button"
                          key={tag}
                          className={`btn btn-sm filter-pill ${active ? "active" : ""}`}
                          onClick={() => toggleTag(tag)}
                        >
                          #{tag}
                        </button>
                      );
                    })}
                  </div>
                </div>
              </div>
            </aside>
          </div>

          <div className="col-lg-8 d-flex flex-column gap-4">
            <section>
              <div className="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                <div>
                  <h2 className="h5 mb-1">Резултати</h2>
                  <p className="text-muted small mb-0">{filteredListings.length} намерени обяви</p>
                </div>
                <AccentBadge tone="primary">Динамични филтри</AccentBadge>
              </div>
              <div className="row g-3">
                {filteredListings.map((listing) => (
                  <div className="col-md-6" key={listing.id}>
                    <ListingCard listing={listing} />
                  </div>
                ))}
                {filteredListings.length === 0 && (
                  <div className="col-12">
                    <div className="alert alert-light border">Няма обяви по избраните критерии. Опитайте с по-малко филтри.</div>
                  </div>
                )}
              </div>
            </section>

            <section>
              <div className="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                <h2 className="h6 mb-0">Последно добавени</h2>
                <AccentBadge tone="secondary">Свежи обяви</AccentBadge>
              </div>
              <div className="row g-3">
                {latestListings.map((listing) => (
                  <div className="col-md-4" key={listing.id}>
                    <ListingCard listing={listing} compact />
                  </div>
                ))}
              </div>
            </section>

            <section>
              <div className="card shadow-sm">
                <div className="card-body">
                  <h3 className="h6 mb-3">Съвети за по-добра обява</h3>
                  <div className="row g-3">
                    {tips.map((tip) => (
                      <div className="col-md-4" key={tip.title}>
                        <TipCard tip={tip} />
                      </div>
                    ))}
                  </div>
                </div>
              </div>
            </section>
          </div>
        </div>
      </main>
    </div>
  );
};

export default App;
