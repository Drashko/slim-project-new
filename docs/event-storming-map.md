# Event Storming Map (DDD) — Marketplace Platform

This Event Storming map captures the key business flows and domain concepts for
a marketplace where sellers pay to list items (e.g., phones and accessories)
and buyers purchase those items. It is structured as:

- **Domain Events** (orange): facts that happened in the domain.
- **Commands** (blue): intentions that trigger behavior.
- **Aggregates** (yellow): consistency boundaries and decision makers.
- **Policies/Processes** (purple): automated reactions and workflows.
- **Read Models** (green): optimized projections for queries and UI.
- **External Systems** (pink): payment providers, shipping carriers, etc.

---

## 1) Seller Journey (Core Flow)

### Account → Listing Fee → Create Listing → Publish

**Commands → Events → Policies**

- **Register Seller**
  - Command: `RegisterSeller`
  - Event: `SellerRegistered`
  - Aggregate: `SellerAccount`
  - Read Model: `SellerProfile`

- **Purchase Listing Plan / Pay Listing Fee**
  - Command: `PurchaseListingPlan`
  - Event: `ListingPlanPurchased`
  - Aggregate: `BillingAccount`
  - Policy: `ActivateListingCredits`
  - External: `PaymentGateway`
  - Read Model: `SellerBillingSummary`

- **Create Listing**
  - Command: `CreateListing`
  - Event: `ListingCreated`
  - Aggregate: `Listing`
  - Policy: `ValidateListingFeeBalance`
  - Read Model: `ListingDraft`

- **Attach Photos & Details**
  - Command: `UpdateListingDetails`
  - Event: `ListingDetailsUpdated`
  - Aggregate: `Listing`

- **Publish Listing**
  - Command: `PublishListing`
  - Event: `ListingPublished`
  - Aggregate: `Listing`
  - Policy: `DeductListingFee`
  - Read Model: `MarketplaceCatalog`

---

## 2) Buyer Journey (Core Flow)

### Discover → Purchase → Order → Delivery

**Commands → Events → Policies**

- **Browse / Search Listings**
  - Command: `SearchListings`
  - Event: `ListingSearchPerformed`
  - Read Model: `ListingSearchResults`

- **View Listing**
  - Command: `ViewListing`
  - Event: `ListingViewed`
  - Read Model: `ListingDetail`

- **Place Order**
  - Command: `PlaceOrder`
  - Event: `OrderPlaced`
  - Aggregate: `Order`
  - Policy: `AuthorizePayment`
  - External: `PaymentGateway`
  - Read Model: `OrderConfirmation`

- **Payment Authorized**
  - Event: `PaymentAuthorized`
  - Policy: `NotifySellerOfOrder`
  - Aggregate: `Order`

- **Order Shipped**
  - Command: `ConfirmShipment`
  - Event: `OrderShipped`
  - Aggregate: `Order`
  - External: `ShippingCarrierAPI`
  - Read Model: `ShipmentTracking`

- **Order Completed**
  - Event: `OrderCompleted`
  - Aggregate: `Order`
  - Read Model: `OrderHistory`

---

## 3) Payments, Payouts & Fees

**Commands → Events → Policies**

- Command: `SetPayoutMethod`
  - Event: `PayoutMethodUpdated`
  - Aggregate: `SellerAccount`

- Command: `CapturePayment`
  - Event: `PaymentCaptured`
  - Aggregate: `Payment`
  - External: `PaymentGateway`

- Policy: `CalculateMarketplaceFee` (on `PaymentCaptured`)
  - Event: `MarketplaceFeeCalculated`
  - Aggregate: `Settlement`

- Policy: `ScheduleSellerPayout` (on `OrderCompleted`)
  - Event: `SellerPayoutScheduled`
  - Aggregate: `Payout`
  - External: `PaymentGateway`

Read Models:
- `MarketplaceRevenueSummary`
- `SellerPayoutHistory`
- `BuyerPaymentHistory`

---

## 4) Listing Compliance & Moderation

**Commands → Events → Policies**

- Command: `SubmitListingForReview`
  - Event: `ListingReviewSubmitted`
  - Aggregate: `ListingReview`

- Command: `ApproveListing`
  - Event: `ListingApproved`
  - Aggregate: `ListingReview`

- Command: `RejectListing`
  - Event: `ListingRejected`
  - Aggregate: `ListingReview`
  - Policy: `NotifySellerOfRejection`

Read Models:
- `ListingReviewQueue`
- `PolicyViolations`

---

## 5) Admin Operations

**Commands → Events → Read Models**

- Command: `SuspendSeller`
  - Event: `SellerSuspended`
  - Aggregate: `SellerAccount`

- Command: `ReactivateSeller`
  - Event: `SellerReactivated`
  - Aggregate: `SellerAccount`

- Command: `RemoveListing`
  - Event: `ListingRemoved`
  - Aggregate: `Listing`
  - Policy: `NotifySellerOfRemoval`

