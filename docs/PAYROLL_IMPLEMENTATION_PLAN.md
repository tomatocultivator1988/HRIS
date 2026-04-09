# Payroll Feature Implementation Plan (Basic)

## 1) Objective and Scope

Build a basic payroll module for HRIS that:
- Defines payroll periods.
- Computes employee pay from attendance + base compensation settings.
- Applies configurable earnings and deductions.
- Supports draft → finalized → paid lifecycle.
- Exposes admin payroll APIs and employee payslip APIs.
- Keeps auditability, validation, and role-based access consistent with current architecture.

Out of scope for initial basic version:
- Government remittance file generation (BIR/SSS/PhilHealth/Pag-IBIG upload formats).
- Bank API disbursement integrations.
- Retroactive recalculation engine across historical closed periods.

---

## 2) Prerequisites

## 2.1 Functional decisions to finalize
- [ ] Payroll frequency: monthly (recommended for v1 basic).
- [ ] Time basis: attendance work_hours + status rules.
- [ ] Overtime handling: include/exclude in v1.
- [ ] Leave pay rule: Approved leave = paid day or unpaid by leave type.
- [ ] Tax model: flat withholding in v1 vs bracket table.
- [ ] Finalization policy: immutable finalized payroll except via reversal adjustment.

## 2.2 Technical prerequisites
- [ ] Confirm Supabase/PostgreSQL permissions for new tables, indexes, constraints, and RLS (if used).
- [ ] Confirm `APP_TIMEZONE` is set correctly (Asia/Manila).
- [ ] Confirm `audit_log` table is available and writable.
- [ ] Confirm current auth middleware and role middleware behavior for admin/employee APIs.
- [ ] Confirm migration execution process (existing pattern: SQL files in `docs/migrations/`).

---

## 3) Architecture Fit (Current Codebase)

Current stack pattern to follow:
- Routing: `config/routes.php`
- Controllers: `src/Controllers/*Controller.php`
- Services: `src/Services/*Service.php`
- Models: `src/Models/*`
- Views: `src/Views/*`
- Core response helpers and auth guards in base controller.

New components to add:
- Models:
  - `PayrollPeriod`
  - `EmployeeCompensation`
  - `PayrollRun`
  - `PayrollLineItem`
  - `PayrollAdjustment` (optional but recommended in v1 for manual corrections)
- Service:
  - `PayrollService`
- Controller:
  - `PayrollController`
- Views:
  - Admin: payroll periods + run processing + disbursement status
  - Employee: payslip history/details

---

## 4) Database Design

## 4.1 Entity relationship summary
- `payroll_periods` 1—* `payroll_runs`
- `employees` 1—* `employee_compensation`
- `payroll_runs` 1—* `payroll_line_items`
- `employees` 1—* `payroll_line_items`
- `payroll_line_items` 1—* `payroll_adjustments` (optional)

## 4.2 Core tables

### A) payroll_periods
Purpose: define covered date ranges and state.
- id (uuid, pk)
- code (varchar, unique) e.g. `2026-04`
- start_date (date)
- end_date (date)
- pay_date (date)
- status (varchar: Draft, Processing, Finalized, Paid, Cancelled)
- created_by (uuid fk users.id/employees.id depending current identity source)
- finalized_by (uuid nullable)
- finalized_at (timestamp nullable)
- paid_by (uuid nullable)
- paid_at (timestamp nullable)
- created_at, updated_at

Constraints:
- start_date <= end_date
- pay_date >= end_date
- unique (start_date, end_date)

### B) employee_compensation
Purpose: baseline pay settings per employee.
- id (uuid, pk)
- employee_id (uuid fk employees.id)
- payroll_type (varchar: Monthly, Daily, Hourly)
- base_salary (numeric(12,2), nullable for hourly-only)
- daily_rate (numeric(12,2), nullable)
- hourly_rate (numeric(12,2), nullable)
- standard_work_hours_per_day (numeric(5,2), default 8.00)
- tax_mode (varchar: Flat, Bracketed, Exempt)
- tax_value (numeric(12,2), default 0.00)
- sss_employee_share (numeric(12,2), default 0.00)
- philhealth_employee_share (numeric(12,2), default 0.00)
- pagibig_employee_share (numeric(12,2), default 0.00)
- effective_start_date (date)
- effective_end_date (date nullable)
- is_active (boolean default true)
- created_at, updated_at

Constraints:
- only one active comp profile per employee at a given date range.

### C) payroll_runs
Purpose: processing batch metadata and totals.
- id (uuid, pk)
- payroll_period_id (uuid fk payroll_periods.id)
- run_number (int default 1)
- status (varchar: Draft, Computed, Finalized, Paid)
- total_gross (numeric(14,2) default 0.00)
- total_deductions (numeric(14,2) default 0.00)
- total_net (numeric(14,2) default 0.00)
- employee_count (int default 0)
- generated_by (uuid)
- generated_at (timestamp)
- finalized_by (uuid nullable)
- finalized_at (timestamp nullable)
- notes (text nullable)
- created_at, updated_at

