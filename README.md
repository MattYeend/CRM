# CRM System
A Laravel 12 CRM system

<!-- TOC -->
## Table of Contents
1. [Tech Stack](#tech-stack)
2. [General Information](#general-information)
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
            2. [PipelineStages](#pipelineStages)
        5. [Quotes](#quotes)
        6. [Orders](#orders)
        7. [Invoices](#invoices)
        8. [Products](#products)
        9. [Parts](#parts)
        10. [Part Images](#part-images)
        11. [Part Categories](#part-categories)
        12. [Suppliers](#suppliers)
        13. [Part Suppliers (Pivot)](#part-suppliers-pivot)
        14. [Bill Of Materials (BOM)](#bill-of-materials-bom)
        15. [Activities](#activities)
        16. [Tasks](#tasks)
        17. [Attachments](#attachments)
        18. [Notes](#notes)
        19. [Users, Roles, and Permissions](#users-roles-and-permissions)
            1. [Users](#users)
            2. [Roles](#roles)
            3. [Permissions](#permissions)
        20. [Are Activities and Tasks the Same?](#are-activities-and-tasks-the-same)
        21. [Relationship Overview Diagram](#relationship-overview-diagram)
3. [How To Setup](#how-to-setup)
4. [How To Contribute](#how-to-contribute)
    1. [Commit Conventions](#commit-conventions)
    2. [Maintainer Merge Strategy](#maintainer-merge-strategy)
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

#### PipelineStages

Each stage belongs to a pipeline.
```bash
Pipeline
   └─ hasMany PipelineStages

Deal
   └─ belongsTo PipelineStage
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

### Orders
`Order`

Orders represent confirmed purchases.

Relationships:
- Products via `order_products`
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
price
total
```

Typical flow:
`Order → Invoice → InvoiceItems`

### Products
`Product`

Products represent goods or services that can be sold.

Products are attached to multiple entities:
```bash
Product
 ├─ belongsToMany Deals (deal_products)
 ├─ belongsToMany Quotes (quote_products)
 └─ belongsToMany Orders (order_products)
```

These pivot tables store:
```bash
quantity
price
total
timestamps
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
reorder_point / reorder_quantity # Reorder Point - Stock level that triggers reorder point, Reorder Quantity - Quantinty to reorder
lead_time_days                   # Days to restock from the supplier
warehouse_location / bin_location
is_active
is_purchasable / is_sellable / is_manufactured
is_serialised / is_batch_tracked
```

Built-in scopes:
```bash
Part::active()       # where is_active = true
Part::lowStock()     # where quantity <= reorder_point
Part::outOfStock()   # where quantity = 0
```

Built-in helpers:
```bash
$part->isLowStock()        # bool
$part->isOutOfStock()      # bool
$part->marginPercentage()  # float|null — (price - cost_price) / price * 100
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
path         # File path or storage URL of the image
alt          # Alt text for accessibility
is_primary   # Boolean — flags the main display image
sort_order   # Controls display order (parts are ordered by this)
```
Enforced constraint: only one image per part may have `is_primary = true`. When a new primary image is saved, all other images for that part are automatically set to `is_primary = false` via a model `saving` event.

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
Example hierarchy:
```bash
Electronics                  # parent_id = null (top-level)
 ├─ Resistors                # parent_id = Electronics.id
 ├─ Capacitors               # parent_id = Electronics.id
 └─ Connectors               # parent_id = Electronics.id
      ├─ PCB Connectors      # parent_id = Connectors.id
      └─ Panel Mount         # parent_id = Connectors.id
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

Built-in scope:
```bash
Supplier::active()   # where is_active = true
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
parent_part_id   # Assembly
child_part_id    # Component
quantity         # Decimal (4dp precision)
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
 └─ Roles
      └─ Permissions

Company
 └─ Contacts

Lead
 └─ Activities / Tasks / Notes / Attachments

Deal
 ├─ Company
 ├─ Contact
 ├─ Owner (User)
 ├─ Pipeline
 ├─ PipelineStage
 ├─ Products
 ├─ Activities
 ├─ Tasks
 ├─ Notes
 └─ Attachments

Quote
 └─ Products

Order
 └─ Products

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
 └─ hasMany PartImages
```

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
- Use the imperative mood (“Add”, not “Added”)
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
| `php artisan make:rule RuleName` | Createa a new rule |
| `php artisan make:test TestName` | Create a new test |

For further CLI commands, visit <a href="https://artisan.page/">here</a>
--- 

## Specific CLI Commands

| Command | Description | 
| --- | --- |
| `php artisan make:service ServiceName` | Creates a new service class |
| `php artisan permission:clear` | Clear permissions if changed |
| `php artisan insights` | Run insights package | 

---

## Events And Listeners

The system uses Laravel Events & Listeners to handle asynchronous workflows.

Example: `CashierPaymentSucceededListener` marks orders as paid automatically when a Stripe payment succeeds.

---

## Sponsor The Project
If you find this project useful, consider sponsoring it to support future development and maintenance.<br>
<a href="https://www.buymeacoffee.com/mattyeend">☕ Buy Me a Coffee</a><br>
<a href="https://github.com/sponsors/MattYeend">💸 Personal GitHub Sponsor</a><br>
<a href="https://github.com/sponsors/MatthewYeend">🏢 Company Github Sponsor</a>
