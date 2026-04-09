# Using Correct Philippine Deductions

## What I Created

### 1. PhilippineDeductionCalculator.php
A proper calculator that computes:
- **SSS**: Based on Monthly Salary Credit table (4.5%)
- **PhilHealth**: 2.5% of salary (max ₱2,500)
- **Pag-IBIG**: 2% of salary (max ₱100)
- **Withholding Tax**: TRAIN Law graduated rates

### 2. Test Script
See deductions for different salary levels:
```bash
php test_deduction_calculator.php
```

### 3. Update Script
Update all employees with correct deductions:
```bash
php update_correct_deductions.php
```

---

## Quick Test

```bash
php test_deduction_calculator.php
```

Output:
```
============================================================
Gross Salary: ₱30,000.00
SSS: ₱1,350.00
PhilHealth: ₱750.00
Pag-IBIG: ₱100.00
Withholding Tax: ₱1,045.05
Total Deductions: ₱3,245.05
Net Pay: ₱26,754.95
============================================================
```

---

## Update All Employees

```bash
php update_correct_deductions.php
```

This will:
1. Read all active employees
2. Calculate correct deductions based on their salary
3. Update the database
4. Show before/after comparison

---

## Comparison: Old vs New

### For ₱30,000 Salary:

**OLD (Wrong):**
```
SSS:        ₱  581.30  ❌
PhilHealth: ₱  450.00  ❌
Pag-IBIG:   ₱  100.00  ✓
Tax:        ₱2,500.00  ❌
Total:      ₱3,631.30
Net Pay:    ₱26,368.70
```

**NEW (Correct):**
```
SSS:        ₱1,350.00  ✓ (4.5% of MSC)
PhilHealth: ₱  750.00  ✓ (2.5% of salary)
Pag-IBIG:   ₱  100.00  ✓ (2% max)
Tax:        ₱1,045.05  ✓ (TRAIN law)
Total:      ₱3,245.05
Net Pay:    ₱26,754.95
```

**Difference**: Employee gets ₱386.25 MORE with correct deductions!

---

## How It Works

### SSS Calculation
```php
$calculator->calculateSSS(30000);
// Returns: ₱1,350.00 (based on MSC table)
```

### PhilHealth Calculation
```php
$calculator->calculatePhilHealth(30000);
// Returns: ₱750.00 (2.5% of ₱30,000)
```

### Pag-IBIG Calculation
```php
$calculator->calculatePagIBIG(30000);
// Returns: ₱100.00 (2% max)
```

### Tax Calculation
```php
$calculator->calculateWithholdingTax(30000, 1350, 750, 100);
// Taxable: ₱30,000 - ₱2,200 = ₱27,800
// Tax: (₱27,800 - ₱20,833) × 15% = ₱1,045.05
```

---

## Update for 2026 Rates

When 2026 official rates are released:

1. Open `src/Services/PhilippineDeductionCalculator.php`
2. Update the tables:
   - SSS contribution table
   - PhilHealth rate (currently 2.5%)
   - Pag-IBIG rate (currently 2%)
   - Tax brackets (TRAIN law)
3. Run `php update_correct_deductions.php` again

---

## Integration with Payroll

The calculator is ready to use. To integrate:

### Option 1: Update existing records
```bash
php update_correct_deductions.php
```

### Option 2: Use in PayrollService
```php
use Services\PhilippineDeductionCalculator;

$calculator = new PhilippineDeductionCalculator();
$deductions = $calculator->calculateAllDeductions($salary);

// Use $deductions['sss'], $deductions['tax'], etc.
```

---

## Notes

- **Current rates**: Based on 2024 (latest available)
- **2026 rates**: Update when officially released
- **Accuracy**: Follows official government tables
- **Testing**: Run test script to verify calculations

---

## Sources

- SSS: https://www.sss.gov.ph/
- PhilHealth: https://www.philhealth.gov.ph/
- Pag-IBIG: https://www.pagibigfund.gov.ph/
- BIR: TRAIN Law (RA 10963)
