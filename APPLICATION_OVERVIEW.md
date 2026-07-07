# GymNanba - Application Overview & Functionality Documentation

## 📋 Executive Summary

**GymNanba** is a comprehensive **SaaS-based Gym Management System** built on Laravel with a multi-tenant architecture. It provides gym owners with a complete platform to manage their operations while offering a Super Admin portal for platform management, tenant control, and business monitoring.

**Tech Stack:**
- Backend: Laravel PHP framework
- Frontend: Vue 3 + Inertia.js
- Styling: Tailwind CSS
- Build Tool: Vite
- Database: Multi-tenant schema separation
- Architecture: Multi-tenant SaaS with separate databases per tenant

---

## 🏗️ System Architecture

### Multi-Tenant Structure
- **Super Admin Portal**: Platform owner controls (admin.gymos.in)
- **Tenant Portals**: Individual gym owner dashboards (subdomain routing)
- **Shared Core**: Central authentication, tenant management, billing
- **Isolated Tenants**: Each gym has its own database and isolated data

---

## 📦 Core Modules & Functionalities

### 1. **AUTHENTICATION & SECURITY** 🔐

#### Super Admin Portal
- Email + Password authentication with mandatory 2FA (Two-Factor Authentication)
- Rate limiting: 5 attempts per 15 minutes per IP
- Account lockout: 30-minute lock after failed attempts
- Session management with timeout protection
- Super admin impersonation capability (log in as gym owner for support)
- Password change enforcement on first login
- IP whitelist configuration (Settings)

#### Gym Owner Authentication
- Multi-user support per tenant with role-based access control
- Staff login capabilities
- Session security with forced password change
- Audit logging for all authentication events

**Key Features:**
- Generic login error messages (prevent email enumeration)
- Bcrypt password hashing with cost factor 12
- Timing-safe string comparison
- Immutable audit trail of all access attempts

---

### 2. **TENANT MANAGEMENT** 🏢

#### Tenant Lifecycle Management
- **Onboarding Wizard**: Step-by-step gym registration
  - Basic Info: Gym name, business type, contact details
  - Location: Address, city, state, PIN, country
  - Branding: Logo, cover photo, social links
  - Operating Hours: Configurable per day of week
  - Database Setup: Automatic subdomain and database creation

- **Tenant Status Management**:
  - `Active`: Full operational access
  - `Suspended`: Gym owner blocked, data preserved
  - `Archived`: Historical data retention

#### Tenant Information
- **Gym Details**: Name, business type, website
- **Owner Information**: Name, email, phone
- **Business Documents**: GST number, PAN, Registration number
- **Contact Information**: Multiple phone numbers, email
- **Branding**: Logo URL, cover photos, social media links
- **Operating Hours**: Day-wise opening/closing times
- **Domain Configuration**:
  - Subdomain routing (tenant.gymos.in)
  - Custom domain support
  - Database mode configuration
  - Single or multi-database setup options

#### Features
- Search and filter tenants
- View tenant details and history
- Edit tenant information
- Suspend/activate tenants
- Archive/delete tenants
- Track tenant member counts
- Monitor last owner login timestamps
- Tenant notes and documentation

---

### 3. **MEMBER MANAGEMENT** 👥

#### Member Core Features
- **Member Registration**: 
  - Member code (unique per gym)
  - Personal information: Name, gender, DOB
  - Contact details: Phone, email
  - Address and ID proof storage
  - Photo upload capability

#### Member Status Tracking
- **Membership Plans**: Active membership with duration tracking
- **Status Types**: Active, Inactive, Expired, Frozen
- **Membership Freeze**: Pause membership up to configurable days
- **Account Balance**: Track member wallet/prepaid balance

#### Member Data Management
- Document storage: ID proof uploads
- Photo archival
- Account notes and remarks
- Soft delete support (data retention)
- Created by tracking (audit trail)

#### Member Lifecycle
- Membership start and expiry dates
- Plan upgrades and downgrades
- Renewal reminders
- Freeze period management
- Balance tracking (in paise for precision)

---

### 4. **MEMBERSHIP PLANS** 📋

