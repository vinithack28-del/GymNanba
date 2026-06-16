# GymOS — Gym Owner Portal
# Module 10: POS / Store

**URL:** `/pos`
**Sub-pages:** `/pos/products`, `/pos/sales`, `/pos/stock`
**Access:** POS staff (billing + view stock only); Owner, Branch manager, Accountant (full)
**Purpose:** Point-of-sale for gym merchandise, supplements, and consumables.

---

## 1. Products Page (`/pos/products`)

**Layout:** Grid of product cards. Filter bar: category dropdown, status toggle (Active / Inactive / All), search.

**Product card:** Thumbnail photo | Name | Category badge | Selling price | Stock count (red if low) | Status | Actions (Edit, Deactivate)

---

## 2. Add / Edit Product Form

| Field | Validation | Required |
|---|---|---|
| **Product name** | 2–100 chars, unique per gym | Yes |
| **Category** | Supplement / Apparel / Equipment / Food & Drink / Other | Yes |
| **SKU / Barcode** | Max 50 chars, unique per gym if provided | No |
| **Unit of measure** | Piece / Kg / Litre / Pack | Yes |
| **Cost price (INR)** | 0–999,999 (stored in paise) | Yes |
| **Selling price (INR)** | > 0; show warning if below cost | Yes |
| **GST rate** | 0 / 5 / 12 / 18 / 28% | Yes |
| **Current stock** | Integer ≥ 0 | Yes |
| **Low stock threshold** | Integer ≥ 1 | Yes |
| **Product photo** | JPG/PNG, max 3 MB | No |
| **Description** | Max 500 chars | No |
| **Status** | Active / Inactive | Yes |

---

## 3. POS Billing Page (`/pos/sales`)

**Split layout:** Left = product browser, Right = cart.

### Product Browser
- Search bar (supports barcode scanner: auto-detects input ≥ 8 digits)
- Category tabs
- Product grid — click product to add to cart

### Cart Panel

| Element | Behaviour |
|---|---|
| Line items | Product name, editable qty (type or +/−), unit price, GST rate, line total |
| Remove item | × button per line |
| Discount | % or flat Rs. amount — requires branch manager or owner permission |
| Subtotal | Auto-calculated |
| GST breakdown | Per-product GST rates summed |
| Total | Subtotal + GST − Discount |
| Member link | Optional: search and link sale to a member (for records) |
| Payment buttons | Cash / UPI / Card |
| Checkout | Creates sale, decrements stock, generates receipt |

### On Checkout
1. Validate stock availability for all items (409 if any are insufficient)
2. Decrement `pos_products.stock_quantity` for each item
3. Create `pos_sales` record
4. Create `pos_sale_items` records
5. Generate receipt (print / WhatsApp / SMS)

---

## 4. Sales History Page (`/pos/sales`)

**Filters:** Date range, Branch, Method, Staff, Member

**Table columns:** Date | Bill # | Member (if linked) | Items summary | Subtotal | GST | Total | Method | Staff | Branch | Actions (View bill, Refund)

---

## 5. Stock Management Page (`/pos/stock`)

**Table:** Product | Category | Current stock | Cost price | Stock value (qty × cost) | Low stock? | Last restock date | Actions (Restock, Adjust, History)

**Low stock indicator:** Amber badge on product row when `current_stock ≤ low_stock_threshold`.

---

## 6. Stock Restock Form

| Field | Required | Notes |
|---|---|---|
| Product | Yes | Dropdown of all products |
| Quantity added | Yes | > 0 |
| Cost price per unit | Yes | May differ from product cost price |
| Supplier name | No | |
| Invoice / reference | No | |
| Date | Yes | Default today |
| Notes | No | |

On submit: `pos_products.stock_quantity += quantity_added`. Creates `pos_stock_movements` record with `type=restock`.

---

## 7. Stock Adjustments

Manual correction with **mandatory reason:**

| Reason | When to use |
|---|---|
| Damaged | Product damaged and written off |
| Expired | Past expiry date |
| Theft | Missing stock suspected theft |
| Count correction | Physical count differs from system |
| Sample / gift | Given away as sample or gift |

- Quantity can be positive (increase) or negative (decrease)
- Owner or manager approval required if decrease > 5 units
- All adjustments logged in `pos_stock_movements` with `type=adjustment`

---

## 8. Low Stock Alerts

| Trigger | Alert shown |
|---|---|
| `stock ≤ threshold` | Amber badge on product card |
| Any product at threshold | Red badge on POS menu sidebar item |
| Alert threshold crossed | Optional email notification to gym owner |

---

## 9. Daily Tally

Available from POS > Sales as "Today's tally" button. Shows for selected date:

- Total sales by payment method (Cash / UPI / Card)
- Total by product category
- Total by staff member
- Total by branch (multi-branch)
- Total GST collected
- Net cash in hand vs card/UPI

Printable one-page summary.

---

## 10. API Endpoints

