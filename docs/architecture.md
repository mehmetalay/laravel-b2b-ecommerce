# Architecture

## Purpose
This document describes the actual architecture of the Laravel 8 B2B platform in this repository, including payment, ERP, frontend, and operational runtime boundaries.

## System Overview
- Backend framework: Laravel 8 (`app/`, `routes/`, `config/`)
- Frontend delivery: Blade templates + modular JavaScript + Vue 3 islands
- Primary database: MySQL/MariaDB (application data)
- Secondary database connection: SQL Server (`sqlsrv`) for ERP source views
- Background processing: Laravel scheduler + queue jobs + command-based sync loops

## Backend Layers
- HTTP layer: controllers, middleware, request validation (`app/Http/*`)
- Application layer: use-case oriented modules (`app/Application/*`)
- Domain/support logic: domain policies/services (`app/Domain/*`, `app/Services/*`)
- Data access: repositories and Eloquent models (`app/Repositories/*`, `app/Models/*`)
- Presentation layer helpers: response mapping (for example payment flow presenter)

## Route Organization
- Main route composition in [`routes/web.php`](../routes/web.php)
- Frontend split by concern under `routes/frontend/*` (cart, order, payment, account, etc.)
- Admin panel grouped under `/aka` prefix and `admin.` name prefix via `routes/backend/*`
- Admin data APIs exposed under `/admin/api/*` in [`routes/web.php`](../routes/web.php)

## Payment Architecture (High-Level)
- Entry controller: [`app/Http/Controllers/BankIntegrationController.php`](../app/Http/Controllers/BankIntegrationController.php)
- Application flow orchestration: `app/Application/Payment/*`
- Provider dispatch: [`PaymentProviderRegistry`](../app/Application/Payment/Services/PaymentProviderRegistry.php) + `config/payment_providers.php`
- Callback integrity and idempotency:
  - Signature verification via `PaymentCallbackSecurityService`
  - Duplicate callback guard via `payment_callback_idempotencies` table

### Payment Module Folder Map
```text
app/Application/Payment
|-- Actions
|-- DTO
|-- Enums
|-- Mappers
|-- Repositories
|-- Services
`-- Validators
```

## Order and ERP Architecture (High-Level)
- Order creation pipeline: [`app/Application/Order/CreateOrderAction.php`](../app/Application/Order/CreateOrderAction.php)
- ERP outbound sync:
  - Orders: `orders:sync-pending` -> `SyncPendingOrdersAction` -> `EtaOrderService`
  - Payments: `payments:sync-pending` -> `EtaBankService`
- ERP inbound sync:
  - SQL Server views read from scheduler tasks in [`app/Console/Kernel.php`](../app/Console/Kernel.php)
  - Account sync service: [`app/Services/ERP/AccountImportService.php`](../app/Services/ERP/AccountImportService.php)

## Frontend Architecture (High-Level)
- Build pipelines coexist:
  - Laravel Mix for legacy/module bundles (`webpack.mix.js`)
  - Vite + Vue 3 for modern/admin components (`vite.config.ts`, `resources/js/app.ts`)
- Rendering model:
  - Blade pages render server HTML
  - Vue components are mounted on `[data-vue]` islands (`resources/js/legacy/mounts.ts`)

## Operational Components
- Scheduler: [`app/Console/Kernel.php`](../app/Console/Kernel.php)
- Queue-backed jobs: `RunExportJob` (`app/Jobs/RunExportJob.php`)
- Retry/state tracking columns for ERP sync:
  - `erp_status`, `erp_attempts`, `erp_processing_at`, `erp_last_error`, `erp_last_failed_at`
- Locking strategy for long-running sync tasks:
  - `BatchLockService` + `batch_locks` persistence

## Integration Boundaries
- Implemented external integration categories in this codebase:
  - Bank virtual POS providers
  - ETA ERP APIs
  - SQL Server ERP data views
  - Mail and SMS services