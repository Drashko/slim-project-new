import React from "react";

const PageHeader = ({ title, subtitle, actions }) => {
  return (
    <div className="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
      <div>
        <h1 className="h3 mb-1">{title}</h1>
        {subtitle && <p className="text-muted mb-0">{subtitle}</p>}
      </div>
      {actions && <div className="d-flex align-items-center gap-2">{actions}</div>}
    </div>
  );
};

export default PageHeader;
