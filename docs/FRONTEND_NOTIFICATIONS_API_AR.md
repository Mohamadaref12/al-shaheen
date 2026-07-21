# دليل الفرونت — واجهات الإشعارات (API v1)

> آخر تحديث: 17 يوليو 2026  
> Base URL: `{APP_URL}/api/v1`  
> Auth: **Bearer Token** (`Authorization: Bearer {token}`) عبر Sanctum  
> Header موصى به: `Accept: application/json`

هذا الملف يشرح **كل APIs الخاصة بالإشعارات** للويب/الموبايل:

1. صندوق الإشعارات داخل التطبيق (Database Inbox)
2. تسجيل/إلغاء توكن FCM لاستقبال Push
3. أنواع الإشعارات التلقائية وحمولة الـ Push

---

## فهرس

| # | Method | Endpoint | Auth | الوصف |
|---|--------|----------|------|--------|
| 1 | `GET` | `/notifications` | مطلوب | قائمة الإشعارات (مع pagination) |
| 2 | `GET` | `/notifications/unread-count` | مطلوب | عدد غير المقروء |
| 3 | `POST` | `/notifications/{id}/read` | مطلوب | تعليم إشعار كمقروء |
| 4 | `POST` | `/notifications/read-all` | مطلوب | تعليم الكل كمقروء |
| 5 | `DELETE` | `/notifications/{id}` | مطلوب | حذف إشعار |
| 6 | `POST` | `/devices/fcm-token` | اختياري | تسجيل جهاز (ضيف أو مسجّل) |
| 7 | `DELETE` | `/devices/fcm-token` | اختياري | إلغاء تسجيل جهاز |
| 8 | `POST` | `/me/devices/fcm-token` | مطلوب | تسجيل جهاز للمستخدم الحالي |
| 9 | `DELETE` | `/me/devices/fcm-token` | مطلوب | إلغاء تسجيل جهاز للمستخدم الحالي |

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

عند الـ pagination يظهر `meta` أيضاً.

---

## نموذج الإشعار (Notification Object)

كل عنصر في قائمة الإشعارات بهذا الشكل:

```json
{
  "id": "9c8f1a2b-....-uuid",
  "type": "article_published",
  "title": "Article published",
  "body": "عنوان المقال…",
  "url": null,
  "status": null,
  "data": {
    "article_id": "12"
  },
  "read_at": null,
  "is_read": false,
  "created_at": "2026-07-17T12:30:00+00:00"
}
```

| الحقل | النوع | ملاحظات |
|-------|------|---------|
| `id` | string (UUID) | معرّف الإشعار — يُستخدم في read/delete |
| `type` | string | نوع الحدث (انظر جدول الأنواع أدناه) |
| `title` | string \| null | عنوان قصير |
| `body` | string \| null | نص توضيحي |
| `url` | string \| null | رابط تنقّل إن وُجد |
| `status` | string \| null | نادر الاستخدام حالياً (غالباً `null`) |
| `data` | object | بيانات إضافية للتنقل/العرض (`article_id`, `news_id`, …) |
| `read_at` | ISO8601 \| null | وقت القراءة |
| `is_read` | boolean | هل مقروء؟ |
| `created_at` | ISO8601 | وقت الإنشاء |

> كل قيم `data` المرسلة من السيرفر هي **strings** (حتى الأرقام)، لتوافق FCM.

---

## 1. قائمة الإشعارات

```
GET /notifications
```

**Auth:** مطلوب

### Query params

| Param | النوع | افتراضي | الوصف |
|-------|------|---------|--------|
| `per_page` | int | `20` | بين `1` و `50` |
| `unread_only` | bool | `false` | إذا `1` / `true` يعرض غير المقروء فقط |
| `page` | int | `1` | رقم الصفحة |

### مثال

```
GET /api/v1/notifications?per_page=20&unread_only=0&page=1
Authorization: Bearer {token}
```

### Response

```json
{
  "success": true,
  "status": "success",
  "message": "Notifications retrieved successfully.",
  "data": [
    {
      "id": "9c8f1a2b-....",
      "type": "comment_approved",
      "title": "Comment approved",
      "body": "Your comment on \"...\" was approved.",
      "url": null,
      "status": null,
      "data": {
        "comment_id": "5",
        "article_id": "12"
      },
      "read_at": null,
      "is_read": false,
      "created_at": "2026-07-17T12:30:00+00:00"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 8,
    "last_page": 1,
    "unread_count": 3
  }
}
```

> `meta.unread_count` مفيد لعرض الـ badge دون طلب إضافي.

---

## 2. عدد غير المقروء

```
GET /notifications/unread-count
```

**Auth:** مطلوب

### Response

```json
{
  "success": true,
  "status": "success",
  "message": "Unread notifications count.",
  "data": {
    "unread_count": 3
  }
}
```

