# Philippine Payroll Deductions (2024 Rates)

## Current Deductions in the Seed Script (WRONG!)

```sql
tax_value = 2500.00                    ❌ Fixed amount (wrong!)
sss_employee_share = 581.30            ❌ Not based on salary
philhealth_employee_share = 450.00     ❌ Not based on salary
pagibig_employee_share = 100.00        ❌ Fixed amount
```

These are just placeholder values for testing!

---

## CORRECT Philippine Deduction Rates (2024)

### 1. SSS (Social Security System)

**Employee Share: 4.5% of Monthly Salary Credit (MSC)**

| Monthly Salary Range | MSC | Employee Share (4.5%) |
|---------------------|-----|----------------------|
| Below ₱4,250 | ₱4,000 | ₱180.00 |
| ₱4,250 - ₱4,749.99 | ₱4,500 | ₱202.50 |
| ₱4,750 - ₱5,249.99 | ₱5,000 | ₱225.00 |
| ... | ... | ... |
| ₱29,750 - ₱30,249.99 | ₱30,000 | ₱1,350.00 |
| ₱30,000+ | ₱30,000 | ₱1,350.00 (max) |

**For ₱30,000 salary: ₱1,350.00** (not ₱581.30!)

### 2. PhilHealth (Philippine Health Insurance)

**Employee Share: 5% of Monthly Basic Salary (2024 rate)**

| Monthly Salary | Premium Rate | Employee Share (50%) |
|---------------|--------------|---------------------|
| ₱10,000 or below | ₱500.00 | ₱250.00 |
| ₱10,000.01 - ₱99,999.99 | 5% of salary | 2.5% of salary |
| ₱100,000+ | ₱5,000.00 | ₱2,500.00 (max) |

**For ₱30,000 salary:**
- Total Premium: ₱30,000 × 5% = ₱1,500
- Employee Share: ₱1,500 ÷ 2 = ₱750.00 (not ₱450!)

### 3. Pag-IBIG (HDMF)

**Employee Share: 1-2% of Monthly Salary**

| Monthly Salary | Employee Rate | Employee Share |
|---------------|---------------|----------------|
| ₱1,500 or below | 1% | Min ₱15.00 |
| ₱1,500.01 - ₱4,999.99 | 2% | ₱30 - ₱100 |
| ₱5,000+ | 2% | Max ₱100.00 |

**For ₱30,000 salary: ₱100.00** ✓ (This one is correct!)

### 4. Withholding Tax (BIR)

**2024 TRAIN Law Tax Table (Monthly)**

| Taxable Income | Tax Rate |
|----------------|----------|
| ₱0 - ₱20,833 | 0% |
| ₱20,833 - ₱33,332 | 15% of excess over ₱20,833 |
| ₱33,333 - ₱66,666 | ₱1,875 + 20% of excess over ₱33,333 |
| ₱66,667 - ₱166,666 | ₱8,541.80 + 25% of excess over ₱66,667 |
| ₱166,667 - ₱666,666 | ₱33,541.80 + 30% of excess over ₱166,667 |
| Over ₱666,666 | ₱183,541.80 + 35% of excess |

**For ₱30,000 salary:**
- Taxable Income: ₱30,000 - ₱1,350 (SSS) - ₱750 (PhilHealth) - ₱100 (Pag-IBIG) = ₱27,800
- Tax: ₱27,800 falls in ₱20,833 - ₱33,332 bracket
- Tax = (₱27,800 - ₱20,833) × 15% = ₱1,045.05 (not ₱2,500!)

---

## CORRECT Deductions for ₱30,000 Salary

```
Gross Salary:              ₱30,000.00

Deductions:
├─ SSS (4.5%):            ₱ 1,350.00  ❌ Was ₱581.30
├─ PhilHealth (2.5%):     ₱   750.00  ❌ Was ₱450.00
├─ Pag-IBIG (2%):         ₱   100.00  ✓ Correct
└─ Withholding Tax:       ₱ 1,045.05  ❌ Was ₱2,500.00
                          ────────────
Total Deductions:         ₱ 3,245.05  (not ₱3,631.30!)

Net Pay:                  ₱26,754.95
```

---

## Why the Seed Script Has Wrong Values

The seed script uses **fixed placeholder values** for testing:

```sql
-- This is WRONG for production!
tax_value = 2500.00,
sss_employee_share = 581.30,
philhealth_employee_share = 450.00,
pagibig_employee_share = 100.00
```

These should be **calculated dynamically** based on:
1. Employee's actual salary
2. Current government rates
3. Tax brackets

---

## What You Should Do

### Option 1: Manual Entry (Current System)
Set correct values per employee in `employee_compensation` table:

```sql
UPDATE employee_compensation
SET 
    base_salary = 30000.00,
    tax_value = 1045.05,           -- Calculate based on tax table
    sss_employee_share = 1350.00,  -- 4.5% of MSC
    philhealth_employee_share = 750.00,  -- 2.5% of salary
    pagibig_employee_share = 100.00      -- 2% (max ₱100)
WHERE employee_id = 'employee-id-here';
```

### Option 2: Automatic Calculation (Recommended)
I can create a proper calculator that:
1. Computes SSS based on salary brackets
2. Computes PhilHealth as 2.5% of salary
3. Computes Pag-IBIG as 2% (max ₱100)
4. Computes tax based on TRAIN law brackets

---

## Sources (2024 Rates)

- **SSS**: https://www.sss.gov.ph/contributions/
- **PhilHealth**: https://www.philhealth.gov.ph/
- **Pag-IBIG**: https://www.pagibigfund.gov.ph/
- **BIR Tax**: TRAIN Law (RA 10963) - 2024 rates

---

## Do You Want Me To:

1. ✅ Create a proper deduction calculator?
2. ✅ Update the seed script with correct formulas?
3. ✅ Add automatic computation in PayrollService?

Let me know and I'll implement the correct Philippine deduction calculations!
