# GitHub Actions CI/CD

## What This Does

This repository uses GitHub Actions for Continuous Integration (CI). Every time you push code or create a pull request, GitHub automatically runs quality checks.

## Checks Performed

### 1. **PHPStan Static Analysis (Level 5)**
- Checks for type errors
- Finds undefined variables
- Detects null pointer issues
- Catches potential bugs before production

### 2. **PHP Syntax Check**
- Validates all PHP files have correct syntax
- Prevents syntax errors from reaching production

### 3. **Code Quality Checks**
- Detects debug statements (var_dump, print_r)
- Warns about die() and exit() usage
- Ensures clean production code

## How to View Results

1. Go to your repository on GitHub
2. Click the **"Actions"** tab
3. See the status of all workflow runs:
   - ✅ Green checkmark = All tests passed
   - ❌ Red X = Something failed (click to see details)
   - ⚫ Yellow dot = Currently running

## When Does It Run?

The CI runs automatically on:
- Every push to `main`, `develop`, or `feature/*` branches
- Every pull request to `main` or `develop`

## Local Testing

Before pushing, you can run the same checks locally:

```bash
# Run PHPStan
php phpstan.phar analyze --level=5 src

# Check PHP syntax
find src -name "*.php" -exec php -l {} \;

# Check for var_dump
grep -r "var_dump" src/ --include="*.php"
```

## Free Tier Limits

GitHub Actions is **100% FREE** for public repositories!

For private repositories:
- 2,000 minutes/month free
- Each workflow run takes ~2-3 minutes
- That's ~600-1000 runs per month for free

## Troubleshooting

### If CI Fails:

1. **Click on the failed workflow** to see details
2. **Read the error message** - it will tell you exactly what's wrong
3. **Fix the issue locally** and test
4. **Push again** - CI will run automatically

### Common Issues:

**PHPStan errors:**
- Fix type hints
- Add proper return types
- Handle null values properly

**Syntax errors:**
- Check for missing semicolons
- Check for unclosed brackets
- Run `php -l filename.php` locally

**Debug statements:**
- Remove all `var_dump()` calls
- Remove `print_r()` calls
- Use proper logging instead

## Benefits

✅ **Catch bugs early** - Before they reach production  
✅ **Code quality** - Maintain high standards automatically  
✅ **Team collaboration** - Everyone's code is checked consistently  
✅ **Professional** - Shows you care about code quality  
✅ **Free** - No cost for public repos  

## Badge (Optional)

Add this to your README.md to show CI status:

```markdown
![CI](https://github.com/YOUR_USERNAME/HRIS/workflows/CI/badge.svg)
```

Replace `YOUR_USERNAME` with your GitHub username.
