import React, { useEffect, useMemo, useState } from "react";

const useAdminOverviewCounts = () => {
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

const OverviewCard = ({ title, count, description, icon, link, isLoading, error }) => (
    <div className="dashboard-top-box admin-overview-card rounded-bottom panel-bg">
        <div className="left">
            <h3>{isLoading ? "—" : count}</h3>
            <p>{error ?? description}</p>
            <a className="dashboard-link" href={link}>
                View {title.toLowerCase()}
            </a>
        </div>
        <div className="right">
            <span className="text-primary">Live</span>
            <div className="part-icon rounded">
                <span><i className={icon}></i></span>
            </div>
        </div>
    </div>
);

const DashboardCards = () => {
    const { counts, loading, error } = useAdminOverviewCounts();
    const sharedError = useMemo(() => (error ? error : null), [error]);

    return (
        <div className="row mb-30 dashboard-overview-row">
            <div className="col-lg-4 col-12 col-xs-12">
                <OverviewCard
                    title="Users"
                    count={counts.users}
                    description="Active accounts with access."
                    icon="fa-light fa-user"
                    link="users"
                    isLoading={loading}
                    error={sharedError}
                />
            </div>
            <div className="col-lg-4 col-12 col-xs-12">
                <OverviewCard
                    title="Roles"
                    count={counts.roles}
                    description="Roles controlling permissions."
                    icon="fa-light fa-id-badge"
                    link="roles"
                    isLoading={loading}
                    error={sharedError}
                />
            </div>
            <div className="col-lg-4 col-12 col-xs-12">
                <OverviewCard
                    title="Ads"
                    count={counts.ads}
                    description="Live ads running today."
                    icon="fa-light fa-bullhorn"
                    link="ads"
                    isLoading={loading}
                    error={sharedError}
                />
            </div>
        </div>
    );
};

export default DashboardCards;
