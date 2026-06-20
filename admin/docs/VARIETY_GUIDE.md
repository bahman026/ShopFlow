# Product, Attribute & Variety — Complete Guide

This document explains the full attribute/product/variety system: what each table does, how they relate, and a step-by-step guide to creating a sellable product with multi-dimensional varieties (e.g. Size × Color).

---

## The Big Picture

```
Ancestor
  └── AttributeGroup  (e.g. "Size", "Color", "Material")
        └── Attribute  (e.g. "S", "M", "L", "Red", "Blue")

Category  ──(attribute_group_category)──  AttributeGroup
  │                                          (which groups apply to this category)
  └── Product  ──(attribute_group_id)──  AttributeGroup (primary variety dimension)
        │
        ├── product_attribute  (descriptive attributes: material, dimensions, etc.)
        │
        └── Variety  ──(attribute_id)──  Attribute  (primary: e.g. "M")
              └── attribute_variety  ──  Attribute  (secondary: e.g. "Red", "Blue")
```

---

## Table Reference

### `ancestors`

Top-level categories for organizing attribute groups. Think of them as "spec sections" on a product page.

| Column | Purpose |
|--------|---------|
| `name` | Display name, e.g. "Technical Specifications", "Dimensions" |
| `order` | Sort order |

---

### `attribute_groups`

Groups of related attributes. Each group belongs to one Ancestor.

| Column | Purpose |
|--------|---------|
| `ancestor_id` | Which ancestor section this group belongs to |
| `name` | Machine/admin label, e.g. "Color", "Size", "RAM" |
| `label` | Human label shown in admin, e.g. "Available Colors" |
| `order` | Sort order |

**Example rows:**

| id | name  | label          |
|----|-------|----------------|
| 1  | Color | Available Colors |
| 2  | Size  | Available Sizes  |

---

### `attributes`

Individual values within a group. Each attribute belongs to one AttributeGroup.

| Column | Purpose |
|--------|---------|
| `attribute_group_id` | Which group this value belongs to |
| `value` | The display value, e.g. "Red", "M", "8GB" |
| `color` | Optional hex or color name (used for color swatches on frontend) |

**Example rows:**

| id | group | value | color   |
|----|-------|-------|---------|
| 1  | Color | Red   | #ff0000 |
| 2  | Color | Blue  | #0000ff |
| 3  | Size  | S     | null    |
| 4  | Size  | M     | null    |
| 5  | Size  | L     | null    |

---

### `attribute_group_category`

Links which attribute groups apply to which categories. Also controls filtering and required enforcement.

| Column | Purpose |
|--------|---------|
| `attribute_group_id` | The group |
| `category_id` | The category |
| `as_filter` | Whether this group can be used as a filter on the category page |
| `required` | Whether products in this category MUST have this group's attributes assigned |

**Example:** Link "Size" and "Color" groups to the "Shirts" category. Mark both as `as_filter = true`. Mark "Size" as `required = true` (every shirt must have size attributes).

---

### `products`

One row per product. Defines what the product is, its SEO data, and which attribute group drives its varieties.

| Column | Purpose |
|--------|---------|
| `heading` | Product name |
| `slug` | URL path |
| `category_id` | Required. Which category this product is in |
| `attribute_group_id` | **The PRIMARY variety dimension** — which attribute group defines how this product's varieties differ (e.g. "Size"). Nullable. Options filtered by category in the admin panel |
| `price` | Base display price (denormalized from cheapest variety) |
| `variety_counts` | Automatically synced — count of varieties; DO NOT set manually |
| `brand_id`, `image_id` | Optional branding and featured image |

---

### `product_attribute` (pivot)

Links a product to **descriptive** attributes — things that are true for all varieties (e.g. material, screen size, weight). These are NOT the variety-defining attributes.

| Column | Purpose |
|--------|---------|
| `product_id` | The product |
| `attribute_id` | The descriptive attribute |
| `is_highlight` | Whether to feature this attribute prominently on the product page |

**Example:** A phone product has `material = Aluminum` and `screen = 6.1 inch` as product_attribute rows. These don't change between the 128GB and 256GB varieties.

---

### `varieties`

Each row is one specific, purchasable combination. The seller sets the price and inventory per variety.

| Column | Purpose |
|--------|---------|
| `product_id` | Which product |
| `attribute_id` | **Primary attribute** — the attribute from the product's `attribute_group` (e.g. "M" from "Size"). Auto-populates `attribute_value` and `color` on save |
| `attribute_value` | Auto-filled from `attribute.value` when `attribute_id` is set. Can be set manually |
| `color` | Auto-filled from `attribute.color`. Used for color swatches |
| `price` | This variety's selling price |
| `sale_price` | Discounted price; shown instead of `price` when set |
| `inventory` | Units in stock |
| `has_stock` | Override: set to false to show "out of stock" regardless of inventory |
| `status` | Published / Draft / Deleted |

**Auto-behaviour on save:**
- `attribute_value` and `color` are automatically populated from the linked `Attribute` record.
- `product.variety_counts` is automatically updated whenever a variety is created or deleted.

---

### `attribute_variety` (pivot)

Links each variety to **additional** attributes from other groups — enabling multi-dimensional varieties.

| Column | Purpose |
|--------|---------|
| `variety_id` | The variety |
| `attribute_id` | An additional attribute (e.g. "Red" from the "Color" group) |

**Rule:** The pair `(variety_id, attribute_id)` is unique. Both FKs cascade on delete.