Constraints:
- unique (payroll_period_id, run_number)

### D) payroll_line_items
Purpose: per-employee computed payroll details.
- id (uuid, pk)
- payroll_run_id (uuid fk payroll_runs.id)
- employee_id (uuid fk employees.id)
- attendance_days (numeric(6,2) default 0.00)
- attendance_hours (numeric(8,2) default 0.00)
- late_minutes (int default 0)
- undertime_minutes (int default 0)
- overtime_hours (numeric(8,2) default 0.00)
- basic_pay (numeric(12,2) default 0.00)
- overtime_pay (numeric(12,2) default 0.00)
- leave_pay (numeric(12,2) default 0.00)
- allowance_total (numeric(12,2) default 0.00)
- adjustment_earnings (numeric(12,2) default 0.00)
- gross_pay (numeric(12,2) default 0.00)
- tax_amount (numeric(12,2) default 0.00)
- sss_amount (numeric(12,2) default 0.00)
- philhealth_amount (numeric(12,2) default 0.00)
- pagibig_amount (numeric(12,2) default 0.00)
- loan_deductions (numeric(12,2) default 0.00)
- adjustment_deductions (numeric(12,2) default 0.00)
- total_deductions (numeric(12,2) default 0.00)
- net_pay (numeric(12,2) default 0.00)
- payment_status (varchar: Unpaid, Paid, Hold)
- payment_reference (varchar nullable)
- remarks (text nullable)
- created_at, updated_at

Constraints:
- unique (payroll_run_id, employee_id)
- net_pay = gross_pay - total_deductions (enforced by logic; optional check constraint).

### E) payroll_adjustments (optional in v1 but recommended)
- id (uuid, pk)
- payroll_line_item_id (uuid fk payroll_line_items.id)
- adjustment_type (varchar: Earning, Deduction)
- category (varchar)
- amount (numeric(12,2))
- reason (text)
- created_by (uuid)
- created_at, updated_at

## 4.3 Suggested SQL migration script (initial)
Create `docs/migrations/create_payroll_tables.sql`:
- [ ] Create `payroll_periods`
- [ ] Create `employee_compensation`
- [ ] Create `payroll_runs`
- [ ] Create `payroll_line_items`
- [ ] Create `payroll_adjustments`
- [ ] Add indexes:
  - `idx_payroll_period_status`
  - `idx_payroll_run_period`
  - `idx_payroll_line_item_employee`
  - `idx_comp_employee_active`
- [ ] Add foreign keys with sensible `ON DELETE` behavior.
- [ ] Add enum/check constraints for statuses and non-negative amounts.

## 4.4 Seed data script
Create `docs/migrations/seed_payroll_defaults.sql`:
- [ ] Insert sample compensation profile rows for active employees (safe defaults).
- [ ] Insert initial payroll period for current month in Draft.

---

## 5) Configuration Updates

## 5.1 Supabase table mapping
Update `config/supabase.php` (or equivalent mapping section):
- [ ] Register new table names for payroll entities.

## 5.2 App config
Update `config/app.php` / env:
- [ ] Add payroll config block:
  - default frequency
  - default work hours/day
  - default overtime multiplier
  - default late penalty mode
- [ ] Add flags for enabling payroll module in UI (feature toggle optional).

## 5.3 Security config
Update `config/security.php` as needed:
- [ ] Add payroll-specific rate limits (compute/finalize endpoints).
- [ ] Add strict validation limits for money fields.

---

## 6) Backend Development Plan (Sequential)

## Phase 1: Models

- [ ] Create `src/Models/PayrollPeriod.php`
  - table, fillable, casts, validation
  - methods: `getByStatus`, `findOverlappingPeriod`, `getCurrentDraft`
- [ ] Create `src/Models/EmployeeCompensation.php`
  - methods: `getActiveByEmployeeAndDate`, `bulkUpsert`
- [ ] Create `src/Models/PayrollRun.php`
  - methods: `createRun`, `getByPeriod`, `finalize`, `markPaid`
- [ ] Create `src/Models/PayrollLineItem.php`
  - methods: `getByRun`, `upsertLineItem`, `getPayslipsByEmployee`
- [ ] Create `src/Models/PayrollAdjustment.php` (if included)

Model validation rules:
- [ ] UUID validation for all FK references.
- [ ] Decimal precision checks for all monetary fields.
- [ ] Non-negative constraints for deductions and computed totals.
- [ ] Status transitions validated against allowed state graph.

## Phase 2: Service Layer

- [ ] Create `src/Services/PayrollService.php`

