# Engineering Decisions

## 1. Payment Flow Refactor Behind a Runtime Flag
- Decision: Keep both legacy and refactored paths in `BankIntegrationController`, gated by `PAYMENT_REFACTORED_BANK_CONTROLLER`.
- Why: Enables progressive rollout and rollback without route changes.
- Tradeoff: Temporary duplication in controller methods until full cutover.

## 2. Centralized Payment Application Layer
- Decision: Consolidate payment orchestration in `app/Application/Payment/*` (actions, services, DTOs, validators).
- Why: Improves separation of concerns and testability for a high-risk domain.
- Tradeoff: More files and indirection compared to controller-centric logic.

## 3. DB-backed Callback Idempotency
- Decision: Use `payment_callback_idempotencies` with unique keys on `(flow_type, model_type, model_id, provider_reference)`.
- Why: Prevents duplicate provider callbacks from causing repeated financial side effects.
- Tradeoff: Additional storage and write path on callback processing.

## 4. Explicit Payment State Machine + Row Locks
- Decision: Manage transitions through `PaymentStateMachine` and `lockForUpdate`.
- Why: Keeps status transitions deterministic under concurrent callbacks/retries.
- Tradeoff: Slightly higher transactional complexity.

## 5. Provider Registry Pattern for Bank Integrations
- Decision: Resolve payment provider implementations via `PaymentProviderRegistry` + `config/payment_providers.php`.
- Why: Makes bank-specific logic pluggable without branching at controller level.
- Tradeoff: Requires consistent provider contracts and registration hygiene.

## 6. ERP Sync as Eventual Consistency
- Decision: Use `erp_status`, `erp_attempts`, and scheduler commands for outbound order/payment sync.
- Why: Avoids blocking user flows on ERP availability and enables controlled retries.
- Tradeoff: Data reaches ERP asynchronously, not in the same transaction as user actions.

## 7. Batch Locking for Long-running Sync Tasks
- Decision: Use `BatchLockService` around ETA send operations and major scheduled sync jobs.
- Why: Prevents overlapping runs and duplicate side effects.
- Tradeoff: Requires lock-timeout tuning and lock health observability.

## 8. Hybrid Frontend Build (Mix + Vite)
- Decision: Keep Laravel Mix for legacy modules while introducing Vite + Vue 3 for modern screens.
- Why: Supports incremental modernization without a disruptive full rewrite.
- Tradeoff: Two build pipelines increase tooling surface area.

## 9. Blade-first with Vue Islands
- Decision: Preserve Blade rendering and mount Vue components selectively via `data-vue`.
- Why: Fits existing Laravel page architecture while enabling richer interactive admin screens.
- Tradeoff: Frontend patterns are mixed (imperative legacy JS + component-driven Vue).