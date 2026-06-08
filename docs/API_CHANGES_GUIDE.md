# دليل التغييرات — API v1

> آخر تحديث: يونيو 2026  
> Base URL: `{{APP_URL}}/api/v1`

هذا الملف يوثّق كل التغييرات والـ APIs الجديدة التي أُضيفت خلال جلسة التطوير الأخيرة.

---

## فهرس المحتويات

1. [رفع الصور](#1-رفع-الصور)
2. [تسجيل Contributor / Writer](#2-تسجيل-contributor--writer)
3. [Home Page APIs](#3-home-page-apis)
4. [Article Page APIs](#4-article-page-apis)
5. [Writers List — تحديث](#5-writers-list--تحديث)
6. [Migration جديدة](#6-migration-جديدة)
7. [ملفات مضافة / معدّلة](#7-ملفات-مضافة--معدّلة)
8. [Postman](#8-postman)
9. [إعداد البيئة](#9-إعداد-البيئة)

---

## 1. رفع الصور

### Endpoint

```
POST /api/v1/uploads/images
Content-Type: multipart/form-data
```

### الحقول

| الحقل | النوع | إلزامي | الوصف |
|-------|------|--------|-------|
| `type` | string | ✅ | نوع الصورة |
| `image` | file | ✅ | ملف الصورة (jpeg, png, webp — حتى 5MB) |

### أنواع الصور (`type`)

| القيمة | الاستخدام | المجلد | العرض الأقصى |
|--------|-----------|--------|--------------|
| `profile` | صورة شخصية (تسجيل / بروفايل) | `uploads/profiles/` | 800px |
| `featured` | صورة مقال / تقرير | `uploads/featured/` | 1920px |
| `portfolio` | معرض أعمال | `uploads/portfolio/` | 2048px |
| `general` | صور عامة | `uploads/general/` | 1920px |

### التخزين

- Disk: `images` (معرّف في `config/filesystems.php`)
- المسار الفعلي: `storage/app/public/image/`
- الرابط العام: `{APP_URL}/storage/image/{path}`
- الصور تُحوَّل تلقائياً إلى **WebP** بجودة **85%** عبر trait `OptimizesImages`

### Response (201)

```json
{
  "success": true,
  "status": "success",
  "message": "Image uploaded successfully.",
  "data": {
    "type": "profile",
    "path": "uploads/profiles/uuid.webp",
    "url": "http://al-shaheen.test/storage/image/uploads/profiles/uuid.webp"
  }
}
```

### التدفق الموصى به

```
1. POST /uploads/images  →  type=profile + image file
2. خذ data.path من الرد
3. أرسل path كنص في profile_photo عند التسجيل
```

---

## 2. تسجيل Contributor / Writer

### Endpoint

```
POST /api/v1/register
Content-Type: application/json
```

### تغيير مهم

`profile_photo` أصبح **نص (مسار الصورة)** وليس ملف.  
يجب رفع الصورة أولاً عبر `/uploads/images` بـ `type=profile`.

### حقول Contributor الإلزامية

| الحقل | النوع | الوصف |
|-------|------|-------|
| `profile_photo` | string | مسار من `uploads/profiles/...` |
| `categories` | array | IDs أقسام الكتابة (واحد على الأقل) |

### حقول Writer الإلزامية (إضافة على Contributor)

| الحقل | النوع | القيم |
|-------|------|-------|
| `experience_level` | string | `junior`, `mid`, `senior`, `expert` |
| `languages` | array | مثال: `["Arabic", "English"]` |
| `editorial_specialties` | array | مثال: `["Politics", "Investigative"]` |

### مثال — Register Contributor

```json
{
  "type": "contributor",
  "name": "Sara Contributor",
  "email": "contributor@example.com",
  "password": "Password@123",
  "password_confirmation": "Password@123",
  "locale": "ar",
  "bio": "Passionate about journalism.",
  "portfolio_link": "https://portfolio.example.com",
  "profile_photo": "uploads/profiles/uuid.webp",
  "categories": [1, 2]
}
```

### مثال — Register Writer

```json
{
  "type": "writer",
  "name": "Omar Writer",
  "email": "writer@example.com",
  "password": "Password@123",
  "password_confirmation": "Password@123",
  "locale": "ar",
  "display_name": "Omar Al-Shaheen",
  "bio": "Experienced journalist.",
  "portfolio_link": "https://writer.example.com",
  "profile_photo": "uploads/profiles/uuid.webp",
  "categories": [1, 2],
  "experience_level": "senior",
  "languages": ["Arabic", "English"],
  "editorial_specialties": ["Politics", "Investigative"]
}
```

### Validation

- `profile_photo` يُتحقق منه عبر Rule `ValidImagePath`
- يجب أن يبدأ بـ `uploads/profiles/`
- يجب أن يكون الملف موجوداً على disk `images`

### حالة Writer بعد التسجيل

`application_status` = `submitted`

---

## 3. Home Page APIs

Prefix: `/api/v1/home`

### 3.1 Top 3 Articles

```
GET /home/top-articles
```

أحدث 3 مقالات منشورة.

| Query | الوصف |
|-------|-------|
| `locale` | `ar` أو `en` |
| `category` | ID القسم الرئيسي |

---

### 3.2 Trending Articles

```
GET /home/trending-article
```

قائمة مقالات رائجة مرتبة حسب `views_count` — الفرونت يحدد العدد عبر `limit`.

| Query | الوصف | الافتراضي |
|-------|-------|-----------|
| `locale` | اختياري | — |
| `category` | اختياري | — |
| `limit` | عدد المقالات | `6` (أقصى 20) |

**Response:** مصفوفة `data[]` وليس كائن واحد.

---

### 3.3 Editor Picks

```
GET /home/editor-picks
```

مقالات معلّمة `is_editor_pick = true` مرتبة بـ `editor_pick_order`.

| Query | الوصف | الافتراضي |
|-------|-------|-----------|
| `locale` | اختياري | — |
| `category` | اختياري | — |
| `limit` | عدد النتائج | `6` (أقصى 20) |

> **ملاحظة:** لعرض Editor Picks يجب تعيين `is_editor_pick = true` على المقالات من لوحة Filament أو عبر Seeder.

---

### 3.4 Filters

```
GET /home/filters
```

يرجع خيارات الفلترة للصفحة الرئيسية:

```json
{
  "categories": [...],
  "locales": [
    { "value": "ar", "label": "Arabic" },
    { "value": "en", "label": "English" }
  ],
  "sort": [
    { "value": "latest", "label": "Latest" },
    { "value": "views",  "label": "Most Read" },
    { "value": "oldest", "label": "Oldest" }
  ]
}
```

---

### 3.5 High-Performance Writers

```
GET /home/writers
```

أبرز الكتّاب حسب مجموع مشاهدات مقالاتهم.

| Query | الوصف | الافتراضي |
|-------|-------|-----------|
| `category_id` | فلترة كتّاب القسم | — |
| `limit` | عدد النتائج | `5` (أقصى 20) |

**Response يتضمن:**
- `articles_count` — عدد المقالات المنشورة
- `total_views` — مجموع المشاهدات

---

## 4. Article Page APIs

Prefix: `/api/v1/articles`

### 4.1 Related Stories

```
GET /articles/{articleId}/related
```

حتى 6 مقالات ذات صلة بناءً على:
- نفس القسم الرئيسي
- أقسام ثانوية مشتركة
- Tags مشتركة

---

### 4.2 Trending Topics

```
GET /articles/{articleId}/trending-topics
```

حتى 10 tags رائجة في نفس قسم المقال، مرتبة بمجموع المشاهدات.

**Response:**

```json
[
  {
    "id": 1,
    "name": "Politics",
    "slug": "politics",
    "total_views": 15000,
    "article_count": 8
  }
]
```

---

### 4.3 Next Read

```
GET /articles/{articleId}/next-read
```

المقال التالي في نفس القسم الرئيسي (حسب `published_at`).  
إذا لا يوجد تالي، يرجع أحدث مقال آخر في نفس القسم.

---

## 5. Writers List — تحديث

```
GET /api/v1/writers
```

### إضافة جديدة

كل كاتب في القائمة يتضمن الآن:

```json
{
  "id": 1,
  "display_name": "...",
  "articles_count": 5,
  "user": { "id": 3, "name": "...", "country": "..." }
}
```

`articles_count` = عدد المقالات المنشورة (`status = published`) للكاتب.

---

## 5.1 Writer Dashboard — Overview

```
GET /api/v1/writers/me/overview
Authorization: Bearer {token}
```

يعيد كل بيانات صفحة **Overview** للكاتب المسجّل دخوله في طلب واحد.

### Response

```json
{
  "success": true,
  "status": "success",
  "message": "Writer overview retrieved successfully.",
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
        "slug": "energy-corridors-what-changes",
        "status": "ready",
        "status_label": "Needs Final Review",
        "due_at": "2026-06-08T20:00:00+00:00",
        "due_description": "Due in 4 hours"
      }
    ],
    "performance_highlights": {
      "best_category": {
        "id": 2,
        "name": "Economy",
        "slug": "economy",
        "total_views": 5200
      },
      "most_saved_story": {
        "id": 8,
        "title": "Policy Brief: Digital Privacy",
        "slug": "policy-brief-digital-privacy",
        "saves_count": 42
      }
    }
  }
}
```

### ملاحظات

| القسم | المصدر |
|-------|--------|
| `published_this_month` | مقالات `published` خلال الشهر الحالي |
| `active_drafts` | `draft`, `submitted`, `under_review`, `ready` |
| `total_readers` | مجموع `views_count` للمقالات المنشورة |
| `avg_read_time` | متوسط `read_time` (بالدقائق) مقارنة بمتوسط الموقع |
| `editorial_queue` | مقالات بحالة `submitted`, `under_review`, `ready`, `scheduled` |
| `due_at` | `scheduled_at` أو `submitted_at + 3 أيام` |
| `best_category` | القسم الأعلى مشاهدة لدى الكاتب |
| `most_saved_story` | المقال الأكثر حفظاً من جدول `saved_articles` |

---

## 6. Migration جديدة

**الملف:** `database/migrations/2026_06_08_000001_add_curation_fields_to_articles_table.php`

| العمود | النوع | الوصف |
|--------|------|-------|
| `is_editor_pick` | boolean | مقال مختار من المحرر |
| `editor_pick_order` | smallint, nullable | ترتيب العرض في Editor Picks |

```bash
php artisan migrate
```

---

## 7. ملفات مضافة / معدّلة

### ملفات جديدة

| الملف | الوصف |
|-------|-------|
| `app/Http/Controllers/Api/V1/ImageUploadController.php` | رفع الصور |
| `app/Http/Controllers/Api/V1/HomeController.php` | Home page APIs |
| `app/Http/Requests/Api/V1/UploadImageRequest.php` | Validation رفع الصور |
| `app/Rules/ValidImagePath.php` | التحقق من مسار الصورة |
| `routes/v1/uploads.php` | Route رفع الصور |
| `app/Http/Controllers/Api/V1/WriterDashboardController.php` | Writer dashboard overview |
| `routes/v1/home.php` | Routes الصفحة الرئيسية |
| `database/migrations/2026_06_08_000001_add_curation_fields_to_articles_table.php` | Editor picks fields |

### ملفات معدّلة

| الملف | التغيير |
|-------|---------|
| `app/Http/Controllers/Api/V1/AuthController.php` | `profile_photo` كنص، ربط categories |
| `app/Http/Requests/Api/V1/RegisterRequest.php` | حقول Contributor/Writer الجديدة |
| `app/Http/Controllers/Api/V1/ArticleController.php` | related, trending-topics, next-read |
| `app/Http/Controllers/Api/V1/WriterController.php` | `articles_count` |
| `app/Models/Article.php` | `scopePublished`, حقول editor pick |
| `app/Models/Writer.php` | علاقة `articles()` |
| `routes/v1/articles.php` | Routes جديدة لصفحة المقال |
| `database/seeders/ArticleSeeder.php` | بذر editor picks |
| `postman/Al-Shaheen.postman_collection.json` | كل الـ endpoints الجديدة |
| `composer.json` | إضافة `spatie/image` |

---

## 8. Postman

تم تحديث `postman/Al-Shaheen.postman_collection.json` بالمجلدات:

| المجلد | الطلبات |
|--------|---------|
| **Uploads** | Upload Profile Image, Upload Featured Image |
| **Home** | Top 3 Articles, Trending Articles, Editor Picks, Filters, High-Performance Writers |
| **Articles** | Related Stories, Trending Topics, Next Read |
| **Auth** | Register Contributor/Writer (JSON + `profile_photo` path) |

### متغيرات Postman

| المتغير | الاستخدام |
|---------|-----------|
| `base_url` | `http://al-shaheen.test/api/v1` |
| `profile_photo_path` | يُعبّأ تلقائياً بعد Upload Profile Image |
| `article_id` | لاختبار Article page APIs |
| `writer_id` | لاختبار Writers APIs |

---

## 9. إعداد البيئة

### متطلبات

```bash
composer require spatie/image   # لمعالجة وتحويل الصور
php artisan storage:link        # لتفعيل روابط /storage/image/...
php artisan migrate             # لحقول editor pick
```

### PHP Extensions

- **GD** أو **Imagick** — مطلوب لمعالجة الصور
- **WebP support** في GD — للتحويل إلى WebP

### Disk `images` — `config/filesystems.php`

```php
'images' => [
    'driver' => 'local',
    'root'   => storage_path('app/public/image'),
    'url'    => rtrim(env('APP_URL'), '/') . '/storage/image',
    'visibility' => 'public',
],
```

---

## ملخص سريع — كل الـ Endpoints الجديدة

| Method | Endpoint | الصفحة |
|--------|----------|--------|
| `POST` | `/uploads/images` | رفع صور |
| `GET` | `/home/top-articles` | Home |
| `GET` | `/home/trending-article?limit=6` | Home — Trending Articles |
| `GET` | `/home/editor-picks` | Home |
| `GET` | `/home/filters` | Home |
| `GET` | `/home/writers` | Home |
| `GET` | `/articles/{id}/related` | Article |
| `GET` | `/articles/{id}/trending-topics` | Article |
| `GET` | `/articles/{id}/next-read` | Article |
| `GET` | `/writers/me/overview` | Writer Dashboard — Overview |

### Endpoints معدّلة

| Method | Endpoint | التغيير |
|--------|----------|---------|
| `POST` | `/register` | حقول Contributor/Writer + `profile_photo` كنص |
| `GET` | `/writers` | إضافة `articles_count` |

---

## Response Format (موحّد)

```json
// نجاح
{
  "success": true,
  "status": "success",
  "message": "...",
  "data": { }
}

// خطأ
{
  "success": false,
  "status": "error",
  "message": "...",
  "data": null
}

// Validation (422)
{
  "success": false,
  "status": "error",
  "message": "Validation failed.",
  "data": { "field": ["error message"] }
}
```