**When to use:** When a product varies by more than one dimension. The primary dimension goes into `varieties.attribute_id` (e.g. Size = M). Each additional dimension goes into this pivot (e.g. Color = Red).

---

## How It All Connects — Shirt Example

**Setup:**

1. **Ancestor:** "Clothing Specs"
2. **Attribute Groups:** "Size" (under Clothing Specs), "Color" (under Clothing Specs)
3. **Attributes:**
   - Size: S, M, L, XL
   - Color: Red (#ff0000), Blue (#0000ff), Black (#000000)
4. **Category:** "Shirts"
   - Linked to "Size" group via `attribute_group_category` (required = true, as_filter = true)
   - Linked to "Color" group via `attribute_group_category` (required = false, as_filter = true)

---

## Step-by-Step: Creating a Shirt with Size × Color Varieties

### Step 1 — Set up the Attribute Groups and Attributes

Go to **Attribute Groups** → Create:
- Name: `Size`, Ancestor: Clothing Specs

Go to **Attributes** → Create for each size:
- Group: Size, Value: S
- Group: Size, Value: M
- Group: Size, Value: L
- Group: Size, Value: XL

Go to **Attribute Groups** → Create:
- Name: `Color`, Ancestor: Clothing Specs

Go to **Attributes** → Create for each color:
- Group: Color, Value: Red, Color: #ff0000
- Group: Color, Value: Blue, Color: #0000ff
- Group: Color, Value: Black, Color: #000000

---

### Step 2 — Link Groups to the Category

Go to **Attribute Group Categories** → Create:
- Attribute Group: Size, Category: Shirts, as_filter: yes, required: yes
- Attribute Group: Color, Category: Shirts, as_filter: yes, required: no

---

### Step 3 — Create the Product

Go to **Products** → Create:

| Field | Value |
|-------|-------|
| Heading | Slim Fit Cotton Shirt |
| Category | Shirts |
| **Attribute Group** | **Size** ← this defines the primary variety dimension; options filtered by "Shirts" |
| Brand | (optional) |
| Price | (base price for display) |

Save the product.

---

### Step 4 — Add Varieties (inside the Product edit page)

Scroll down to **Variety Details**. Add one row per available combination:

| Attribute (Size) | Additional Attributes (Color) | Price | Inventory |
|-----------------|-------------------------------|-------|-----------|
| M | Red | 450,000 | 10 |
| M | Blue | 450,000 | 5 |
| M | Black | 450,000 | 8 |
| L | Red | 450,000 | 3 |
| L | Blue | 450,000 | 0 |
| L | Black | 450,000 | 7 |
| XL | Red | 450,000 | 2 |

> **Note:** You only create rows for combinations that actually exist in your inventory. "L / Blue" with inventory 0 is still a row (it exists, just out of stock). "XL / Blue" not having a row means that combination is simply unavailable — the frontend will not show it.

**How the fields work:**
- **Attribute** → select from the Size group (S, M, L, XL). Auto-populates `attribute_value` on save.
- **Additional Attributes** → select from all OTHER groups (Color group). Stored in `attribute_variety` pivot. Preloaded, searchable by "GroupName / Value".

---

### Step 5 — Add Descriptive Product Attributes (optional)

In the **Product Attributes** section of the product edit page, add attributes that describe the product as a whole (not variety-specific):

| Attribute | Highlight? |
|-----------|-----------|
| Material: Cotton | yes |
| Fit: Slim | no |

These go into `product_attribute` and are shown in the product spec table on the frontend.

---

## How the Frontend Queries Varieties

Given a product, the frontend should:

1. **Fetch all varieties** with their pivot attributes in one query (cached under `varieties.product.{product_id}`):

```php
$varieties = Variety::query()
    ->with('attributes') // eager-load the pivot (color etc.)
    ->where('product_id', $productId)
    ->where('status', VarietyStatusEnum::PUBLISHED)
    ->get();
```

2. **Build available primary options** (sizes):
```php
$availableSizes = $varieties->pluck('attribute_id')->unique();
```

3. **When user selects a size**, show available secondary options (colors):
```php
$colorsForM = $varieties
    ->where('attribute_id', $mAttributeId)
    ->flatMap->attributes
    ->unique('id');
```

4. **When user selects size + color**, find the exact variety:
```php
$variety = $varieties->first(function (Variety $v) use ($sizeId, $colorId) {
    return $v->attribute_id === $sizeId
        && $v->attributes->contains('id', $colorId);
});
```

5. If `$variety` is null → that combination doesn't exist (don't show it).
   If `$variety->has_stock === false` or `$variety->inventory === 0` → show as out of stock.

> Do all of this in memory from the cached collection — do NOT run one query per combination.

---

## Summary — Which Table Does What

| Table | Role |
|-------|------|
| `ancestors` | Top-level grouping for attribute groups (spec sections) |
| `attribute_groups` | Groups of related values (Size, Color, RAM…) |
| `attributes` | Individual values within a group (M, Red, 8GB…) |
| `attribute_group_category` | Which groups apply to which category; required/filter flags |
| `products` | The product itself; `attribute_group_id` = primary variety dimension |
| `product_attribute` | Descriptive attributes shared across all varieties |
| `varieties` | One row per purchasable combination; `attribute_id` = primary (e.g. Size=M) |
| `attribute_variety` | Additional attributes per variety (e.g. Color=Red) via pivot |
