# Writer Dashboard API Guide

> Base URL: `{{APP_URL}}/api/v1`  
> Auth: **Bearer Token** (`auth:sanctum`) — كل الـ endpoints تتطلب تسجيل دخول كـ **Writer**

---

## فهرس

| # | Endpoint | صفحة الواجهة |
|---|----------|--------------|
| 1 | `GET /writers/me/overview` | Overview |
| 2 | `GET /writers/me/articles` | My Articles |
| 3 | `GET /writers/me/drafts` | Drafts |
| 4 | `GET /writers/me/analytics` | Analytics |
| 5 | `GET /writers/me/articles/{id}/preview` | Preview |
| 6 | `PUT /writers/profile` | Edit Public Profile |
| 7 | `POST /articles` | Create |
| 8 | `PUT /articles/{id}` | Edit |

---

## Response Format

```json
{
  "success": true,
  "status": "success",
  "message": "...",
  "data": { }
}
```

### Paginated Response

```json
{
  "success": true,
  "data": [ ... ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 42,
    "last_page": 3,
    "summary": { }
  }
}
```

---

## 1. Overview

```
GET /writers/me/overview
```

صفحة **Overview** — إحصائيات، طابور التحرير، وأبرز الأداء.

### Response

```json
{
  "data": {
    "stats": {
      "published_this_month": {
        "value": 14,
        "sub_label": "+3 this week"
      },
      "active_drafts": {
        "value": 5,
        "sub_label": "2 near final"
      },
      "total_readers": {
        "value": "28.4K",
        "raw_value": 28400,
        "sub_label": "+18% growth"
      },
      "avg_read_time": {
        "value": "4m 20s",
        "minutes": 4.3,
        "sub_label": "Above site avg"
      }
    },
    "editorial_queue": [
      {
        "id": 12,
        "title": "Energy Corridors: What Changes After the New Summit",
        "slug": "energy-corridors",
        "status": "ready",
        "status_label": "Needs Final Review",
        "due_at": "2026-06-09T12:00:00+00:00",
        "due_description": "Due in 4 hours"
      }
    ],
    "performance_highlights": {
      "best_category": {
        "id": 2,
        "name": "Economy",
        "slug": "economy",
        "total_views": 12500
      },
      "most_saved_story": {
        "id": 8,
        "title": "Policy Brief: Digital Privacy",
        "slug": "policy-brief-digital-privacy",
        "saves_count": 214
      }
    }
  }
}
```

### مصدر البيانات

| الحقل | المنطق |
|-------|--------|
| `published_this_month` | مقالات `published` خلال الشهر الحالي |
| `active_drafts` | `draft`, `submitted`, `under_review`, `ready` |
| `total_readers` | مجموع `views_count` للمقالات المنشورة |
| `avg_read_time` | متوسط `read_time` مقارنة بمتوسط الموقع |
| `editorial_queue` | `submitted`, `under_review`, `ready`, `scheduled` |
| `due_at` | `scheduled_at` أو `submitted_at + 3 أيام` |

---

## 2. My Articles

```
GET /writers/me/articles
```

صفحة **My Articles** — الإحصائيات العلوية + جدول المقالات.

### Query Parameters

| Param | القيم | الافتراضي |
|-------|-------|-----------|
| `status` | `draft`, `submitted`, `under_review`, `ready`, `scheduled`, `published`, `rejected`, `archived` | الكل |
| `category` | ID القسم | — |
| `search` | نص البحث في العنوان والملخص | — |
| `sort` | `latest`, `oldest`, `views`, `saves` | `latest` |
| `per_page` | 1–50 | `15` |

### Response

```json
{
  "data": [
    {
      "id": 1,
      "title": "Energy Corridors: What Changes After the New Summit",
      "slug": "energy-corridors",
      "status": "published",
      "status_label": "Published",
      "category": { "id": 2, "name": "Economy", "slug": "economy" },
      "published_at": "2026-06-06T10:00:00+00:00",
      "updated_at": "2026-06-07T08:00:00+00:00",
      "scheduled_at": null,
      "published_label": "Published 2 days ago",
      "views_count": 4800,
      "views_formatted": "4.8K",
      "comments_count": 32,
      "saves_count": 214
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 3,
    "last_page": 1,
    "summary": {
      "total_articles": 3,
      "total_views": 13800,
      "total_views_formatted": "13.8K",
      "total_saves": 673
    }
  }
}
```

### Status Labels (للفرونت)

| `status` | `status_label` |
|----------|----------------|
| `published` | Published |
| `submitted` | In Review |
| `under_review` | In Review |
| `ready` | Needs Final Review |
| `scheduled` | Scheduled |
| `draft` | Draft |
| `rejected` | Rejected |
| `archived` | Archived |

