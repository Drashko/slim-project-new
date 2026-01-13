export const roles = [
  {
    key: "ROLE_ADMIN",
    name: "Administrators",
    description: "Full control of authentication, authorization, and auditing settings.",
    permissions: ["admin.access", "admin.users.manage", "admin.roles.manage", "admin.permissions.manage", "admin.audit.view"],
    members: 3,
  },
  {
    key: "ROLE_MANAGER",
    name: "Access managers",
    description: "Trusted users who can onboard teammates and keep role assignments tidy.",
    permissions: ["admin.users.manage", "admin.roles.manage", "profile.view"],
    members: 5,
  },
  {
    key: "ROLE_API",
    name: "API clients",
    description: "Service users that authenticate via tokens but do not access the admin UI.",
    permissions: ["api.access"],
    members: 2,
  },
  {
    key: "ROLE_USER",
    name: "Standard users",
    description: "Signed-in users with access to their own profile and session details.",
    permissions: ["profile.view"],
    members: 18,
  },
];

export const permissionGroups = [
  {
    name: "Identity",
    description: "Authentication endpoints and profile access.",
    permissions: ["profile.view", "auth.refresh"],
  },
  {
    name: "User administration",
    description: "Managing team accounts, invitations, and suspensions.",
    permissions: ["admin.users.manage", "admin.roles.manage", "admin.permissions.manage"],
  },
  {
    name: "Oversight",
    description: "Read-only visibility into system events and API usage.",
    permissions: ["admin.audit.view", "api.access"],
  },
];

export const auditLog = [
  {
    title: "Role assignment updated",
    actor: "Amelia Admin",
    detail: "Granted ROLE_MANAGER to user@example.com",
    timeAgo: "2 minutes ago",
    severity: "success",
  },
  {
    title: "Permission matrix published",
    actor: "Jordan Lee",
    detail: "Updated admin.permissions.manage for Administrators",
    timeAgo: "34 minutes ago",
    severity: "info",
  },
  {
    title: "API token issued",
    actor: "Service account",
    detail: "Created refresh token for api@example.com",
    timeAgo: "1 hour ago",
    severity: "warning",
  },
  {
    title: "Login challenge",
    actor: "Unknown user",
    detail: "Two failed sign-in attempts blocked by rate limits",
    timeAgo: "3 hours ago",
    severity: "danger",
  },
];
