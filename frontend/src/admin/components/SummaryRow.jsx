import React from "react";

const SummaryRow = ({ label, value }) => (
  <div className="d-flex justify-content-between border-bottom py-2">
    <span className="text-secondary">{label}</span>
    <span className="fw-semibold">{value}</span>
  </div>
);

export default SummaryRow;
