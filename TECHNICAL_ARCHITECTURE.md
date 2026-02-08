# Comment Moderation System - Technical Architecture

## 📐 System Architecture

### High-Level Overview
```
┌─────────────────┐
│  User Browser   │
│  (Twig Template)│
└────────┬────────┘
         │ POST /api/commentaires
         │ (AJAX Fetch)
         ↓
┌─────────────────────────────────────┐
│   API Controller                     │
│   CommentaireApiController          │
│   - Receives comment data           │
│   - Calls moderation service        │
│   - Routes to Archive or Publish    │
└──────┬──────────────┬───────────────┘
       │              │
       ↓              ↓
   ┌────────┐    ┌──────────────┐
   │Archive │    │   Publish    │
   │Comment │    │   Comment    │
   └────┬───┘    └───────┬──────┘
        │                │
        ↓                ↓
   ┌──────────────┐  ┌────────────┐
   │CommentArchive│  │Commentaire │
   │   Table      │  │   Table    │
   │ (BLOQUE)     │  │ (VALIDE)   │
   └──────────────┘  └────────────┘
```

---

## 📦 Components

### 1. **Entities**

#### Commentaire (Original)
```php
class Commentaire {
    int id
    string contenu
    DateTime date_publication
    string statut (VALIDE|BLOQUE|EN_ATTENTE)
    Article article
}
```

#### CommentaireArchive (New)
```php
class CommentaireArchive {
    int id
    string contenu              // The blocked comment
    DateTime date_publication   // When user tried to post
    string user_name            // Who posted it
    string user_email           // Contact info
    string reason              // Why blocked (always "inappropriate")
    Article article            // Related article
    DateTime archived_at        // When it was blocked
}
```

### 2. **Controllers**

#### CommentaireApiController
```
POST /api/commentaires
├─ Receive JSON (contenu, article_id, user_name, user_email)
├─ Validate article exists
├─ Call CommentModerationService.analyze()
├─ IF inappropriate:
│  ├─ Create CommentaireArchive record
│  ├─ Save to database
│  └─ Return 403 Forbidden + Warning
└─ IF appropriate:
   ├─ Create Commentaire record
   ├─ Save to database
   └─ Return 201 Created + Success
```

### 3. **Services**

#### CommentModerationService
```php
class CommentModerationService {
    
    analyze(string $text): bool {
        // Layer 1: Fast keyword check
        foreach ($this->badWords as $word) {
            if (contains($text, $word)) {
                return true; // BLOCK
            }
        }
        
        // Layer 2: AI sentiment analysis (HuggingFace API)
        try {
            $response = huggingfaceAPI->analyze($text);
            if (isToxic($response)) {
                return true; // BLOCK
            }
        } catch (Exception) {
            return false; // DEFAULT: ALLOW
        }
        
        return false; // ALLOW
    }
}
```

**Blocked Keywords:**
```
hate, terrible, awful, useless, dumb, stupid, disgusting,
offensive, idiot, worst, fuck, shit, bitch, asshole, bastard
```

### 4. **Repositories**

#### CommentaireArchiveRepository
```php
class CommentaireArchiveRepository extends ServiceEntityRepository {
    public function save(CommentaireArchive $entity)
    public function remove(CommentaireArchive $entity)
    // Custom query methods can be added here
}
```

---

## 🗄️ Database Schema

### commentaire Table
```sql
CREATE TABLE commentaire (
    id INT PRIMARY KEY AUTO_INCREMENT,
    article_id INT NOT NULL,
    contenu LONGTEXT NOT NULL,
    date_publication DATETIME NOT NULL,
    statut VARCHAR(50) NOT NULL DEFAULT 'en_attente',
    
    FOREIGN KEY (article_id) REFERENCES article(id),
    INDEX (statut),
    INDEX (date_publication)
);
```

### commentaire_archive Table
```sql
CREATE TABLE commentaire_archive (
    id INT PRIMARY KEY AUTO_INCREMENT,
    article_id INT NOT NULL,
    contenu LONGTEXT NOT NULL,
    date_publication DATETIME NOT NULL,
    user_name VARCHAR(255),
    user_email VARCHAR(255),
    reason VARCHAR(50) DEFAULT 'inappropriate',
    archived_at DATETIME NOT NULL,
    
    FOREIGN KEY (article_id) REFERENCES article(id),
    INDEX (reason),
    INDEX (archived_at)
);
```

---

## 🔄 Request/Response Flow

### Scenario 1: Appropriate Comment

```
Client Request:
POST /api/commentaires
{
    "contenu": "Great article!",
    "article_id": 1,
    "user_name": "John",
    "user_email": "john@example.com"
}

↓ API Processing:
1. Validate article #1 exists ✓
2. Analyze "Great article!" via moderation
   - Keyword check: PASS (no bad words)
   - AI check: PASS (positive sentiment)
3. Create Commentaire record
   - contenu: "Great article!"
   - statut: "VALIDE"
4. Save to database
5. Return response

Server Response (201 Created):
{
    "success": true,
    "message": "Comment posted successfully",
    "status": "VALIDE",
    "comment_id": 31
}

Client Action:
- Show green success message
- Reload page to show new comment
```