- Command: `RefundOrder`
  - Event: `OrderRefunded`
  - Aggregate: `Order`
  - External: `PaymentGateway`
  - Read Model: `RefundStatus`

---

## 6) Suggested Bounded Contexts

- **Accounts**: SellerAccount, BuyerAccount, Roles
- **Listings**: Listing, ListingDetails, Photos
- **Orders**: Order, OrderItem, Status transitions
- **Payments**: Payment, Settlement, Payout, Fees
- **Compliance**: ListingReview, Policy, Enforcement
- **Shipping**: Shipment, Tracking
- **Reporting**: Analytics, Dashboards, Alerts

---

## 7) Cross-Cutting Policies

- **ValidateListingFeeBalance** on `CreateListing`.
- **DeductListingFee** on `PublishListing`.
- **AuthorizePayment** on `OrderPlaced`.
- **NotifySellerOfOrder** on `PaymentAuthorized`.
- **ScheduleSellerPayout** on `OrderCompleted`.

---

## 8) Key Read Models for UI

- `SellerProfile`
- `SellerBillingSummary`
- `ListingDraft`
- `MarketplaceCatalog`
- `ListingSearchResults`
- `ListingDetail`
- `OrderConfirmation`
- `OrderHistory`
- `ShipmentTracking`
- `SellerPayoutHistory`
- `MarketplaceRevenueSummary`
- `AdminComplianceDashboard`

---

## 9) Open Questions for Discovery Workshops

- Is the listing fee per item, per category, or subscription-based?
- Are listings time-limited or auto-renewed?
- How are disputes between buyers and sellers handled?
- What payout cadence is required (instant, weekly, monthly)?
- Are buyer payments held in escrow until delivery confirmation?
- Which listing categories require stricter compliance review?

---

## 10) Example Database Tables

The following tables are a lightweight example schema for implementing the
marketplace flows above. Names and columns can be adapted to match the chosen
storage engine and ORM.

**Accounts & Identity**

- `users` (id, email, password_hash, role, status, created_at)
- `seller_profiles` (id, user_id, display_name, phone, rating, created_at)
- `buyer_profiles` (id, user_id, display_name, phone, created_at)
- `addresses` (id, user_id, type, line1, line2, city, region, postal_code, country)

**Listings**

- `listing_plans` (id, name, price, listing_credits, expires_in_days, created_at)
- `seller_listing_credits` (id, seller_id, available_credits, updated_at)
- `listings` (id, seller_id, title, description, category_id, price, currency, status, created_at)
- `listing_photos` (id, listing_id, url, sort_order, created_at)
- `listing_categories` (id, name, parent_id)

**Orders & Payments**

- `orders` (id, buyer_id, seller_id, listing_id, status, total_amount, currency, created_at)
- `order_events` (id, order_id, event_type, payload_json, created_at)
- `payments` (id, order_id, status, amount, currency, provider, provider_ref, created_at)
- `refunds` (id, payment_id, amount, reason, status, created_at)

**Payouts & Fees**

- `marketplace_fees` (id, order_id, fee_amount, currency, created_at)
- `seller_payouts` (id, seller_id, order_id, amount, currency, status, scheduled_at, paid_at)
- `payout_methods` (id, seller_id, provider, account_ref, created_at)

**Compliance & Moderation**

- `listing_reviews` (id, listing_id, status, reviewer_id, notes, created_at, reviewed_at)
- `policy_violations` (id, listing_id, violation_type, notes, created_at)

**Shipping**

- `shipments` (id, order_id, carrier, tracking_number, status, shipped_at, delivered_at)

---

## 11) Example Domain Models & Features

Below is a compact list of domain models (DDD) and the key product features
they enable in the marketplace.

**Accounts & Identity**

- **SellerAccount**
  - Features: seller onboarding, account verification, seller dashboard.
- **BuyerAccount**
  - Features: buyer onboarding, saved profiles, purchase history.

**Listings**

- **Listing**
  - Features: create/edit listing, publish/unpublish, pricing & availability.
- **ListingPlan**
  - Features: paid listing tiers, listing credits, expiration rules.
- **ListingPhoto**
  - Features: multi-image gallery, photo ordering, moderation checks.

**Orders & Payments**

- **Order**
  - Features: order placement, status tracking, cancellation.
- **Payment**
  - Features: authorization, capture, refunds.
- **MarketplaceFee**
  - Features: fee calculation, revenue reporting.
- **Payout**
  - Features: seller payout scheduling, payout status tracking.

**Compliance & Moderation**

- **ListingReview**
  - Features: listing approval workflow, rejection reasons.
- **PolicyViolation**
  - Features: enforcement actions, compliance reporting.

**Shipping**

- **Shipment**
  - Features: carrier selection, tracking updates, delivery confirmation.

**Core Platform Features (Aggregated)**

- Paid listing flow: `ListingPlan` → `Listing` → `ListingPublished`.
- Purchase flow: `Listing` → `Order` → `Payment` → `Shipment`.
- Settlement flow: `Payment` → `MarketplaceFee` → `Payout`.
