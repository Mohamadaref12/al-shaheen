# Al Shaheen 360 — Project Documentation

## فكرة المشروع

**Al Shaheen 360** منصة إخبارية متكاملة تعمل كـ Newsroom System وليس مجرد بلوق.
تتيح للكتّاب نشر مقالات وتقارير، وللقراء الاشتراك بباقات، وللمحررين مراجعة المحتوى قبل نشره.

> "The platform should behave like a newsroom system, not a flat blog."

---

## Stack التقني

| الطبقة | التقنية |
|--------|---------|
| Backend | Laravel 13 |
| Admin Panel | Filament v5 |
| Frontend | React |
| Database | MySQL |
| Local Server | Laravel Herd |
| URL المحلي | http://al-shaheen.test |

---

## هيكل الصفحات (Information Architecture)

```
Home
├── News          → Category Page / Article Page
├── Reports       → Report Detail Page
├── Interviews    → Interview List / Interview Detail
├── Opinion       → Category Page
├── Multimedia    → Video / Audio content
├── Writers       → Writer Directory / Writer Profile / Submit Content / Dashboard
├── Training      → Course List / Course Detail / Lesson
├── Subscribe     → Packages
├── About
└── Contact
```

> القاعدة: الأقسام الرئيسية تبقى بين 6-8 لتجنب تشتيت هوية المنصة.

---

## نموذج الأدوار (Role Model)

| # | الدور | الوصف |
|---|-------|-------|
| 01 | **Reader / Registered User** | يقرأ، يحفظ، يعلّق، يتابع الكتّاب، يشترك بنشرات |
| 02 | **Contributor** | كاتب خارجي يرسل مقالاته للمراجعة التحريرية |
| 03 | **Writer** | كاتب داخلي أو معتمد، لديه Dashboard متقدم وإحصائيات |
| 04 | **Editor** | يراجع، يعدّل، يجدول، وينشر المحتوى |
| 05 | **Admin** | تحكم كامل: يوزرات، أقسام، باقات، إعدادات، مودريشن |

> **Verified Writer** هو **Flag** على رول Writer — وليس رول منفصل.

---

## بيانات التسجيل حسب الدور

| الحقل | Reader | Contributor | Writer | Verified |
|-------|--------|------------|--------|---------|
| Name, Email, Password | ✓ | ✓ | ✓ | ✓ |
| Country, Language | ✓ | ✓ | ✓ | ✓ |
| Bio, Profile Photo | — | ✓ | ✓ | ✓ |
| Writing Categories | — | ✓ | ✓ | ✓ |
| Portfolio Link | — | ✓ | ✓ | ✓ |
| Experience Level | — | — | ✓ | ✓ |
| Languages, Specialties | — | — | ✓ | ✓ |
| ID Verification, Media Affiliation | — | — | — | ✓ |
| Sample Publications | — | — | — | ✓ |

> Application Status للكاتب: `draft → submitted → under_review → approved / rejected / suspended`

---

## Packages & Permissions Matrix

| الدور | Registered Access | Article Submission | Editorial Queue | Analytics |
|-------|:-----------------:|:------------------:|:---------------:|:---------:|
| Free Reader | — | — | — | — |
| Registered User | ✓ | — | — | — |
| Contributor | ✓ | ✓ | — | — |
| Writer / Premium Writer | ✓ | ✓ | — | Optional |
| Premium Subscriber | ✓ | — | — | — |
| Editor | Invite Only | N/A | ✓ | — |

> Package logic منفصل عن Role logic في قاعدة البيانات.

---

## نموذج المحتوى (Content Model)

- **Article**: قسم رئيسي واحد + أقسام ثانوية متعددة + تاجات بلا حد
- **Report**: محتوى تحليلي منفصل، يدعم ملف PDF، يمكن أن يكون Premium
- **Content Submission**: كل سبمشن يمر بمراجعة Editor قبل النشر

### Article Status Flow
```
draft → submitted → under_review → published
                              ↘ rejected
```

---

## منطق صفحة القسم (Category Logic)

كل قسم رئيسي له صفحة خاصة تحتوي:
1. **Landing Page** — وصف قصير، chips، وأدوات ترتيب
2. **Filters** — منطقة، تاريخ، ترند، فورمات، الأكثر قراءة
3. **Article Cards** — عنوان، ملخص، قسم، وقت، حفظ/مشاركة
4. **Right Rail** — مواضيع رائجة، Editor Picks، أبرز الكتّاب

