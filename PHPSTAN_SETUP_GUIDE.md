# 🔍 PHPStan Static Analysis Setup Guide

## What is PHPStan?

PHPStan is a **FREE** static analysis tool that finds bugs in your PHP code WITHOUT running it. It's like having an extra pair of eyes that catches:

- ✅ Undefined variables
- ✅ Wrong return types
- ✅ Null pointer issues
- ✅ Type mismatches
- ✅ Dead code
- ✅ Unreachable code
- ✅ Missing properties
- ✅ And much more!

### Benefits:
- **FREE** - No cost, runs locally
- **Fast** - Analyzes entire codebase in seconds
- **No runtime overhead** - Runs before deployment
- **Catches bugs early** - Before they reach production

---

## Installation (3 methods)

### Method 1: Using Composer (Recommended)

```bash
composer require --dev phpstan/phpstan
```

Then run:
```bash
vendor/bin/phpstan analyze
```

### Method 2: Download PHAR (No Composer)

```bash
# Download PHPStan
wget https://github.com/phpstan/phpstan/releases/latest/download/phpstan.phar

# Make it executable
chmod +x phpstan.phar

# Run analysis
php phpstan.phar analyze
```

### Method 3: Use Docker (Isolated)

```bash
docker run --rm -v $(pwd):/app phpstan/phpstan analyze
```

---

## Quick Start

### 1. Run Your First Analysis

```bash
# If installed via Composer:
vendor/bin/phpstan analyze

# If using PHAR:
php phpstan.phar analyze

# If using Docker:
docker run --rm -v $(pwd):/app phpstan/phpstan analyze
```

### 2. Review Results

PHPStan will show you a list of issues found:

```
------ ---------------------------------------------------------------
 Line   src/Services/PayrollService.php
------ ---------------------------------------------------------------
 150    Variable $compensation might not be defined.
 200    Method generatePayrollRun() should return array but returns null.
 250    Call to an undefined method Model::getByPosition().
------ ---------------------------------------------------------------
```

### 3. Fix Issues

Go through each issue and fix it. Common fixes:

```php
// BEFORE (PHPStan error: Variable might not be defined)
if ($condition) {
    $result = doSomething();
}
return $result; // ❌ $result might not be defined!

// AFTER (Fixed)
$result = null;
if ($condition) {
    $result = doSomething();
}
return $result; // ✅ $result is always defined
```

---

## Configuration

We've already created `phpstan.neon` with sensible defaults:

```neon
parameters:
    level: 5  # Good balance of strictness
    paths:
        - src
    excludePaths:
        - src/Views  # Skip view templates
```

### Analysis Levels (0-9)

- **Level 0**: Basic checks (undefined variables)
- **Level 5**: Recommended (good balance) ← **WE START HERE**
- **Level 9**: Maximum strictness (very strict!)

You can change the level in `phpstan.neon`:

```neon
parameters:
    level: 6  # Increase strictness
```

---

## Common Issues & Fixes

### Issue 1: "Variable might not be defined"

```php
// BAD
if ($condition) {
    $result = getValue();
}
return $result; // ❌ Might not be defined

// GOOD
$result = null;
if ($condition) {
    $result = getValue();
}
return $result; // ✅ Always defined
```

### Issue 2: "Method should return X but returns Y"

```php
// BAD
public function getUser(): array
{
    $user = $this->db->find($id);
    return $user; // ❌ Might return null
}

// GOOD
public function getUser(): ?array  // Note the ?
{
    $user = $this->db->find($id);
    return $user; // ✅ Nullable return type
}
```

### Issue 3: "Call to undefined method"

```php
// BAD
$model->someMethod(); // ❌ Method doesn't exist

// GOOD - Add the method or use correct method name
$model->existingMethod(); // ✅ Method exists
```

### Issue 4: "Property might not be initialized"

```php
// BAD
class MyClass {
    private string $name; // ❌ Not initialized
}

// GOOD
class MyClass {
    private string $name = ''; // ✅ Initialized
    // OR
    public function __construct(string $name) {
        $this->name = $name; // ✅ Initialized in constructor
    }
}
```

---

## Ignoring Specific Errors

Sometimes you need to ignore false positives. Add to `phpstan.neon`:

