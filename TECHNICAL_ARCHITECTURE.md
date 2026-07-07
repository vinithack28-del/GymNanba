# GymNanba - Technical Architecture & Implementation Guide

## 📐 System Architecture Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                        GYMNAMBA PLATFORM                         │
└─────────────────────────────────────────────────────────────────┘
                               │
                ┌──────────────┼──────────────┐
                │              │              │
        ┌───────▼────────┐ ┌──▼──────────┐ ┌─▼──────────┐
        │   SUPER ADMIN  │ │   TENANT 1  │ │  TENANT N  │
        │    PORTAL      │ │   (GYM A)   │ │  (GYM Z)   │
        │ admin.gymos.in │ │ gyma.gymos. │ │ gymz.gymos │
        │                │ │    in       │ │   .in      │
        │ Shared DB      │ │ Isolated DB │ │ Isolated DB│
        └────────────────┘ └─────────────┘ └────────────┘
```

---

## 🎯 Module Breakdown by Controllers

### Authentication Module (`app/Http/Controllers/Auth/`)

#### `AuthenticatedSessionController`
- **Methods**:
  - `create()`: Display login form
  - `store()`: Process login (email + password validation, 2FA)
  - `destroy()`: Logout user

- **Responsibilities**:
  - Rate limiting per IP (5 attempts/15 minutes)
  - Account lockout after 5 failed attempts
  - Generic error messages (prevent email enumeration)
  - Session creation
  - 2FA verification

#### `PasswordChangeController`
- **Methods**:
  - Change password
  - Password history validation
  - Enforce password change on first login

- **Responsibilities**:
  - Password strength validation
  - Bcrypt hashing
  - Session invalidation on password change

---

### Admin Portal Controllers (`app/Http/Controllers/Admin/`)

#### `DashboardController`
- **Methods**: `index()`
- **Displays**:
  - Active tenant count
  - Monthly Recurring Revenue (MRR)
  - Upcoming renewals
  - Active subscriptions
  - Failed payments
  - Revenue trends
  - Tenant list with statuses

#### `TenantController`
- **Methods**:
  - `index()`: List all tenants with search/filter
  - `create()`: Show tenant creation wizard
  - `store()`: Create new tenant + DB setup
  - `show()`: View tenant details
  - `edit()`: Edit tenant information
  - `update()`: Update tenant data
  - `deletePage()`: Confirmation page
  - `destroy()`: Delete/archive tenant

- **Operations**:
  - Subdomain management
  - Database creation/assignment
  - Multi-step wizard processing
  - Tenant status management
  - Owner user creation
  - Audit logging

#### `PlanController`
- **Methods**:
  - `index()`: List all plans
  - `store()`: Create new plan
  - `update()`: Modify plan
  - `destroy()`: Delete plan

- **Plan Configuration**:
  - Duration type (month/year/days)
  - Price and GST
  - Session limits
  - Freeze settings
  - Inclusions and tags
  - Capacity management

#### `SubscriptionController`
- **Methods**: `index()`
- **Shows**:
  - Tenant-plan assignments
  - Trial status
  - Active/expired subscriptions
  - Renewal dates
  - Billing information

#### `AdminInvoiceController`
- **Methods**:
  - `index()`: List invoices
  - `storeRenewal()`: Generate renewal invoice
  - `storePartPayment()`: Record partial payment
  - `storePayment()`: Record full payment

- **Operations**:
  - Invoice generation
  - Payment collection
  - Renewal processing
  - Payment reconciliation

#### `AuditLogController`
- **Methods**: `index()`
- **Shows**:
  - All admin actions
  - Immutable event trail
  - Search and filter capability
  - Action history with timestamps
  - User and IP information

#### `SettingsController`
- **Methods**:
  - `index()`: Show settings
  - `updateLanguage()`: Change platform language

- **Configuration**:
  - Admin 2FA settings
  - Password management
  - IP whitelist
  - Language selection
  - Email configuration

#### `LocalizationController`
- **Methods**: `update()`
- **Handles**: Session language change

---

### Tenant Portal Controllers (`app/Http/Controllers/Tenant/`)

#### `TenantPortalController`
- **Methods**:
  - `dashboard()`: Tenant owner dashboard
  - `comingSoon()`: Feature preview page

- **Dashboard Shows**:
  - Member statistics
  - Revenue overview
  - Attendance trends
  - Class utilization
  - Equipment status

#### `MemberController`
- **Methods**:
  - `index()`: List members with search/filter
  - `create()`: Create member form
  - `store()`: Add new member
  - `show()`: View member details
  - `edit()`: Edit member form
  - `update()`: Save member changes
  - `destroy()`: Deactivate member

- **Member Operations**:
  - Member code generation
  - Profile management
  - Membership plan assignment
  - Status tracking (Active/Inactive/Expired/Frozen)
  - Balance management
  - Document upload

#### `MemberRegistrationController`
- **Methods**: Handle member self-registration
- **Operations**:
  - Token-based registration
  - Form submission processing
  - Profile creation
  - Welcome email sending

#### `MembershipPlanController`
- **Methods**:
  - `index()`: List membership plans
  - `create()`: Create plan form
  - `store()`: Add new plan
  - `show()`: View plan details
  - `edit()`: Edit plan form
  - `update()`: Save plan changes
  - `destroy()`: Delete/archive plan

- **Plan Management**:
  - Duration configuration
  - Pricing and taxes
  - Session limits
  - Freeze capabilities
  - Inclusions management

#### `ClassController`
- **Methods**:
  - `index()`: List classes
  - `create()`: Create class
  - `store()`: Add class
  - `edit()`: Edit class
  - `update()`: Update class
  - `destroy()`: Delete class
  - `book()`: Book class for member
  - `unbook()`: Cancel booking

- **Class Management**:
  - Scheduling and timing
  - Instructor assignment
  - Capacity management
  - Class type categorization
  - Recurring schedules

#### `AttendanceController`
- **Methods**:
  - `index()`: View attendance records
  - `store()`: Record check-in/check-out
  - Reports and summaries

- **Attendance Tracking**:
  - Timestamp recording
  - Member identification
  - Session validation
  - Duplicate prevention

#### `AssessmentController`
- **Methods**:
  - `index()`: List member assessments
  - `store()`: Record new assessment
  - `show()`: View assessment history
  - Progress tracking

- **Assessment Management**:
  - Body measurements
  - Fitness metrics
  - Progress tracking over time
  - Goal monitoring

#### `EquipmentController`
- **Methods**:
  - `index()`: List equipment
  - `create()`: Add equipment form
  - `store()`: Add new equipment
  - `edit()`: Edit equipment
  - `update()`: Save equipment changes
  - `destroy()`: Remove equipment
  - `logService()`: Record maintenance

- **Equipment Management**:
  - Inventory tracking
  - Condition status
  - Maintenance scheduling
  - Service history
  - Cost tracking

#### `LockerController`
- **Methods**:
  - `index()`: List lockers
  - `create()`: Create locker
  - `store()`: Add locker
  - `assignLocker()`: Assign to member
  - `releaseLocker()`: Remove assignment

- **Locker Management**:
  - Locker registration
  - Member assignment
  - Duration tracking
  - Availability monitoring

#### `StaffController`
- **Methods**:
  - `index()`: List staff
  - `create()`: Create staff form
  - `store()`: Add staff member
  - `edit()`: Edit staff
  - `update()`: Save changes
  - `destroy()`: Deactivate staff

- **Staff Management**:
  - Profile creation
  - Role assignment
  - Permission configuration
  - Department assignment
  - Branch assignment

#### `PaymentController`
- **Methods**:
  - `index()`: List payments
  - `store()`: Record payment
  - `show()`: View payment details

- **Payment Operations**:
  - Payment method recording
  - Amount processing
  - Invoice linking
  - Reconciliation
  - Receipt generation

#### `InvoiceController`
- **Methods**:
  - `index()`: List invoices
  - `show()`: View invoice details
  - `download()`: PDF export
  - `email()`: Send invoice

- **Invoice Management**:
  - Invoice generation
  - Tax calculations
  - Status tracking
  - PDF rendering

#### `ExpenseController`
- **Methods**:
  - `index()`: List expenses
  - `create()`: Create expense form
  - `store()`: Add expense
  - `edit()`: Edit expense
  - `update()`: Save expense
  - `destroy()`: Delete expense

- **Expense Management**:
  - Categorization
  - Amount tracking
  - Approval workflow
  - Reporting

#### `PosController`
- **Methods**:
  - `apiProducts()`: GET products list
  - `apiStoreProduct()`: POST create product
  - `apiUpdateProduct()`: PUT update product
  - `apiSales()`: GET sales history
  - `apiCheckout()`: POST complete sale
  - `apiStock()`: GET stock levels
  - `apiRestock()`: POST add stock
  - `apiAdjust()`: POST adjust stock

- **POS Operations**:
  - Product management
  - Sales transaction processing
  - Stock inventory control
  - Receipt generation
  - Daily reconciliation

#### `ReportController`
- **Methods**: Generate various reports
- **Reports Available**:
  - Member reports (Active/Inactive/Expiring)
  - Attendance summaries
  - Revenue by plan
  - Class utilization
  - POS sales analysis
  - Equipment maintenance
  - Staff performance

#### `SettingController`
- **Methods**: Manage tenant settings
- **Configuration**:
  - Business information
  - Operating hours
  - GST settings
  - Email templates
  - Currency and localization

#### `RenewalController`
- **Methods**: Handle membership renewals
- **Operations**:
  - Auto-renewal processing
  - Renewal reminders
  - Renewal invoice generation

#### `BranchController`
- **Methods**:
  - `index()`: List branches
  - `create()`: Create branch
  - `store()`: Add branch
  - `edit()`: Edit branch
  - `update()`: Save branch
  - `destroy()`: Delete branch

- **Branch Management**:
  - Multi-location setup
  - Staff assignment
  - Operating hours per branch
  - Equipment assignment

---

### Public Portal (`app/Http/Controllers/Public/`)

#### `OnlineRegistrationController`
- **Methods**:
  - `show()`: Display registration form
  - `submit()`: Process registration
  - `success()`: Confirmation page

- **Registration Flow**:
  - Token validation
  - Member profile creation
  - Plan auto-assignment
  - Welcome email sending

---

## 🗄️ Data Model Relationships

```
Tenant (Gym Organization)
├── Branch (Multiple Locations)
│   ├── Members
│   ├── Staff
│   ├── Equipment
│   └── GymClasses
├── GymMembershipPlans
├── Subscriptions
├── Payments
├── Invoices
├── PosSales
├── Expenses
├── Integrations
└── Audit Logs