#### Plan Creation & Management
- **Plan Types**:
  - Monthly plans
  - Quarterly plans
  - Yearly plans
  - Custom duration days

#### Plan Features
- **Duration Configuration**:
  - Duration type (month/year/days)
  - Duration value and calculated duration in days
  - Session limits (if applicable)

- **Pricing**:
  - Plan price in paise (for precision)
  - GST applicability
  - GST rate configuration
  - Tax-inclusive pricing

- **Capacity Management**:
  - Maximum members per plan
  - Grace days for renewals

- **Benefits Configuration**:
  - Inclusions (locker, class access, etc.)
  - Custom tags and categorization
  - Freeze allowed (yes/no)
  - Maximum freeze days

- **Status Management**: Active/Inactive plans

#### Plan Analytics
- Members enrolled in each plan
- Revenue tracking per plan
- Utilization metrics

---

### 5. **ATTENDANCE TRACKING** 📍

#### Check-in/Check-out System
- Member attendance logging (date, time)
- Attendance status tracking
- Daily attendance reports
- Monthly attendance summaries

#### Attendance Features
- Automatic timestamp recording
- Member identification (card/QR/manual)
- Session validation against membership plan
- Duplicate visit prevention
- Late entry tracking

#### Attendance Reports
- Individual member attendance history
- Gym-wide daily attendance
- Monthly attendance charts
- Attendance compliance tracking

---

### 6. **GYM CLASSES** 🏋️

#### Class Management
- Class creation and scheduling
- Class type categorization (Yoga, CrossFit, Pilates, etc.)
- Instructor assignment
- Timing and duration configuration
- Capacity management

#### Class Booking System
- Member booking capabilities
- Booking confirmations
- Cancellation management
- Waiting list support
- Capacity enforcement

#### Class Scheduling
- Recurring class patterns
- Holiday exceptions
- Seasonal changes
- Time slot management
- Day-wise scheduling

#### Class Features
- Trainer/instructor assignment
- Member feedback collection
- Attendance tracking per class
- Revenue attribution
- Performance metrics

---

### 7. **EQUIPMENT MANAGEMENT** 🔧

#### Equipment Inventory
- Equipment registration (name, category, serial number)
- Equipment categorization (Cardio, Strength, Functional, etc.)
- Condition tracking (Functional, Maintenance, Repair, Retired)
- Location tracking within branch

#### Equipment Maintenance
- **Service Records**:
  - Service date and type (Repair, Maintenance, Inspection)
  - Cost tracking
  - Service provider details
  - Service notes
  - Next service date tracking

#### Equipment Lifecycle
- Equipment status monitoring
- Maintenance schedule management
- Cost allocation
- Equipment retirement and replacement
- Maintenance cost reports

#### Equipment Features
- Equipment photos/documentation
- Warranty tracking
- Supplier information
- Maintenance history
- Service cost analytics

---

### 8. **LOCKER MANAGEMENT** 🔒

#### Locker Administration
- Locker registration (locker number, size, location)
- Location mapping (Floor, zone)
- Size categorization (Small, Medium, Large)

#### Locker Assignment
- Assign lockers to members
- Assignment period tracking (start/end dates)
- Member limit per locker
- Locker key/code management
- Assignment status tracking

#### Locker Features
- Availability tracking
- Maintenance status
- Damage recording and repairs
- Renewal reminders
- Locker utilization reports

---

### 9. **PAYMENT & INVOICING** 💰

#### Invoice Management
- Automatic invoice generation for memberships
- Invoice customization
- Tax calculations and application
- Invoice numbering and sequence
- Invoice status tracking (Draft, Issued, Paid, Cancelled)

#### Payment Processing
- Payment collection (cash, card, online)
- Payment method tracking
- Payment date recording
- Bank reconciliation support
- Partial payment handling

#### Payment Types
- **Full Payment**: Complete invoice settlement
- **Partial Payment**: Installment/part payment support
- **Renewals**: Automatic renewal invoicing
- **Grace Period**: Configurable renewal grace days

#### Billing Features
- Invoice PDF generation
- Payment reminders
- Overdue tracking
- Payment history per member
- Revenue reconciliation
- Tenant-level payment tracking

---

### 10. **STAFF MANAGEMENT** 👨‍💼

