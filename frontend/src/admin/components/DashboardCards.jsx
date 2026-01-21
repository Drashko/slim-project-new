import React, { useEffect, useMemo, useState } from "react";

const defaultCopy = {
    loading: "—",
    error: "Unable to load overview metrics.",
    liveLabel: "Live",
    cards: [
        {
            title: "Users",
            description: "Active accounts with access.",
            icon: "fa-solid fa-user",
            link: "admin/users",
            linkLabel: "View users",
        },
        {
            title: "Roles",
            description: "Roles controlling permissions.",
            icon: "fa-solid fa-id-badge",
            link: "admin/roles",
            linkLabel: "View roles",
        },
        {
            title: "Ads",
            description: "Live ads running today.",
            icon: "fa-solid fa-bullhorn",
            link: "admin/ads",
            linkLabel: "View ads",
        },
    ],
};

const useAdminOverviewCounts = (errorMessage) => {
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
                setStatus({ loading: false, error: errorMessage });
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

const OverviewCard = ({ title, count, description, icon, link, linkLabel, isLoading, error, liveLabel, loadingLabel }) => (
    <div className="dashboard-top-box admin-overview-card rounded-bottom panel-bg">
        <div className="left">
            <h3>{isLoading ? loadingLabel : count}</h3>
            <p>{error ?? description}</p>
            <a className="dashboard-link" href={link}>
                {linkLabel}
            </a>
        </div>
        <div className="right">
            <span className="text-primary">{liveLabel}</span>
            <div className="part-icon rounded">
                <span><i className={icon}></i></span>
            </div>
        </div>
    </div>
);

const DashboardCards = ({ copy = defaultCopy }) => {
    const { counts, loading, error } = useAdminOverviewCounts(copy.error ?? defaultCopy.error);
    const sharedError = useMemo(() => (error ? error : null), [error]);
    const cards = copy.cards ?? defaultCopy.cards;

    return (
        <div className="row mb-30 dashboard-overview-row">
            <div className="col-lg-4 col-12 col-xs-12">
                <OverviewCard
                    title={cards[0]?.title ?? defaultCopy.cards[0].title}
                    count={counts.users}
                    description={cards[0]?.description ?? defaultCopy.cards[0].description}
                    icon={cards[0]?.icon ?? defaultCopy.cards[0].icon}
                    link={cards[0]?.link ?? defaultCopy.cards[0].link}
                    linkLabel={cards[0]?.linkLabel ?? defaultCopy.cards[0].linkLabel}
                    isLoading={loading}
                    error={sharedError}
                    liveLabel={copy.liveLabel ?? defaultCopy.liveLabel}
                    loadingLabel={copy.loading ?? defaultCopy.loading}
                />
            </div>
            <div className="col-lg-4 col-12 col-xs-12">
                <OverviewCard
                    title={cards[1]?.title ?? defaultCopy.cards[1].title}
                    count={counts.roles}
                    description={cards[1]?.description ?? defaultCopy.cards[1].description}
                    icon={cards[1]?.icon ?? defaultCopy.cards[1].icon}
                    link={cards[1]?.link ?? defaultCopy.cards[1].link}
                    linkLabel={cards[1]?.linkLabel ?? defaultCopy.cards[1].linkLabel}
                    isLoading={loading}
                    error={sharedError}
                    liveLabel={copy.liveLabel ?? defaultCopy.liveLabel}
                    loadingLabel={copy.loading ?? defaultCopy.loading}
                />
            </div>
            <div className="col-lg-4 col-12 col-xs-12">
                <OverviewCard
                    title={cards[2]?.title ?? defaultCopy.cards[2].title}
                    count={counts.ads}
                    description={cards[2]?.description ?? defaultCopy.cards[2].description}
                    icon={cards[2]?.icon ?? defaultCopy.cards[2].icon}
                    link={cards[2]?.link ?? defaultCopy.cards[2].link}
                    linkLabel={cards[2]?.linkLabel ?? defaultCopy.cards[2].linkLabel}
                    isLoading={loading}
                    error={sharedError}
                    liveLabel={copy.liveLabel ?? defaultCopy.liveLabel}
                    loadingLabel={copy.loading ?? defaultCopy.loading}
                />
            </div>
        </div>
    );
};

export default DashboardCards;