---

## Writer Profile

- يُعامَل كـ Product Feature لبناء الثقة وتكرار الزيارات
- يحتوي: الاسم، Verified Badge، Bio، Location، اللغات، الأقسام، Social Links
- يعرض: قائمة المقالات المنشورة مع القسم والوقت وعدد المشاهدات

---

## جداول قاعدة البيانات

| الجدول | الوصف |
|--------|-------|
| `users` | كل المستخدمين — role + is_verified flag + locale |
| `writer_profiles` | بيانات الكاتب التفصيلية وحالة الطلب |
| `writer_profile_categories` | pivot: أقسام اهتمام الكاتب |
| `categories` | الأقسام (هرمية عبر parent_id، max 6-8 رئيسي) |
| `tags` | التاجات |
| `articles` | المقالات — subtitle, video_embed, read_time, is_breaking, locale |
| `article_secondary_categories` | pivot: أقسام ثانوية للمقال |
| `article_tags` | pivot: تاجات المقال |
| `comments` | تعليقات القراء على المقالات (مع moderation) |
| `saved_articles` | مقالات محفوظة لدى القارئ |
| `follows` | متابعة القراء للكتّاب |
| `reports` | التقارير (PDF + Premium + locale) |
| `events` | الفعاليات والمؤتمرات |
| `newsletter_subscribers` | المشتركون في النشرة البريدية |
| `ads` | الإعلانات حسب placement zone |
| `subscription_packages` | باقات الاشتراك |
| `subscriptions` | اشتراكات المستخدمين (+ plan field) |
| `content_submissions` | سبمشن المحتوى للمراجعة |
| `training_courses` | الدورات التدريبية |
| `training_lessons` | الدروس داخل كل دورة |
| `user_course_progress` | تقدم المستخدم في الدورات |

> التفاصيل الكاملة للـ ERD في ملف [ERD.md](ERD.md)

---

## سير العمل (Workflow)

### نشر مقال
```
Writer/Contributor → draft → submitted → under_review → ready → published
                                                    ↘ rejected
```
> Verified Writer يختصر مسار المراجعة لكن لا يتجاوز الـ Editorial Control.

### تسجيل كاتب (4 خطوات)
```
1. Account (name, email, phone, country, language)
2. Profile (bio, photo, categories, portfolio)
3. Expertise (experience level, languages, specialties)
4. Portfolio (sample work, ID verification للـ Verified tier)
```

### الاشتراك
```
User → اختيار باقة → دفع → Subscription نشطة (plan field) → وصول للمحتوى المقيّد
```

---

## Permission Logic & Implementation Notes

### Permission Logic
- Open publishing غير مسموح — الـ default: `submit → review → publish`
- Verified status تختصر مسار المراجعة، لكن لا تتجاوز Editorial Control
- Package logic منفصل عن Role logic في قاعدة البيانات

### Implementation Notes
- الـ roles تُخزن في `users.role`
- الـ packages تُخزن في `subscriptions.plan`
- يجب عمل Permission Middleware Layer حتى تتطور الـ rules دون تغيير الـ controllers
- Future: نظام Revenue Share أو Bounty للكتّاب المميزين

> **"Editorial governance is the product moat."**

---

## Ad Placements

| Zone | الحجم | الموقع |
|------|-------|--------|
| Leaderboard Banner | 728×90 | أعلى الصفحة الرئيسية |
| Hero Takeover | Full-width | تحت الـ nav، الرئيسية فقط |
| In-Feed Native Ad | — | بين البطاقات في category/homepage |
| Mid-Article Banner | 300×250 | بعد الفقرة 3-4 من المقال |
| Right Rail Rectangle | 300×600 | sidebar ثابت |
| Footer Banner | 728×90 | footer الموقع |

**Ad Categories:** News & Media, Government, Education, Tech & Startups, Finance, Real Estate, Healthcare, Events & Conferences

> Premium Subscribers يرون تجربة ad-light. الإعلانات الـ Native تُعلَّم بوضوح "Sponsored".

---

## Dashboards

### Writer Dashboard
- Overview، My Articles، Create، Drafts، Analytics
- Create Form: Title، Subtitle، Primary Category، Secondary Category، Tags، Featured Image، Article Body
- Actions: Save Draft / Submit

### Editor Dashboard
- إحصائيات: Drafts، Pending، Published، Total Views
- Content Queue: Article، Author، Category، Status (Pending/Ready)، Action (Review)

