# ShopFlow Shipping & Logistics Guide

A complete reference for the shipping system: how the tables relate, how to configure a new carrier, and how the frontend should resolve shipping costs at checkout.

---

## Overview

The shipping system has three levels:

```
shipping_lines          (Carrier / Company)
    └── shipping_methods    (Service tier with rules)
            └── shipping_cities  (Per-city cost & availability)
```

| Level | Table | Purpose |
|---|---|---|
| 1 | `shipping_lines` | The carrier company (e.g. Post, Express Courier) |
| 2 | `shipping_methods` | A specific service offered by that carrier, with order rules |
| 3 | `shipping_cities` | Per-city cost override, schedule, and availability for a method |

---

## Table Reference

### `shipping_lines`

The top-level carrier entity. Minimal by design.

| Column | Type | Description |
|---|---|---|
| `id` | PK | — |
| `name` | string | Carrier name shown in admin (e.g. "Post Office", "Same-day Courier") |
| `cost` | unsigned int | Base cost in Toman. Can be overridden per city in `shipping_cities` |

---

### `shipping_methods`

A service tier belonging to a carrier. Defines who can use it, minimum order requirements, and blackout periods.

| Column | Type | Description |
|---|---|---|
| `id` | PK | — |
| `shipping_line_id` | FK → `shipping_lines` | The carrier this method belongs to |
| `name` | string | Service name (e.g. "Standard Post", "Overnight Express") |
| `type` | string | Service category (e.g. Express, Special, Economy) |
| `min_count` | unsigned int (nullable) | Minimum item quantity required to use this method |
| `min_amount` | unsigned int (nullable) | Minimum order amount required to use this method |
| `for` | `ShippingMethodForEnum` (int) | Audience: `CUSTOMER=10` (default), `PARTNER=20`, `EMPLOYEE=30` |
| `disable_from` | datetime (nullable) | Start of unavailability window |
| `disable_to` | datetime (nullable) | End of unavailability window |
| `status` | boolean | `true` = active (default), `false` = hidden from all users |

---

### `shipping_cities`

Per-city (or per-province) configuration for a shipping method. Overrides cost and availability at a geographic level.

| Column | Type | Description |
|---|---|---|
| `id` | PK | — |
| `shipping_method_id` | FK → `shipping_methods` | Which method this config applies to |
| `province_id` | FK → `provinces` (nullable) | Province-level rule. One of province or city must be set. |
| `city_id` | FK → `cities` (nullable) | City-level rule. Takes priority over province-level. |
| `pay_on_delivery` | boolean | If true, no online payment needed — paid on delivery |
| `amount` | unsigned int (nullable) | Shipping cost for this city. Null = postpaid. 0 = free. |
| `sending_days` | string (nullable) | Days this method ships: single day, even/odd, or a pattern |
| `disable_from` | datetime (nullable) | Start of city-specific unavailability |
| `disable_to` | datetime (nullable) | End of city-specific unavailability |
| `delay` | unsigned int (nullable) | Extra delivery delay in days for this city |
| `description` | text (nullable) | Human-readable schedule detail (e.g. "up to 72 hours", "18:00–22:00 today") |
| `status` | boolean | Active / inactive for this city |

---

## Relationships

```
ShippingLine
    hasMany  ShippingMethod

ShippingMethod
    belongsTo  ShippingLine
    hasMany    ShippingCity

ShippingCity
    belongsTo  ShippingMethod
    belongsTo  Province (nullable)
    belongsTo  City (nullable)
```

---

## Step-by-step: Add a New Carrier

**Example: Add "Tap30 Express" delivery**

### Step 1 — Create the Shipping Line

In admin → Logistics → Shipping Lines → Create:

| Field | Value |
|---|---|
| Name | Tap30 Express |
| Cost | 50,000 |

### Step 2 — Create Shipping Methods for that Line

In admin → Logistics → Shipping Methods → Create:

| Field | Value |
|---|---|
| Shipping Line | Tap30 Express |
| Name | Tap30 – Same Day |
| Type | Express |
| Min Amount | 200,000 |
| For | Customer |
| Status | Active |

Repeat for other tiers (e.g. "Tap30 – Scheduled" for next-day).

### Step 3 — Configure Per-city Availability

In admin → Logistics → Shipping Cities → Create:

| Field | Value |
|---|---|
| Shipping Method | Tap30 – Same Day |
| City | Tehran |
| Amount | 45,000 |
| Pay on Delivery | No |
| Sending Days | 1,2,3,4,5 (Saturday–Wednesday) |
| Delay | 0 |
| Description | Delivered within 2 hours |
| Status | Active |

---

## Frontend: Resolving Shipping at Checkout

At checkout the frontend receives the user's city. The resolution steps:

```
1. Load all active ShippingCities for that city_id
   → also load ShippingCities for the user's province_id (fallback)

2. For each ShippingCity, load its ShippingMethod
   → check method.status = active
   → check method.min_count ≤ cart item count
   → check method.min_amount ≤ cart total
   → check method.disable_from / disable_to (not in blackout)

3. Check ShippingCity
   → status = active
   → disable_from / disable_to (not in blackout)
   → sending_days includes today

4. Resolve cost:
   → if pay_on_delivery: show "Pay on delivery"
   → if amount = 0: show "Free"
   → if amount is null: show "Postpaid (calculated on delivery)"
   → otherwise: show amount (in Toman)

5. Present the filtered list to the user
```

**Priority rule:** city-level `shipping_cities` records take priority over province-level records for the same method.

---

## Implementation Status

| Table | Status | Notes |
|---|---|---|
| `shipping_lines` | ✅ Done | Migration, model, factory, Filament resource, tests |
| `shipping_methods` | ✅ Done | Migration, model, factory, Filament resource, tests. `ShippingMethodForEnum` for `for` field. |
| `shipping_cities` | ⏳ Pending | Next to implement (Phase 3) |

---

## Important Notes

- A `shipping_city` entry can target a **province** (applies to all cities in that province) or a specific **city**. Use city-level entries to override province defaults.
- `amount = 0` means free shipping — do not confuse with `null` (postpaid).
- `sending_days` format is not yet standardised — define the pattern constants in your frontend or a config file before using this field.
- The `for` field on `shipping_methods` allows restricting methods to partners or employees (e.g. internal logistics) without exposing them to customers.
