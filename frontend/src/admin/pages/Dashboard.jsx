import React from "react";
import SummaryRow from "../components/SummaryRow";

const Dashboard = ({ user }) => {
  if (!user) {
    return null;
  }

  const email = user.email ?? "";
  const roles = Array.isArray(user.roles) ? user.roles : [];

  return (
    <div className="card card-outline card-info mt-4">
      <div className="card-header">
        <h3 className="card-title">
          <i className="fa-solid fa-circle-info me-2" aria-hidden="true"></i>
          React admin summary
        </h3>
      </div>
      <div className="card-body">
        <p className="text-muted mb-2">Mounted via Plates react_mount.</p>
        <SummaryRow label="Signed in as" value={email || "—"} />
        <SummaryRow label="Roles" value={roles.length > 0 ? roles.join(", ") : "—"} />
      </div>
    </div>
  );
};

export default Dashboard;