---

## MVP Build Order

> "Sequence matters. Start with the editorial core, then layer monetization and growth modules."

| الفاز | الاسم | المحتوى |
|-------|-------|--------|
| **Phase 1** | Foundation | Auth, profiles, taxonomy, homepage, category pages, article pages |
| **Phase 2** | CMS | Writer registration, dashboard, editor queue, moderation workflow, media upload |
| **Phase 3** | Monetization | Subscriptions, premium entitlements, newsletter, package controls |
| **Phase 4** | Optimization | Analytics, search, writer ranking, personalization, multilingual automation |

---

## Media Training Tab

تاب تدريبي مدمج في المنصة — learning hub للصحفيين والمساهمين والكتّاب.

- متاح من الـ navigation الرئيسية كتاب "Training"
- يحتوي: courses، video lessons، editorial guides
- متاح لـ: Registered Users، Contributors، Writers
- المحتوى Premium مقفل لـ Premium Subscribers + Verified Writers

### Training Content Categories
1. Journalism Fundamentals — fact-checking, source verification, story structure
2. Writing for Digital — SEO writing, headline crafting, mobile-first formats
3. Editorial Standards — house style, ethics, editorial policy
4. Video & Multimedia — scripting, on-camera delivery, short-form video
5. Investigative Reporting — data journalism, FOIA, source protection
6. Contributor Onboarding — how to submit, get reviewed, and get published

### جداول الـ Training

| الجدول | الوصف |
|--------|-------|
| `training_courses` | الدورات التدريبية (title, category, level, is_premium) |
| `training_lessons` | الدروس داخل كل دورة (video_url, duration_minutes, sort_order) |
| `user_course_progress` | تقدم المستخدم في الدورات |

> Training is a retention and quality tool — it raises the floor of contributor quality.

---

## Decisions Locked ✅

- Categories: multi-select على مستوى البروفايل + primary/secondary على مستوى المقال
- Editorial Flow مغلق: Writer يرسل → Editor يوافق → Admin يتحكم بالباقات والأدوار
- Navigation ثابتة: Home, News, Reports, Interviews, Opinion, Multimedia, Writers, Submit Content, Subscribe, About, Contact
- الأقسام الرئيسية 6-8 كحد أقصى

---

## ما تم حتى الآن

- [x] إنشاء مشروع Laravel
- [x] تثبيت Filament v3
- [x] تشغيل Migrations الأساسية
- [x] تصميم ERD
- [x] توثيق DOCS.md و ERD.md الكاملين

## ما تبقى (Phase 1 — Foundation)

- [ ] إنشاء Migrations لكل الجداول
- [ ] إنشاء Models مع العلاقات
- [ ] إعداد Filament Resources (Users, Articles, Categories, Tags)
- [ ] بناء صفحة Home (Blade)
- [ ] بناء Category Page
- [ ] بناء Article Page
- [ ] Writer Directory & Profile pages
- [x] توثيق المشروع

## المهام القادمة — حسب MVP Build Order

### Phase 1 — Foundation
- [ ] Migrations لجميع الجداول
- [ ] Models مع العلاقات
- [ ] Auth (Register/Login) + User Roles
- [ ] Filament Admin Panel — Categories, Tags, Users
- [ ] Homepage + Category Pages + Article Pages (Blade)
- [ ] Breaking News Ticker
- [ ] AR/EN Language Support

### Phase 2 — CMS
- [ ] Writer Registration (4-step onboarding)
- [ ] Writer Dashboard (Create, Drafts, Analytics)
- [ ] Editor Dashboard (Content Queue — Pending/Ready)
- [ ] Editorial Flow + Moderation Workflow
- [ ] Media Upload (Images, Video Embed, PDF)
- [ ] Filament Resources: Articles, Reports, Submissions
- [ ] Comments مع Moderation
- [ ] Saved Articles + Follows
- [ ] Media Training Tab (Courses + Lessons)

### Phase 3 — Monetization
- [ ] Subscription Packages + plan-based Access
- [ ] Premium Entitlements (Reports, Ad-light)
- [ ] Newsletter Subscription
- [ ] Ad Zones System
- [ ] Events System

### Phase 4 — Optimization
- [ ] Analytics (Writer + Platform)
- [ ] Search
- [ ] Writer Ranking + Personalization
- [ ] Multilingual Automation
