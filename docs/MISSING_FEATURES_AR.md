# Al Shaheen 360 — ما تم وما لا يزال ناقصاً

> آخر مراجعة: 10 يونيو 2026  
> المرجع: ملف `Al Shaheen 360_compressed.pdf` + `docs/AL_SHAHEEN_360_SCENARIO_AR.md`  
> الغرض: مقارنة المواصفات (wireframes + سيناريو المنصة) مع حالة التنفيذ الحالية في المشروع.

---

## ملخص سريع

| الطبقة | التقدّم التقريبي | الملاحظة |
|--------|------------------|----------|
| **Database + Models** | ~95% | الجداول والعلاقات والـ seeders جاهزة |
| **Filament Admin** | ~90% | 23 resource، ترتيب القائمة مُحدَّث (Users → Content) |
| **REST API v1** | ~65% | معظم endpoints القراءة والكاتب موجودة |
| **Frontend (React)** | 0% | الـ PDF كله wireframes لواجهة غير مبنية بعد |
| **Monetization فعلي** | ~30% | بنية DB موجودة، الدفع والـ gating غير مكتملين |

**الخلاصة:** الـ Backend قوي كأساس، لكن **الواجهة الأمامية بالكامل** وعدة **APIs للمستخدم المسجّل والمحرر والإعلانات** لا تزال ناقصة.

---

## ما تم تنفيذه ✅

### البنية التحتية
- [x] Laravel 13 + Filament v5
- [x] 26 migration + 25 model + 15 seeder
- [x] Class Table Inheritance للأدوار (readers, contributors, writer, editors, admins)
- [x] Sanctum auth للـ API
- [x] توثيق: `DOCS.md`, `ERD.md`, `AL_SHAHEEN_360_SCENARIO_AR.md`, `WRITER_DASHBOARD_API.md`

### Filament Admin (23 Resource)
- [x] **Users:** Users, Readers, Contributors, Writers, Editors, Admins
- [x] **Content:** Articles, Reports, Interviews, Media Items, Comments, Content Submissions
- [x] **Catalog:** Categories, Tags
- [x] **Marketing:** Ads, Newsletter Subscribers
- [x] **Events:** Events
- [x] **Monetization:** Payments
- [x] **Subscriptions:** Packages, Subscriptions
- [x] **Training:** Courses, Lessons, User Course Progress

### API v1 — موجود
| المجال | Endpoints |
|--------|-----------|
| Auth | register, login, logout, me, profile, change-password |
| Home | top-articles, trending, editor-picks, filters, writers |
| Articles | CRUD + related + trending-topics + next-read |
| Categories | primary/secondary + trending + editor-picks + writers |
| Writers | list, show, profile update, dashboard (overview, articles, drafts, analytics, preview) |
| Comments | list, create, delete |
| Reports / Interviews / Media / Events / Tags | list + show |
| Training | courses + progress |
| Newsletter | subscribe, unsubscribe |
| Subscriptions | packages, subscribe, list |
| Uploads | images |

### Editorial & Content (Backend)
- [x] Article status workflow: `draft → submitted → under_review → ready → scheduled → published → rejected → archived`
- [x] Writer application status: `draft → submitted → under_review → approved → rejected → suspended`
- [x] Primary + secondary categories + tags على المقال
- [x] Article revisions + article_views
- [x] Comments moderation (pending / approved / rejected / spam)
- [x] Breaking news flag (`is_breaking`) + premium flag (`is_premium`)
- [x] Ad placements في DB (6 zones) + ad categories

---

## ما لا يزال ناقصاً ❌

### 1. الواجهة الأمامية (Frontend) — الأولوية القصوى

الـ PDF (22 صفحة) يصف wireframes لموقع **React**. المشروع حالياً: `welcome.blade.php` فقط.

| الشاشة (حسب PDF) | Backend | Frontend |
|------------------|:-------:|:--------:|
| Homepage (ticker, hero, category rows, newsletter, video, opinion, writers, events, footer) | ⚠️ APIs متفرقة | ❌ |
| Category Page (filters, lead story, grid, right rail) | ✅ | ❌ |
| Article Page (byline, body, author card, related, comments, next read, premium CTA) | ✅ | ❌ |
| Writers Directory & Profile (+ Follow) | ⚠️ | ❌ |
| Registration 4 خطوات (Account → Profile → Expertise → Portfolio) | ⚠️ | ❌ |
| Writer Dashboard UI | ✅ API | ❌ |
| Editor Dashboard UI (Content Queue) | ❌ API | ❌ |
| Subscribe Page | ⚠️ | ❌ |
| About & Contact | ❌ | ❌ |
| Training Tab | ✅ API | ❌ |

