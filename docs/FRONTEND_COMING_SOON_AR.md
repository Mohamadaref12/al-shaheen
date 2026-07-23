# دليل الفرونت — Coming Soon (بوابة المعاينة)

> آخر تحديث: 22 يوليو 2026  
> Base URL: `{APP_URL}/api/v1`  
> Auth: **غير مطلوب** لهذه الـ endpoints  
> Header موصى به: `Accept: application/json`

عندما يكون الموقع في وضع **Coming Soon**، الزائر العادي ما يقدر يستدعي باقي الـ API.  
الفرونت يعرض صفحة Coming Soon + فورم للمفتاح، وبعد النجاح يمرّر المفتاح مع كل طلب.

> **مهم:** واجهة Coming Soon على الفرونت فقط. الباكند: إعدادات داشبورد + حماية الـ API — بدون صفحة Coming Soon على Laravel وبدون قفل `/admin`.

---

## تفعيل / إيقاف البوابة

من **لوحة التحكم (Filament)**:

`Settings` → **Coming Soon**  
أو: `{APP_URL}/admin/coming-soon-settings`

| الحقل | الوظيفة |
|-------|---------|
| Enable Coming Soon | تفعيل / إيقاف بوابة الـ API |
| Access key | مفتاح المعاينة (مشفّر في قاعدة البيانات) |

قيم `.env` (`COMING_SOON_ENABLED` / `COMING_SOON_ACCESS_KEY`) تُستخدم كـ **fallback** إذا لم تُحفظ إعدادات من الداشبورد بعد.

الفرونت يعتمد على `GET /coming-soon/status` → `data.enabled`.

---

## فهرس

| # | Method | Endpoint | Auth | الوصف |
|---|--------|----------|------|--------|
| 1 | `GET` | `/coming-soon/status` | لا | هل البوابة مفعّلة؟ وهل الطلب الحالي مفتوح؟ |
| 2 | `POST` | `/coming-soon/unlock` | لا | إدخال مفتاح الوصول |

---

## شكل الرد العام

```json
{
  "success": true,
  "status": "success",
  "message": "...",
  "data": { }
}
```

عند الخطأ:

```json
{
  "success": false,
  "status": "error",
  "message": "...",
  "data": null
}
```

---

## التدفق المطلوب من الفرونت

```
1. عند فتح التطبيق → GET /coming-soon/status
2. إذا enabled=false        → الموقع عادي (لا Coming Soon)
3. إذا enabled=true:
     أ) عندك key محفوظ محلياً؟
        - نعم → أرسله بهيدر X-Coming-Soon-Key مع كل طلب
        - لا  → اعرض صفحة Coming Soon
     ب) المستخدم يدخل المفتاح → POST /coming-soon/unlock
     ج) عند النجاح → احفظ المفتاح (localStorage) واعرض الموقع
4. أي API يرجع 503 مع coming_soon=true → رجّع المستخدم لصفحة Coming Soon
```

---

## 1) Status — حالة البوابة

```http
GET /api/v1/coming-soon/status
Accept: application/json
```

### Response (مثال — البوابة مفعّلة وغير مفتوحة)

```json
{
  "success": true,
  "status": "success",
  "message": null,
  "data": {
    "enabled": true,
    "unlocked": false,
    "header": "X-Coming-Soon-Key"
  }
}
```

| الحقل | النوع | المعنى |
|-------|-------|--------|
| `enabled` | `boolean` | `true` = Coming Soon مفعّل من السيرفر |
| `unlocked` | `boolean` | `true` = هذا الطلب مسموح (فيه key/cookie صالح، أو البوابة مطفأة) |
| `header` | `string` | اسم الهيدر الذي يجب إرساله بعد الفتح |

### متى تعتبر الموقع مفتوحاً؟

```ts
const open = !data.enabled || data.unlocked;
```

---

## 2) Unlock — فتح الموقع بالمفتاح

```http
POST /api/v1/coming-soon/unlock
Accept: application/json
Content-Type: application/json

{
  "key": "THE_ACCESS_KEY"
}
```

### Response (نجاح — 200)

```json
{
  "success": true,
  "status": "success",
  "message": "Access granted.",
  "data": {
    "enabled": true,
    "unlocked": true,
    "header": "X-Coming-Soon-Key",
    "key": "THE_ACCESS_KEY"
  }
}
```

احفظ `data.key` عندك (مثلاً `localStorage`) واستخدمه كقيمة للهيدر.

### Response (مفتاح غلط — 403)

```json
{
  "success": false,
  "status": "error",
  "message": "Invalid access key.",
  "data": null
}
```

### Validation (422)

إذا ما أُرسل `key`:

```json
{
  "message": "The key field is required.",
  "errors": {
    "key": ["The key field is required."]
  }
}
```

### إذا البوابة مطفأة

`POST /unlock` يرجع نجاحاً مع:

```json
{
  "enabled": false,
  "unlocked": true
}
```

لا تحتاج تخزين key في هذه الحالة.

---

## 3) الهيدر الإلزامي بعد الفتح

بعد unlock ناجح، **كل طلبات الـ API** (ما عدا status/unlock) لازم تحمل:

```http
X-Coming-Soon-Key: THE_ACCESS_KEY
```

مثال:

```http
GET /api/v1/home/filters?locale=ar
Accept: application/json
X-Coming-Soon-Key: THE_ACCESS_KEY
```

بدون الهيدر (والبوابة مفعّلة) يرجع السيرفر **503**:

```json
{
  "success": false,
  "status": "error",
  "message": "Coming soon. Provide a valid access key.",
  "data": {
    "coming_soon": true,
    "unlock_url": "https://{APP_URL}/api/v1/coming-soon/unlock"
  }
}
```

إذا رأيت `data.coming_soon === true` → امسّح الـ key المحفوظ (إذا كان باطلاً) واعرض صفحة Coming Soon.

---

## مثال تكامل (React / Axios)

### تخزين المفتاح

```ts
const STORAGE_KEY = 'coming_soon_key';
const HEADER_NAME = 'X-Coming-Soon-Key';

export function getComingSoonKey(): string | null {
  return localStorage.getItem(STORAGE_KEY);
}

export function setComingSoonKey(key: string) {
  localStorage.setItem(STORAGE_KEY, key);
}

export function clearComingSoonKey() {
  localStorage.removeItem(STORAGE_KEY);
}
```

### Axios interceptor

```ts
api.interceptors.request.use((config) => {
  const key = getComingSoonKey();
  if (key) {
    config.headers[HEADER_NAME] = key;
  }
  return config;
});

api.interceptors.response.use(
  (res) => res,
  (error) => {
    const data = error.response?.data?.data;
    if (error.response?.status === 503 && data?.coming_soon) {
      clearComingSoonKey();
      // وجّه المستخدم لصفحة /coming-soon في الـ React Router
      window.location.assign('/coming-soon');
    }
    return Promise.reject(error);
  }
);
```

### صفحة Coming Soon

```ts
async function checkGate() {
  const { data } = await api.get('/coming-soon/status');
  const gate = data.data;

  if (!gate.enabled) return 'open';
  if (getComingSoonKey()) return 'open'; // الهيدر رح ينرسل تلقائياً
  return 'locked';
}

async function unlock(key: string) {
  const { data } = await api.post('/coming-soon/unlock', { key });
  if (data.success && data.data.key) {
    setComingSoonKey(data.data.key);
    return true;
  }
  return false;
}
```

---

## ملاحظات مهمة للفرونت

1. **التصميم عندكم** — صفحة Coming Soon (عنوان، لوجو، فورم المفتاح، رسائل خطأ) من الفرونت.
2. **المفتاح مش حساب مستخدم** — هو مفتاح مشترك للمعاينة قبل الإطلاق.
3. **لا تعتمد على Cookie المتصفح وحدها للـ SPA** — الأفضل تخزين المفتاح محلياً وإرساله بالهيدر (خصوصاً إذا الفرونت والدومين مختلفين / CORS).
4. **`/admin` مستثنى** — لوحة Filament تبقى متاحة بدون المفتاح (من جهة الباكند).
5. **عند الإطلاق الرسمي** الباكند يطفّئ البوابة (`COMING_SOON_ENABLED=false`) — عندها `status` يرجع `enabled: false` وتقدروا تخفوا صفحة Coming Soon تلقائياً.

---

## Checklist للفرونت

- [ ] استدعاء `GET /coming-soon/status` عند الإقلاع
- [ ] صفحة Coming Soon مع حقل المفتاح + رسالة خطأ عند 403
- [ ] `POST /coming-soon/unlock` وحفظ `data.key`
- [ ] إرسال `X-Coming-Soon-Key` مع كل طلبات الـ API
- [ ] معالجة `503` + `coming_soon: true` بإرجاع المستخدم لصفحة القفل
- [ ] إذا `enabled: false` → تخطّي البوابة بالكامل

---

## Postman

موجود في الـ collection:

- مجلد **Coming Soon** → `Status` / `Unlock`
- متغير البيئة: `coming_soon_key`
- Pre-request على مستوى الـ collection يضيف الهيدر تلقائياً إذا المتغير موجود

الملفات:

- `postman/Al-Shaheen.postman_collection.json`
- `postman/Al-Shaheen.postman_environment.json`