Member
├── Membership (Active Plan)
├── Attendance Logs
├── Assessments
├── Class Bookings
├── Locker Assignments
├── Invoices
├── Payments
└── Audit Logs

GymClass
├── Instructor (Staff)
├── Class Bookings
├── Attendance
└── Schedule

Equipment
├── Service Records
└── Branch Location

Staff
├── User Account
├── Assigned Classes
├── Attendance
└── Permissions

Invoice
├── Member
├── Payments
└── Line Items

PosSale
├── Member (Optional)
├── Sale Items
└── Staff (Operator)

Subscription
├── Tenant
├── Plan
└── Payments
```

---

## 🔐 Authentication Flow

```
1. LOGIN REQUEST
   ├── Email + Password validation
   ├── Rate limiting check
   └── Account lockout check

2. PASSWORD VERIFICATION
   ├── Bcrypt comparison
   └── Timing-safe check

3. 2FA VERIFICATION
   ├── Generate/Send 2FA code
   └── Verify code

4. SESSION CREATION
   ├── Generate session token
   ├── Store in Redis/Session
   └── Set cookies

5. REDIRECT
   ├── Super Admin → Admin Dashboard
   └── Tenant User → Tenant Dashboard
```

---

## 🔄 Key API Endpoints (v1/pos)

```
GET  /v1/pos/products           - List products
POST /v1/pos/products           - Create product
PUT  /v1/pos/products/{id}      - Update product