**Navigation المطلوبة في PDF:** Home, News, Reports, Interviews, Opinion, Multimedia, Writers, Submit Content, Subscribe, About, Contact

---

### 2. Phase 1 — Foundation (نواقص API)

| # | الميزة | الحالة | التفاصيل |
|---|--------|--------|----------|
| 1 | **Saved Articles** | ✅ | `GET me/social?type=saved`, `POST articles/{id}/save` (toggle) |
| 2 | **Follow Writers** | ✅ | `GET me/social?type=following`, `POST writers/{id}/follow` (toggle), `GET writers/{id}/followers` |
| 3 | **Breaking News Ticker** | ✅ | `GET home/breaking-news` |
| 4 | **Forgot / Reset Password** | ❌ | `ForgotPasswordRequest` و `ResetPasswordRequest` موجودان — لا routes |
| 5 | **About & Contact** | ❌ | مذكورة في navigation — لا Filament resource ولا API |
| 6 | **Site Settings** | ❌ | مذكورة في سيناريو Admin — غير موجودة |

---

### 3. Phase 2 — CMS / Editorial

| # | الميزة | الحالة | التفاصيل |
|---|--------|--------|----------|
| 1 | **Editor Dashboard API** | ❌ | Content Queue, Review, Approve/Reject/Schedule — Filament فقط |
| 2 | **Contributor Dashboard API** | ❌ | إرسال محتوى + متابعة الحالة — غير موجود |
| 3 | **Content Submissions API** | ❌ | Filament فقط — لا API للمساهم/الكاتب |
| 4 | **Writer Registration 4-step** | ⚠️ | register واحد — ناقص: phone, ID verification, sample work, media affiliation كـ flow متعدد الخطوات |
| 5 | **Permission Middleware** | ❌ | موصى به في PDF — لا policies/middleware للأدوار والصلاحيات |
| 6 | **Policies / Observers** | ❌ | غير مُنفَّذة |

**بيانات التسجيل الناقصة مقارنة بالـ PDF:**

| الحقل | PDF | الحالي |
|-------|-----|--------|
| Phone | ✅ Writer registration | ❌ محذوف من `users` |
| ID Verification | ✅ Verified tier | ✅ DB + Filament — ❌ API flow |
| Sample Publications | ✅ Verified tier | ✅ DB — ❌ API flow |
| Media Affiliation | ✅ Verified tier | ✅ DB — ❌ API flow |
| 4-step wizard UI | ✅ | ❌ |

---

### 4. Phase 3 — Monetization

| # | الميزة | الحالة | التفاصيل |
|---|--------|--------|----------|
| 1 | **Ads API** | ❌ | 6 zones في PDF — Filament فقط، لا endpoint لجلب إعلانات حسب `placement` |
| 2 | **Ad-light للـ Premium** | ❌ | `ad_light` في `subscription_packages` — غير مُطبَّق في API |
| 3 | **Premium Content Gating** | ❌ | `is_premium` على Reports/Interviews/Media/Training — لا فحص اشتراك عند القراءة |
| 4 | **Payment Gateway** | ❌ | Subscribe ينشئ subscription مباشرة بدون Stripe/PayPal/… |
| 5 | **Payments API** | ❌ | Filament فقط |

**Ad Placements المطلوبة (PDF ص 20–21):**

1. Leaderboard Banner (728×90) — أعلى Homepage
2. Hero Takeover — full-width، Homepage فقط
3. In-Feed Native Ad — بين بطاقات المقالات، labeled "Sponsored"
4. Mid-Article Banner (300×250) — بعد الفقرة 3–4
5. Right Rail Rectangle (300×600) — sidebar ثابت
6. Footer Banner (728×90) — site-wide

**Ad Categories:** News & Media, Government, Education, Technology, Finance, Real Estate, Healthcare, Events & Conferences

---

### 5. Phase 4 — Optimization

| # | الميزة | الحالة |
|---|--------|--------|
| 1 | Global Search API | ❌ (بحث داخل articles فقط عبر query param) |
| 2 | Platform Analytics Dashboard | ❌ |
| 3 | Personalization / Recommendations | ❌ |
| 4 | Multilingual Automation | ❌ (locale ar/en موجود، لا ترجمة تلقائية) |
| 5 | Notifications (email / push / in-app) | ❌ |
| 6 | Writer Ranking متقدم | ⚠️ (high-performing writers API موجود جزئياً) |

---