Core methods:
- [ ] `createPayrollPeriod(array $data): array`
- [ ] `listPayrollPeriods(array $filters): array`
- [ ] `generatePayrollRun(string $periodId, array $options): array`
- [ ] `recomputePayrollLine(string $runId, string $employeeId): array`
- [ ] `applyManualAdjustment(string $lineItemId, array $payload): array`
- [ ] `finalizePayrollRun(string $runId, string $actorId): array`
- [ ] `markPayrollAsPaid(string $runId, array $paymentData, string $actorId): array`
- [ ] `getRunSummary(string $runId): array`
- [ ] `getEmployeePayslips(string $employeeId, array $filters): array`
- [ ] `getEmployeePayslipDetail(string $employeeId, string $lineItemId): array`

Business logic checklist:
- [ ] Resolve active compensation profile for each employee in period.
- [ ] Pull attendance records by date range from `attendance`.
- [ ] Incorporate leave status from approved leave/attendance "On Leave".
- [ ] Compute basic pay by payroll type:
  - Monthly: prorate by working days/hours policy
  - Daily: days × daily_rate
  - Hourly: hours × hourly_rate
- [ ] Compute overtime pay (if enabled) with multiplier.
- [ ] Compute lateness/undertime deductions.
- [ ] Add allowance/earning adjustments.
- [ ] Compute statutory and configured deductions.
- [ ] Compute gross, total deductions, and net.
- [ ] Round consistently at 2 decimals at final step.
- [ ] Aggregate run totals and employee count.

State transition rules:
- [ ] Draft period -> Processing -> Finalized -> Paid
- [ ] Block recompute after finalized unless explicit rollback endpoint.
- [ ] Block paid mark when run is not finalized.

## Phase 3: Controller Layer

- [ ] Create `src/Controllers/PayrollController.php`
- [ ] Wire constructor with DI container resolution pattern.
- [ ] Apply auth/role checks:
  - admin-only for period/run management
  - employee scope for personal payslip endpoints

Controller methods:
- [ ] `indexView` (optional payroll web page)
- [ ] `createPeriod`
- [ ] `listPeriods`
- [ ] `generateRun`
- [ ] `getRun`
- [ ] `finalizeRun`
- [ ] `markRunPaid`
- [ ] `updateLineItem` (adjustments/remarks)
- [ ] `employeePayslips`
- [ ] `employeePayslipDetail`

Response contract pattern:
- [ ] success: `{ success: true, message, data }`
- [ ] error: `{ success: false, message, errors? }`
- [ ] validation error uses existing `validationError(...)`.

## Phase 4: Routes

Update `config/routes.php`:

Web routes:
- [ ] `GET /payroll` -> `PayrollController@indexView` (admin)
- [ ] `GET /payslips` -> employee payslip page (optional route/controller method)

REST API routes:
- [ ] `POST /api/payroll/periods`
- [ ] `GET /api/payroll/periods`
- [ ] `POST /api/payroll/runs/generate`
- [ ] `GET /api/payroll/runs/{id}`
- [ ] `PUT /api/payroll/runs/{id}/finalize`
- [ ] `PUT /api/payroll/runs/{id}/pay`
- [ ] `PUT /api/payroll/line-items/{id}`
- [ ] `GET /api/payroll/payslips` (employee own list)
- [ ] `GET /api/payroll/payslips/{id}` (employee own detail)

Optional legacy compatibility:
- [ ] Add `.php` alias routes for parity if required by existing frontend conventions.

Middleware:
- [ ] `logging`, `auth`, and `role:admin` for management endpoints.
- [ ] `logging`, `auth` for employee payslip endpoints with ownership check in controller/service.

---

## 7) API Specification

## 7.1 Create payroll period
`POST /api/payroll/periods`

Request:
```json
{
  "code": "2026-04",
  "start_date": "2026-04-01",
  "end_date": "2026-04-30",
  "pay_date": "2026-05-05"
}
```

Success response:
```json
{
  "success": true,
  "message": "Payroll period created",
  "data": {
    "period": {
      "id": "uuid",
      "code": "2026-04",
      "status": "Draft"
    }
  }
}
```

## 7.2 Generate payroll run
`POST /api/payroll/runs/generate`

Request:
```json
{
  "payroll_period_id": "uuid",
  "include_overtime": true,
  "employee_ids": ["uuid1", "uuid2"]
}
```

Success response includes run summary and first-page line items.

## 7.3 Finalize payroll run
`PUT /api/payroll/runs/{id}/finalize`

Checks:
- run exists
- run status is `Computed` or `Draft` after computation
- no negative net pay unless explicitly allowed flag

## 7.4 Mark payroll paid
`PUT /api/payroll/runs/{id}/pay`

