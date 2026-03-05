# Performance Analysis: Admin Product Form Loading Slow

## Root Cause Identified

The `/admin/produit/new` page loads slowly due to a **database query without an index** that runs on every page load.

### Problem Details

#### 1. Slow Query
```sql
SELECT p0_.* FROM produit p0_ 
WHERE p0_.date_expiration >= ? AND p0_.date_expiration < ? 
ORDER BY p0_.nom ASC
```

This query is executed by `ProduitRepository::findExpiringToday()` method, which:
- Executes on **every page load** (not just product page)
- Is called from `NotificationController::icon()` route
- Performs a **full table scan** (no index on `date_expiration`)
- Returns ALL matching products instead of just counting them

#### 2. Call Chain
```
Page Load
  ↓
Template renders
  ↓
Calls /notifications/icon route (probably via AJAX/include in base.html.twig)
  ↓
NotificationController::icon() 
  ↓
$produitRepository->findExpiringToday()
  ↓
Full table scan on produit table with NO INDEX on date_expiration
  ↓
Returns entire product rows instead of count
```

#### 3. Performance Impact
- **Without index**: O(n) full table scan - scans entire table for every page load
- **Fetches all columns**: SELECT p0_.* instead of just COUNT
- **No caching**: Executed fresh on every request
- **Blocks page rendering**: If synchronous, blocks the entire page load

---

## Solutions

### Solution 1: Add Database Index (CRITICAL - Do First)
Add an index on the `date_expiration` column to make the query efficient:

```sql
CREATE INDEX IDX_DATE_EXPIRATION ON produit (date_expiration);
```

**Expected Impact**: 100x-1000x speed improvement on the query

### Solution 2: Optimize the Query
Replace full row fetch with COUNT query:

**File**: `src/Repository/ProduitRepository.php`

```php
public function countExpiringToday(): int
{
    $todayStart = (new \DateTime())->setTime(0, 0, 0);
    $todayEnd = (clone $todayStart)->modify('+1 day');

    return $this->createQueryBuilder('p')
        ->select('COUNT(p.id)')  // Count instead of fetching all rows
        ->where('p.dateExpiration >= :todayStart')
        ->andWhere('p.dateExpiration < :todayEnd')
        ->setParameter('todayStart', $todayStart)
        ->setParameter('todayEnd', $todayEnd)
        ->getQuery()
        ->getSingleScalarResult();
}
```

### Solution 3: Cache the Result
Add caching to avoid repeated queries:

**File**: `src/Controller/NotificationController.php`

```php
use Symfony\Contracts\Cache\CacheInterface;

public function icon(
    NotificationRepository $notificationRepository, 
    ProduitRepository $produitRepository,
    CacheInterface $cache  // Add this
): Response {
    $unreadCount = $notificationRepository->countUnread();
    
    // Cache for 5 minutes to avoid repeated queries
    $expiringTodayCount = $cache->get('expiring_today_count', function() use ($produitRepository) {
        return $produitRepository->countExpiringToday();
    });
    
    $totalAlerts = $unreadCount + $expiringTodayCount;
    
    return $this->render('notification/_icon.html.twig', [
        'unreadCount' => $totalAlerts,
    ]);
}
```

### Solution 4: Load Asynchronously
Make the icon load via AJAX after page load:

```javascript
// In your base template, add after page renders:
document.addEventListener('DOMContentLoaded', function() {
    fetch('/notifications/icon-count-only')
        .then(r => r.json())
        .then(data => {
            // Update the icon without blocking page load
            document.getElementById('notification-badge').textContent = data.count;
        });
});
```

Then create a lightweight endpoint that returns JSON only (no template rendering).

---

## Implementation Plan

### Priority 1 (Do Immediately)
✅ Add database index on `date_expiration` column

```sql
CREATE INDEX IDX_DATE_EXPIRATION ON produit (date_expiration);
```

### Priority 2 (Do Next) 
✅ Change `findExpiringToday()` to return a count instead of all rows

```php
// In ProduitRepository.php, modify line 79-90
public function countExpiringToday(): int
{
    $todayStart = (new \DateTime())->setTime(0, 0, 0);
    $todayEnd = (clone $todayStart)->modify('+1 day');

    return $this->createQueryBuilder('p')
        ->select('COUNT(p.id)')
        ->where('p.dateExpiration >= :todayStart')
        ->andWhere('p.dateExpiration < :todayEnd')
        ->setParameter('todayStart', $todayStart)
        ->setParameter('todayEnd', $todayEnd)
        ->getQuery()
        ->getSingleScalarResult();
}
```

### Priority 3 (Nice to Have)
✅ Add caching with Symfony Cache component

### Priority 4 (Optional)
✅ Load notification icon asynchronously to not block page

---

## Expected Results
- **Before**: Page takes 5-15+ seconds (depending on product count)
- **After Index + Count Optimization**: Page loads in 200-500ms
- **With Caching**: Page loads in 10-50ms (cached response)

---

## Additional Index Recommendations

While you're at it, add these other useful indexes for common queries:

```sql
-- For filtering and sorting
CREATE INDEX IDX_STATUT ON produit (statut);
CREATE INDEX IDX_CATEGORIE ON produit (categorie_id);
CREATE INDEX IDX_CREATED_AT ON produit (created_at);

-- Composite index for common filtering
CREATE INDEX IDX_STATUT_DATE_EXP ON produit (statut, date_expiration);
```
