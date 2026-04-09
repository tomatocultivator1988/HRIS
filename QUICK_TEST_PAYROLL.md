# Quick Test Payroll (Hiligaynon Guide)

## Problema: Nag-exist na ang payroll period

May duha ka solusyon:

### Solusyon 1: Gumamit sang bag-o nga code (Pinaka-simple)

Sa test page o simple payroll UI:
- Imbis nga `2026-04`, gamita ang `2026-06` o `JUNE-2026`
- Bag-o nga dates:
  - Start: `2026-06-01`
  - End: `2026-06-30`
  - Pay: `2026-07-05`

Pero kailangan mo man mag-add sang attendance para sa June:

```sql
-- Add attendance for June 2026
INSERT INTO attendance (employee_id, date, time_in, time_out, status, work_hours, remarks, created_at)
SELECT 
    e.id,
    d.date,
    d.date + TIME '08:00:00',
    d.date + TIME '17:00:00',
    'Present',
    8.00,
    'Sample attendance',
    NOW()
FROM employees e
CROSS JOIN (
    SELECT generate_series('2026-06-01'::date, '2026-06-30'::date, '1 day'::interval)::date AS date
) d
WHERE e.is_active = TRUE
  AND EXTRACT(DOW FROM d.date) NOT IN (0, 6);
```

### Solusyon 2: I-reset ang tanan (Para sa testing)

Kung gusto mo mag-start from scratch:

```bash
psql -U postgres -d your_database -f docs/migrations/reset_payroll_for_testing.sql
```

**WARNING**: Madelete ang TANAN nga payroll data! Gamita lang ini sa testing.

### Solusyon 3: I-delete lang ang specific period

```sql
-- Delete specific period and its related data
DELETE FROM payroll_line_items 
WHERE payroll_run_id IN (
    SELECT id FROM payroll_runs WHERE payroll_period_id = 'PERIOD_ID_HERE'
);

DELETE FROM payroll_runs WHERE payroll_period_id = 'PERIOD_ID_HERE';
DELETE FROM payroll_periods WHERE id = 'PERIOD_ID_HERE';

-- Or delete by code
DELETE FROM payroll_line_items 
WHERE payroll_run_id IN (
    SELECT id FROM payroll_runs 
    WHERE payroll_period_id IN (SELECT id FROM payroll_periods WHERE code = '2026-04')
);

DELETE FROM payroll_runs 
WHERE payroll_period_id IN (SELECT id FROM payroll_periods WHERE code = '2026-04');

DELETE FROM payroll_periods WHERE code = '2026-04';
```

## Pinaka-Simple nga Paagi

1. Buksan ang test page: `test_payroll_api.html`
2. Ilisan ang code sa `2026-06`
3. Ilisan ang dates sa June
4. Click "Create Period"
5. Pero wala pa attendance para sa June, so zero pa gihapon

## Para makita ang detailed breakdown

Kung nag-work na ang payroll (may attendance na):

1. Go to `/payroll/simple`
2. Generate run
3. View results - makita mo na:
   - Employee names
   - Days worked
   - Basic pay
   - Overtime
   - Gross pay
   - Tax breakdown
   - SSS, PhilHealth, PagIBIG
   - Total deductions
   - Net pay

Ang bag-o nga table may 11 columns na - detailed na gid!
