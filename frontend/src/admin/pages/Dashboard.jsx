import React from "react";
import DashboardCards from "../components/DashboardCards.jsx";

const Dashboard = () => {
    return (
        <div>
            <section className="text-center py-5">
                <h1 className="h3 mb-3">Admin demo message</h1>
                <p className="text-muted mb-0">This is a lightweight React placeholder for the admin home page.</p>
            </section>
            <DashboardCards/>
        </div>

    )
}

export default Dashboard;