#### Staff Registration
- Staff profile creation
- Personal details: Name, phone, email
- Employment details: Join date, salary (in paise)
- Role assignment and permissions
- Branch assignment

#### Staff Documentation
- ID proof upload (Aadhaar, PAN, Passport)
- Photo storage
- Address and contact information
- Document verification

#### Staff Status Management
- Active/Inactive status
- Deactivation dates
- Soft deletion support
- Status history tracking

#### Staff Management Features
- Salary tracking (in paise)
- Role-based access control
- Staff assignment to classes/sessions
- Performance tracking
- Leave management integration (future)
- Attendance/login monitoring

---

### 11. **EXPENSE TRACKING** 📊

#### Expense Management
- Expense category creation
- Expense logging with date and amount
- Description and notes
- Attachment support
- Expense approval workflow

#### Expense Categories
- Utilities (electricity, water, internet)
- Maintenance and repairs
- Staff salaries
- Inventory and supplies
- Marketing and advertising
- Other operational costs

#### Expense Reporting
- Monthly expense summaries
- Category-wise expense breakdown
- Budget vs actual analysis
- Expense trends
- Cost analysis by period

---

### 12. **MEMBER ASSESSMENTS** 📈

#### Assessment Creation
- Assessment parameter definition
- Assessment type categorization
- Measurement units configuration
- Progress tracking

#### Assessment Recording
- Initial assessment capture
- Periodic progress assessments
- Member measurement tracking
  - Weight, BMI, body fat percentage
  - Muscle measurements
  - Fitness indicators
  - Performance metrics

#### Assessment Analytics
- Progress tracking over time
- Goal achievement monitoring
- Body composition analysis
- Fitness improvement trends
- Personalized recommendations

---

### 13. **BRANCHES MANAGEMENT** 🏢

#### Multi-Branch Support
- Create and manage multiple gym locations
- Branch-specific staff assignment
- Member assignment to branches
- Branch operating hours
- Branch contact information

#### Branch Features
- Switch branch functionality for users
- "All branches" view option
- Branch-specific reporting
- Independent equipment inventory per branch
- Branch capacity management
- Inter-branch member transfers

---

### 14. **POS SYSTEM** 🛍️

#### Product Management
- Product catalog creation
- Product categorization
- Price management
- Stock level tracking
- Reorder points and alerts

#### Sales Management
- Point of sale checkout
- Member-based sales attribution
- Sale history tracking
- Receipt generation
- Member wallet deduction

#### Stock Management
- **Restock Operations**: Add new inventory
- **Adjustment**: Correct stock counts
- **Stock Movement Tracking**: Audit trail of all stock changes
- **Low Stock Alerts**: Inventory warnings
- **FIFO/Inventory valuation**: Cost tracking

#### Sales Features
- Item-level transaction tracking
- Sale item details (product, quantity, price)
- Sale date and staff recording
- Return and adjustment processing
- Daily sales reports

---

### 15. **SUBSCRIPTION MANAGEMENT** (Super Admin) 💳

#### Subscription Handling
- Tenant-to-plan assignment
- Subscription status tracking (Active, Trial, Suspended, Cancelled)
- Billing date configuration
- Auto-renewal settings
- Cancellation and reactivation

#### Trial Management
- Trial period configuration
- Trial end dates
- Conversion to paid plans
- Trial cancellation tracking

#### Renewal Management
- Automatic renewal processing
- Manual renewal initiation
- Renewal payment collection
- Renewal failure handling

---

### 16. **ADMIN DASHBOARD** 📊

#### Super Admin Dashboard
- Platform KPIs:
  - Total active tenants
  - Monthly Recurring Revenue (MRR)
  - Upcoming renewals
  - Active subscriptions
  - Failed payments
  
- Tenant List with status indicators
- Recent tenant activity
- Revenue trends
- Subscription status overview
- Payment status tracking

#### Gym Owner Dashboard
- Member statistics
- Revenue overview
- Attendance trends
- Class utilization
- Equipment status
- Staff efficiency metrics
- Expense tracking
- Top-performing items (POS)

---

### 17. **AUDIT LOGGING** 🔍