Request:
```json
{
  "payment_date": "2026-05-05",
  "payment_reference": "BATCH-2026-05-05-001"
}
```

## 7.5 Employee payslip list/detail
- `GET /api/payroll/payslips?year=2026&month=04`
- `GET /api/payroll/payslips/{line_item_id}`

Ownership enforcement:
- employee can only access own line items.
- admin can view all (optional admin query endpoint).

---

## 8) Validation Rules

Input validation:
- [ ] Dates must be `Y-m-d`.
- [ ] `start_date <= end_date`.
- [ ] `pay_date >= end_date`.
- [ ] Decimal fields: max 2 fraction digits, non-negative unless explicit signed adjustment.
- [ ] UUID format for all IDs.
- [ ] Status updates only through allowed transitions.

Domain validation:
- [ ] Employee must be active to be included.
- [ ] Compensation profile must exist and be effective for period.
- [ ] Prevent duplicate run generation for same period unless run_number increment policy.
- [ ] Prevent payment on non-finalized run.

---

## 9) Error Handling Strategy

Error classes to use consistently:
- [ ] `ValidationException` -> 422
- [ ] `NotFoundException` -> 404
- [ ] `AuthorizationException` -> 403
- [ ] `AuthenticationException` -> 401
- [ ] Generic exception -> 500 with safe message