استخدمه لـ polling خفيف على الـ badge، أو بعد فتح الشاشة.

---

## 3. تعليم إشعار كمقروء

```
POST /notifications/{id}/read
```

**Auth:** مطلوب  
**Body:** لا حاجة

### Response (نجاح)

```json
{
  "success": true,
  "status": "success",
  "message": "Notification marked as read.",
  "data": {
    "id": "9c8f1a2b-....",
    "type": "comment_approved",
    "title": "Comment approved",
    "body": "...",
    "url": null,
    "status": null,
    "data": {},
    "read_at": "2026-07-17T12:45:00+00:00",
    "is_read": true,
    "created_at": "2026-07-17T12:30:00+00:00"
  }
}
```

### Response (غير موجود)

```json
{
  "success": false,
  "status": "error",
  "message": "Notification not found.",
  "data": null
}
```

الحالة: `404`

---

## 4. تعليم الكل كمقروء

```
POST /notifications/read-all
```

**Auth:** مطلوب  
**Body:** لا حاجة

### Response

```json
{
  "success": true,
  "status": "success",
  "message": "All notifications marked as read.",
  "data": {
    "unread_count": 0
  }
}
```

---

## 5. حذف إشعار

```
DELETE /notifications/{id}
```

**Auth:** مطلوب

### Response (نجاح)

```json
{
  "success": true,
  "status": "success",
  "message": "Notification deleted.",
  "data": null
}
```

### Response (غير موجود)

`404` + `"Notification not found."`

---

## 6–9. تسجيل جهاز FCM (Push)

الإشعارات الفورية (Push) تعتمد على تسجيل **FCM device token**.

يوجد مساران متكافئان تقريباً:

| المسار | Auth | متى تستخدمه |
|--------|------|-------------|
| `POST/DELETE /devices/fcm-token` | اختياري | ضيف، أو قبل/بعد تسجيل الدخول |
| `POST/DELETE /me/devices/fcm-token` | مطلوب | بعد تسجيل الدخول (موصى به للتطبيق) |

عند وجود مستخدم مصادق، يُربط التوكن بـ `user_id` حتى تصل الإشعارات الشخصية.

### تسجيل توكن

```
POST /devices/fcm-token
POST /me/devices/fcm-token
```

#### Body (JSON)

```json
{
  "token": "FCM_DEVICE_TOKEN",
  "platform": "android",
  "locale": "ar"
}
```

| الحقل | مطلوب؟ | القيم |
|-------|--------|--------|
| `token` | نعم | string ≤ 2048 |
| `platform` | لا | `web` \| `dashboard` \| `android` \| `ios` — الافتراضي `web` |
| `locale` | لا | مثل `ar` / `en` (≤ 8) |

#### Response

```json
{
  "success": true,
  "status": "success",
  "message": "Device registered for push notifications.",
  "data": {
    "id": 42,
    "platform": "android"
  }
}
```

> نفس الـ `token` يُحدَّث (upsert) إذا سُجّل مرة أخرى — لا مشكلة بإعادة الإرسال عند كل فتح للتطبيق.

### إلغاء التسجيل

```
DELETE /devices/fcm-token
DELETE /me/devices/fcm-token
```

#### Body (JSON)

```json
{
  "token": "FCM_DEVICE_TOKEN"
}
```

#### Response

```json
{
  "success": true,
  "status": "success",
  "message": "Device unregistered.",
  "data": null
}
```

**موعد الاستدعاء المقترح:** عند Logout احذف التوكن حتى لا تصل إشعارات المستخدم السابق لنفس الجهاز.

---

## تدفق مقترح للفرونت / الموبايل

```
1) احصل على FCM token من Firebase SDK
2) POST /me/devices/fcm-token  (بعد login)
   أو POST /devices/fcm-token   (ضيف / قبل login)
3) اشترك بـ Topics العامة إن رغبت (من جهة العميل):
   - articles
   - news
   - opinions
4) اعرض Inbox عبر GET /notifications
5) عند الضغط على إشعار: POST /notifications/{id}/read ثم تنقّل حسب type + data
6) عند Logout: DELETE .../fcm-token
```

### Topics العامة (Broadcast)

عند نشر محتوى جديد، السيرفر يرسل Push إلى topic (بدون Inbox شخصي للجميع):

| Topic | متى |
|-------|-----|
| `articles` | نشر مقال |
| `news` | نشر خبر |
| `opinions` | نشر رأي |

الاشتراك بـ topic يتم من **عميل Firebase** (React Native / Flutter / Web)، وليس عبر API الباكند حالياً.

---

## حمولة Push (FCM Data)

الرسالة تحتوي `notification` (عنوان/نص ظاهر) + `data` (كلها strings):

```json
{
  "title": "Article published",
  "body": "عنوان المقال…",
  "url": "https://...",
  "type": "article_published",
  "article_id": "12"
}
```

