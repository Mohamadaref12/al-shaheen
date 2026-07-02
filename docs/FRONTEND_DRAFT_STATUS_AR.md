# دليل الفرونت — المسودات، Continue، والحالات (مقالات + أخبار)

> آخر تحديث: يونيو 2026  
> Base URL: `{APP_URL}/api/v1`  
> المصادقة: `Authorization: Bearer {token}` (Sanctum)

هذا الملف يشرح كيف يتعامل الفرونت مع **حفظ المسودة**، **Continue / إرسال للمراجعة**، وقوائم المسودات لمقالات الكاتب وأخباره.

---

## فهرس

1. [القواعد الأساسية](#1-القواعد-الأساسية)
2. [قيم status المسموحة](#2-قيم-status-المسموحة)
3. [Continue — إرسال للمراجعة](#3-continue--إرسال-للمراجعة)
4. [قراءة الـ response بعد PUT](#4-قراءة-الـ-response-بعد-put)
5. [أي API لأي شاشة؟](#5-أي-api-لأي-شاشة)
6. [تدفق الشاشات](#6-تدفق-الشاشات)
7. [ترجمة GPT (مقالات + أخبار)](#7-ترجمة-gpt-مقالات--أخبار)
8. [أخطاء شائعة](#8-أخطاء-شائعة)
9. [أمثلة كود](#9-أمثلة-كود)
10. [ملخص Endpoints](#10-ملخص-endpoints)

---

## 1. القواعد الأساسية

| القاعدة | التفاصيل |
|---------|----------|
| النشر من الفرونت | **ممنوع** — لا ترسل `published` في `POST` أو `PUT` |
| Continue | أرسل `status: "pending"` فقط |
| حفظ مسودة | أرسل `status: "draft"` |
| بعد كل `PUT` | اقرأ `data.status` و `data.is_in_drafts` — لا تعتمد على HTTP 200 وحده |
| خروج من المسودات | إذا `is_in_drafts === false` → احذف العنصر من قائمة المسودات في الواجهة |

النشر الفعلي يتم من **لوحة المحرر (Filament)** — ليس من API الكاتب.

---

## 2. قيم status المسموحة

### عند الإنشاء والتعديل (`POST` / `PUT`)

| القيمة المرسلة | مقال (DB) | خبر (DB) | يظهر في API كـ |
|----------------|-----------|----------|----------------|
| `draft` | `draft` | `draft` | `draft` |
| `pending` | `submitted` | `under_review` | `pending` |
| `published` | ❌ 422 | ❌ 422 | — |
| `archived` | ❌ 422 | ❌ 422 | — |

> للأخبار: أرسل دائماً `pending` — لا ترسل `under_review` من الفرونت.

### حقول إضافية في الـ response

```json
{
  "status": "pending",
  "status_label": "In Review",
  "is_in_drafts": false
}
```

| الحقل | المعنى |
|-------|--------|
| `status` | الحالة بصيغة الفرونت (`draft` \| `pending` \| `published` \| …) |
| `status_label` | نص للعرض في الواجهة |
| `is_in_drafts` | `true` = يجب أن يظهر في تاب المسودات |

---

## 3. Continue — إرسال للمراجعة

### مقال

```http
PUT /api/v1/articles/{id}
Content-Type: application/json
Authorization: Bearer {token}
```

```json
{
  "status": "pending",
  "title_ar": "عنوان المقال",
  "content_ar": "محتوى المقال...",
  "title_en": "English title",
  "content_en": "English content..."
}
```

### خبر

```http
PUT /api/v1/news/{id}
Content-Type: application/json
Authorization: Bearer {token}
```

```json
{
  "status": "pending",
  "title_ar": "عنوان الخبر",
  "content_ar": "محتوى الخبر..."
}
```

> يمكن إرسال أي حقول أخرى مع `status` (عنوان، محتوى، tags، صورة، …). الحقول غير المرسلة لا تتغير.

---

## 4. قراءة الـ response بعد PUT

### نجاح — انتقل من المسودات

```json
{
  "success": true,
  "message": "Article updated successfully.",
  "data": {
    "id": 12,
    "status": "pending",
    "status_label": "In Review",
    "is_in_drafts": false
  }
}
```

**إجراء الفرونت:**

1. احذف العنصر من state / قائمة المسودات
2. أضفه لتاب «قيد المراجعة» أو أعد جلب قائمة المقالات/الأخبار

### نجاح — بقي مسودة

```json
{
  "data": {
    "status": "draft",
    "is_in_drafts": true
  }
}
```

**إجراء الفرونت:** يبقى في تاب المسودات.

### فشل — محاولة نشر

```json
{
  "success": false,
  "message": "Validation failed.",
  "errors": {
    "status": ["The selected status is invalid."]
  }
}
```

**إجراء الفرونت:** اعرض رسالة أن النشر من المحرر فقط — لا تستخدم `published`.

---

## 5. أي API لأي شاشة؟

### تاب المسودات (Drafts)

| المحتوى | Endpoint | ماذا يعرض |
|---------|----------|-----------|
| مقالات | `GET /writers/me/drafts` | `draft` و `ready` فقط |
| أخبار | `GET /news/my-drafts` | `draft` فقط |

بعد **Continue** (`pending`) العنصر **لا يعود** في هذين الـ endpoint.

### تاب قيد المراجعة

| المحتوى | Endpoint | ملاحظة |
|---------|----------|--------|
| مقالات | `GET /writers/me/articles` | فلتر `status=submitted` أو اعرض العناصر التي `status === "pending"` في الـ response |
| أخبار | `GET /news/my-news?status=under_review` | أو اعرض من القائمة الكاملة حيث `status === "pending"` |

### تاب منشور

| المحتوى | Endpoint |
|---------|----------|
| مقالات | `GET /writers/me/articles?status=published` |
| أخبار | `GET /news/my-news?status=published` |

### ملخص في response المسودات

**مقالات** — `GET /writers/me/drafts`:

```json
{
  "summary": {
    "total_drafts": 3,
    "pending_count": 2,
    "ready_to_publish": 1,
    "avg_completion": 72
  }
}
```

**أخبار** — `GET /news/my-drafts`:

```json
{
  "summary": {
    "total_drafts": 2,
    "under_review_count": 4,
    "ready_to_publish": 4,
    "avg_completion": 65
  }
}
```

> `pending_count` / `under_review_count` = عناصر أرسلت للمراجعة (خارج قائمة المسودات).

---

## 6. تدفق الشاشات

```
┌──────────────────┐
│  إنشاء / تعديل   │
└────────┬─────────┘
         │
    ┌────┴────┐
    ▼         ▼
 Save      Continue
 draft     pending
    │         │
    ▼         ▼
 is_in_    is_in_
 drafts:   drafts:
 true      false
    │         │
    ▼         ▼
 تاب       تاب
 المسودات  قيد المراجعة
```

| زر الواجهة | `status` المرسل | الشاشة بعد النجاح |
|------------|-----------------|-------------------|
| حفظ مسودة | `draft` | المسودات |
| Continue | `pending` | قيد المراجعة |
| نشر | — | **لا يوجد في الفرونت** |

---

## 7. ترجمة GPT (مقالات + أخبار)

> **دليل مفصّل:** `docs/FRONTEND_AI_TRANSLATION_AR.md`

### قبل Create (فورم جديد — بدون id)

| Method | Endpoint |
|--------|----------|
| `POST` | `/articles/ai/translate` |
| `POST` | `/news/ai/translate` |

يرسل محتوى الفورم (`title_ar`, `content_ar`, …) ويرجع `suggestions` — الفرونت يملأ الحقول ثم `POST /articles` أو `POST /news`.

### بعد Create (محفوظ مسبقاً)

| Method | Endpoint |
|--------|----------|
| `POST` | `/articles/{id}/ai/translate` |
| `POST` | `/news/{id}/ai/translate` |

ثم تطبيق يدوي عبر `PUT` مع `status: "draft"` أو `"pending"`.

---

## 8. أخطاء شائعة

| الخطأ | السبب | الحل |
|-------|-------|------|
| `pending` يرجع 422 للأخبار | نسخة API قديمة | حدّث الباك — `pending` مقبول الآن |
| `PUT` نجح لكن العنصر لسا بالمسودات | لم يُرسل `status: "pending"` أو نسخة قديمة للمقالات | تأكد من `status` في body واقرأ `is_in_drafts` |
| أرسل `published` ورجع 200 قديم | الباك القديم كان يتجاهل الحالة | حدّث الباك — الآن 422 |
| Continue ثم العنصر بالمسودات | الفرونت لم يحدّث القائمة محلياً | بعد `is_in_drafts: false` احذف من state المسودات |
| الخبر يظهر `under_review` في DB | طبيعي | اعرضه للمستخدم كـ `pending` |

---

## 9. أمثلة كود

### TypeScript — Continue

```typescript
type WorkspaceItem = 'article' | 'news';

async function submitForReview(
  type: WorkspaceItem,
  id: number,
  payload: Record<string, unknown>
) {
  const path = type === 'article' ? `/articles/${id}` : `/news/${id}`;

  const { data } = await api.put(path, {
    ...payload,
    status: 'pending', // لا تستخدم published
  });

  const item = data.data;

  if (item.is_in_drafts === false) {
    draftsStore.remove(id);
    submittedStore.add(item);
  }

  return item;
}
```

### TypeScript — حفظ مسودة

```typescript
async function saveDraft(type: WorkspaceItem, id: number, payload: Record<string, unknown>) {
  const path = type === 'article' ? `/articles/${id}` : `/news/${id}`;

  const { data } = await api.put(path, {
    ...payload,
    status: 'draft',
  });

  return data.data; // is_in_drafts: true
}
```

### React — زر Continue

```tsx
const handleContinue = async () => {
  try {
    const result = await submitForReview('article', articleId, formValues);

    if (result.status === 'pending') {
      toast.success('تم إرسال المقال للمراجعة');
      navigate('/writer/submitted');
    }
  } catch (err) {
    if (err.response?.status === 422) {
      toast.error('تعذر الإرسال — تحقق من الحقول والحالة');
    }
  }
};
```

---

## 10. ملخص Endpoints

| Method | Endpoint | الغرض |
|--------|----------|--------|
| `GET` | `/writers/me/drafts` | مسودات المقالات |
| `GET` | `/writers/me/articles` | كل مقالات الكاتب (مع فلتر status) |
| `PUT` | `/articles/{id}` | تعديل مقال — `status`: `draft` \| `pending` |
| `POST` | `/articles` | إنشاء مقال — `status`: `draft` \| `pending` |
| `GET` | `/news/my-drafts` | مسودات الأخبار |
| `GET` | `/news/my-news` | كل أخبار الكاتب (مع فلتر status) |
| `PUT` | `/news/{id}` | تعديل خبر — `status`: `draft` \| `pending` |
| `POST` | `/news` | إنشاء خبر — `status`: `draft` \| `pending` |

---

## Checklist للمطور الفرونت

- [ ] Continue يرسل `PUT` مع `status: "pending"`
- [ ] لا يُرسل `published` أو `archived` من الفرونت
- [ ] بعد `PUT` يُقرأ `status` و `is_in_drafts`
- [ ] عند `is_in_drafts: false` يُزال العنصر من تاب المسودات
- [ ] تاب قيد المراجعة يستخدم `my-articles` / `my-news` وليس `my-drafts`
- [ ] للأخبار: يُعرض `pending` للمستخدم (حتى لو DB = `under_review`)
- [ ] ترجمة GPT: تطبيق يدوي عبر `PUT` بعد مراجعة `suggestions`

---

*للتفاصيل الإضافية عن Writer Dashboard راجع `docs/WRITER_DASHBOARD_API.md`.*