Error response format:
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "field_name": "error detail"
  }
}
```

Operational safeguards:
- [ ] Wrap run generation/finalization in DB transaction boundaries.
- [ ] Add idempotency guard for generate/finalize endpoints (request token or run status check).
- [ ] Log full context in server logs without exposing sensitive values to client.

---

## 10) Security Considerations

- [ ] Enforce role checks on all payroll admin endpoints.
- [ ] Enforce employee ownership on payslip APIs.
- [ ] Never expose compensation internals not needed by employee.
- [ ] Rate-limit heavy endpoints: generate/finalize/pay.
- [ ] Validate and sanitize all text inputs (remarks/reason/reference).
- [ ] Ensure no secrets in logs.
- [ ] If Supabase RLS is enabled, add payroll RLS policies before go-live.
- [ ] Add CSRF protection on any form-based web route if applicable.

---

## 11) Testing Plan

## 11.1 Unit tests
- [ ] Payroll computation scenarios:
  - monthly, daily, hourly
  - no attendance
  - with overtime
  - with late/undertime
  - with leave days
  - with manual adjustments
- [ ] Status transition tests:
  - valid transitions
  - invalid transitions rejected
- [ ] Validation tests for request payloads.

## 11.2 Integration tests
- [ ] API create period -> generate run -> finalize -> pay happy path.
- [ ] Duplicate period code handling.
- [ ] Recompute blocked after finalize.
- [ ] Employee can read own payslip only.
- [ ] Admin can list/view full run details.
- [ ] Error propagation and response contract.

## 11.3 Data integrity tests
- [ ] Sum of line-item gross equals run total_gross.
- [ ] Sum of line-item deductions equals run total_deductions.
- [ ] Sum of line-item net equals run total_net.
- [ ] Unique constraints and FK constraints enforced.

## 11.4 Performance tests
- [ ] Generate run for N employees (define target N) under acceptable response time.
- [ ] Ensure indexes are used on period/run/employee lookups.

---

## 12) Deployment Plan

## 12.1 Pre-deployment
- [ ] Backup database snapshot.
- [ ] Validate migration scripts in staging.
- [ ] Prepare rollback SQL scripts:
  - drop newly created tables/indexes (if needed)
  - revert route/controller deployments
- [ ] Confirm env/config entries exist in production.

## 12.2 Deployment steps (sequence)
- [ ] Deploy migration SQL files to production database.
- [ ] Deploy backend code (models/services/controllers/routes).
- [ ] Clear route/config cache (if enabled by project scripts).
- [ ] Run smoke tests for payroll endpoints.
- [ ] Enable UI links/menu items only after API smoke tests pass.

## 12.3 Post-deployment validation
- [ ] Create sample period in production (or staging-like prod shadow).
- [ ] Generate test payroll run for restricted test users.
- [ ] Verify payslip visibility by employee account.
- [ ] Verify audit logs for create/generate/finalize/pay actions.
- [ ] Monitor error logs and API latency.

## 12.4 Rollback strategy
- [ ] Disable payroll routes via feature toggle or route rollback commit.
- [ ] Revert deployment artifact.
- [ ] Execute rollback SQL only if absolutely required and data-safe.
- [ ] Restore DB snapshot if irreversible corruption occurs.

---

## 13) Implementation Checklist by File

Database / docs:
- [ ] `docs/migrations/create_payroll_tables.sql`
- [ ] `docs/migrations/seed_payroll_defaults.sql`
- [ ] `docs/database-schema.sql` update with payroll entities

Config:
- [ ] `config/supabase.php`
- [ ] `config/app.php` / `.env` additions
- [ ] `config/routes.php`

Backend:
- [ ] `src/Models/PayrollPeriod.php`
- [ ] `src/Models/EmployeeCompensation.php`
- [ ] `src/Models/PayrollRun.php`
- [ ] `src/Models/PayrollLineItem.php`
- [ ] `src/Models/PayrollAdjustment.php` (optional)
- [ ] `src/Services/PayrollService.php`
- [ ] `src/Controllers/PayrollController.php`

Frontend (optional v1 screens):
- [ ] `src/Views/payroll/index.php` (admin)
- [ ] `src/Views/payroll/payslips.php` (employee)
- [ ] Add nav links for admin and employee dashboards.

Tests:
- [ ] `tests/Unit/PayrollServiceTest.php`
- [ ] `tests/Integration/PayrollApiIntegrationTest.php`
- [ ] Add/update smoke test script for payroll endpoints.

---

## 14) Suggested Execution Order (Granular)

1. Finalize business rules and field definitions.
2. Draft SQL schema and constraints.
3. Review schema with stakeholders.
4. Create migration SQL files.
5. Apply migrations in local environment.
6. Add table mappings/config.
7. Implement models with validation.
8. Implement `PayrollService` calculation methods.
9. Implement `PayrollController`.
10. Register routes + middleware.
11. Add API request/response tests.
12. Add UI pages/hooks (if included in phase).
13. Perform end-to-end test run.
14. Run performance checks and optimize indexes.
15. Prepare deployment and rollback scripts.
16. Deploy to staging, validate.
17. Deploy to production, monitor.

---

## 15) Definition of Done (DoD)

Payroll basic feature is considered complete when:
- [ ] All migration scripts are applied and validated.
- [ ] All payroll endpoints are implemented and secured.
- [ ] Computation logic passes unit and integration tests.
- [ ] Employees can view own payslips.
- [ ] Admin can create period, generate run, finalize, and mark paid.
- [ ] Logs/audit entries are recorded for critical payroll actions.
- [ ] Deployment and rollback procedures are documented and tested.

---

## 16) Production Architecture (Target State)

## 16.1 Core modules
- Payroll Domain:
  - Period management
  - Run generation and recomputation
  - Line-item calculations
  - Finalization and payment status
- Employee Data Domain:
  - Employee master profile, employment status, department, location
  - Compensation profile (effective-dated)
  - Tax profile and filing status
  - Benefits enrollments and deductions
- Compliance Domain:
  - Federal/state/local withholding calculation pipeline
  - Employer contribution calculations
  - Filing/export generation and validation logs
- Document Domain:
  - Pay stub generation and secure delivery
  - Historical archive with immutable snapshots

## 16.2 Layered flow
1. API/request validation and authorization.
2. Payroll orchestration service and domain rules.
3. Calculation engine with rule versioning and audit context.
4. Persistence layer (Supabase/PostgreSQL) with transaction boundaries.
5. Event/audit publishing and downstream integration dispatch.

## 16.3 Integration points
- HRIS integration:
  - Employee profile sync (hire/termination/role/location changes)
  - Attendance and leave approved records
  - Position/grade and compensation plan changes
- Accounting integration:
  - Journal entry export per payroll run
  - Cost center and department allocations
  - Liability accounts for taxes and benefits
  - Payment reconciliation status return
- Optional external integrations:
  - Banking/disbursement files
  - Tax filing providers
  - Timekeeping providers

---

## 17) Functional Requirements (Production Scope)

## 17.1 Employee data management
- [ ] Effective-dated compensation with historical traceability.
- [ ] Mid-cycle hire/termination support with automatic proration.
- [ ] Employment type support: regular, probationary, contractual, part-time.
- [ ] Multi-location and multi-tax-jurisdiction support.
- [ ] Validation of mandatory payroll profile fields before inclusion in run.

## 17.2 Salary and earnings calculations
- [ ] Payroll frequency support: monthly, semi-monthly, bi-weekly, weekly.
- [ ] Proration rules for partial periods (new hire, transfer, termination).
- [ ] Overtime, shift differential, holiday, night differential support.
- [ ] Manual earnings adjustments with maker-checker approval flow.
- [ ] Rule versioning so historical runs use prior approved rules.

## 17.3 Deductions and benefits administration
- [ ] Statutory deductions (federal/state/local taxes, social programs).
- [ ] Employer share calculations and liabilities.
- [ ] Pre-tax and post-tax benefit deductions.
- [ ] Loan, garnishment, and recurring deduction schedules.
- [ ] Deduction priority and cap rules to prevent negative net pay.

## 17.4 Pay stub and employee self-service
- [ ] Payslip generation (PDF/HTML) with payroll-period and YTD metrics.
- [ ] Employee access controls for own records only.
- [ ] Download watermarking and document access audit logging.
- [ ] Reissued/corrected stub version tracking.

## 17.5 Compliance reporting
- [ ] Federal/state/local withholding summaries.
- [ ] Employer liabilities and remittance schedules.
- [ ] Year-end forms and payroll register reports.
- [ ] Adjustment and amendment reporting for corrected runs.

---

## 18) Database Schema Additions for Production Readiness

In addition to Phase 1 tables, introduce:
- `employee_tax_profiles` (jurisdiction, filing status, allowances, exemptions, effective dates)
- `benefit_plans` (plan type, tax treatment, employer/employee sharing rules)
- `employee_benefit_enrollments` (employee enrollment with effective dates)
- `payroll_tax_transactions` (line-item tax breakdown by authority/rule version)
- `payroll_journal_entries` (accounting export payload and posting status)
- `pay_stub_documents` (file reference/hash/version/access metadata)
- `compliance_filing_batches` (filing period, status, submission reference)
- `payroll_error_events` (error category, payload snapshot, retry state)

Mandatory constraints and indexes:
- [ ] Unique constraints on effective-date overlaps per employee/profile type.
- [ ] Composite indexes for high-frequency filters:
  - `(payroll_run_id, employee_id)`
  - `(employee_id, effective_start_date, effective_end_date)`
  - `(jurisdiction_code, payroll_run_id)`
- [ ] Idempotency keys for generate/finalize/pay operations.

---

## 19) API Surface (Production)

## 19.1 Payroll operations
- `POST /api/payroll/periods`
- `GET /api/payroll/periods`
- `POST /api/payroll/runs/generate`
- `POST /api/payroll/runs/{id}/recompute`
- `PUT /api/payroll/runs/{id}/finalize`
- `PUT /api/payroll/runs/{id}/approve`
- `PUT /api/payroll/runs/{id}/pay`
- `POST /api/payroll/runs/{id}/reverse`

## 19.2 Employee and adjustment operations
- `GET /api/payroll/payslips`
- `GET /api/payroll/payslips/{id}`
- `PUT /api/payroll/line-items/{id}`
- `POST /api/payroll/line-items/{id}/adjustments`
- `POST /api/payroll/line-items/{id}/approve-adjustment`

## 19.3 Compliance and accounting
- `GET /api/payroll/compliance/reports?period_id={id}&jurisdiction={code}`
- `POST /api/payroll/compliance/validate`
- `POST /api/payroll/accounting/export-journal`
- `POST /api/payroll/accounting/posting-status`

API standards:
- [ ] Idempotency-Key header for mutation endpoints.
- [ ] Correlation-Id header propagated to logs and audit.
- [ ] Strict pagination for list endpoints.

---

## 20) Security, Audit, and Controls

## 20.1 Security protocols
- [ ] RBAC with least privilege: payroll admin, payroll approver, finance auditor, employee.
- [ ] MFA required for finalize/pay/reverse endpoints.
- [ ] Data encryption in transit and at rest.
- [ ] PII masking in logs and non-production datasets.
- [ ] Secret rotation policy for service keys and integration credentials.

## 20.2 Audit trail requirements
- [ ] Log who/what/when/where for all create/update/finalize/pay/reverse actions.
- [ ] Store before/after snapshots for compensation and adjustment changes.
- [ ] Immutable event entries for finalized payroll runs.
- [ ] Link audit event IDs to request Correlation-Id.

## 20.3 Segregation of duties
- [ ] Maker-checker approval for manual adjustments above threshold.
- [ ] Separate role required for final approval vs run generation.
- [ ] Emergency override actions require reason and secondary approval.

---

## 21) Compliance Validation Framework

## 21.1 Regulatory scope
- [ ] Federal tax withholding rules.
- [ ] State tax withholding and unemployment obligations.
- [ ] Local/municipal payroll tax obligations.
- [ ] Statutory benefits and remittance compliance.

## 21.2 Validation strategy
- [ ] Version every compliance rule with effective dates.
- [ ] Pre-run validation gate blocks run if mandatory compliance data missing.
- [ ] Post-calc validation compares expected vs calculated liabilities.
- [ ] Reconciliation report for tax/benefit totals before finalization.
- [ ] Automated alerts for threshold breaches or missing jurisdiction mappings.

## 21.3 Audit-ready evidence
- [ ] Rule version used per employee line item.
- [ ] Input source references (employee profile, compensation, attendance, benefits).
- [ ] Signed export artifacts and checksum/hashes for submitted files.

---

## 22) Performance and Scalability Benchmarks

Target benchmark for production payroll processing:
- [ ] 10,000+ employees processed per run in <= 15 minutes (P95).
- [ ] Recompute single employee line item in <= 2 seconds (P95).
- [ ] Payslip list endpoint response <= 500ms (P95) with pagination.
- [ ] Finalize operation <= 60 seconds after successful validations.

Scalability controls:
- [ ] Batch processing in chunks (e.g., 500 employees/batch).
- [ ] Queue-based worker execution with retry policy.
- [ ] Database indexing and query plan review before go-live.
- [ ] Warm caches for tax and benefits rules by jurisdiction.

Operational error handling for edge cases:
- [ ] Mid-cycle hires: prorated basic and benefits from hire date.
- [ ] Mid-cycle terminations: last-day proration and final pay checks.
- [ ] Retroactive compensation changes: adjustment-only rerun path.
- [ ] Missing attendance: configurable fallback policy and exception queue.
- [ ] Negative net scenarios: deduction capping and carry-forward rules.

---

## 23) Sprint Timeline and Resource Allocation

Assume 2-week sprints and cross-functional team.

Sprint milestones:
- Sprint 1: finalized requirements, compliance matrix, architecture decision records.
- Sprint 2: database extensions, migration hardening, baseline model/services.
- Sprint 3: calculation engine v1 (earnings, deductions, taxes, benefits).
- Sprint 4: API endpoints, security middleware, audit trail integration.
- Sprint 5: pay stub generation, compliance reporting, accounting export.
- Sprint 6: performance tuning for 10,000+ employees and resiliency hardening.
- Sprint 7: UAT fixes, pilot rollout, training execution.
- Sprint 8: phased production rollout and hypercare monitoring.

Resource allocation (minimum):
- Product/Domain: 1 product owner + 1 payroll SME + 1 compliance SME.
- Engineering: 1 tech lead, 3 backend engineers, 1 frontend engineer.
- Quality: 2 QA engineers (automation + UAT facilitation).
- Operations: 1 DevOps/SRE + 1 support lead.
- Finance/HR stakeholders: approvers for UAT and go-live.

---

## 24) Testing Strategy (Unit, Integration, UAT)

## 24.1 Unit testing
- [ ] Rule-level tests for earnings, taxes, deductions, and benefits.
- [ ] Boundary tests for proration and rounding.
- [ ] Status transition and authorization tests.

## 24.2 Integration testing
- [ ] End-to-end period -> run -> finalize -> pay flow.
- [ ] HR sync integration and accounting export contract tests.
- [ ] Compliance validation pipeline with multi-jurisdiction fixtures.
- [ ] Failure-injection tests for external integration downtime.

## 24.3 UAT
- [ ] Payroll admin scenarios across regular and exception cases.
- [ ] Finance reconciliation and posting validation.
- [ ] Employee payslip self-service acceptance checks.
- [ ] Formal sign-off checklist per stakeholder group.

Exit criteria:
- [ ] Zero severity-1 defects open.
- [ ] All compliance critical tests passed.
- [ ] Performance benchmarks met in staging-like environment.

---

## 25) Rollout, Training, Monitoring, and Rollback

## 25.1 Phased rollout strategy
- Phase A: internal payroll team sandbox validation.
- Phase B: pilot group rollout (5-10% workforce).
- Phase C: controlled expansion to 30-50%.
- Phase D: full organization rollout after success criteria met.

Pilot group selection criteria:
- [ ] Representative mix: departments, locations, employment types.
- [ ] Include edge-case population (new hires, recent terminations, shift workers).
- [ ] Managers and finance partners available for fast feedback.
- [ ] Low business risk concentration for first-wave confidence.

## 25.2 Training documentation requirements
- [ ] Payroll admin runbook (create/generate/finalize/pay/reverse).
- [ ] Finance reconciliation guide (journal export/posting checks).
- [ ] Employee payslip access guide and FAQ.
- [ ] Incident response SOP for payroll processing failures.

## 25.3 Post-deployment monitoring
- [ ] Dashboards: run duration, failure rate, queue depth, API latency.
- [ ] Business metrics: reconciliation variance, correction rate, off-cycle count.
- [ ] Alerting: failed finalization, compliance mismatch, export rejection.
- [ ] Daily hypercare review for first 2 payroll cycles.

## 25.4 Rollback procedures (expanded)
- [ ] Feature flag to disable payroll write operations immediately.
- [ ] Revert application artifact to previous stable release.
- [ ] Stop outbound accounting/tax file dispatch.
- [ ] Restore database snapshot if data integrity is compromised.
- [ ] Execute documented communication protocol to payroll stakeholders.

---

## 26) Actual Implemented Payroll Flow (Current Code)

This section reflects the live backend flow currently implemented in code.

## 26.1 Core lifecycle
1. Admin creates a payroll period.
   - Endpoint: `POST /api/payroll/periods`
   - Validates required fields and overlapping periods.
   - Requires `Idempotency-Key`.
   - Initial period status: `Draft`.
2. Admin generates a payroll run for a period.
   - Endpoint: `POST /api/payroll/runs/generate`
   - Requires `payroll_period_id`.
   - Requires `Idempotency-Key`.
   - Creates run as `Draft`, computes line items from active employees + compensation + attendance, then updates run to `Computed`.
   - Period status moves to `Processing`.
3. Admin optionally recomputes a single employee line in a run.
   - Endpoint: `POST /api/payroll/runs/{id}/recompute`
   - Requires `employee_id` and `Idempotency-Key`.
   - Rebuilds employee line item and refreshes run totals.
   - Recompute is blocked once run is `Finalized`, `Approved`, or `Paid`.
4. Admin finalizes the run.
   - Endpoint: `PUT /api/payroll/runs/{id}/finalize`
   - Requires `Idempotency-Key`.
   - Allowed from `Draft` or `Computed`.
   - Run status becomes `Finalized`; period status becomes `Finalized`.
5. Admin approves the finalized run.
   - Endpoint: `PUT /api/payroll/runs/{id}/approve`
   - Requires `Idempotency-Key`.
   - Allowed only from `Finalized`.
   - Run status becomes `Approved`.
6. Admin marks payroll as paid.
   - Endpoint: `PUT /api/payroll/runs/{id}/pay`
   - Requires `Idempotency-Key`.
   - Allowed from `Finalized` or `Approved`.
   - Run status becomes `Paid`; period status becomes `Paid`.
7. Admin can reverse a computed/finalized/approved/paid run when needed.
   - Endpoint: `POST /api/payroll/runs/{id}/reverse`
   - Requires `Idempotency-Key` and `reason`.
   - Run status becomes `Reversed`.
   - Period status is set back to `Processing`.

## 26.2 Adjustment and payslip flow
1. Admin applies manual earning or deduction adjustments on a line item.
   - Endpoint: `PUT /api/payroll/line-items/{id}`
   - Requires `Idempotency-Key`.
   - Creates adjustment record, recalculates gross/deductions/net, then refreshes run totals.
2. Employee views own payslip list.
   - Endpoint: `GET /api/payroll/payslips`
   - Supports `year`, `month`, `limit`, `offset`.
3. Employee views own payslip details.
   - Endpoint: `GET /api/payroll/payslips/{id}`
   - Ownership is enforced in service layer.

## 26.3 Data and control behavior
- Mutation endpoints above enforce idempotency replay/in-progress protection.
- Service-level file locks protect critical operations from concurrent collisions.
- Activity logging is recorded for create/generate/recompute/finalize/approve/pay/reverse/adjustment actions.
- Current run status values in model validation: `Draft`, `Computed`, `Finalized`, `Approved`, `Paid`, `Reversed`.

## 26.4 API example payloads per step

Use these request examples as a ready-to-run sequence for Postman/cURL.

Common headers for mutation endpoints:
- `Authorization: Bearer <JWT_TOKEN>`
- `Content-Type: application/json`
- `Idempotency-Key: <UNIQUE_KEY_PER_REQUEST>`

### A) Create payroll period
`POST /api/payroll/periods`

Request body:
```json
{
  "code": "2026-05",
  "start_date": "2026-05-01",
  "end_date": "2026-05-31",
  "pay_date": "2026-06-05"
}
```

### B) Generate payroll run
`POST /api/payroll/runs/generate`

Request body:
```json
{
  "payroll_period_id": "period-uuid",
  "include_overtime": true,
  "employee_ids": ["emp-uuid-1", "emp-uuid-2"]
}
```

### C) Recompute single employee line
`POST /api/payroll/runs/{id}/recompute`

Request body:
```json
{
  "employee_id": "emp-uuid-1"
}
```

### D) Finalize run
`PUT /api/payroll/runs/{id}/finalize`

Request body:
```json
{}
```

### E) Approve run
`PUT /api/payroll/runs/{id}/approve`

Request body:
```json
{}
```

### F) Mark run as paid
`PUT /api/payroll/runs/{id}/pay`

Request body:
```json
{
  "payment_date": "2026-06-05",
  "payment_reference": "BATCH-2026-06-05-001"
}
```

### G) Reverse run
`POST /api/payroll/runs/{id}/reverse`

Request body:
```json
{
  "reason": "Payroll correction requested by finance"
}
```

### H) Apply manual line-item adjustment
`PUT /api/payroll/line-items/{id}`

Request body:
```json
{
  "adjustment_type": "Deduction",
  "category": "Late Penalty",
  "amount": 150,
  "reason": "Manual attendance correction"
}
```

### I) List payslips (employee)
`GET /api/payroll/payslips?year=2026&month=05&limit=20&offset=0`

### J) Payslip detail (employee)
`GET /api/payroll/payslips/{line_item_id}`