GET  /v1/pos/sales              - List sales
POST /v1/pos/sales              - Checkout (create sale)

GET  /v1/pos/stock              - Get stock levels
POST /v1/pos/stock/restock      - Add inventory
POST /v1/pos/stock/adjust       - Adjust stock
```

---

## 📊 Database Schema Organization

### Super Admin Database (Shared)
- `users` - All system users
- `admin_accounts` - Super admin info
- `tenants` - Gym organizations
- `tenant_payment` - Platform payments
- `subscriptions` - Plan assignments
- `plans` - Subscription plans
- `admin_audit_logs` - Platform audit trail

### Per-Tenant Database
- `users` - Tenant users
- `members` - Gym members
- `staff` - Gym staff
- `branches` - Gym locations
- `gym_membership_plans` - Gym plans
- `gym_classes` - Classes
- `class_bookings` - Class enrollments
- `attendance_logs` - Check-in/out
- `member_assessments` - Progress tracking
- `equipment` - Equipment inventory
- `equipment_service_records` - Maintenance logs
- `lockers` - Locker inventory
- `locker_assignments` - Member assignments
- `payments` - Transaction records
- `invoices` - Billing documents
- `pos_products` - POS inventory
- `pos_sales` - POS transactions
- `pos_sale_items` - Sale items
- `pos_stock_movements` - Stock history
- `expenses` - Cost records
- `integrations` - Third-party connections
- `owner_audit_logs` - Tenant audit trail

---

## 🚀 Request Lifecycle

```
HTTP Request
    │
    ├─ Route Matching (web.php / api.php)
    │   │
    │   └─ Middleware Stack
    │       ├─ Authentication (verify user)
    │       ├─ Authorization (role check)
    │       ├─ Tenant User (tenant validation)
    │       └─ Custom Middleware
    │
    ├─ Controller Execution
    │   │
    │   ├─ Input Validation (Form Request)
    │   ├─ Business Logic (Service Layer)
    │   ├─ Database Operations (Eloquent)
    │   └─ Audit Logging
    │
    └─ Response
        ├─ Inertia Render (Vue component)
        ├─ JSON Response (API)
        └─ Redirect (Form submission)