### أزرار Edit / Preview

| الزر | API |
|------|-----|
| **Edit** | `PUT /articles/{id}` |
| **Preview** | `GET /writers/me/articles/{id}/preview` |

---

## 3. Drafts (Editorial Workspace)

```
GET /writers/me/drafts
```

صفحة **Drafts** — إدارة المسودات قبل النشر.

### الحالات المشمولة

`draft`, `ready` فقط (المسودات الفعلية في مساحة العمل)

### Response

```json
{
  "data": {
    "summary": {
      "total_drafts": 3,
      "ready_to_publish": 1,
      "avg_completion": 65
    },
    "drafts": [
      {
        "id": 4,
        "title": "How Commodity Routes Are Reshaping Regional Markets",
        "slug": "commodity-routes",
        "status": "draft",
        "status_label": "Draft",
        "category": { "id": 2, "name": "Economy", "slug": "economy" },
        "last_edited_at": "2026-06-08T10:00:00+00:00",
        "last_edited_label": "Edited 2 hours ago",
        "readiness": 85,
        "word_count": 1240,
        "notes": "Needs final quote"
      }
    ]
  }
}
```

### حقول الجدول

| الحقل | الوصف |
|-------|-------|
| `readiness` | نسبة الجاهزية 0–100 (محسوبة من اكتمال المحتوى) |
| `word_count` | عدد كلمات `content` |
| `notes` | ملاحظات الكاتب (`writer_notes`) |
| `last_edited_label` | نص نسبي مثل "Edited yesterday" |

### أزرار Actions

| الزر | API |
|------|-----|
| **Continue** | `PUT /articles/{id}` — فتح المحرر وتحديث المحتوى |
| **Preview** | `GET /writers/me/articles/{id}/preview` |

### تحديث الملاحظات

```json
PUT /articles/{id}
{ "writer_notes": "Needs final quote" }
```

### منطق `readiness`

| العنصر | النقاط |
|--------|--------|
| عنوان | 15% |
| ملخص (excerpt) | 15% |
| قسم رئيسي | 10% |
| صورة | 10% |
| المحتوى (حتى 1500 كلمة) | 50% |
| حالة `ready` | 85% كحد أدنى |

---

## 4. Analytics (Performance Desk)

```
GET /writers/me/analytics
```

صفحة **Analytics** — مؤشرات الأداء والرسوم البيانية.

### Response

```json
{
  "data": {
    "summary": {
      "monthly_reads": {
        "value": "128.6K",
        "raw_value": 128600,
        "change": "+12.4%"
      },
      "average_ctr": {
        "value": "7.8%",
        "raw_value": 7.8,
        "change": "+1.1%"
      },
      "read_completion": {
        "value": "63%",
        "raw_value": 63,
        "change": "+4.3%"
      },
      "returning_readers": {
        "value": "41%",
        "raw_value": 41,
        "change": "+2.7%"
      }
    },
    "weekly_reads": [
      { "day": "Mon", "date": "2026-06-02", "reads": 1200 },
      { "day": "Tue", "date": "2026-06-03", "reads": 1450 },
      { "day": "Wed", "reads": 1380 },
      { "day": "Thu", "reads": 1520 },
      { "day": "Fri", "reads": 1680 },
      { "day": "Sat", "reads": 1100 },
      { "day": "Sun", "reads": 1250 }
    ],
    "traffic_sources": [
      { "source": "organic_search", "label": "Organic Search", "count": 420, "percentage": 42 },
      { "source": "homepage", "label": "Homepage", "count": 310, "percentage": 31 },
      { "source": "social", "label": "Social", "count": 170, "percentage": 17 },
      { "source": "newsletter", "label": "Newsletter", "count": 100, "percentage": 10 }
    ],
    "category_performance": [
      { "category": { "id": 2, "name": "Economy", "slug": "economy" }, "score": 92, "total_views": 12500 },
      { "category": { "id": 3, "name": "Technology", "slug": "technology" }, "score": 81, "total_views": 9800 }
    ],
    "top_articles": [ ... ]
  }
}
```

### شرح المؤشرات

| المؤشر | المصدر |
|--------|--------|
| `monthly_reads` | مجموع `views_count` لشهر الحالي |
| `average_ctr` | نسبة الحفظ ÷ المشاهدات (تقريب لـ CTR) |
| `read_completion` | متوسط `read_time` للكاتب مقارنة بالموقع |
| `returning_readers` | من `article_views` — قراء عادوا أكثر من مرة |
| `weekly_reads` | مشاهدات آخر 7 أيام من `article_views` |
| `traffic_sources` | تصنيف `referrer` من `article_views` |
| `category_performance` | نسبة مشاهدات كل قسم من الأعلى (0–100) |

