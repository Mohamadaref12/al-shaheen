# الإيميلات التلقائية — Al Shaheen

توثيق لنظام الإيميلات الآلية في المشروع (تحديث: يوليو 2026).

---

## نظرة عامة

| البند | التفاصيل |
|--------|-----------|
| آلية الإرسال | `TransactionalMailer` + `TransactionalMail` (Queue) |
| اللغات | عربي (`ar`) وإنجليزي (`en`) حسب `user.locale` أو لغة الزائر |
| قوالب الإيميل | `resources/views/emails/` |
| نصوص الترجمة | `lang/ar/emails.php` و `lang/en/emails.php` |
| التنبيهات الداخلية | Filament database notifications (بالإضافة للإيميل للفريق) |
| Queue | `QUEUE_CONNECTION=database` — يشترط `php artisan queue:work` |

### ملفات الكود الرئيسية

| الملف | الدور |
|-------|--------|
| `app/Support/TransactionalMailer.php` | إرسال الإيميلات |
| `app/Support/AdminNotifier.php` | إيميل + تنبيه داخلي للمحررين/المشرفين |
| `app/Mail/TransactionalMail.php` | Mailable عام |
| `app/Mail/PasswordResetCodeMail.php` | إيميل رمز إعادة كلمة المرور |

---

## 1. إيميلات الفريق التحريري

**المستلمون:** محررون + مشرفون (`AdminNotifier::editorsAndAdmins()` أو `admins()` للتواصل فقط).

| # | مفتاح الترجمة | متى يُرسل | المحفّز (Trigger) | رابط الإجراء |
|---|---------------|-----------|------------------|--------------|
| 1 | `staff.article_review` | مقال بانتظار المراجعة | `ArticleObserver` — حالة: `submitted`, `under_review`, `review`, `ready`, `scheduled` | تعديل المقال في Filament |
| 2 | `staff.news_review` | خبر بانتظار المراجعة | `NewsObserver` — حالة: `under_review` | تعديل الخبر |
| 3 | `staff.opinion_review` | رأي بانتظار المراجعة | `OpinionObserver` — حالة: `under_review` | تعديل الرأي |
| 4 | `staff.comment_pending` | تعليق جديد معلّق | `CommentObserver` — عند الإنشاء بحالة `pending` | تعديل التعليق |
| 5 | `staff.contact_new` | رسالة تواصل جديدة | `ContactMessageObserver` — عند الإنشاء بحالة `new` | عرض الرسالة |
| 6 | `staff.writer_application` | طلب كاتب جديد | `WriterObserver` — عند `application_status = submitted` | تعديل طلب الكاتب |
| 7 | `staff.submission_review` | مشاركة محتوى جديدة | `ContentSubmissionObserver` — حالة: `submitted`, `under_review`, `pending`, `review` | تعديل المشاركة |

> **ملاحظة:** كل إيميل فريق يُرسل **مع** تنبيه الجرس داخل لوحة Filament.

---

## 2. إيميلات الكتّاب

**المستلم:** حساب المستخدم المرتبط بالكاتب/المؤلف (`User` عبر `author` أو `writer.user`).

| # | مفتاح الترجمة | متى يُرسل | المحفّز |
|---|---------------|-----------|---------|
| 1 | `writer.application_received` | استلام طلب الانضمام ككاتب | `WriterObserver` — إنشاء بحالة `submitted` أو تحديث إلى `submitted` |
| 2 | `writer.application_approved` | قبول الطلب | `WriterObserver` — `application_status = approved` |
| 3 | `writer.application_rejected` | رفض الطلب | `WriterObserver` — `application_status = rejected` (قد تتضمن ملاحظات) |
| 4 | `writer.application_suspended` | تعليق الحساب | `WriterObserver` — `application_status = suspended` |
| 5 | `writer.verified_granted` | منح شارة كاتب موثّق | `WriterVerificationReview::grantVerifiedTier()` |
| 6 | `writer.verified_revoked` | إلغاء الشارة | `WriterVerificationReview::revokeVerifiedTier()` |
| 7 | `writer.article_ready` | المقال جاهز للنشر | `ArticleObserver` — حالة `ready` |
| 8 | `writer.article_published` | المقال منشور | `ArticleObserver` — حالة `published` |
| 9 | `writer.article_rejected` | المقال مرفوض | `ArticleObserver` — حالة `rejected` (مع `writer_notes` إن وُجدت) |
| 10 | `writer.news_published` | الخبر منشور | `NewsObserver` — حالة `published` |
| 11 | `writer.opinion_published` | الرأي منشور | `OpinionObserver` — حالة `published` |
| 12 | `writer.submission_approved` | المشاركة مقبولة | `ContentSubmissionObserver` — حالة `approved` |
| 13 | `writer.submission_rejected` | المشاركة مرفوضة | `ContentSubmissionObserver` — حالة `rejected` |