## مصفوفة: PDF Spec ↔ التنفيذ

```
الميزة                      Admin    API      Frontend
──────────────────────────────────────────────────────
Auth (register/login)        —       ✅        ❌
Saved Articles               —       ✅        ❌
Follow Writers               —       ✅        ❌
Homepage                     —       ⚠️        ❌
Category Pages               ✅      ✅        ❌
Article Pages                ✅      ✅        ❌
Writer Profile               ✅      ✅        ❌
Writer Dashboard             —       ✅        ❌
Editor Dashboard             ✅      ❌        ❌
Contributor Flow             ✅      ❌        ❌
Comments + Moderation        ✅      ✅        ❌
Editorial Workflow           ✅      ⚠️        ❌
Reports / Interviews / Media ✅      ✅        ❌
Training                     ✅      ✅        ❌
Events                       ✅      ✅        ❌
Newsletter                   ✅      ✅        ❌
Subscriptions                ✅      ⚠️        ❌
Ads (6 zones)                ✅      ❌        ❌
Payments                     ✅      ❌        ❌
Premium Gating               —       ❌        ❌
About / Contact              ❌      ❌        ❌
Search                       —       ❌        ❌
Notifications                —       ❌        ❌
```

**Legend:** ✅ مكتمل · ⚠️ جزئي · ❌ غير موجود

---

## معايير قبول MVP (PDF §27) — حالة كل بند

| # | المعيار | الحالة |
|---|---------|--------|
| 1 | الزائر يتصفح Homepage والأقسام والمقالات | ⚠️ API فقط، لا frontend |
| 2 | التسجيل وتسجيل الدخول | ✅ API |
| 3 | القارئ يحفظ ويعلّق ويتابع الكاتب | ✅ API (حفظ/متابعة + تعليق) — ❌ frontend |
| 4 | الكاتب ينشئ مسودة ويرسل للمراجعة | ✅ API |
| 5 | المحرر يقبل/يرفض/يجدول | ✅ Filament — ❌ Editor API |
| 6 | الأدمن يدير الأقسام والمقالات والكتّاب | ✅ Filament |
| 7 | primary + secondary categories + tags | ✅ |
| 8 | Home structure: ticker, hero, category rows, footer | ❌ frontend |
| 9 | Article Page: author card + related stories | ⚠️ API — ❌ frontend |
| 10 | بنية الاشتراكات والإعلانات | ⚠️ DB + Admin — بدون payment/ads API |
| 11 | Training courses + lessons | ✅ |

---

## الأولويات المقترحة للتنفيذ

### أولاً — يفتح MVP للقارئ
1. Frontend React: Home, Category, Article, Auth
2. ~~Saved Articles API~~ ✅
3. ~~Follow Writers API~~ ✅
4. Forgot / Reset Password

### ثانياً — يكمل غرفة الأخبار
5. Editor Dashboard API (Content Queue)
6. Contributor / Content Submissions API
7. Permission Middleware + Policies

### ثالثاً — Monetization
8. Ads API + ad-light logic
9. Premium content gating middleware
10. Payment gateway integration

### رابعاً — نمو وتحسين
11. Global Search
12. Notifications
13. About / Contact / Site Settings
14. Platform Analytics

---

## ملاحظات تقنية مهمة (من PDF)

- **Verified Writer** = flag (`is_verified_writer`) وليس role منفصل ✅ مطبَّق
- **Package logic** منفصل عن **Role logic** ✅ في DB — ⚠️ يحتاج middleware
- **لا نشر مباشر** من Contributor/Writer — submit → review → publish ✅
- **Permission middleware layer** موصى به — ❌ غير موجود بعد
- **Soft Deletes** للجداول المهمة — تحقق عند التنفيذ
- **SEO fields** — موجودة على Article ✅

---

## ملفات مرجعية

| الملف | المحتوى |
|-------|---------|
| `docs/AL_SHAHEEN_360_SCENARIO_AR.md` | السيناريو الكامل للمنصة |
| `docs/DOCS.md` | Stack + IA + جداول + build order |
| `docs/ERD.md` | مخطط قاعدة البيانات |
| `docs/WRITER_DASHBOARD_API.md` | توثيق Writer Dashboard API |
| `docs/API_CHANGES_GUIDE.md` | دليل تغييرات API |

---

## سجل التحديثات

| التاريخ | التغيير |
|---------|---------|
| 2026-06-10 | إنشاء الملف — مراجعة أولى مقابل PDF + codebase |
| 2026-06-10 | Saved Articles + Follow Writers + Breaking News Ticker APIs |
