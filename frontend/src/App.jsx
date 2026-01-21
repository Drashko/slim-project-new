import React, {useEffect, useMemo, useState} from "react";

const App = () => {

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
                                    Slim Ads keeps your listings, promo campaigns, and analytics in one place—optimized
                                    for
                                    speed, clarity, and measurable conversions.
                                </p>
                                <div className="d-flex flex-wrap gap-3 justify-content-center justify-content-lg-start">
                                    <button className="btn btn-light btn-lg fw-semibold">Create campaign</button>
                                    <button className="btn btn-outline-light btn-lg fw-semibold">View demo workspace
                                    </button>
                                </div>
                            </div>
                            <div className="col-lg-5">
                                <div className="card shadow-lg border-0">
                                    <div className="card-body p-4">
                                        <h5 className="fw-semibold mb-3">Ads insights</h5>
                                        <p className="text-muted mb-0">
                                            Keep tabs on campaign performance and respond quickly to new opportunities.
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

}

export default App;