---

## 3. إيميلات القرّاء والزوار

| # | مفتاح الترجمة | متى يُرسل | المحفّز | المستلم |
|---|---------------|-----------|---------|---------|
| 1 | `reader.contact_received` | تأكيد استلام رسالة التواصل | `ContactMessageObserver` — عند الإرسال من API | بريد المرسل (`contact_messages.email`) |
| 2 | `reader.contact_replied` | رد على رسالة التواصل | `ViewContactMessage` — زر **Mark as Replied** + نص الرد | بريد المرسل |
| 3 | `reader.comment_pending` | التعليق قيد المراجعة | `CommentObserver` — عند إنشاء تعليق `pending` | مستخدم التعليق |
| 4 | `reader.comment_approved` | التعليق وُوفق عليه | `CommentObserver` — تحديث الحالة إلى `approved` | مستخدم التعليق |
| 5 | `reader.comment_rejected` | التعليق رُفض | `CommentObserver` — تحديث الحالة إلى `rejected` | مستخدم التعليق |
| 6 | `reader.welcome` | ترحيب بعد التسجيل | `AuthController::register()` | المستخدم الجديد |
| 7 | `reader.newsletter_subscribed` | تأكيد الاشتراك بالنشرة | `NewsletterController::subscribe()` | بريد المشترك |
| 8 | `reader.newsletter_unsubscribed` | تأكيد إلغاء الاشتراك | `NewsletterController::unsubscribe()` | بريد المشترك |
| 9 | `reader.password_reset` | رمز إعادة تعيين كلمة المرور | `PasswordResetController::forgot()` | المستخدم |

### API إعادة كلمة المرور

```
POST /api/v1/forgot-password
Body: { "email": "user@example.com" }

POST /api/v1/reset-password
Body: {
  "email": "user@example.com",
  "code": "123456",
  "password": "newpassword",
  "password_confirmation": "newpassword"
}
```

الرمز صالح **15 دقيقة** ويُخزَّن في Cache.

---

## إعداد البيئة

### تطوير محلي (بدون SMTP)

```env
MAIL_MAILER=log
QUEUE_CONNECTION=database
```

الإيميلات تظهر في `storage/logs/laravel.log`.

### إنتاج

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_FROM_ADDRESS=news@alshaheen.com
MAIL_FROM_NAME="Al Shaheen"
QUEUE_CONNECTION=database
```

ثم تشغيل عامل الطابور:

```bash
php artisan queue:work
```

---

## Observers المسجّلة

في `app/Providers/AppServiceProvider.php`:

- `ArticleObserver`
- `NewsObserver`
- `OpinionObserver`
- `CommentObserver`
- `ContactMessageObserver`
- `WriterObserver`
- `ContentSubmissionObserver`

---

## ملخص العدد

| الفئة | العدد |
|-------|-------|
| فريق تحريري | 7 |
| كتّاب | 13 |
| قرّاء وزوار | 9 |
| **المجموع** | **29 إيميلاً تلقائياً** |

---

## ما لم يُنفَّذ بعد (مقترحات لاحقة)

- نشرة بريدية دورية (ملخص أسبوعي)
- تنبيه خبر عاجل (`is_breaking`)
- تذكير مقال مجدول (`scheduled`)
- إيصال دفع
- تنبيه انتهاء اشتراك
