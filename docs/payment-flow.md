# Payment Flow

## Scope
This flow covers card payments and payment-link payments handled by:
- [`routes/frontend/bank-integration.php`](../routes/frontend/bank-integration.php)
- [`app/Http/Controllers/BankIntegrationController.php`](../app/Http/Controllers/BankIntegrationController.php)

## Main Endpoints
- `POST /bank-integrations/payment/request`
- `POST /bank-integrations/payment/response`
- `POST /bank-integrations/payment-link/request/{token}`
- `POST /bank-integrations/payment-link/response`

## Callback Processing Pipeline
The callback pipeline is centralized in the payment application layer:
- `ProcessPaymentResponseAction` / `ProcessPaymentLinkResponseAction`
- `HandlePaymentCallbackAction`
- `CallbackModelResolver`
- `CallbackSignatureValidator`
- `CallbackProcessorService`
- `PaymentSuccessHandler` / `PaymentFailureHandler`

## Idempotency and Safety
- Callback deduplication is backed by `payment_callback_idempotencies`
- Unique key: `(flow_type, model_type, model_id, provider_reference)`
- Payment state transitions are guarded with:
  - `lockForUpdate` on payment rows
  - explicit state rules in `PaymentStateMachine`
  - payload safety checks (`OID` consistency and user association)

## Mermaid: Payment Callback Flow
```mermaid
flowchart TD
    A[Bank provider callback<br/>paymentId or paymentLinkId] --> B[BankIntegrationController<br/>paymentResponse/paymentLinkResponse]
    B --> C[HandlePaymentCallbackAction]
    C --> D[Resolve model<br/>Payment or PaymentLink]
    D --> E[Verify callback signature]
    E -->|invalid| F[Return error post-message view]
    E -->|valid| G[CallbackProcessorService]
    G --> H{Idempotency<br/>provider_reference}
    H -->|duplicate| I[Return stored result]
    H -->|first seen| J[Resolve callback via provider]
    J --> K{Success?}
    K -->|yes| L[PaymentSuccessHandler]
    K -->|no| M[PaymentFailureHandler]
    L --> N[Persist transition + side effects]
    M --> O[Persist failure transition]
    N --> P[Render post-message / JSON / HTML]
    O --> P
```

## State Outcomes
- `SUCCESS`: payment completed and side effects executed
- `FAILED`: payment marked failed with failure reason
- `REFUNDED` / `CANCELLED`: handled through admin refund/cancel flow with gateway call + transition update