```

---

## 📦 Service Layer (`app/Services/`)

### `Admin/` Services
- Tenant onboarding service
- Subscription management service
- Payment processing service
- Audit logging service

### `Auth/` Services
- Authentication service
- 2FA service
- Password management service

### `Tenant/` Services
- Member management service
- Class management service
- Equipment management service
- Payment processing service
- Attendance service
- POS service
- Report generation service

### `Tenancy/` Services
- Database connection management
- Tenant context service
- Schema management

---

## 🧪 Testing Strategy

### Unit Tests
- Model methods
- Service layer logic
- Helper functions
- Validation rules

### Feature Tests
- Authentication flows
- CRUD operations
- Authorization checks
- Multi-tenant isolation

### Integration Tests
- Payment processing
- Email notifications
- External API calls
- Database transactions

### Load Tests
- Concurrent user handling
- Database query performance
- Response time metrics
- Memory usage

---

## 📋 Configuration Files

- `config/app.php` - Application settings
- `config/auth.php` - Authentication providers
- `config/database.php` - Database connections
- `config/mail.php` - Email configuration
- `config/filesystems.php` - File storage
- `config/session.php` - Session management
- `config/cache.php` - Caching
- `config/queue.php` - Job queue
- `config/logging.php` - Logging
- `config/gym.php` - Custom gym settings
- `config/permission.php` - Permission setup

---

## 🔗 Frontend Architecture

### Vue 3 Components
- Page components (Dashboard, Members, Plans, etc.)
- Reusable components (Form inputs, Tables, Modals)
- Layout components (Sidebar, Header, Footer)

### State Management
- Inertia.js props
- Vue reactive state
- Session storage

### Routing
- Inertia.js server-side routing
- Browser history management
- Client-side navigation

---

## 💻 Development Workflow

```
1. CLONE REPOSITORY
   ├─ Install PHP dependencies (composer install)
   ├─ Install JavaScript dependencies (npm install)
   └─ Configure .env file

2. DATABASE SETUP
   ├─ Run migrations (php artisan migrate)
   ├─ Seed data (php artisan db:seed)
   └─ Create super admin user

3. START DEVELOPMENT
   ├─ Run Laravel dev server (php artisan serve)
   ├─ Run Vite dev server (npm run dev)
   └─ Access application

4. DEVELOPMENT TASKS
   ├─ Create migrations
   ├─ Build components
   ├─ Implement features
   └─ Write tests

5. DEPLOYMENT
   ├─ Build assets (npm run build)
   ├─ Migrate database
   ├─ Run cache commands
   └─ Set permissions
```

---

## 🎯 Key Features Implementation

### Multi-Tenancy
- Middleware-based tenant resolution
- Database switching per request
- Scoped queries using `forTenant()` helper
- Automatic tenant context preservation

### Role-Based Access Control
- Permission system with modules
- Middleware-based authorization
- Policy-based access checks
- Dynamic role assignment

### Audit Logging
- Observer-based automatic logging
- Manual event logging
- Immutable log table
- Comprehensive filtering and search

### Email Notifications
- Mailable classes for templates
- Queue-based sending
- Template customization
- Localization support

---

## 📈 Performance Optimization

- Database query optimization (eager loading)
- Caching strategies (Redis)
- Asset minification (Vite)
- CDN integration ready
- Database indexing on key columns
- N+1 query prevention
- Pagination for large datasets

---

## 🔐 Security Implementation

- SQL Injection: Parameterized queries
- XSS: Vue.js auto-escaping
- CSRF: Token-based validation
- Rate Limiting: Per-IP tracking
- Authentication: 2FA + password hashing
- Authorization: Role-based policies
- Data Encryption: HTTPS + encrypted storage
- Input Validation: Centralized request validation

---

**Last Updated**: June 2026  
**Version**: 2.0 Complete Edition