حقول شائعة داخل `data`:

| المفتاح | المعنى |
|---------|--------|
| `type` | نوع الحدث |
| `url` | رابط فتح (إن وُجد) |
| `title` / `body` | نسخة نصية إضافية |
| `article_id` | مقال |
| `news_id` | خبر |
| `opinion_id` | رأي |
| `comment_id` | تعليق |
| `writer_id` | ملف كاتب |
| `submission_id` | طلب إرسال محتوى |

**Android channel:** `al_shaheen_default`  
**Android click action:** `FLUTTER_NOTIFICATION_CLICK`

---

## أنواع الإشعارات التلقائية (`type`)

هذه القيم تظهر في Inbox وفي `data.type` للـ Push.

### مقالات

| type | المستلم | متى | `data` |
|------|---------|-----|--------|
| `article_ready` | الكاتب | الموافقة (ready) | `article_id` |
| `article_published` | الكاتب + topic `articles` | النشر | `article_id` |
| `article_rejected` | الكاتب | الرفض | `article_id` |

### أخبار

| type | المستلم | متى | `data` |
|------|---------|-----|--------|
| `news_published` | الكاتب + topic `news` | النشر | `news_id` |
| `news_rejected` | الكاتب | الرفض | `news_id` |

### آراء

| type | المستلم | متى | `data` |
|------|---------|-----|--------|
| `opinion_published` | الكاتب + topic `opinions` | النشر | `opinion_id` |
| `opinion_rejected` | الكاتب | الرفض | `opinion_id` |

### تعليقات

| type | المستلم | متى | `data` |
|------|---------|-----|--------|
| `comment_pending` | صاحب التعليق | إرسال تعليق للمراجعة | `comment_id`, `article_id` |
| `comment_approved` | صاحب التعليق | الموافقة | `comment_id`, `article_id` |
| `comment_rejected` | صاحب التعليق | الرفض | `comment_id`, `article_id` |

### طلبات الكاتب / الحساب

| type | المستلم | متى | `data` |
|------|---------|-----|--------|
| `application_received` | المتقدّم | استلام الطلب | `writer_id` |
| `application_approved` | المتقدّم | قبول الطلب | `writer_id` |
| `application_rejected` | المتقدّم | رفض الطلب | `writer_id` |
| `application_suspended` | الكاتب | تعليق الحساب | `writer_id` |

### إرسالات المحتوى (Submissions)

| type | المستلم | متى | `data` |
|------|---------|-----|--------|
| `submission_approved` | الكاتب | قبول الإرسال | `submission_id` |
| `submission_rejected` | الكاتب | رفض الإرسال | `submission_id` |

---

## خريطة تنقّل مقترحة حسب `type`

| type | شاشة مقترحة |
|------|-------------|
| `article_*` | صفحة المقال عبر `data.article_id` |
| `news_*` | صفحة الخبر عبر `data.news_id` |
| `opinion_*` | صفحة الرأي عبر `data.opinion_id` |
| `comment_*` | صفحة المقال + التركيز على التعليق |
| `application_*` | حالة طلب الكاتب / الملف الشخصي |
| `submission_*` | تفاصيل الإرسال أو لوحة الكاتب |

إذا وُجد `url` غير فارغ، يمكن فتحه مباشرة كأولوية.

---

## أخطاء شائعة

| الحالة | السبب |
|--------|--------|
| `401` | بدون توكن أو توكن منتهي — على endpoints الـ Inbox و `/me/...` |
| `404` | `id` إشعار غير موجود أو ليس للمستخدم الحالي |
| `422` | validation — مثلاً نقص `token` أو `platform` غير مسموح |

مثال validation لـ FCM:

```json
{
  "message": "The token field is required.",
  "errors": {
    "token": ["The token field is required."]
  }
}
```

(شكل Laravel الافتراضي لأخطاء التحقق)

---

## ملخص سريع للتكامل

1. بعد Login: سجّل FCM عبر `POST /me/devices/fcm-token` مع `platform: android|ios|web`.
2. Inbox: `GET /notifications` + badge من `meta.unread_count` أو `/unread-count`.
3. عند الفتح: `POST /notifications/{id}/read`.
4. زر "تعليم الكل": `POST /notifications/read-all`.
5. سواييب حذف: `DELETE /notifications/{id}`.
6. عند Logout: `DELETE /me/devices/fcm-token` بنفس الـ token.
7. للمحتوى العام: اشترك بـ topics `articles` / `news` / `opinions` من Firebase Client SDK.

---

## Postman

المجلد موجود في الكولكشن:

`postman/Al-Shaheen.postman_collection.json` → **Notifications**

الطلبات الجاهزة:

- Register FCM Token → `POST {{base_url}}/me/devices/fcm-token`
- List Notifications
- Unread Count
- Mark As Read
- Mark All As Read
- Delete Notification
