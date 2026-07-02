# دليل الفرونت — ترجمة GPT قبل الإنشاء (مقالات + أخبار)

> آخر تحديث: يونيو 2026  
> Base URL: `{APP_URL}/api/v1`  
> المصادقة: `Authorization: Bearer {token}`

## الفكرة

الكاتب يفتح **فورم إنشاء جديد** (بدون `id`)، يكتب بالعربي أو الإنجليزي، يضغط **ترجمة**، يستلم الحقول باللغة الثانية، يراجعها، ثم يضغط **حفظ / إنشاء**.

**لا يحتاج `POST /articles` أو `POST /news` قبل الترجمة.**

---

## التدفق (قبل Create)

```
┌─────────────────────┐
│  فورم إنشاء جديد     │  (لا يوجد id بعد)
│  المستخدم يكتب AR    │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│  POST .../ai/translate │  ← يرسل محتوى الفورم
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│  Response: suggestions │  title_en, content_en, ...
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│  الفرونت يملأ الحقول   │  في نفس الفورم (مراجعة يدوية)
│  باللغة الثانية      │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│  POST /articles      │  أو POST /news
│  status: draft       │
└─────────────────────┘
```

---

## Endpoints — ترجمة بدون Create

| المحتوى | Endpoint | يحتاج id؟ |
|---------|----------|-----------|
| مقال | `POST /api/v1/articles/ai/translate` | ❌ لا |
| خبر | `POST /api/v1/news/ai/translate` | ❌ لا |

### التحقق من توفر الخدمة

| المحتوى | Endpoint |
|---------|----------|
| مقال | `GET /api/v1/articles/ai/status` |
| خبر | `GET /api/v1/news/ai/status` |

```json
{
  "data": {
    "translation_available": true,
    "enabled": true
  }
}
```

---

## Request — ترجمة من الفورم (قبل Create)

### مثال: كتب بالعربي ويريد ترجمة للإنجليزي

```http
POST /api/v1/articles/ai/translate
Content-Type: application/json
Authorization: Bearer {token}
```

```json
{
  "title_ar": "عنوان المقال بالعربية",
  "subtitle_ar": "عنوان فرعي",
  "content_ar": "محتوى المقال الكامل بالعربية...",
  "excerpt_ar": "ملخص قصير",
  "seo_title_ar": "SEO عربي",
  "seo_description_ar": "وصف SEO"
}
```

أو حدد اللغات صراحة:

```json
{
  "source_locale": "ar",
  "target_locale": "en",
  "title_ar": "...",
  "content_ar": "..."
}
```

> إذا لم تُرسل `source_locale` / `target_locale`، الباك يكتشف اللغة تلقائياً ويترجم للغة الأخرى (`ar` ↔ `en`).

### نفس الشيء للخبر

```http
POST /api/v1/news/ai/translate
```

```json
{
  "title_ar": "عنوان الخبر",
  "content_ar": "محتوى الخبر...",
  "excerpt_ar": "ملخص"
}
```

### حقول مدعومة

| الحقل |
|-------|
| `title_ar` / `title_en` / `title` |
| `subtitle_ar` / `subtitle_en` / `subtitle` |
| `content_ar` / `content_en` / `content` |
| `excerpt_ar` / `excerpt_en` / `excerpt` |
| `seo_title_ar` / `seo_title_en` / `seo_title` |
| `seo_description_ar` / `seo_description_en` / `seo_description` |

- `content` يقبل **نص عادي** أو **TipTap JSON**
- يمكن استخدام `locale` + حقول عامة (`title`, `content`) بدل `_ar` / `_en`

---

## Response

```json
{
  "success": true,
  "message": "Translation generated successfully.",
  "data": {
    "available": true,
    "source_locale": "ar",
    "target_locale": "en",
    "apply_hint": "Copy fields from suggestions into the create form, then POST /articles with both locales. Nothing is auto-applied.",
    "suggestion": {
      "id": 15,
      "article_id": null,
      "news_id": null,
      "kind": "translation",
      "source_locale": "ar",
      "target_locale": "en",
      "suggestions": {
        "title_en": "English headline",
        "subtitle_en": "English subtitle",
        "content_en": "Full English content...",
        "excerpt_en": "Short excerpt",
        "seo_title_en": "SEO title",
        "seo_description_en": "SEO description"
      },
      "notes": [
        { "field": "title", "reason": "..." }
      ],
      "status": "completed"
    }
  }
}
```

| الحقل | المعنى |
|-------|--------|
| `suggestion.article_id` / `news_id` | `null` = ترجمة قبل الإنشاء |
| `suggestions` | الحقول المترجمة — انسخها للفورم |
| `notes` | ملاحظات اختيارية من GPT |