---

## 5. Preview

```
GET /writers/me/articles/{articleId}/preview
```

معاينة مقال للكاتب — **أي حالة** ما عدا `archived`.

- لا يزيد `views_count` (عكس `GET /articles/{id}` العام)
- يرجع المحتوى الكامل + العلاقات
- `is_preview: true` إذا المقال غير منشور

### Response

```json
{
  "data": {
    "id": 4,
    "title": "...",
    "content": "...",
    "status": "draft",
    "status_label": "Draft",
    "published_label": "Updated 1 day ago",
    "is_preview": true,
    "author": { "id": 3, "name": "Omar Writer" },
    "primary_category": { "id": 2, "name": "Economy", "slug": "economy" },
    "secondary_categories": [],
    "tags": []
  }
}
```

---

## 6. Edit Public Profile

```
PUT /writers/profile
Content-Type: application/json
```

صفحة **Edit Public Profile**.

### Body (كل الحقول اختيارية)

```json
{
  "display_name": "Omar Al-Shaheen",
  "bio": "Experienced journalist.",
  "profile_photo": "uploads/profiles/uuid.webp",
  "portfolio_link": "https://portfolio.example.com",
  "experience_level": "senior",
  "languages": ["Arabic", "English"],
  "editorial_specialties": ["Politics", "Economy"],
  "location": "Riyadh, SA",
  "social_links": {
    "twitter": "https://x.com/omar",
    "linkedin": "https://linkedin.com/in/omar"
  },
  "media_affiliation": "Al-Shaheen News"
}
```

---

## 7. Create Article

```
POST /articles
Content-Type: application/json
```

صفحة **Create** — من الـ sidebar.

```json
{
  "primary_category_id": 1,
  "title": "عنوان المقال",
  "slug": "article-slug",
  "content": "محتوى المقال...",
  "excerpt": "ملخص قصير",
  "locale": "ar",
  "status": "draft",
  "tags": [1, 2],
  "secondary_categories": [3]
}
```

---

## 8. Edit Article

```
PUT /articles/{articleId}
Content-Type: application/json
```

زر **Edit** من جدول My Articles.

```json
{
  "title": "عنوان محدّث",
  "content": "محتوى محدّث...",
  "status": "submitted"
}
```

> الكاتب يعدّل مقالاته فقط. المحرر/الأدمن يعدّلون أي مقال.

---

## خريطة الواجهة → APIs

```
Writer Dashboard
├── Overview            → GET /writers/me/overview
│   ├── stats (4 cards)
│   ├── editorial_queue
│   └── performance_highlights
├── My Articles         → GET /writers/me/articles
│   ├── summary (total articles/views/saves)
│   ├── Edit            → PUT  /articles/{id}
│   └── Preview         → GET  /writers/me/articles/{id}/preview
├── Create              → POST /articles
├── Drafts              → GET  /writers/me/drafts
│   ├── summary (total/ready/avg completion)
│   ├── Continue        → PUT  /articles/{id}
│   └── Preview         → GET  /writers/me/articles/{id}/preview
├── Analytics           → GET  /writers/me/analytics
│   ├── summary KPIs (4 cards)
│   ├── weekly_reads chart
│   ├── traffic_sources chart
│   └── category_performance bars
└── Edit Public Profile → PUT /writers/profile
```

---

## أخطاء شائعة

| Code | السبب |
|------|-------|
| `401` | Token مفقود أو منتهي |
| `403` | المستخدم ليس Writer |
| `404` | المقال غير موجود أو لا يخص الكاتب |
| `422` | Validation فاشل |

---

## Postman

مجلد **Writers** يتضمن:

- Writer Dashboard Overview
- My Articles
- My Drafts
- Writer Analytics
- Preview My Article
- Update My Writer Profile

### متطلبات الاختبار

1. سجّل دخول كـ Writer → خذ `token`
2. عيّن `Authorization: Bearer {{token}}`
3. عيّن `article_id` لمقال يخص الكاتب المسجّل

---

## Migration

```bash
php artisan migrate
```

| العمود | الجدول | الوصف |
|--------|--------|-------|
| `writer_notes` | `articles` | ملاحظات الكاتب على المسودة (حقل Notes) |

---

## الملفات ذات الصلة

| الملف | الوصف |
|-------|-------|
| `app/Http/Controllers/Api/V1/WriterDashboardController.php` | كل endpoints الداشبورد |
| `app/Http/Controllers/Api/V1/WriterController.php` | Profile update |
| `app/Http/Controllers/Api/V1/ArticleController.php` | Create / Edit |
| `routes/v1/writers.php` | Routes الداشبورد |
| `routes/v1/articles.php` | Routes المقالات |