#### Admin Audit Trail
- **Super Admin Actions**:
  - Tenant creation/suspension/deletion
  - Plan management
  - Subscription changes
  - Admin login attempts
  - Impersonation sessions
  - Settings modifications
  
- **Tenant Owner Actions**:
  - Member creation/update/delete
  - Payment processing
  - Staff management
  - Equipment maintenance
  - Class scheduling
  - Expense logging

#### Audit Features
- Immutable logging (no UPDATE/DELETE at DB level)
- Timestamp precision
- User identification
- Action type categorization
- Before/after value tracking
- IP address logging
- Search and filter capabilities
- Long-term retention (configurable)

---

### 18. **REPORTING & ANALYTICS** 📈

#### Available Reports
- **Membership Reports**: Active/Inactive/Expiring members
- **Attendance Reports**: Daily, weekly, monthly summaries
- **Revenue Reports**: By plan, by member, over time
- **Expense Reports**: By category, monthly summaries
- **POS Reports**: Sales by product, revenue trends
- **Class Reports**: Attendance, utilization, trainer performance
- **Equipment Reports**: Maintenance schedule, costs
- **Staff Reports**: Performance, attendance, utilization

#### Report Features
- Date range filtering
- Department/branch filtering
- Export capabilities (PDF, CSV)
- Scheduled report generation
- Email delivery options
- Customizable report templates
- Trend analysis and comparison

---

### 19. **SETTINGS & CONFIGURATION** ⚙️

#### Admin Settings
- **Security Settings**:
  - 2FA recovery codes
  - Password management
  - IP whitelist configuration
  - Session timeout settings
  - API key management

- **Email Configuration**:
  - SMTP settings
  - Email templates
  - Sender configuration
  - Email verification

- **Language Management**:
  - Platform language selection (English, Hindi, Tamil, Telugu)
  - Multi-language support
  - Regional customization
  - Language-specific content management

- **System Settings**:
  - Platform name and branding
  - Currency and localization
  - Date and time format
  - Default values

#### Gym Owner Settings
- **Business Settings**:
  - Gym name and branding
  - Operating hours
  - Contact information
  - Tax configuration (GST rates)

- **Member Settings**:
  - Default grace period days
  - Freeze policy
  - Grace days for renewal

- **User Management**:
  - Staff account creation
  - Permission assignment
  - Role configuration
  - Access control

---

### 20. **MEMBER REGISTRATION (Public)** 🔗

#### Self-Registration
- Public registration link generation
- Token-based access (security)
- Registration form population
- Email verification
- Auto-member creation upon registration

#### Registration Features
- Membership plan auto-assignment
- Welcome email with credentials
- First login prompt
- Terms and conditions acceptance
- Data privacy compliance

---

### 21. **COMMUNICATION & NOTIFICATIONS** 📧

#### Email Templates
1. **Welcome Email**: New tenant onboarding
2. **Registration Link**: Member self-registration invitation
3. **Registration Confirmation**: Successful member registration
4. **Membership Expiry Warning**: Pre-expiry notifications
5. **Payment Reminder**: Outstanding invoice notifications
6. **Renewal Invoice**: Subscription renewal notice
7. **Suspension Notice**: Tenant/member suspension communication
8. **Password Reset**: Secure password change flow

#### Notification Features
- Transactional email delivery
- Template customization
- Batch email sending
- Delivery status tracking
- Email logging and audit

---

### 22. **ROLE-BASED ACCESS CONTROL (RBAC)** 🔑

#### User Roles
- **Super Admin**: Full platform access
- **Tenant Owner**: Full tenant access
- **Staff**: Limited access based on permissions
- **Guest/Member**: Public member portal access

#### Permission System
- **Modules**: 
  - Dashboard, Members, Classes, Equipment, POS, Payments, Reports, Settings, Staff, Audit Log

- **Permission Types**: 
  - View, Create, Edit, Delete, Export, Approve

- **Dynamic Permissions**: 
  - Role-based permission assignment
  - Module-level control
  - Feature-specific access

---

## 🔄 Key Workflows

### 1. **Tenant Onboarding**
1. Super admin initiates tenant creation
2. Complete multi-step registration wizard
3. Unique subdomain assignment
4. Database creation
5. Tenant admin user creation
6. Welcome email sent
7. Owner logs in and configures gym
8. Initial setup completed

