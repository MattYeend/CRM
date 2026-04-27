# CRM System

A Laravel 12 CRM system

<!-- TOC -->
## Table of Contents

1. [Tech Stack](#tech-stack)
2. [How To Setup](#how-to-setup)
3. [How To Contribute](#how-to-contribute)
    1. [Commit Conventions](#commit-conventions)
    2. [Maintainer Merge Strategy](#maintainer-merge-strategy)
4. [General Information](#general-information)
    1. [Key Highlights](#key-highlights)
    2. [Core Features](#core-features)
    3. [Key Functional Areas](#key-functional-areas)
        1. [CRM Features](#crm-features)
        2. [ERP Features](#erp-features)
    4. [Information](#information)
        1. [Core Sales Flow](#core-sales-flow)
        2. [Leads](#leads)
        3. [Deals](#deals)
        4. [Pipelines and Stages](#pipelines-and-stages)
            1. [Pipelines](#pipelines)
            2. [Pipeline Stages](#pipeline-stages)
        5. [Quotes](#quotes)
        6. [Orders](#orders)
        7. [Invoices](#invoices)
        8. [Products](#products)
        9. [Product Stock Movements](#product-stock-movements)
        10. [Parts](#parts)
        11. [Part Images](#part-images)
        12. [Part Categories](#part-categories)
        13. [Suppliers](#suppliers)
        14. [Part Suppliers (Pivot)](#part-suppliers-pivot)
        15. [Part Stock Movements](#part-stock-movements)
        16. [Bill Of Materials (BOM)](#bill-of-materials-bom)
        17. [Activities](#activities)
        18. [Tasks](#tasks)
        19. [Attachments](#attachments)
        20. [Notes](#notes)
        21. [Users, Roles, and Permissions](#users-roles-and-permissions)
            1. [Users](#users)
            2. [Roles](#roles)
            3. [Permissions](#permissions)
        22. [Companies](#companies)
        23. [Industries](#industries)
        24. [Job Titles](#job-titles)
        25. [Learnings](#learnings)
        26. [Logs](#logs)
        27. [Are Activities and Tasks the Same?](#are-activities-and-tasks-the-same)
        28. [Relationship Overview Diagram](#relationship-overview-diagram)
5. [General CLI Commands](#general-cli-commands)
6. [Specific CLI Commands](#specific-cli-commands)
7. [Events And Listeners](#events-and-listeners)
8. [Sponsor The Project](#sponsor-the-project)
<!-- /TOC -->

---

## Tech Stack

| Tech | Version |
|------|---------|
| PHP | 8.4.6 |
| Laravel Installer | 5.14.0 |
| Laravel | 12.28.1 |
| Composer | 2.8.8 |
| NPM | 11.5.2 |
| Node | v23.11.0 |
| VueJS | 3.5.18 |
| MySQL | 8.0.42 |

---

## General Information

This project is an all-in-one CRM/ERP system designed to help businesses manage customers, leads, sales pipelines, projects, and internal workflows from a single, unified platform. It is built with Laravel 12, following established Laravel open-source conventions and best practices. The architecture emphasises clean separation of concerns, modular design, extensibility, and long-term maintainability.

### Key Highlights

- **Built in Laravel 12** - Leveraging the latest framework features for performance, security, and scalability
- **Modular & Extensible** - Easily add new modules or integrate with external APIs
- **User-Friendly Interface** - Modern, responsive design for smooth navigation and usability

### Core Features

- **Customer & Lead Management** - Organise contacts, track leads, and maintain detailed profiles
- **Role-Based Access Control** - Secure user management with customizable permissions
- **Analytics & Reporting** - Gain insights into business performance with dynamic dashboards

### Key Functional Areas

#### CRM Features

- **Lead management & qualification** - Capture leads from multiple sources, track status, score and qualify prospects, and convert them into deals or customers
- **Deal pipelines & stage tracking** - Visual sales pipelines with configurable stages, probability tracking, forecasting, and performance insights
- **Contact & company management** - Centralised database of individuals and organisations, including communication history, linked deals, and related projects
- **Activity logging (calls, emails, notes)** - Full interaction timeline per lead, contact, or deal to maintain context and improve collaboration
- **Task assignment & follow-ups** - Assign tasks to team members, set deadlines, reminders, and ensure timely follow-ups

#### ERP Features

- **Project management** - Manage projects from initiation to completion, assign team members, track progress, and monitor milestones
- **Invoicing & billing** - Generate invoices, manage payment statuses, track outstanding balances, and maintain financial records
- **Role-based access control** - Fine-grained permission system to control access to modules, actions, and sensitive data
- **Workflow automation** - Automate repetitive processes such as status changes, notifications, and task creation based on business rules
- **Reporting & dashboards** - Real-time insights into sales performance, revenue, pipeline health, and operational metrics

---

## How To Setup

Follow these steps to set up the project locally:

1. Clone the repository

```bash
git clone https://github.com/MattYeend/CRM.git
cd CRM
```

2. Install PHP dependencies

```bash
composer install
```

3. Install Node dependencies

```bash
npm install && npm run build
```

4. Set up environment

```bash
cp .env.example .env
php artisan key:generate
```

5. Add the following lines to your `.env` file, and put in your own variables:

```bash
STRIPE_KEY=your-stripe-key
STRIPE_SECRET=your-stripe-secret
STRIPE_WEBHOOK_SECRET=your-stripe-webhook-secret

CASHIER_CURRENCY=GBP
CASHIER_CURRENCY_LOCALE=en_GB
```

Note: Ensure your `User` model uses Laravel Cashier's `Billable` trait so Stripe customers and payments work correctly.

6. Configure your database in `.env` and run migrations:

```bash
php artisan migrate
```

7. Seed all tables if needed:

```bash
php artisan db:seed
```

8. Set up storage

```bash
php artisan storage:link
```

9. Run the development servers

```bash
php artisan serve
npm run dev
```

---

## How To Contribute

This project follows the standard Laravel OSS fork-and-pull-request workflow, used by most open-source Laravel packages and applications.

1. Fork the repository.
2. Create a new branch: `git checkout -b feature/your-feature-name`.
3. Make your changes and commit: `git commit -m '#issue-number Add your message here'`.
4. Run `php artisan insights` and make any relevant changes that it might suggest.
5. Ensure that the relevant language file(s) have been created.
6. Ensure there's relevant tests and that they work and pass.
7. If anything requires `vue.js` changes, run: `npm i && npm run build`.
8. Push to your fork: `git push origin feature/your-feature-name`.
9. Create a Pull Request.

Please follow the code style and commit message conventions.

---

### Commit Conventions

To keep the commit history clean and consistent, please follow these conventions:

```graphql
#issue-number Short, clear description in the imperative mood
```

Examples

```graphql
#42 Add customer export feature
#87 Fix validation for lead creation
#101 Refactor role permission checks
```

Guidelines:

- Reference an issue number where applicable
- Use the imperative mood ("Add", not "Added")
- Keep commits focused and descriptive
- Avoid bundling unrelated changes into a single commit
Maintainers may squash commits on merge.

---

### Maintainer Merge Strategy

For clarity and transparency:

- External contributors do not merge directly
- All changes enter the project via Pull Requests
- Pull Requests are reviewed before merging
- The preferred merge method is Squash and Merge
- Keeps `main` and `develop` history clean
- One commit per feature or fix
- Commit message may be edited by maintainers
The `main` and `develop` branches are protected and should never be pushed to directly.

---

## Information

### Core Sales Flow

The typical lifecycle in the CRM looks like:

```bash
Lead
   ↓
Deal (in a Pipeline Stage)
   ↓
Quote
   ↓
Order
   ↓
Invoice
```
Products can be attached to deals, quotes, and orders via pivot tables.

### Leads

`Lead`

Represents a potential customer before becoming a deal.

Typical links:

- May link to a Company
- May link to a Contact
- Can have:
    - Activities
    - Tasks
    - Notes
    - Attachments

These are attached using polymorphic relationships.

A lead can be converted into a Deal via the `convertToDeal()` method.

Built-in accessors:

```bash
$lead->full_name          # Concatenated first and last name
$lead->display_name       # Full name if available, otherwise email
$lead->contact_info       # Formatted string combining email and phone
$lead->age_in_days        # Days since the lead was created
$lead->is_stale           # bool — not updated in 30+ days
$lead->is_hot             # bool — updated within the last 7 days
$lead->is_eligible_for_conversion  # bool — contacted but not yet converted
$lead->is_high_priority   # bool — updated within 3 days with no contact
$lead->is_low_priority    # bool — no contact in 60+ days
```

Built-in scopes:

```bash
Lead::stale()                    # updated_at older than 30 days
Lead::hot()                      # updated_at within last 7 days
Lead::eligibleForConversion()    # contacted but not converted
Lead::highPriority()             # updated within 3 days, no contact activity
Lead::lowPriority()              # no contact in 60+ days
Lead::converted()                # has a conversion activity
Lead::unconverted()              # no conversion activity
Lead::contacted()                # has a contact activity
Lead::uncontacted()              # no contact activity
Lead::ownedBy($userId)           # filter by owner_id
Lead::assignedTo($userId)        # filter by assigned_to
Lead::fromSource($source)        # filter by source channel
Lead::real()                     # exclude test records
```

### Deals

`Deal`

Deals represent active sales opportunities.

Key relationships:

- `company_id → Company`
- `contact_id → Contact`
- `owner_id → User`
- `pipeline_id → Pipeline`
- `stage_id → PipelineStage`

A deal can have:

- Products (many-to-many via `deal_products`)
- Tasks (polymorphic)
- Notes (polymorphic)
- Attachments (polymorphic)
- Activities (polymorphic)

Example:

```bash
Deal
 ├─ belongsTo Company
 ├─ belongsTo Contact
 ├─ belongsTo User (owner)
 ├─ belongsTo Pipeline
 ├─ belongsTo PipelineStage
 ├─ belongsToMany Products
 ├─ morphMany Tasks
 ├─ morphMany Notes
 ├─ morphMany Attachments
 └─ morphMany Activities
```

Status constants: `open`, `won`, `lost`, `archived`

Built-in accessors:

```bash
$deal->is_open           # bool
$deal->is_won            # bool
$deal->is_lost           # bool
$deal->is_closed         # bool — won or lost
$deal->is_overdue        # bool — past close date and still open
$deal->formatted_value   # e.g. "5000.00"
```

Built-in scopes:

```bash
Deal::open()
Deal::won()
Deal::lost()
Deal::archived()
Deal::closed()
Deal::withStatus($status)
Deal::overdue()
Deal::ownedBy($userId)
Deal::forCompany($companyId)
Deal::forPipeline($pipelineId)
Deal::forStage($stageId)
Deal::inCurrency($currency)
Deal::real()
```

### Pipelines and Stages

Deals are organised inside pipelines.

#### Pipelines

Represents a sales pipeline.

Example:

```bash
Sales Pipeline
 ├─ Prospect
 ├─ Qualified
 ├─ Proposal
 ├─ Negotiation
 └─ Won
```

Built-in accessors:

```bash
$pipeline->is_default    # bool
$pipeline->stage_count   # int
$pipeline->deal_count    # int
```

Built-in scopes:

```bash
Pipeline::default()
Pipeline::withDeals()
Pipeline::withoutDeals()
Pipeline::real()
```

#### Pipeline Stages

Each stage belongs to a pipeline.

```bash
Pipeline
   └─ hasMany PipelineStages

Deal
   └─ belongsTo PipelineStage
```

Built-in accessors:

```bash
$stage->is_open      # bool — neither won nor lost
$stage->is_won       # bool
$stage->is_lost      # bool
$stage->deal_count   # int
```

Built-in scopes:

```bash
PipelineStage::won()
PipelineStage::lost()
PipelineStage::open()
PipelineStage::forPipeline($pipelineId)
PipelineStage::ordered()
PipelineStage::real()
```

### Quotes

`Quote`

Quotes represent proposed pricing before an order is placed.

Quotes can contain:

- Products (many-to-many via `quote_products`)
- Pricing data
- Associated company/contact
- Activities / tasks / notes / attachments

Typical flow:

```bash
Deal → Quote
Quote → Products
```

Built-in accessors:

```bash
$quote->is_sent              # bool — sent_at is populated
$quote->is_accepted          # bool — accepted_at is populated
$quote->formatted_subtotal   # e.g. "1200.00"
$quote->formatted_tax        # e.g. "240.00"
$quote->formatted_total      # e.g. "1440.00"
```

Built-in scopes:

```bash
Quote::sent()
Quote::unsent()
Quote::accepted()
Quote::pending()        # sent but not yet accepted
Quote::forDeal($dealId)
Quote::inCurrency($currency)
Quote::real()
```

### Orders

`Order`

Orders represent confirmed purchases.

Relationships:

- Products via `order_products
- Likely linked to:
    - Company
    - Contact
    - Quote

Example structure:

```bash
Order
 ├─ belongsTo Company
 ├─ belongsTo Contact
 └─ belongsToMany Products
```

Status constants: `pending`, `paid`, `failed`

Helper methods:

```bash
$order->getMarkAsPaid()      # marks as paid, sets paid_at
$order->getMarkAsFailed()    # marks as failed, sets paid_at
$order->getMarkAsPending()   # marks as pending, clears paid_at
```

Built-in scopes:

```bash
Order::pending()
Order::failed()
Order::notPaid()
Order::paid()
Order::search($term)
Order::real()
```

### Invoices

`Invoice`

Invoices represent billing documents.

Invoices contain `InvoiceItems`, which store line items.

```bash
Invoice
   └─ hasMany InvoiceItems
```

`InvoiceItem` includes

```bash
invoice_id
product_id
quantity
unit_price
line_total
```

Typical flow:
`Order → Invoice → InvoiceItems`

Status constants: `draft`, `sent`, `paid`, `overdue`, `cancelled`

Built-in accessors:

```bash
$invoice->is_overdue             # bool
$invoice->is_paid                # bool
$invoice->is_draft               # bool
$invoice->is_sent                # bool
$invoice->is_cancelled           # bool
$invoice->formatted_subtotal     # e.g. "1200.00"
$invoice->formatted_tax          # e.g. "240.00"
$invoice->formatted_total        # e.g. "1440.00"
```

Built-in scopes:

```bash
Invoice::draft()
Invoice::sent()
Invoice::paid()
Invoice::overdue()
Invoice::cancelled()
Invoice::withStatus($status)
Invoice::outstanding()
Invoice::forCompany($companyId)
Invoice::inCurrency($currency)
Invoice::dueBefore($date)
Invoice::real()
```

### Products

`Product`

Products represent goods or services that can be sold.

Products are attached to multiple entities:

```bash
Product
 ├─ belongsToMany Deals (deal_products)
 ├─ belongsToMany Quotes (quote_products)
 ├─ belongsToMany Orders (order_products)
 ├─ hasMany ProductStockMovements
 ├─ morphMany Activities
 ├─ morphMany Tasks
 ├─ morphMany Notes
 └─ morphMany Attachments
```

These pivot tables store:

```bash
quantity
price
total
timestamps
```

Status constants: `active`, `discontinued`, `pending`, `out_of_stock`

Built-in accessors:

```bash
$product->is_active          # bool
$product->is_discontinued    # bool
$product->is_low_stock       # bool — quantity at or below reorder point
$product->is_out_of_stock    # bool — quantity is zero
$product->formatted_price    # e.g. "19.99"
```

Built-in scopes:

```bash
Product::active()
Product::discontinued()
Product::pending()
Product::withStatus($status)
Product::lowStock()
Product::outOfStock()
Product::inCurrency($currency)
Product::real()
Product::search($term)
```

### Product Stock Movements

`ProductStockMovement`

Tracks individual stock movement events for a product.

Key relationships:

- `product_id → Product`
- `created_by → User`

Movement type constants: `in`, `out`, `adjustment`, `transfer`, `return`

Notable fields:

```bash
type
quantity
quantity_before
quantity_after
reference
notes
```

Built-in helpers:

```bash
$movement->getIsInbound()    # bool — type is 'in' or 'return'
$movement->getIsOutbound()   # bool — type is 'out'
```

Built-in scopes:

```bash
ProductStockMovement::ofType($type)
ProductStockMovement::inbound()
ProductStockMovement::outbound()
ProductStockMovement::forProduct($productId)
ProductStockMovement::real()
```
Example:
```bash
Product
 └─ hasMany ProductStockMovements
 ```

### Parts

`Part`
Parts represent physical components, materials, or stock items. They sit on the ERP/inventory side of the system and are linked to a `Product`, a `PartCategory`, and one or more `Suppliers`.
Key relationships:

- `product_id` → `Product` (the parent product this part belongs to)
- `category_id` → `PartCategory` (the part's classification)
- `supplier_id` → `Supplier` (the primary/default supplier)
- `suppliers` (many-to-many via `part_suppliers` — all suppliers that can provide this part)

A part also has:

Images (via `PartImage`)
A preferred supplier (filtered via the `part_suppliers` pivot where `is_preferred = true`)

Example:

```bash
Part
 ├─ belongsTo Product
 ├─ belongsTo PartCategory (as category)
 ├─ belongsTo Supplier (primarySupplier — via supplier_id)
 ├─ belongsToMany Suppliers (via part_suppliers)
 │    └─ pivot: supplier_sku, unit_cost, lead_time_days, is_preferred
 ├─ hasMany PartImages (images — ordered by sort_order)
 ├─ hasOne  PartImage (primaryImage — where is_primary = true)
 ├─ hasMany PartStockMovement (stockMovements)
 ├─ hasMany PartSerialNumber (serialNumbers)
 ├─ hasMany BillOfMaterial (billOfMaterials — as parent/assembly)
 └─ hasMany BillOfMaterial (usedInAssemblies — as component)
```

Notable fields:

```bash
sku         # Internal stock-keeping unit
part_number # Manufacturer or supplier part number
barcode
brand / manufacturer
type / status
unit_of_measure
height / width / length / weight / volume
colour / material
price / cost_price / currency
tax_rate / tax_code / discount_percentage
quantity
min_stock_level / max_stock_level
reorder_point / reorder_quantity # Reorder Point - Stock level that triggers reorder point, Reorder Quantity - Quantity to reorder
lead_time_days                   # Days to restock from the supplier
warehouse_location / bin_location
is_active
is_purchasable / is_sellable / is_manufactured
is_serialised / is_batch_tracked
```

Part type constants: `raw_material`, `finished_good`, `consumable`, `spare_part`, `sub_assembly`

Part status constants: `active`, `discontinued`, `pending`, `out_of_stock`

Built-in scopes:

```bash
Part::active()
Part::lowStock()
Part::outOfStock()
Part::ofType($type)
Part::ofStatus($status)
Part::purchasable()
Part::sellable()
Part::manufactured()
Part::serialised()
Part::batchTracked()
Part::real()
Part::search($term)
```

Built-in helpers:

```bash
$part->getIsLowStock()        # bool
$part->getIsOutOfStock()      # bool
$part->getMarginPercentage()  # float|null - (price - cost_price) / price * 100
$part->getBomCost()           # float|null - recursive BOM cost
$part->hasBom().              # bool
```

### Part Images

`PartImage`
Part images store one or more images associated with a part.

Key relationships:

- `part_id → Part`

```bash
PartImage
   └─ belongsTo Part
```

Notable fields:

```bash
part_id
image        # File path or storage URL of the image
alt          # Alt text for accessibility
is_primary   # Boolean — flags the main display image
sort_order   # Controls display order (parts are ordered by this)
```

Enforced constraint: only one image per part may have `is_primary = true`. When a new primary image is saved, all other images for that part are automatically set to `is_primary = false` via a model `saving` event.

Built-in accessors:

```bash
$image->image_url                # Full storage URL of the image
$image->thumbnail_url            # URL of the thumbnail version
$image->thumbnail_or_image_url   # Thumbnail URL with fallback to main image
```

Built-in scopes:

```bash
PartImage::primary()    # where is_primary = true
PartImage::real()       # exclude test records
```

From the Part model's perspective:

```bash
Part
 ├─ hasMany PartImages (images — ordered by sort_order)
 └─ hasMany PartImages (primaryImage — where is_primary = true)
```

### Part Categories

`PartCategory`
Part categories allow parts to be organised into a hierarchical taxonomy. Categories can be nested — a category can have a parent category and many child categories.

Key relationships:

- `parent_id → PartCategory` (self-referential — the parent category)
- `children → PartCategory` (self-referential — subcategories)
- `parts → Part`

```bash
PartCategory
 ├─ belongsTo PartCategory (parent)
 ├─ hasMany PartCategories (children)
 └─ hasMany Parts
```

Notable fields:

```bash
parent_id    # Nullable — null means a top-level category
name
slug         # Auto-generated from name on create; regenerated on name change
description
```

The `slug` is automatically generated from name using `Str::slug()` via model boot events. If the name is updated, the slug regenerates automatically.

Built-in accessors:

```bash
$category->slug        # URL-friendly slug generated from name
$category->full_path   # e.g. "Electronics > Connectors > PCB Connectors"
```

Built-in utility methods:

```bash
$category->ancestors()   # Collection of parent categories from top-most down
```

Built-in scopes:

```bash
PartCategory::withName($name)
PartCategory::real()
```

Example hierarchy:

```bash
Electronics                  # parent_id = null (top-level)
 ├─ Resistors                # parent_id = Electronics.id
 ├─ Capacitors               # parent_id = Electronics.id
 └─ Connectors               # parent_id = Electronics.id
      ├─ PCB Connectors      # parent_id = Connectors.id
      └─ Panel Mount         # parent_id = Connectors.id
```

### Part Serial Numbers

`PartSerialNumber`

Tracks uniquely identifiable serialised instances of a part.

Key relationships:

- `part_id → Part`

Status constants: `in_stock`, `sold`, `returned`, `scrapped`

Notable fields:

```bash
serial_number
status
batch_number
manufactured_at
expires_at
```

Built-in helpers:

```bash
$serial->getIsExpired()              # bool — expiry date has passed
$serial->getIsExpiringSoon($days)    # bool — expiry within $days (default 30)
```

Built-in scopes:

```bash
PartSerialNumber::inStock()
PartSerialNumber::expiringSoon($days)
PartSerialNumber::forPart($partId)
PartSerialNumber::real()
```

### Part Stock Movements

`PartStockMovement`

Tracks individual stock movement events for a part.

Key relationships:

- `part_id → Part`
- `created_by → User`

Movement type constants: `in`, `out`, `adjustment`, `transfer`, `return`

Notable fields:

```bash
type
quantity
quantity_before
quantity_after
reference
notes
```

Built-in helpers:

```bash
$movement->getIsInbound()    # bool — type is 'in' or 'return'
$movement->getIsOutbound()   # bool — type is 'out'
```

Built-in scopes:

```bash
PartStockMovement::ofType($type)
PartStockMovement::inbound()
PartStockMovement::outbound()
PartStockMovement::forPart($partId)
PartStockMovement::real()
```

### Suppliers

`Supplier`

Suppliers represent external companies or individuals that provide parts. They hold full contact, address, and commercial details.

Key relationships:

- `parts` (many-to-many via `part_suppliers`)
- `partSuppliers` (direct access to pivot records via `PartSupplier`)

```bash
Supplier
 ├─ belongsToMany Parts (via part_suppliers)
 │    └─ pivot: supplier_sku, unit_cost, lead_time_days, is_preferred
 └─ hasMany PartSuppliers
```

Notable fields:

```bash
name / code
email / phone / website
address_line_1 / address_line_2 / city / county / postcode / country
currency
payment_terms
tax_number
contact_name / contact_email / contact_phone
is_active
notes
```

Built-in accessors:

```bash
$supplier->full_address    # Combined address as a single formatted string
$supplier->website_host    # Bare domain without scheme or www
```

Built-in scopes:

```bash
Supplier::active()
Supplier::inactive()
Supplier::inCountry($country)
Supplier::inCurrency($currency)
Supplier::real()
Supplier::search($term)
```

The name attribute is filtered through the `HasTestPrefix` trait — test suppliers are automatically prefixed when `is_test = true`.

### Part Suppliers (Pivot)

`PartSupplier`
The `part_suppliers` pivot table links parts to suppliers and stores supplier-specific pricing and ordering information for each part-supplier combination. It extends Laravel's `Pivot` class rather than a standard `Model`.

```bash
part_suppliers
 ├─ part_id
 ├─ supplier_id
 ├─ supplier_sku       # The supplier's own SKU/reference for this part
 ├─ unit_cost          # Cost from this supplier (decimal:2)
 ├─ lead_time_days     # Delivery lead time from this supplier
 └─ is_preferred       # Boolean — marks this as the preferred supplier for the part
```

Built-in accessors:

```bash
$pivot->formatted_unit_cost          # e.g. "12.50"
$pivot->getTotalCostFor($quantity)   # float — quantity * unit_cost
```

Built-in scopes:

```bash
PartSupplier::preferred()
PartSupplier::forPart($partId)
PartSupplier::forSupplier($supplierId)
PartSupplier::real()
```

The `PartSupplier` pivot is used explicitly when marking preferred suppliers:

```bash
Part::preferredSupplier()
   └─ belongsToMany Suppliers via part_suppliers
        └─ wherePivot('is_preferred', true)
```

Relationships on the pivot:

```bash
PartSupplier
 ├─ belongsTo Part
 └─ belongsTo Supplier
```

A part may have one primary supplier (set directly via `supplier_id` on the `parts` table) and many additional suppliers via the pivot, of which one can be flagged as `is_preferred`.
Full supplier relationship summary for a part:

```bash
Part
 ├─ primarySupplier()    → belongsTo Supplier (via supplier_id — quick default)
 ├─ suppliers()          → belongsToMany Supplier (all via part_suppliers pivot)
 └─ preferredSupplier()  → belongsToMany Supplier (pivot where is_preferred = true)
```

### Bill Of Materials (BOM)

`BillOfMaterials`

Defines how parts are assembled - i.e. which components (child parts) are required to build a parent part.

Core Concepts

- Parent Part → the assembly (finished or intermediate)
- Child Part → the component used in that assembly
- Quantity → how much of the child is required

Relationships
From `Part` model

```php
// Components required to build this part
public function billOfMaterials(): HasMany
{
    return $this->hasMany(BillOfMaterial::class, 'parent_part_id');
}

// Assemblies where this part is used
public function usedInAssemblies(): HasMany
{
    return $this->hasMany(BillOfMaterial::class, 'child_part_id');
}
```

From `BillOfMaterial` model

```bash
BillOfMaterial
 ├─ belongsTo Part (parentPart)
 ├─ belongsTo Part (childPart)
 ├─ belongsTo User (creator)
 ├─ belongsTo User (updater)
 ├─ belongsTo User (deleter)
 └─ belongsTo User (restorer)
```

Structure

```bash
Part (Parent)
 └─ hasMany BillOfMaterial
      ├─ child_part_id → Part (component)
      └─ quantity

Part (Child)
 └─ hasMany BillOfMaterial (usedInAssemblies)
      └─ parent_part_id → Part (assembly)
```

Fields

```bash
parent_part_id      # Assembly
child_part_id       # Component
quantity            # Decimal (4dp precision)
scrap_percentage    # Decimal (2dp) — added to effective quantity calculation
unit_of_measure
notes

# System
meta (json)
is_test

# Audit
created_by / updated_by / deleted_by / restored_by
timestamps...
soft deletes...
```

Built-in helpers:

```bash
$bom->effectiveQuantity()   # quantity adjusted for scrap percentage
$bom->lineCost()            # direct cost for this BOM line
$bom->totalCost()           # recursive cost including sub-assemblies
```

Built-in scopes:

```bash
BillOfMaterial::forParentPart($partId)
BillOfMaterial::forChildPart($partId)
BillOfMaterial::testEntries()
BillOfMaterial::real()
```

Example

```bash
Bike (Parent Part)
 ├─ Wheel (x2)
 ├─ Frame (x1)
 └─ Chain (x1)
```

Stored as

```bash
parent_part_id = Bike
child_part_id  = Wheel
quantity       = 2

parent_part_id = Bike
child_part_id  = Frame
quantity       = 1
```

### Activities

`Activity`

Activities are event logs or interactions related to a record.

They are polymorphic using:

```bash
subject_type
subject_id
```

Meaning activities can belong to:

- Leads
- Deals
- Quotes
- Orders
- Companies
- Contacts

Example:

```bash
Activity
   └─ morphTo subject
```

Examples of activities:

- call made
- email sent
- meeting held
- deal stage changed
- note added

Subject type constants:

```bash
Activity::ACTIVITY_COMPANY   # Company::class
Activity::ACTIVITY_DEAL      # Deal::class
Activity::ACTIVITY_TASK      # Task::class
Activity::ACTIVITY_USER      # User::class
```

Built-in scopes:

```bash
Activity::assignedTo($userId)
Activity::forSubjectType($type)
Activity::forSubject($type, $id)
Activity::real()
```

### Tasks

`Task`

Tasks are actionable to-dos assigned to users.

Tasks are also polymorphic:

```bash
taskable_type
taskable_id
```

Meaning tasks can belong to:

- Deals
- Leads
- Contacts
- Companies

Example:

```bash
Task
   └─ morphTo taskable
```

Status constants: `pending`, `completed`, `cancelled`

Priority constants: `low`, `medium`, `high`

Built-in accessors:

```bash
$task->is_overdue      # bool — past due date and not completed/cancelled
$task->is_pending      # bool
$task->is_completed    # bool
$task->is_cancelled    # bool
```

Built-in scopes:

```bash
Task::status($status)
Task::priority($priority)
Task::pending()
Task::completed()
Task::cancelled()
Task::overdue()
Task::dueBefore($date)
Task::dueAfter($date)
Task::assignedTo($userId)
Task::real()
Task::searchTitle($term)
```

### Attachments

`Attachment`

Attachments allow files to be uploaded to many entities.

They use a polymorphic relationship:

```bash
attachable_type
attachable_id
```

So attachments can belong to:

- Deals
- Leads
- Contacts
- Companies
- Quotes
- Orders

Example:

```bash
Attachment
   └─ morphTo attachable
```

Attachable type constants:

```bash
Attachment::ATTACHABLE_COMPANY
Attachment::ATTACHABLE_DEAL
Attachment::ATTACHABLE_TASK
Attachment::ATTACHABLE_USER
```

Built-in accessors:

```bash
$attachment->size_formatted              # e.g. "1.25 MB"
$attachment->mime_type                   # e.g. "application/pdf"
$attachment->filename_without_extension  # filename without extension
$attachment->file_extension              # e.g. "pdf"
$attachment->download_url                # authenticated download URL
```

Built-in scopes:

```bash
Attachment::real()
Attachment::attachableType($type)
Attachment::attachableId($id)
```

When an attachment is deleted, the underlying file is automatically removed from storage via the `deleting` model event.

### Notes

`Note`

Notes are internal comments.

They use a polymorphic relationship:

```bash
notable_type
notable_id
```

So notes can be attached to:

- Deals
- Leads
- Contacts
- Companies

Notable type constants:

```bash
Note::NOTABLE_COMPANY
Note::NOTABLE_DEAL
Note::NOTABLE_TASK
Note::NOTABLE_USER
```

Built-in scopes:

```bash
Note::ofNotableType($type)    # filter by class basename e.g. "Company"
Note::forNotable($model)      # filter by a specific model instance
Note::real()
```

### Users, Roles, and Permissions

#### Users

`User`

Users represent system accounts.

Many records reference users for ownership and auditing.

Examples:

```bash
owner_id
created_by
updated_by
deleted_by
restored_by
```

Example from the `Deal` model:

```bash
Deal
 ├─ owner() → User
 ├─ creator() → User
 ├─ updater() → User
 ├─ deleter() → User
 └─ restorer() → User
```

Built-in helper methods:

```bash
$user->isSuperAdmin()               # bool
$user->isAdmin()                    # bool
$user->isUser()                     # bool
$user->hasRole($roleIdOrName)       # bool
$user->permissions()                # Collection of permission names
$user->getAllPermissions()          # array — cached for 60 minutes
$user->hasPermission($permission)   # bool
$user->clearPermissionCache()       # clears cached permissions
```

Built-in scopes:

```bash
User::admins()
User::superAdmins()
User::standardUsers()
User::real()
```

#### Roles

`Role`

Roles group permissions.

Example roles:

- User
- Admin
- Super Admin

Each User belongs to a single Role.

```bash
User
   └─ belongsTo Role
```

This means:

- One role can be assigned to many users
- Each user can have only one role

```bash
Role
   └─ hasMany Users
```

Role ID constants:

```bash
Role::ROLE_USER         # 1
Role::ROLE_ADMIN        # 2
Role::ROLE_SUPER_ADMIN  # 3
```

Built-in accessors:

```bash
$role->is_admin        # bool
$role->is_super_admin  # bool
$role->user_count      # int
```

Built-in scopes:

```bash
Role::admins()
Role::forUser($userId)
Role::withPermission($permissionName)
```

#### Permissions

`Permission`

Permissions define system capabilities.

Examples:

- create_deal
- edit_invoice
- delete_product

Permissions are assigned to roles.
`Role → belongsToMany Permissions`

This creates a structure like:

```bash
User
   ↓
Roles
   ↓
Permissions
```

Built-in helper methods:

```bash
$permission->hasRole($roleName)          # bool
$permission->hasAnyRole($roleNames)      # bool
$permission->hasAllRoles($roleNames)     # bool
```

Built-in accessors:

```bash
$permission->is_assigned    # bool — assigned to at least one role
$permission->role_count     # int
```

Built-in scopes:

```bash
Permission::assigned()
Permission::unassigned()
Permission::forRole($roleId)
Permission::search($term)
```

### Companies

`Company`

Companies represent organisations within the CRM.

Key relationships:

- `industry_id → Industry`

A company can have:

- Deals (one-to-many)
- Invoices (one-to-many)
- Activities (polymorphic)
- Tasks (polymorphic)
- Notes (polymorphic)
- Attachments (polymorphic)

Example:

```bash
Company
 ├─ belongsTo Industry
 ├─ hasMany Deals
 ├─ hasMany Invoices
 ├─ morphMany Activities
 ├─ morphMany Tasks
 ├─ morphMany Notes
 └─ morphMany Attachments
```

Built-in accessors:

```bash
$company->contact_full_name          # Combined first and last name of the primary contact
$company->full_address               # Full postal address as a single formatted string
$company->has_deals                  # bool — true if the company has any open deals
$company->has_outstanding_invoices   # bool — true if any invoices are unpaid
$company->website_host               # Host portion of the website URL without scheme or trailing slash
```

Built-in scopes:

```bash
Company::real()
Company::inIndustry($industryId)
Company::inCountry($country)
Company::withDeals()
Company::withoutDeals()
Company::withOutstandingInvoices()
```

### Industries

`Industry`

Industries are used to classify and group companies within the CRM.

Key relationships:

- `companies → Company` (one-to-many)

```bash
Industry
 └─ hasMany Companies
```

Notable fields:

```bash
name
slug    # Auto-generated from name on create; regenerated on name change
```

The `slug` is automatically generated from the name using `Str::slug()` via model boot events. If the name is updated, the slug regenerates automatically.

Example:

```bash
Industry
 ├─ Technology
 │    └─ slug: technology
 ├─ Manufacturing
 │    └─ slug: manufacturing
 └─ Healthcare
      └─ slug: healthcare
```

Audit relationships:

```bash
Industry
 ├─ belongsTo User (creator)
 ├─ belongsTo User (updater)
 ├─ belongsTo User (deleter)
 └─ belongsTo User (restorer)
```

### Job Titles

`JobTitle`

Job titles represent the position or role of a user within their organisation.

Key relationships:

- `users → User` (one-to-many)

```bash
JobTitle
 └─ hasMany Users
```

Notable fields:

```bash
title
short_code
group
```

Title group constants:

```bash
JobTitle::GROUP_C_SUITE      # CEO, CTO, CFO, COO, etc.
JobTitle::GROUP_EXECUTIVE    # President, VP, EVP, SVP, MD, Director, etc.
JobTitle::GROUP_DIRS         # MD, Director, Technical Director, Sales Director, etc.
```

Built-in accessors:

```bash
$jobTitle->is_csuite      # bool — title is in GROUP_C_SUITE
$jobTitle->is_executive   # bool — title is in GROUP_EXECUTIVE
$jobTitle->is_director    # bool — title is in GROUP_DIRS
$jobTitle->user_count     # int
```

Built-in scopes:

```bash
JobTitle::csuite()
JobTitle::executive()
JobTitle::directors()
JobTitle::inGroup($group)
JobTitle::real()
JobTitle::search($term)
```

### Learnings

`Learning`

Learnings represent training or educational resources that can be assigned to users.

Key relationships:

- `questions → LearningQuestion` (one-to-many)
- `users → User` (many-to-many via `LearningUser` pivot)

```bash
Learning
 ├─ hasMany LearningQuestions
 └─ belongsToMany Users (via LearningUser pivot)
      └─ pivot: is_complete, completed_at, score, is_test, meta
```

Each `LearningQuestion` has many `LearningAnswer` options, one or more of which may be marked correct.

Completion status constants:
```bash
Learning::COMPLETE
Learning::INCOMPLETE
```

Built-in scopes:
```bash
Learning::forUser($userId)
Learning::completedForUser($userId)
Learning::incompleteForUser($userId)
Learning::real()
```

The `LearningUser` pivot tracks per-user completion state and exposes its own scopes:
```bash
LearningUser::completedBetween($start, $end)
LearningUser::completedForUser($userId)
LearningUser::incompleteForUser($userId)
LearningUser::forUser($userId)
LearningUser::realForUser($userId)
```

### Logs

`Log`

The `Log` model provides a centralised audit trail for user actions and system events throughout the application.

Key relationships:

- `loggedInUser → User` (the user who performed the action)
- `relatedToUser → User` (the user related to the action, if applicable)

Notable fields:

```bash
action_id           # Integer constant identifying the action type
data                # JSON array of additional context
logged_in_user_id   # The acting user
related_to_user_id  # The affected user (optional)
```

Logging a new entry:

```php
Log::log(Log::ACTION_CREATE_USER, ['new_user_id' => $user->id], Auth::id(), $user->id);
```

Action constant groups include: Login/Logout, User Management, MFA/Settings, Role/Permission Management, Errors/Cache, Activities, Attachments, Companies, Deals, Invoices, Invoice Items, Job Titles, Leads, Learnings, Notes, Orders, Permissions, Pipelines, Pipeline Stages, Products, Quotes, Roles, Tasks, Parts, Suppliers, Part Categories, Part Images, Part Stock Movements, Part Serial Numbers, Bill Of Materials, and Company Industries.

Built-in scopes:

```bash
Log::ofAction($action)   # filter by action_id constant
```

### Are Activities and Tasks the Same?

No - they serve different purposes.

**Activities**

Activities represent things that happened.

Examples:

- Call logged
- Meeting recorded
- Email sent
- Deal moved stage
- They are historical records.

**Tasks**

Tasks represent things that need to be done.

Examples:

- Call customer tomorrow
- Send proposal
- Follow up next week

### Relationship Overview Diagram

```bash
User
 ├─ belongsTo Role
 │    └─ belongsToMany Permissions
 ├─ belongsTo JobTitle
 └─ belongsToMany Learnings (via LearningUser)

Industry
 └─ hasMany Companies

Company
 ├─ belongsTo Industry
 ├─ hasMany Deals
 ├─ hasMany Invoices
 ├─ morphMany Activities
 ├─ morphMany Tasks
 ├─ morphMany Notes
 └─ morphMany Attachments

Lead
 └─ Activities / Tasks / Notes / Attachments

Deal
 ├─ Company
 ├─ Owner (User)
 ├─ Pipeline
 ├─ PipelineStage
 ├─ Products (via deal_products)
 ├─ Activities
 ├─ Tasks
 ├─ Notes
 └─ Attachments

Quote
 └─ Products (via quote_products)

Order
 └─ Products (via order_products)

Invoice
 └─ InvoiceItems

PartCategory
 ├─ parent PartCategory (self-referential)
 └─ children PartCategories (self-referential)

Supplier
 └─ belongsToMany Parts (via part_suppliers)

Part
 ├─ belongsTo Product
 ├─ belongsTo PartCategory
 ├─ belongsTo Supplier (primarySupplier)
 ├─ belongsToMany Suppliers (via part_suppliers)
 │    └─ PartSupplier pivot: supplier_sku, unit_cost, lead_time_days, is_preferred
 ├─ hasMany PartImages
 ├─ hasMany PartStockMovements
 ├─ hasMany PartSerialNumbers
 ├─ hasMany BillOfMaterial (as parent)
 └─ hasMany BillOfMaterial (as child/component)

 ProductStockMovement
 ├─ belongsTo Product
 └─ belongsTo User (createdBy)

 PartStockMovement
 ├─ belongsTo Part
 └─ belongsTo User (createdBy)

 Product
 ├─ belongsToMany Deals (via deal_products pivot)
 ├─ belongsToMany Quotes (via quote_products pivot)
 ├─ belongsToMany Orders (via order_products pivot)
 ├─ hasMany InvoiceItems
 ├─ hasMany ProductStockMovements
 ├─ morphMany Activities
 ├─ morphMany Tasks
 ├─ morphMany Notes
 ├─ morphMany Attachments
 ├─ belongsTo User (creator)
 ├─ belongsTo User (updater)
 ├─ belongsTo User (deleter)
 └─ belongsTo User (restorer)

 Product
 └─ hasMany ProductStockMovements
      ├─ type (in, out, adjustment, transfer, return)
      ├─ quantity_before / quantity_after
      └─ created_by → User

 Product
 ├─ Deal → deal_products (quantity, price, total)
 ├─ Quote → quote_products (quantity, price, total, meta)
 ├─ Order → order_products (quantity, price, total, meta)
 └─ Invoice → InvoiceItem (quantity, unit_price, line_total)

Note
 ├─ morphTo Notable (Company, Deal, Task, User)
 ├─ belongsTo User (user_id optional)
 ├─ belongsTo User (created_by)
 ├─ belongsTo User (updated_by)
 ├─ belongsTo User (deleted_by)
 ├─ belongsTo User (restored_by)
 ├─ morphMany Attachments
 ├─ morphMany Activities
 ├─ morphMany Tasks
 └─ belongsToMany Users (via NoteUser pivot)

 NoteUser (pivot)
 ├─ belongsTo Note
 ├─ belongsTo User
 ├─ belongsTo User (created_by)
 ├─ belongsTo User (updated_by)
 ├─ belongsTo User (deleted_by)
 └─ belongsTo User (restored_by)

Task
 ├─ morphTo Taskable (Company, Deal, Task, User)
 ├─ belongsTo User (assigned_to)
 ├─ belongsTo User (created_by)
 ├─ belongsTo User (updated_by)
 ├─ belongsTo User (deleted_by)
 ├─ belongsTo User (restored_by)
 ├─ morphMany Activities
 ├─ morphMany Attachments
 └─ morphMany Notes

  Learning
  ├─ hasMany Questions
  ├─ belongsToMany Users (via LearningUser)
  ├─ morphMany Activities
  ├─ morphMany Tasks
  ├─ morphMany Notes
  └─ morphMany Attachments

 LearningQuestion
  ├─ belongsTo Learning
  └─ hasMany Answers

 LearningAnswer
  └─ belongsTo Question

 LearningUser (pivot)
  ├─ belongsTo Learning
  └─ belongsTo User
```

---

## General CLI Commands

| Command | Description |
| --- | --- |
| `php artisan make:model ModelName -mcr` | Create a model, migration, and resource controller |
| `php artisan make:model ModelName -a` or `php artisan make:model ModelName --all` | Create a model, migration, factory, seeder, controller, resource, request(s) |
| `php artisan make:model ModelName` | Create a model |
| `php artisan make:controller ControllerName` | Create a controller |
| `php artisan make:controller ControllerName --resource` | Create a resource controller |
| `php artisan make:migration migration_name_table` | Create a migration |
| `php artisan make:seeder SeederName` | Create a seeder |
| `php artisan make:factory FactoryName` | Create a factory |
| `php artisan make:request RequestName` | Creates a form request for validation |
| `php artisan make:event EventName` | Creates an event class |
| `php artisan make:listener ListenerName` | Creates a listener class |
| `php artisan make:job JobName` | Creates a queued job |
| `php artisan make:rule RuleName` | Create a new rule |
| `php artisan make:test TestName` | Create a new test |
| `php artisan queue:work` | Starts the queue worker to process queued jobs (e.g. emails, notifications) |

For further CLI commands, visit <a href="https://artisan.page/">here</a>

---

## Specific CLI Commands

| Command | Description |
| --- | --- |
| `php artisan make:service ServiceName` | Creates a new service class |
| `php artisan make:class NameRegistry` |  Creates a custom class (e.g. registry/helper/service class such as a NameRegistry) |
| `php artisan permission:clear` | Clear permissions if changed |
| `php artisan insights` | Run insights package |

---

## Events And Listeners

The system uses Laravel Events & Listeners to handle asynchronous workflows.

Event listeners are registered in `AppServiceProvider::boot()` using `Event::listen()`.

| Event | Listener | Description |
| --- | --- | --- |
| `Illuminate\Auth\Events\Registered` | `App\Listeners\SendWelcomeEmail` | Sends a welcome email to newly created users including their login credentials |
| `cashier.payment_succeeded` | Closure in `AppServiceProvider` | Marks the associated order as paid when a Stripe payment succeeds |

---

## Sponsor The Project

If you find this project useful, consider sponsoring it to support future development and maintenance.<br>
<a href="https://www.buymeacoffee.com/mattyeend">☕ Buy Me a Coffee</a><br>
<a href="https://github.com/sponsors/MattYeend">💸 Personal GitHub Sponsor</a><br>
<a href="https://github.com/sponsors/MatthewYeend">🏢 Company Github Sponsor</a>