```neon
parameters:
    ignoreErrors:
        # Ignore specific error message
        - '#Variable \$myVar might not be defined#'
        
        # Ignore errors in specific file
        - '#.*#'
          path: src/Legacy/OldCode.php
```

**Use sparingly!** It's better to fix the issue than ignore it.

---

## Integration with CI/CD

### GitHub Actions

Create `.github/workflows/phpstan.yml`:

```yaml
name: PHPStan

on: [push, pull_request]

jobs:
  phpstan:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v3
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.2'
    
    - name: Download PHPStan
      run: wget https://github.com/phpstan/phpstan/releases/latest/download/phpstan.phar
    
    - name: Run PHPStan
      run: php phpstan.phar analyze --error-format=github
```

### Pre-commit Hook

Create `.git/hooks/pre-commit`:

```bash
#!/bin/bash
php phpstan.phar analyze --no-progress --error-format=raw
if [ $? -ne 0 ]; then
    echo "PHPStan found errors. Commit aborted."
    exit 1
fi
```

Make it executable:
```bash
chmod +x .git/hooks/pre-commit
```

---

## Tips & Best Practices

### 1. Start with Level 5
Don't jump to level 9 immediately. Start at level 5 and gradually increase.

### 2. Fix Issues Incrementally
Don't try to fix everything at once. Fix one file at a time.

### 3. Use Baseline for Legacy Code
If you have lots of existing issues:

```bash
php phpstan.phar analyze --generate-baseline
```

This creates `phpstan-baseline.neon` that ignores existing issues. New issues will still be caught!

### 4. Run Before Committing
Make it a habit to run PHPStan before committing code.

### 5. Add Type Hints
PHPStan works better with type hints:

```php
// BETTER
public function getUser(string $id): ?array
{
    return $this->db->find($id);
}

// vs WORSE
public function getUser($id)
{
    return $this->db->find($id);
}
```

---

## Performance Tips

### Parallel Processing
PHPStan can use multiple CPU cores:

```neon
parameters:
    parallel:
        maximumNumberOfProcesses: 4
```

### Memory Limit
Increase if analysis fails:

```neon
parameters:
    memoryLimit: 1G
```

### Cache Results
PHPStan caches results for faster subsequent runs. Clear cache if needed:

```bash
php phpstan.phar clear-result-cache
```

---

## Troubleshooting

### "Class not found"

Add bootstrap file to `phpstan.neon`:

```neon
parameters:
    bootstrapFiles:
        - src/bootstrap.php
```

### "Out of memory"

Increase memory limit:

```bash
php -d memory_limit=1G phpstan.phar analyze
```

### "Too many errors"

Start with lower level:

```neon
parameters:
    level: 3  # Lower level
```

---

## What to Analyze

### ✅ DO Analyze:
- Controllers
- Services
- Models
- Middleware
- Core classes

### ❌ DON'T Analyze:
- View templates (mixed PHP/HTML)
- Third-party libraries
- Generated code
- Legacy code (use baseline instead)

---

## Expected Results

After running PHPStan on this codebase, you might see:

- **50-200 issues** initially (normal for existing code)
- **Common issues:**
  - Undefined variables
  - Missing return types
  - Nullable return values
  - Type mismatches

**Don't panic!** Fix them one by one. Each fix makes your code more robust.

---

## Cost & Resources

| Resource | Cost | Notes |
|----------|------|-------|
| PHPStan | **FREE** | Open source |
| Execution time | ~10-30 seconds | For ~36,000 lines of code |
| Memory | ~256MB | Configurable |
| Disk space | ~5MB | For PHAR file |

---

## Next Steps

1. ✅ Install PHPStan (choose method above)
2. ✅ Run first analysis: `php phpstan.phar analyze`
3. ✅ Review results
4. ✅ Fix high-priority issues first
5. ✅ Gradually increase level (5 → 6 → 7)
6. ✅ Add to CI/CD pipeline
7. ✅ Make it part of development workflow

---

## Resources

- PHPStan Docs: https://phpstan.org/user-guide/getting-started
- Rule Levels: https://phpstan.org/user-guide/rule-levels
- Config Reference: https://phpstan.org/config-reference
- GitHub: https://github.com/phpstan/phpstan

---

**🎉 That's it! You now have FREE static analysis for your HRIS system!**

PHPStan will catch bugs before they reach production, saving you time and headaches.