**لا يُطبَّق شيء تلقائياً** — الفرونت يملأ الفورم من `suggestions`.

---

## بعد الترجمة — Create

عندما يرضى المستخدم عن الترجمة:

### مقال

```http
POST /api/v1/articles
```

```json
{
  "status": "draft",
  "primary_category_id": 1,
  "title_ar": "عنوان المقال بالعربية",
  "content_ar": "محتوى عربي...",
  "title_en": "English headline",
  "content_en": "English content...",
  "excerpt_ar": "...",
  "excerpt_en": "..."
}
```

### خبر

```http
POST /api/v1/news
```

```json
{
  "status": "draft",
  "category_id": 1,
  "title_ar": "...",
  "content_ar": "...",
  "title_en": "...",
  "content_en": "..."
}
```

---

## ترجمة بعد Create (اختياري)

إذا المقال/الخبر **محفوظ مسبقاً** وبدك تترجم من المحتوى المخزّن:

| المحتوى | Endpoint |
|---------|----------|
| مقال | `POST /api/v1/articles/{id}/ai/translate` |
| خبر | `POST /api/v1/news/{id}/ai/translate` |

```json
{
  "source_locale": "ar",
  "target_locale": "en"
}
```

ثم طبّق النتيجة عبر `PUT /articles/{id}` أو `PUT /news/{id}`.

---

## مثال TypeScript — ترجمة قبل Create

```typescript
async function translateBeforeCreate(
  type: 'article' | 'news',
  formValues: Record<string, unknown>
) {
  const path = type === 'article'
    ? '/articles/ai/translate'
    : '/news/ai/translate';

  const { data } = await api.post(path, {
    source_locale: 'ar',
    target_locale: 'en',
    title_ar: formValues.title_ar,
    subtitle_ar: formValues.subtitle_ar,
    content_ar: formValues.content_ar,
    excerpt_ar: formValues.excerpt_ar,
    seo_title_ar: formValues.seo_title_ar,
    seo_description_ar: formValues.seo_description_ar,
  });

  const suggestions = data.data.suggestion?.suggestions ?? {};

  // املأ الفورم — المستخدم يراجع قبل الحفظ
  return {
    title_en: suggestions.title_en,
    subtitle_en: suggestions.subtitle_en,
    content_en: suggestions.content_en,
    excerpt_en: suggestions.excerpt_en,
    seo_title_en: suggestions.seo_title_en,
    seo_description_en: suggestions.seo_description_en,
  };
}

async function createArticle(form: Record<string, unknown>) {
  return api.post('/articles', {
    ...form,
    status: 'draft',
  });
}
```

### تسلسل الأزرار في الواجهة

```
[ كتابة AR ] → [ ترجمة GPT ] → [ مراجعة EN ] → [ حفظ مسودة ] → POST /articles
```

---

## أخطاء شائعة

| الخطأ | السبب | الحل |
|-------|-------|------|
| 422 validation | لا يوجد عنوان ولا محتوى | أرسل `title_ar` أو `content_ar` على الأقل |
| `translation_available: false` | لا يوجد مفتاح OpenAI | Admin → AI Settings |
| استخدام `/{id}/ai/translate` قبل Create | لا يوجد id | استخدم `/ai/translate` بدون id |
| توقع حفظ تلقائي | التصميم مقصود | انسخ `suggestions` للفورم ثم `POST` |

---

## ملخص Endpoints

| متى | مقال | خبر |
|-----|------|-----|
| **قبل Create** | `POST /articles/ai/translate` | `POST /news/ai/translate` |
| بعد Create | `POST /articles/{id}/ai/translate` | `POST /news/{id}/ai/translate` |
| حالة الخدمة | `GET /articles/ai/status` | `GET /news/ai/status` |
| إنشاء | `POST /articles` | `POST /news` |

---

## Checklist للفرونت

- [ ] زر «ترجمة» يستدعي `POST .../ai/translate` **بدون** id
- [ ] يرسل محتوى الفورم الحالي (`title_ar`, `content_ar`, …)
- [ ] يملأ حقول EN من `data.suggestion.suggestions`
- [ ] يعرض `notes` للمراجعة (اختياري)
- [ ] زر «حفظ» يستدعي `POST /articles` أو `POST /news` باللغتين
- [ ] لا يعتمد على `article_id` / `news_id` قبل الإنشاء

---

*للمسودات والحالات راجع `docs/FRONTEND_DRAFT_STATUS_AR.md`.*
