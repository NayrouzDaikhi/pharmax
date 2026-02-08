# Comment Moderation System - User Guide

## 🎯 Quick Start Guide

### For Users (Readers of Blog Posts)

#### How It Works
1. **View an article** on the blog
2. **Scroll to the comments section** at the bottom
3. **Type your comment** in the text area
4. **Click "Post comment"** button
5. **Instant feedback:**
   - ✅ Green message → Comment posted successfully!
   - ⚠️  Red warning → Comment was inappropriate

#### What Gets Your Comment Blocked?
The system blocks comments containing:
- Offensive/profane language
- Hateful or discriminatory content
- Aggressive or threatening tone
- Spam or advertising

#### Real Examples

**✅ POSTED - These comments work fine:**
- "Great article! I learned something new."
- "I agree with some points but disagree with others."
- "Thanks for sharing this insight!"
- "Really well-written and informative."

**❌ BLOCKED - These won't be posted:**
- "This is terrible and awful."
- "I hate this stupid article."
- "You are an idiot."
- "This is disgusting content."

---

### For Administrators

#### Overview
The system automatically:
1. Analyzes every comment before posting
2. Blocks inappropriate content
3. Archives blocked comments for review
4. Shows warning message to users
5. Only displays approved comments

#### Accessing Archived Comments

**Via Database:**
```bash
php bin/console doctrine:query:sql "SELECT * FROM commentaire_archive;"
```

**Output Example:**
```
ID | User      | Comment                    | Reason          | Date
---|-----------|----------------------------|-----------------|----------
1  | Jane Doe  | "terrible, awful content"  | inappropriate   | 2026-02-08
2  | Troll U.  | "you are stupid..."        | inappropriate   | 2026-02-08
```

#### Customizing the Moderation

**Update Blocked Keywords:**
Edit `src/Service/CommentModerationService.php`:

```php
private array $badWords = [
    'fuck',           // Profanity
    'shit',
    'bitch',
    'asshole',
    'hate',           // Negative sentiment
    'terrible',
    'awful',
    'useless',
    // Add more keywords here...
];
```

**Adjust AI Sensitivity:**
Edit the same file, find the line:
```php
$result['score'] > 0.4  // Lower = more lenient, Higher = stricter
```

---

## 📊 System Statistics

### Current Status
- **Total Comments**: 30+ (all valid posts)
- **Blocked Comments**: 3+ (archived)
- **Approval Rate**: 90%+
- **Average Response Time**: <1 second

### Moderation Flow
```
User Comment Input
↓
Keyword Check (Instant)
↓
AI Analysis (< 1 sec)
↓
Decision
├→ ✅ Approved (Posted, visible, in DB)
└→ ❌ Blocked (Archived, hidden, warning shown)
```

---

## 🔐 Safety & Privacy

### What We Protect
✅ User experience - Clean, safe comments  
✅ Community standards - Prevent harassment  
✅ Content quality - Only relevant comments  
✅ Admin efficiency - Automatic moderation  

### Data Retention
- **Valid Comments**: Stored indefinitely in `commentaire` table
- **Blocked Comments**: Stored in `commentaire_archive` for audit trail
- **User Info**: Name and email stored with archived comments

---

## ❓ FAQ

**Q: My comment was blocked but I think it's appropriate. What can I do?**
A: Contact the site administrator. They can review archived comments and manually approve if needed.

**Q: How fast is the moderation?**
A: Instant! Comments are analyzed before posting. You get feedback within 1-2 seconds.

**Q: Can I edit my comment?**
A: Once posted, comments are displayed as-is. If you block blocked, re-post with revised content.

**Q: What if the system blocks my comment by mistake?**
A: The system uses both automated and AI analysis. If truly inappropriate, contact admin for review.

**Q: Do you collect my personal data?**
A: Only name and email are optionally stored with your comment. No tracking or marketing use.

---

## 🚀 API Information (For Developers)

### Endpoint
```
POST /api/commentaires
Content-Type: application/json
```

### Request Body
```json
{
  "contenu": "Your comment text here",
  "article_id": 1,
  "user_name": "Your Name",
  "user_email": "email@example.com"
}
```

### Responses

**✅ Success (201 Created)**
```json
{
  "success": true,
  "message": "Comment posted successfully",
  "status": "VALIDE",
  "comment_id": 31
}
```

**❌ Blocked (403 Forbidden)**
```json
{
  "success": false,
  "warning": "⚠️ Your comment was detected as inappropriate...",
  "status": "BLOQUE",
  "message": "Comment blocked due to inappropriate content"
}
```

---

## 📈 Future Improvements

Coming soon:
- Admin dashboard with moderation analytics
- Custom approval workflow
- User reputation system
- Advanced spam detection
- Multi-language support
- Comment editing capability

---

**Questions or Issues?**
Contact: admin@pharmax-cms.local
Last Updated: February 8, 2026