### Scenario 2: Inappropriate Comment

```
Client Request:
POST /api/commentaires
{
    "contenu": "This is terrible and awful",
    "article_id": 1,
    "user_name": "Jane",
    "user_email": "jane@example.com"
}

↓ API Processing:
1. Validate article #1 exists ✓
2. Analyze "This is terrible and awful"
   - Keyword check: FOUND "terrible", "awful" ✗
   - BLOCK immediately
3. Create CommentaireArchive record
   - contenu: "This is terrible and awful"
   - user_name: "Jane"
   - user_email: "jane@example.com"
   - reason: "inappropriate"
   - archived_at: NOW
4. Save to archive table
5. Return response

Server Response (403 Forbidden):
{
    "success": false,
    "warning": "⚠️ Your comment was detected as inappropriate...",
    "status": "BLOQUE",
    "message": "Comment blocked due to inappropriate content"
}

Client Action:
- Show red warning message
- Don't reload page
- Keep comment in textarea
- User can revise and resubmit
```

---

## 🖥️ Frontend Implementation

### JavaScript API Handling
```javascript
// 1. Form Submission
document.getElementById('commentForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    // 2. Prepare data
    const payload = {
        contenu: textarea.value,
        article_id: articleId,
        user_name: 'Customer',
        user_email: null
    };
    
    // 3. Send to API
    const response = await fetch('/api/commentaires', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(payload)
    });
    
    const data = await response.json();
    
    // 4. Handle response
    if (response.ok && data.success) {
        // SUCCESS: Show green message and reload
        displaySuccess(data.message);
        setTimeout(() => location.reload(), 2000);
    } else {
        // BLOCKED: Show red warning
        displayWarning(data.warning);
    }
});
```

### Template Changes (Twig)
```twig
{# Filter to show only VALIDE comments #}
{% set validComments = article.commentaires|filter(c => c.statut == 'VALIDE') %}

{# Display filtered comments #}
{% for comment in validComments %}
    <div class="comment">
        {{ comment.contenu }}
    </div>
{% endfor %}

{# API form instead of traditional form #}
<form id="commentForm">
    <textarea name="contenu"></textarea>
    <button type="submit">Post comment</button>
    <div id="commentResponse"></div>
</form>
```

---

## 🧪 Testing

### Test Scenarios

#### Test 1: Positive Comment
```
Input: "Great article!"
Process: Keyword check PASS, AI check PASS
Result: HTTP 201, Comment posted
```

#### Test 2: Negative Comment
```
Input: "This is terrible and awful"
Process: Keyword check FAIL ("terrible", "awful")
Result: HTTP 403, Comment archived, Warning shown
```

#### Test 3: Offensive Comment
```
Input: "You are stupid and disgusting"
Process: Keyword check FAIL ("stupid", "disgusting")
Result: HTTP 403, Comment archived
```

### Running Tests
```bash
php test_moderation_final.php
```

Expected Output:
```
✅ Positive comments → HTTP 201
❌ Negative comments → HTTP 403
✅ Comments filtered in template
✅ Archives contain blocked comments
```

---

## 🔒 Security Considerations

### Input Validation
- ✅ JSON validation
- ✅ Article existence check
- ✅ Content length limits (max 1000 chars)
- ✅ SQL injection prevention (Doctrine ORM)

### Rate Limiting
- Consider adding per-user limits in future
- Currently: No rate limiting (can add)

### XSS Prevention
- ✅ Data stored as plain text
- ✅ Escaped on display via Twig
- ✅ No HTML tags in content

### Error Handling
- ✅ Try-catch in moderation service
- ✅ Fails safely (allows comment if API down)
- ✅ Graceful error messages to users

---

## 📈 Performance

### Response Times
- User Input → API: ~100ms
- Keyword Check: ~1ms
- AI Analysis: ~500-1000ms
- Database Save: ~50ms
- Total: ~600-1200ms (< 2 seconds)

### Optimization Opportunities
1. Cache AI results for similar comments
2. Async background archive saving
3. Queue system for bulk processing
4. CDN caching for static resources

---

## 🚀 Deployment

### Prerequisites
- PHP 8.1+
- Symfony 6.2+
- MySQL/MariaDB
- HuggingFace API key

### Installation
```bash
# Set environment
export APP_ENV=prod

# Set API key
echo "HUGGINGFACE_API_KEY=your_key_here" >> .env.local

# Run migrations
php bin/console doctrine:migrations:migrate

# Clear cache
php bin/console cache:clear
```

---

## 📊 Monitoring & Maintenance

### Key Metrics
- Total comments posted
- Comments blocked per day
- Most common blocked keywords
- AI model accuracy
- API response times

### Admin Dashboard (Future)
- [ ] View archived comments
- [ ] Approve/reject comments
- [ ] Edit blocked comment list
- [ ] View moderation statistics
- [ ] Configure sensitivity levels

---

**Last Updated**: February 8, 2026
**Version**: 1.0
**Status**: Production Ready