```
GET /api/v1/pos/products
  Query: category, status, search
  Response: { products: [...] }

POST /api/v1/pos/products
  Body: { name, category, sku?, unit, cost_paise, price_paise, gst_rate,
          current_stock, low_stock_threshold, description?, status }
  Response 201: { product_id }

PUT /api/v1/pos/products/:id
  Body: partial update
  Response 200: { product }

POST /api/v1/pos/sales
  Body: { items: [{ product_id, qty, unit_price_paise }], method,
          reference?, discount_paise?, member_id?, branch_id, notes? }
  Response 201: { sale_id, bill_number, receipt_url }
  Error 409: INSUFFICIENT_STOCK { product_id, product_name, available }

GET /api/v1/pos/sales
  Query: from, to, branch_id, staff_id, method, member_id, page, limit
  Response: { sales: [...], total }

GET /api/v1/pos/stock
  Query: branch_id, low_stock_only
  Response: { products: [...] }

POST /api/v1/pos/stock/restock
  Body: { product_id, quantity, cost_paise, supplier?, reference?, date, notes? }
  Response 200: { new_stock_quantity }

POST /api/v1/pos/stock/adjust
  Body: { product_id, quantity_change, reason, notes? }
  Response 200: { new_stock_quantity }
  Error 403: APPROVAL_REQUIRED (decrease > 5 units)
```

---

## 11. Database Schema

```sql
CREATE TABLE pos_products (
  id                   UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  tenant_id            UUID NOT NULL REFERENCES tenants(id),
  name                 VARCHAR(100) NOT NULL,
  category             VARCHAR(50) NOT NULL,
  sku                  VARCHAR(50),
  unit                 VARCHAR(20) NOT NULL DEFAULT 'piece',
  cost_paise           INTEGER NOT NULL CHECK (cost_paise >= 0),
  price_paise          INTEGER NOT NULL CHECK (price_paise > 0),
  gst_rate             NUMERIC(5,2) NOT NULL DEFAULT 0,
  stock_quantity       INTEGER NOT NULL DEFAULT 0,
  low_stock_threshold  INTEGER NOT NULL DEFAULT 5,
  photo_url            TEXT,
  description          TEXT,
  status               VARCHAR(20) NOT NULL DEFAULT 'active',
  created_at           TIMESTAMPTZ NOT NULL DEFAULT NOW(),

  CONSTRAINT uq_product_name UNIQUE (tenant_id, name)
);

CREATE TABLE pos_sales (
  id             UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  tenant_id      UUID NOT NULL REFERENCES tenants(id),
  branch_id      UUID NOT NULL REFERENCES branches(id),
  bill_number    VARCHAR(20) NOT NULL UNIQUE,
  member_id      UUID REFERENCES members(id),
  subtotal_paise INTEGER NOT NULL,
  gst_paise      INTEGER NOT NULL DEFAULT 0,
  discount_paise INTEGER NOT NULL DEFAULT 0,
  total_paise    INTEGER NOT NULL,
  method         VARCHAR(20) NOT NULL,
  reference      VARCHAR(100),
  notes          TEXT,
  sold_by        UUID REFERENCES staff(id),
  created_at     TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE pos_sale_items (
  id               UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  sale_id          UUID NOT NULL REFERENCES pos_sales(id),
  product_id       UUID NOT NULL REFERENCES pos_products(id),
  qty              INTEGER NOT NULL CHECK (qty > 0),
  unit_price_paise INTEGER NOT NULL,
  gst_rate         NUMERIC(5,2) NOT NULL,
  line_total_paise INTEGER NOT NULL
);

CREATE TABLE pos_stock_movements (
  id          UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  product_id  UUID NOT NULL REFERENCES pos_products(id),
  tenant_id   UUID NOT NULL REFERENCES tenants(id),
  type        VARCHAR(20) NOT NULL,  -- restock | adjustment | sale | return
  quantity    INTEGER NOT NULL,      -- positive or negative
  cost_paise  INTEGER,               -- for restock movements
  reason      TEXT,
  reference   VARCHAR(100),
  created_by  UUID REFERENCES staff(id),
  created_at  TIMESTAMPTZ NOT NULL DEFAULT NOW()
);
```

---

## 12. Validation Rules

| Rule | Detail |
|---|---|
| Stock cannot go negative | Default: block. `allow_negative_stock` setting overrides. |
| GST rate | Must be one of: 0, 5, 12, 18, 28 |
| Selling price | Must be > 0 |
| Discount | Requires branch_manager or owner role |
| Decrease > 5 units | Requires owner/manager approval |

---

## 13. Access Control

| Role | Billing | View products/stock | Edit products | Restock | Adjust stock |
|---|---|---|---|---|---|
| Gym owner | Yes | Yes | Yes | Yes | Yes |
| Branch manager | Yes | Yes | Yes | Yes | Yes |
| Accountant | No | Yes | No | No | No |
| POS staff | Yes | Yes | No | No | No |
| Receptionist | No | No | No | No | No |
| Trainer | No | No | No | No | No |
