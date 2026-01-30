import React, { useEffect, useState } from "react";

const translationKeys = {
  badge: "home.react.badge",
  title: "home.react.title",
  subtitle: "home.react.subtitle",
  cta_primary: "home.react.cta_primary",
  cta_secondary: "home.react.cta_secondary",
  card_title: "home.react.card_title",
  card_body: "home.react.card_body",
};

const buildTranslations = (payload) =>
  Object.fromEntries(
    Object.entries(translationKeys).map(([key, messageKey]) => [
      key,
      payload[messageKey] ?? "",
    ])
  );

const resolveLocaleFromPath = (pathname) => {
  if (!pathname) {
    return "en";
  }

  const segments = pathname.split("/").filter(Boolean);
  return segments[0] ?? "en";
};

const App = () => {
  const [translations, setTranslations] = useState(() => buildTranslations({}));

  useEffect(() => {
    const locale = resolveLocaleFromPath(window.location.pathname);
    const controller = new AbortController();

    const loadTranslations = async () => {
      try {
        const response = await fetch(`/api/localization/${locale}`, {
          signal: controller.signal,
        });

        if (!response.ok) {
          console.warn("Failed to load translations for", locale);
          return;
        }

        const payload = await response.json();
        setTranslations(buildTranslations(payload));
      } catch (error) {
        if (error.name !== "AbortError") {
          console.warn("Failed to load translations.", error);
        }
      }
    };

    loadTranslations();

    return () => controller.abort();
  }, []);

  return (
    <div className="app-shell min-vh-100">
      <main className="page-content">
        <section className="hero-banner py-5">
          <div className="container">
            <div className="row align-items-center gy-4">
              <div className="col-lg-7 text-center text-lg-start">
                <span className="badge bg-light text-primary fw-semibold mb-3">
                  {translations.badge}
                </span>
                <h1 className="display-5 fw-bold mb-3">
                  {translations.title}
                </h1>
                <p className="lead mb-4">{translations.subtitle}</p>
                <div className="d-flex flex-wrap gap-3 justify-content-center justify-content-lg-start">
                  <button className="btn btn-light btn-lg fw-semibold">
                    {translations.cta_primary}
                  </button>
                  <button className="btn btn-outline-light btn-lg fw-semibold">
                    {translations.cta_secondary}
                  </button>
                </div>
              </div>
              <div className="col-lg-5">
                <div className="card shadow-lg border-0">
                  <div className="card-body p-4">
                    <h5 className="fw-semibold mb-3">
                      {translations.card_title}
                    </h5>
                    <p className="text-muted mb-0">
                      {translations.card_body}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>
      </main>
    </div>
  );
};

export default App;
