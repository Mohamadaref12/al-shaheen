# دليل الفرونت — التحديثات الأخيرة (API v1)

> آخر تحديث: 17 يونيو 2026  
> Base URL: `{APP_URL}/api/v1`  
> Header موصى به: `Accept: application/json`

هذا الملف يشرح **كل التعديلات الجديدة** التي تحتاجها واجهة الفرونت.  
الردود كلها بنفس الشكل المعتاد:

```json
{
  "success": true,
  "status": "success",
  "message": "...",
  "data": { ... },
  "meta": { ... }
}
```

> `meta` يظهر فقط في الـ endpoints التي فيها pagination.

---

## فهرس

1. [حقل `is_saved` على المقالات](#1-حقل-is_saved-على-المقالات)
2. [صفحة الكاتب Writer Profile](#2-صفحة-الكاتب-writer-profile)
3. [الإعلانات Ads](#3-الإعلانات-ads)
4. [فلاتر صفحة القسم Category Page](#4-فلاتر-صفحة-القسم-category-page)
5. [ملخص Endpoints](#5-ملخص-endpoints)
6. [أمثلة تكامل سريعة](#6-أمثلة-تكامل-سريعة)

---

## 1. حقل `is_saved` على المقالات

### المشكلة التي حُلّت

الفرونت كان يحتاج يعرف: **هل المستخدم الحالي حفظ هالمقال؟**  
عشان يعرض أيقونة Save مفعّلة أو لا — بدون ما يجيب كل المقالات المحفوظة ويقارن IDs يدوياً.

### الحل

كل response يرجّع مقالات صار فيه حقل:

```json
"is_saved": true
```

| حالة المستخدم | قيمة `is_saved` |
|---------------|-----------------|
| زائر (بدون token) | `false` |
| مسجّل دخول + المقال محفوظ | `true` |
| مسجّل دخول + المقال مو محفوظ | `false` |

### Auth اختياري على endpoints عامة

Endpoints القراءة العامة (مثل `/articles`, `/home/*`) **ما بتطلب** login،  
لكن إذا بعتتو Bearer token، الباك يرجّع `is_saved` الصحيح:

```http
Authorization: Bearer {token}
```

### Endpoints المتأثرة

| Endpoint | `is_saved` |
|----------|------------|
| `GET /articles` | على كل مقال |
| `GET /articles/{id}` | على المقال |
| `GET /articles/{id}/related` | على كل مقال |
| `GET /articles/{id}/next-read` | على المقال |
| `GET /home/breaking-news` | على كل مقال |
| `GET /home/top-articles` | على كل مقال |
| `GET /home/trending-article` | على كل مقال |
| `GET /home/editor-picks` | على كل مقال |
| `GET /primary-categories/{id}/trending-article` | على كل مقال |
| `GET /primary-categories/{id}/editor-picks` | على كل مقال |
| `GET /primary-categories/{id}/articles` | على كل مقال |
| `GET /primary-categories/{id}` (مقالات مدمجة) | على كل مقال |
| `GET /secondary-categories/{id}/articles` | على كل مقال |
| `GET /secondary-categories/{id}` | على كل مقال |
| `GET /me/social?type=saved` | دائماً `true` |
| `GET /writers/{id}` → `articles[]` | على كل مقال |

### Toggle حفظ / إلغاء حفظ

```
POST /articles/{articleId}/save
Authorization: Bearer {token}   ← مطلوب
```

**Response:**

```json
{
  "data": {
    "article_id": 12,
    "saved": true,
    "action": "saved"
  }
}
```

| `action` | المعنى |
|----------|--------|
| `saved` | تم الحفظ |
| `unsaved` | تم إلغاء الحفظ |

### قائمة المقالات المحفوظة

```
GET /me/social?type=saved
Authorization: Bearer {token}
```

---

## 2. صفحة الكاتب Writer Profile

### Endpoint

```
GET /writers/{writerId}
```

### التعديلات

#### أ) روابط السوشيال

```json
{
  "social_links": {
    "twitter": "https://x.com/username",
    "linkedin": "https://linkedin.com/in/username"
  },
  "twitter": "https://x.com/username",
  "linkedin": "https://linkedin.com/in/username"
}
```

| الحقل | الاستخدام بالفرونت |
|-------|-------------------|
| `twitter` | إذا `null` → لا تعرض أيقونة Twitter |
| `linkedin` | إذا `null` → لا تعرض أيقونة LinkedIn |
| `social_links` | نفس البيانات بشكل object |

> الباك ي normalizes المفاتيح (`x` → `twitter`).

#### ب) المقالات المنشورة

```json
{
  "articles_count": 12,
  "articles": [
    {
      "id": 5,
      "title": "...",
      "slug": "...",
      "excerpt": "...",
      "featured_image_url": "...",
      "read_time": 5,
      "views_count": 340,
      "published_at": "2026-06-10T12:00:00+00:00",
      "is_saved": false,
      "primary_category": { "id": 1, "name": "News", "slug": "news" },
      "tags": [{ "id": 1, "name": "Politics", "slug": "politics" }]
    }
  ],
  "articles_meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 12,
    "last_page": 1
  }
}
```

#### Query params (اختيارية)

| Param | الوصف |
|-------|-------|
| `locale` | `ar` \| `en` — فلترة مقالات الكاتب |
| `per_page` | عدد المقالات بالصفحة (افتراضي 15) |
| `page` | رقم الصفحة |

#### حقول إضافية مفيدة

| الحقل | الوصف |
|-------|-------|
| `profile_photo_url` | رابط الصورة جاهز للعرض |
| `is_verified_writer` | Badge التوثيق |
| `user.name` / `user.country` | اسم المستخدم وبلده |
| `categories[]` | أقسام الكاتب |

---

## 3. الإعلانات Ads

### Endpoint

```
GET /ads
```

**Auth:** غير مطلوب (public)

### Query params

| Param | القيم | الوصف |
|-------|-------|-------|
| `placement` | `leaderboard` \| `hero` \| `in_feed` \| `mid_article` \| `right_rail` \| `footer` | مكان الإعلان |
| `ad_category` | string | فلترة حسب تصنيف الإعلان |
| `limit` | 1–20 | عدد النتائج (افتراضي 10) |

### أمثلة

```
GET /ads?placement=leaderboard&limit=1
GET /ads?placement=in_feed&limit=5
GET /ads?placement=right_rail
GET /ads?placement=mid_article&limit=1
GET /ads?placement=footer&limit=1
```

### Response

```json
{
  "data": [
    {
      "id": 1,
      "title": "Ad Title",
      "placement": "leaderboard",
      "image_url": "http://.../storage/image/ads/banner.webp",
      "link_url": "https://sponsor.example.com",
      "ad_category": "Finance",
      "is_native": false,
      "starts_at": "2026-05-18T06:38:55+00:00",
      "ends_at": "2026-06-22T06:38:55+00:00"
    }
  ]
}
```

### Placements — أين تستخدم كل واحد

| `placement` | مكان العرض بالواجهة |
|-------------|---------------------|
| `leaderboard` | Banner أعلى الصفحة (728×90) |
| `hero` | Hero Takeover — أسفل الـ nav بالـ Homepage |
| `in_feed` | بين بطاقات المقالات (Native Ad) |
| `mid_article` | داخل المقال بعد فقرة 3–4 |
| `right_rail` | Sidebar ثابت (300×600) |
| `footer` | Banner أسفل الموقع |

> إذا `is_native: true` → اعرض label **Sponsored** بوضوح.

> **Ad-light للـ Premium:** لسه غير مطبّق — المشترك Premium حالياً يشوف نفس الإعلانات.

---

## 4. فلاتر صفحة القسم Category Page

### 4.1 جلب خيارات الفلاتر

#### قسم رئيسي

```
GET /primary-categories/{categoryId}/filters
```

**Response:**

```json
{
  "data": {
    "category": {
      "id": 1,
      "name": "News",
      "slug": "news",
      "description": "...",
      "image": "..."
    },
    "secondary_categories": [
      { "id": 3, "parent_id": 1, "name": "Politics", "slug": "politics", "image": "..." }
    ],
    "locales": [
      { "value": "ar", "label": "Arabic" },
      { "value": "en", "label": "English" }
    ],
    "sort": [
      { "value": "latest", "label": "Latest" },
      { "value": "views", "label": "Most Read" },
      { "value": "trending", "label": "Trending" },
      { "value": "oldest", "label": "Oldest" }
    ],
    "formats": [
      { "value": "all", "label": "All Content" },
      { "value": "breaking", "label": "Breaking" },
      { "value": "premium", "label": "Premium" },
      { "value": "video", "label": "Video" }
    ],
    "date_ranges": [
      { "value": "all", "label": "All Time" },
      { "value": "today", "label": "Today" },
      { "value": "week", "label": "This Week" },
      { "value": "month", "label": "This Month" }
    ]
  }
}
```

#### قسم فرعي

```
GET /secondary-categories/{categoryId}/filters
```

نفس الفلاتر (`locales`, `sort`, `formats`, `date_ranges`) **بدون** `secondary_categories`.

---

### 4.2 جلب المقالات مع الفلاتر

#### قسم رئيسي

```
GET /primary-categories/{categoryId}/articles
```

#### قسم فرعي

```
GET /secondary-categories/{categoryId}/articles
```

### Query params

| Param | القيم | الوصف |
|-------|-------|-------|
| `secondary` | ID | فلترة بقسم فرعي (**رئيسي فقط**) |
| `locale` | `ar` \| `en` | اللغة |
| `sort` | `latest` \| `views` \| `trending` \| `oldest` | الترتيب |
| `format` | `all` \| `breaking` \| `premium` \| `video` | نوع المحتوى |
| `date_range` | `all` \| `today` \| `week` \| `month` | الفترة الزمنية |
| `from_date` | `YYYY-MM-DD` | تاريخ بداية (اختياري) |
| `to_date` | `YYYY-MM-DD` | تاريخ نهاية (اختياري) |
| `per_page` | 1–50 | عدد النتائج (افتراضي 15) |
| `page` | number | رقم الصفحة |

### مثال

```
GET /primary-categories/1/articles?locale=ar&sort=views&format=breaking&date_range=week&secondary=3&per_page=15&page=1
```

### Response (مع pagination)

```json
{
  "data": [
    {
      "id": 10,
      "title": "...",
      "slug": "...",
      "featured_image_url": "...",
      "read_time": 4,
      "views_count": 890,
      "published_at": "...",
      "is_saved": false,
      "primary_category": { "id": 1, "name": "News", "slug": "news" },
      "tags": []
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 42,
    "last_page": 3
  }
}
```

---

### 4.3 تدفق صفحة القسم الموصى به

```
1. GET /primary-categories/{id}/filters     → بناء UI الفلاتر + chips الأقسام الفرعية
2. GET /primary-categories/{id}/articles      → شبكة المقالات (Lead + Grid)
3. GET /primary-categories/{id}/trending-article  → Sidebar: Trending
4. GET /primary-categories/{id}/editor-picks      → Sidebar: Editor Picks
5. GET /primary-categories/{id}/writers           → Sidebar: Top Writers
6. GET /ads?placement=right_rail&limit=1            → Sidebar Ad
7. GET /ads?placement=in_feed&limit=5              → Native ads بين البطاقات
```

---

## 5. ملخص Endpoints

| Method | Endpoint | Auth | جديد / محدّث |
|--------|----------|------|--------------|
| `GET` | `/articles` | اختياري | ✅ `is_saved` |
| `GET` | `/articles/{id}` | اختياري | ✅ `is_saved` |
| `GET` | `/articles/{id}/related` | اختياري | ✅ `is_saved` |
| `GET` | `/articles/{id}/next-read` | اختياري | ✅ `is_saved` |
| `POST` | `/articles/{id}/save` | ✅ | موجود (toggle) |
| `GET` | `/home/*` (مقالات) | اختياري | ✅ `is_saved` |
| `GET` | `/writers/{id}` | اختياري | ✅ social + articles |
| `GET` | `/ads` | — | ✅ Ads API |
| `GET` | `/primary-categories/{id}/filters` | — | ✅ **جديد** |
| `GET` | `/primary-categories/{id}/articles` | اختياري | ✅ **جديد** |
| `GET` | `/secondary-categories/{id}/filters` | — | ✅ **جديد** |
| `GET` | `/secondary-categories/{id}/articles` | اختياري | ✅ **جديد** |

---

## 6. أمثلة تكامل سريعة

### أ) بطاقة مقال + زر Save

```javascript
// جلب المقالات — أرسل token إذا المستخدم مسجل
const res = await fetch('/api/v1/articles?category=1', {
  headers: {
    Accept: 'application/json',
    ...(token && { Authorization: `Bearer ${token}` }),
  },
});
const { data } = await res.json();

// عرض أيقونة Save
data.forEach(article => {
  const saved = article.is_saved; // true | false
});

// Toggle save
await fetch(`/api/v1/articles/${id}/save`, {
  method: 'POST',
  headers: { Authorization: `Bearer ${token}`, Accept: 'application/json' },
});
```

### ب) صفحة الكاتب

```javascript
const res = await fetch('/api/v1/writers/1?locale=ar&per_page=10', {
  headers: {
    Accept: 'application/json',
    ...(token && { Authorization: `Bearer ${token}` }),
  },
});
const { data: writer } = await res.json();

if (writer.twitter) { /* render Twitter icon → writer.twitter */ }
if (writer.linkedin) { /* render LinkedIn icon → writer.linkedin */ }

writer.articles.forEach(article => { /* article cards */ });
```

### ج) صفحة القسم

```javascript
// 1) الفلاتر
const filters = await fetch('/api/v1/primary-categories/1/filters').then(r => r.json());

// 2) المقالات
const params = new URLSearchParams({
  locale: 'ar',
  sort: 'latest',
  format: 'all',
  date_range: 'all',
  per_page: '15',
});
const articles = await fetch(`/api/v1/primary-categories/1/articles?${params}`, {
  headers: token ? { Authorization: `Bearer ${token}` } : {},
}).then(r => r.json());
```

### د) الإعلانات

```javascript
const leaderboard = await fetch('/api/v1/ads?placement=leaderboard&limit=1').then(r => r.json());
const inFeed = await fetch('/api/v1/ads?placement=in_feed&limit=5').then(r => r.json());
```

---

## Postman

كل الـ endpoints موجودة في collection:  
`postman/Al-Shaheen.postman_collection.json`

أقسام: **Ads**, **Primary Categories** (Filters + Articles), **Writers** (Show Writer).

---

## ملاحظات مهمة

1. **`is_saved`** — أرسلوا Bearer token على أي request فيه مقالات إذا بدكم حالة الحفظ صحيحة.
2. **`featured_image_url` / `profile_photo_url` / `image_url`** — جاهزة للعرض مباشرة (URLs كاملة).
3. **Pagination** — استخدموا `meta.current_page`, `meta.last_page`, `meta.total` للـ infinite scroll أو pagination UI.
4. **404** — إذا `categoryId` أو `writerId` مو موجود أو مو approved، يرجع `{ "success": false, "message": "..." }` مع status 404.