### 2. **Member Enrollment**
1. Member registers via public link or staff creates
2. Profile information collected
3. Membership plan assigned
4. Initial invoice generated
5. Payment processed
6. Member account activated
7. Welcome email sent
8. Attendance tracking begins

### 3. **Payment Processing**
1. Invoice generated (membership/POS sale/renewal)
2. Payment reminder sent if overdue
3. Payment collected (multiple methods supported)
4. Partial payment option available
5. Receipt generated
6. Account reconciliation
7. Audit log entry created

### 4. **Class Booking**
1. Member selects class
2. Availability checked
3. Membership plan validity verified
4. Session limit verified
5. Booking confirmed
6. Confirmation sent
7. Attendance marked at class time
8. Analytics updated

---

## 🗄️ Core Data Models

### Primary Entities
- **Users**: Authentication and profile
- **Tenant**: Gym organization
- **Branch**: Multiple gym locations
- **Member**: Gym members/customers
- **Staff**: Gym employees
- **GymMembershipPlan**: Membership offerings
- **GymClass**: Class schedules
- **Equipment**: Gym equipment inventory
- **Locker**: Member locker assignments
- **Payment**: Transaction records
- **Invoice**: Billing documents
- **PosSale**: Point of sale transactions
- **Attendance**: Check-in/check-out logs
- **MemberAssessment**: Progress tracking
- **Expense**: Cost records
- **Subscription**: Tenant billing
- **AdminAuditLog**: Platform audit trail
- **OwnerAuditLog**: Tenant audit trail

---

## 🔐 Security Features

1. **Authentication**: Email + password + 2FA
2. **Authorization**: Role-based access control
3. **Data Isolation**: Multi-tenant database separation
4. **Audit Trail**: Immutable event logging
5. **Input Validation**: Server-side validation + sanitization
6. **SQL Injection Prevention**: Parameterized queries (Laravel Eloquent)
7. **XSS Protection**: Vue.js auto-escaping + CSP headers
8. **CSRF Protection**: Token-based CSRF prevention
9. **Rate Limiting**: Per-IP login attempt limiting
10. **Session Security**: Secure session management + timeout
11. **Password Security**: Bcrypt hashing + timing-safe comparison
12. **Data Encryption**: HTTPS enforced
13. **Soft Deletes**: Data retention and recovery capability

---

## 📊 Technology Stack

| Component | Technology |
|-----------|-----------|
| **Backend** | Laravel 11 PHP |
| **Frontend** | Vue.js 3 + Inertia.js |
| **Styling** | Tailwind CSS 4.0 |
| **Build Tool** | Vite 8 |
| **Database** | MySQL (Multi-tenant) |
| **Authentication** | Laravel built-in + 2FA |
| **ORM** | Laravel Eloquent |
| **Queue System** | Laravel Queue |
| **Caching** | Redis (optional) |
| **Testing** | PHPUnit |

---

## 🚀 Scalability & Performance

1. **Multi-Tenant Architecture**: Isolated databases per tenant
2. **Query Optimization**: Eager loading, indexing
3. **Caching Layer**: Redis support for session/cache
4. **Load Balancing**: Ready for horizontal scaling
5. **CDN Support**: Static asset delivery
6. **API-First Design**: RESTful APIs for extensibility
7. **Background Jobs**: Queue-based processing

---

## 📈 Future Extensibility

The architecture supports future modules:
- Health & Fitness Tracking
- Advanced Analytics & AI
- Mobile Applications (iOS/Android)
- Integration Marketplace
- SMS Notifications
- Video Conferencing Classes
- Virtual Coaching
- Social Features
- Loyalty Programs
- Advanced Reporting

---

## ✅ Deployment Readiness

- Multi-environment configuration
- Database migration support
- Backup and recovery procedures
- Monitoring and alerting
- Error tracking and logging
- Performance monitoring
- Security scanning
- Automated testing
- Continuous deployment ready

---

**Last Updated**: June 2026  
**Version**: 2.0 Complete Edition  
**Confidentiality**: Internal Use Only
