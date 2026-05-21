# Al Shaheen 360 — شرح السيناريو الكامل للمنصة

## 1. نظرة عامة

**Al Shaheen 360** هي منصة إخبارية متكاملة مبنية بمنطق **Newsroom System**، أي أنها ليست مجرد موقع يعرض مقالات، بل نظام كامل لإدارة المحتوى الإخباري والتحريري من البداية للنهاية.

المنصة تخدم عدة أنواع من المستخدمين:

- قارئ عادي يدخل ليقرأ الأخبار والمقالات.
- قارئ مسجل يستطيع حفظ المقالات، التعليق، متابعة الكتّاب، والاشتراك بالنشرات.
- Contributor يرسل محتوى للمراجعة.
- Writer لديه لوحة تحكم ويكتب مقالات وتقارير.
- Editor يراجع المحتوى ويوافق عليه أو يرفضه.
- Admin يدير النظام كاملاً من المستخدمين وحتى الباقات والإعلانات.

الفكرة الأساسية هي أن كل محتوى منشور على المنصة يجب أن يمر ضمن مسار تحريري واضح، بحيث تبقى جودة المحتوى وهوية المنصة قوية ومنظمة.

---

## 2. الهدف من المنصة

الهدف هو بناء منصة إخبارية احترافية تشبه أنظمة غرف الأخبار الحديثة، وتدعم:

1. نشر الأخبار والمقالات والتحليلات.
2. إدارة كتّاب ومساهمين ومحررين.
3. مراجعة المحتوى قبل النشر.
4. عرض صفحات أقسام واضحة ومنظمة.
5. إنشاء ملفات كتّاب قوية لرفع الثقة.
6. دعم الاشتراكات المدفوعة والمحتوى المميز.
7. عرض إعلانات بدون تخريب تجربة القراءة.
8. توفير قسم تدريب إعلامي للكتّاب والمساهمين.
9. دعم اللغة العربية والإنجليزية.
10. بناء قابلية للتوسع لاحقاً مثل البحث، التحليلات، التخصيص، والترشيحات.

---

## 3. المبدأ الرئيسي: Newsroom وليس Blog

الفرق الأساسي بين Blog و Newsroom هو طريقة إدارة المحتوى.

في الـ Blog غالباً الكاتب ينشر مباشرة، والتصنيف بسيط، والصفحات قليلة.

أما في Al Shaheen 360:

- يوجد أدوار واضحة.
- يوجد محرر يراجع قبل النشر.
- يوجد أقسام رئيسية وفرعية.
- يوجد مقالات، تقارير، مقابلات، فيديوهات، تدريبات، وإعلانات.
- يوجد نظام صلاحيات وباقات.
- يوجد إدارة للكتاب والمساهمين.
- يوجد Content Queue للمراجعة.
- يوجد قواعد تحريرية لحماية جودة المنصة.

لذلك يجب التعامل مع المشروع كنظام إدارة غرفة أخبار وليس فقط CMS بسيط.

---

## 4. الأدوار داخل المنصة

### 4.1 Free Reader

هو الزائر غير المسجل.

يستطيع:

- فتح الصفحة الرئيسية.
- قراءة المقالات العامة.
- تصفح الأقسام.
- مشاهدة ملفات الكتّاب العامة.
- مشاهدة بعض التقارير أو المقابلات المفتوحة.

لا يستطيع:

- حفظ المقالات.
- التعليق.
- متابعة الكتّاب.
- الوصول للمحتوى المدفوع.
- إرسال محتوى.

---

### 4.2 Registered User

هو قارئ قام بإنشاء حساب.

يستطيع:

- تسجيل الدخول.
- حفظ المقالات.
- التعليق على المقالات.
- متابعة الكتّاب.
- الاشتراك بالنشرة البريدية.
- الوصول لبعض محتوى Training المجاني.
- الاشتراك بباقات مدفوعة.

لا يستطيع:

- نشر مقال.
- الدخول إلى Content Queue.
- مراجعة محتوى.

---

### 4.3 Contributor

هو مساهم خارجي يريد إرسال محتوى للمنصة.

يستطيع:

- إنشاء ملف Contributor.
- اختيار أقسام الكتابة التي يهتم بها.
- إرسال مقال أو محتوى للمراجعة.
- متابعة حالة المحتوى: draft, submitted, under_review, approved, rejected.
- مشاهدة ملاحظات المحرر عند الرفض.

لا يستطيع:

- نشر المحتوى مباشرة.
- تعديل محتوى بعد دخوله مرحلة مراجعة إلا إذا تم فتحه له.
- الوصول لصلاحيات المحرر.

---

### 4.4 Writer

هو كاتب داخلي أو معتمد داخل المنصة.

يستطيع:

- الدخول إلى Writer Dashboard.
- إنشاء مقالات.
- حفظ Drafts.
- إرسال المحتوى للمراجعة.
- مشاهدة مقالاته المنشورة.
- مشاهدة Analytics حسب الصلاحية.
- تحديث ملفه الشخصي.
- الوصول لتدريبات خاصة حسب الباقة أو التوثيق.

مهم جداً: حتى الكاتب لا ينشر مباشرة بشكل افتراضي، بل يرسل المحتوى للمراجعة.

---

### 4.5 Verified Writer

هذا ليس دوراً مستقلاً.

هو Writer لديه علامة توثيق `is_verified_writer = true`.

الفائدة:

- يعطي ثقة للقارئ.
- يظهر Badge في Writer Profile.
- يمكن أن يختصر بعض خطوات المراجعة.
- يمكن أن يعطي وصولاً لبعض Training Premium.

لكنه لا يعني أن الكاتب يستطيع تجاوز السياسة التحريرية.

---

### 4.6 Editor

هو المسؤول عن مراجعة المحتوى.

يستطيع:

- فتح Content Queue.
- مراجعة المقالات المرسلة.
- تعديل المحتوى أو طلب تعديلات.
- رفض المحتوى مع ملاحظات.
- اعتماد المحتوى.
- جدولة المحتوى.
- نشر المحتوى.
- إدارة التعليقات أو مراجعتها حسب الصلاحية.

الـ Editor هو قلب المنطق التحريري في المنصة.

---

### 4.7 Admin

هو المسؤول الأعلى عن النظام.

يستطيع:

- إدارة المستخدمين.
- إدارة الأدوار.
- إدارة الأقسام والتاجات.
- إدارة المقالات والتقارير والمقابلات.
- إدارة الباقات والاشتراكات.
- إدارة الإعلانات.
- إدارة صفحات About و Contact.
- إدارة Training.
- ضبط إعدادات الموقع العامة.

---

## 5. السيناريو العام لتجربة القارئ

### 5.1 دخول الزائر إلى الصفحة الرئيسية

عندما يدخل الزائر إلى الموقع، تظهر له الصفحة الرئيسية بتصميم Newsroom واضح.

الصفحة الرئيسية تحتوي:

1. Header فيه اللوغو والقائمة والبحث وتغيير اللغة وتسجيل الدخول.
2. Breaking News Ticker لعرض الأخبار العاجلة أو التنبيهات المهمة.
3. Hero Cluster يحتوي القصة الرئيسية وأهم الأخبار أو العروض التحريرية.
4. Category Rows للأقسام مثل Politics, Economy, Culture, Technology.
5. Modules إضافية مثل Editor Picks, Most Read, Reports, Interviews.
6. Footer يحتوي روابط الموقع والنشرة وروابط السوشيال.

الهدف من الصفحة الرئيسية هو أن يشعر المستخدم أن الموقع منصة إخبارية منظمة وليست صفحة مقالات عشوائية.

---

### 5.2 تصفح قسم معين

عندما يضغط المستخدم على قسم مثل Politics أو Economy، ينتقل إلى Category Page.

صفحة القسم تحتوي:

- عنوان القسم.
- وصف مختصر للقسم.
- فلاتر مثل المنطقة، التاريخ، الترند، نوع المحتوى، الأكثر قراءة.
- Lead Story لهذا القسم.
- شبكة مقالات.
- Sidebar أو Right Rail يحتوي Trending Topics و Editor Picks و High-performing Writers.

المستخدم يستطيع قراءة المقالات أو حفظها إذا كان مسجلاً.

---

### 5.3 فتح مقال

عند فتح مقال، يجب أن تظهر المعلومات التالية في الأعلى:

- Category chip.
- العنوان.
- Subtitle.
- اسم الكاتب.
- تاريخ النشر.
- Read time.
- أزرار المشاركة.
- زر الحفظ.

داخل المقال:

- نص المقال.
- صور أو فيديوهات داخلية.
- اقتباسات بارزة.
- مصادر أو References.
- Tags.

في الجانب أو أسفل المقال:

- Related Stories.
- Trending Topics.
- Premium CTA إذا كان يوجد محتوى مدفوع.
- Author Card.
- Comments.
- Next Read.

---

## 6. سيناريو التسجيل كقارئ

إذا أراد المستخدم استخدام ميزات إضافية، يقوم بإنشاء حساب.

المعلومات المطلوبة:

- Name
- Email
- Password
- Country
- Language

بعد التسجيل يصبح Registered User.

يستطيع بعدها:

- حفظ المقالات.
- التعليق.
- متابعة الكتّاب.
- الاشتراك في النشرة.
- شراء باقة Premium.

---

## 7. سيناريو الاشتراك المدفوع

الاشتراك يجب أن يكون منفصلاً عن الدور.

يعني:

- Premium Subscriber هو قارئ مشترك، وليس كاتباً.
- Writer يمكن أن يكون لديه باقة Premium Writer، لكن هذا لا يعطيه صلاحيات Editor.
- Editor له صلاحيات تحريرية بسبب الدور، وليس بسبب الباقة.

### خطوات الاشتراك

```text
User → Subscribe Page → Select Package → Payment → Active Subscription → Premium Access
```

بعد نجاح الدفع:

- يتم إنشاء Subscription.
- يتم تسجيل Payment.
- يحصل المستخدم على صلاحيات الوصول للمحتوى المميز حسب الباقة.
- إذا كانت الباقة تدعم Ad-light، تقل الإعلانات في تجربته.

---

## 8. سيناريو تسجيل Contributor أو Writer

إذا أراد شخص أن يصبح كاتباً أو مساهماً، يدخل إلى Submit Content أو Writer Registration.

### الخطوة 1: Account

يدخل:

- Full Name
- Email
- Phone
- Country
- Languages
- Password

### الخطوة 2: Profile

يدخل:

- Bio
- Profile Photo
- Writing Categories
- Portfolio Link

### الخطوة 3: Expertise

يدخل:

- Experience Level
- Languages
- Editorial Specialties
- Short Intro

### الخطوة 4: Portfolio / Verification

يدخل:

- Sample Work
- ID Verification إذا أراد Verified Tier
- Media Affiliation
- Submit Application

بعد الإرسال تصبح حالة الطلب:

```text
submitted
```

ثم يراجعها Editor أو Admin.

---

## 9. سيناريو قبول الكاتب

عندما يرسل الكاتب طلبه، يظهر الطلب في لوحة الإدارة أو قائمة المراجعة.

المسؤول يستطيع:

- قراءة بيانات الكاتب.
- مراجعة portfolio.
- مراجعة samples.
- قبول الطلب.
- رفض الطلب مع ملاحظات.
- تعليق الحساب أو وضعه under_review.

إذا تم القبول:

- يتم تحديث role إلى Writer أو Contributor حسب القرار.
- يتم تحديث application_status إلى approved.
- يظهر للكاتب Dashboard.

إذا تم الرفض:

- تبقى الحالة rejected.
- تظهر ملاحظات للمستخدم.

---

## 10. سيناريو إنشاء مقال

يدخل الكاتب إلى Writer Dashboard ثم يختار Create Article.

النموذج يحتوي:

- Title
- Subtitle
- Primary Category
- Secondary Categories
- Tags
- Featured Image
- Article Body
- Video Embed اختياري
- Is Premium اختياري حسب الصلاحية

يمكنه الضغط على:

- Save Draft
- Submit for Review

إذا ضغط Save Draft:

```text
status = draft
```

إذا ضغط Submit:

```text
status = submitted
```

---

## 11. سيناريو المراجعة التحريرية

عندما يرسل الكاتب المقال، يظهر في Editor Dashboard داخل Content Queue.

المحرر يرى:

- عنوان المقال.
- اسم الكاتب.
- القسم.
- الحالة.
- تاريخ الإرسال.
- زر Review.

داخل شاشة المراجعة، يستطيع المحرر:

1. قراءة المحتوى.
2. تعديل المحتوى إذا كانت الصلاحية تسمح.
3. إضافة ملاحظات.
4. رفض المقال.
5. إرجاعه للتعديل.
6. وضعه Ready.
7. جدولته للنشر.
8. نشره.

المسار الكامل:

```text
draft → submitted → under_review → ready → scheduled → published
                              ↘ rejected
                              ↘ archived
```

---

## 12. سيناريو رفض المقال

إذا كان المحتوى غير مناسب، يضغط المحرر Reject.

يجب أن يكتب سبب الرفض أو ملاحظات.

تصبح الحالة:

```text
rejected
```

الكاتب يرى أن المقال مرفوض مع سبب الرفض.

يمكن لاحقاً السماح له بإنشاء نسخة جديدة أو تعديل المسودة حسب قرار المنتج.

---

## 13. سيناريو نشر المقال

إذا تم قبول المقال:

1. يضعه المحرر `ready`.
2. يمكن نشره مباشرة أو جدولته.
3. عند النشر تصبح الحالة `published`.
4. يظهر المقال في:
   - الصفحة الرئيسية إذا تم اختياره.
   - صفحة القسم.
   - صفحة الكاتب.
   - نتائج البحث لاحقاً.
   - Related Stories حسب المنطق.

---

## 14. سيناريو التقارير Reports

التقرير يختلف عن المقال لأنه غالباً يكون أعمق وقد يحتوي ملف PDF.

التقرير يمكن أن يكون:

- مجاني.
- Premium.
- مرتبط بقسم.
- مرتبط بكاتب.

إذا كان Premium، لا يستطيع المستخدم العادي تحميله أو قراءته كاملاً بدون اشتراك.

---

## 15. سيناريو المقابلات Interviews

المقابلات لها صفحة خاصة لأنها نوع محتوى مهم.

كل Interview يحتوي:

- عنوان.
- اسم الضيف.
- صفة الضيف.
- صورة الضيف.
- محتوى المقابلة.
- فيديو اختياري.
- كاتب أو محرر مسؤول.

تظهر في:

- Interviews Page.
- Homepage modules.
- Related content.

---

## 16. سيناريو Multimedia

قسم Multimedia يعرض محتوى فيديو أو صوت أو gallery.

كل Media Item يحتوي:

- Title
- Type: video/audio/gallery
- Media URL
- Thumbnail
- Duration
- Description
- Transcript اختياري
- Category
- Premium flag اختياري

يمكن ربط الوسائط بمقال أو تقرير لاحقاً إذا احتجنا.

---

## 17. سيناريو Writer Profile

كل كاتب يجب أن يكون لديه صفحة Profile لأنها تزيد ثقة القارئ.

صفحة الكاتب تعرض:

- الصورة.
- الاسم.
- علامة التوثيق.
- Bio.
- Location.
- Languages.
- Specialties.
- Social Links.
- Published Articles.
- عدد المشاهدات أو القراءات.
- زر Follow.

عندما يتابع القارئ كاتباً، يتم إنشاء record في `follows`.

---

## 18. سيناريو التعليقات Comments

القارئ المسجل يستطيع التعليق على المقال.

لكن التعليقات يجب أن تمر على Moderation.

حالات التعليق:

```text
pending → approved
       ↘ rejected
       ↘ spam
```

لا يظهر التعليق للعامة إلا إذا كان approved.

---

## 19. سيناريو حفظ المقالات Saved Articles

المستخدم المسجل يستطيع الضغط على Save داخل المقال أو البطاقة.

يتم إنشاء record في `saved_articles`.

في حسابه يستطيع مشاهدة قائمة المقالات المحفوظة.

---

## 20. سيناريو النشرة البريدية Newsletter

يمكن للمستخدم الاشتراك في النشرة من:

- Homepage.
- Footer.
- Article page.
- Subscribe page.

يتم حفظه في `newsletter_subscribers`.

الحالات:

```text
active
unsubscribed
```

---

## 21. سيناريو الإعلانات Ads

الإعلانات يجب أن تكون منظمة حسب placements.

أماكن الإعلانات:

1. Leaderboard Banner — أعلى الصفحة الرئيسية.
2. Hero Takeover — أسفل الـ nav في الصفحة الرئيسية فقط.
3. In-Feed Native Ad — بين بطاقات المقالات.
4. Mid-Article Banner — بعد الفقرة 3 أو 4 داخل المقال.
5. Right Rail Rectangle — sidebar ثابت.
6. Footer Banner — أسفل الموقع.

### قواعد الإعلانات

- الإعلانات الأصلية Native يجب أن تكون عليها كلمة Sponsored.
- المشترك Premium يرى تجربة أخف من الإعلانات.
- لا يجوز أن يختلط الإعلان مع المحتوى التحريري بطريقة مضللة.

---

## 22. سيناريو Training

قسم Training هو learning hub داخل المنصة.

هدفه:

- تدريب المساهمين.
- تحسين جودة الكتابة.
- تعليم قواعد التحرير.
- تقليل أخطاء المحتوى قبل المراجعة.

الأقسام التدريبية:

1. Journalism Fundamentals.
2. Writing for Digital.
3. Editorial Standards.
4. Video & Multimedia.
5. Investigative Reporting.
6. Contributor Onboarding.

المستخدم يستطيع فتح Course، ثم Lesson، ويتم حفظ تقدمه في `user_course_progress`.

بعض الدروس تكون Premium.

---

## 23. سيناريو لوحة الإدارة Admin

Admin يدخل إلى Filament Admin Panel.

يدير:

- Users.
- Writer Applications.
- Categories.
- Tags.
- Articles.
- Reports.
- Interviews.
- Media Items.
- Comments.
- Training Courses.
- Subscription Packages.
- Subscriptions.
- Payments.
- Ads.
- Pages.
- Site Settings.

الأدمن هو الذي يضبط النظام، لكن لا يفضل أن يستخدم كل شيء للنشر اليومي. النشر والتحرير اليومي يجب أن يبقى عند Editor للحفاظ على workflow واضح.

---

## 24. العلاقة بين الأدوار والباقات

هذه نقطة مهمة جداً.

### Role

يمثل وظيفة المستخدم داخل النظام.

أمثلة:

- reader
- contributor
- writer
- editor
- admin

### Package / Subscription

يمثل ما دفعه المستخدم أو نوع وصوله التجاري.

أمثلة:

- free
- premium
- premium_writer
- ad_light

### لماذا يجب الفصل بينهم؟

لأن المستخدم قد يكون:

- قارئ Premium لكنه ليس Writer.
- Writer لكنه ليس Premium Subscriber.
- Editor لديه صلاحية مراجعة لكنه لا يحتاج باقة مدفوعة.
- Admin يتحكم بالنظام بغض النظر عن الاشتراك.

لذلك:

```text
Role = صلاحيات وظيفية / تحريرية
Package = صلاحيات تجارية / وصول للمحتوى
```

---

## 25. السيناريو الكامل من البداية للنهاية

### حالة 1: قارئ عادي

```text
Visitor opens website
→ sees Homepage
→ reads public article
→ likes writer
→ registers
→ follows writer
→ saves article
→ subscribes to newsletter
```

### حالة 2: قارئ Premium

```text
Registered User
→ opens Subscribe page
→ selects package
→ completes payment
→ gets active subscription
→ reads premium reports
→ sees ad-light experience
```

### حالة 3: Contributor

```text
User
→ applies as contributor
→ fills profile and portfolio
→ gets approved
→ submits article
→ editor reviews
→ article gets rejected or approved
```

### حالة 4: Writer

```text
Writer logs in
→ opens dashboard
→ creates article draft
→ selects primary and secondary categories
→ adds tags and image
→ submits for review
→ editor approves
→ article is published
→ writer sees analytics
```

### حالة 5: Editor

```text
Editor logs in
→ opens content queue
→ reviews submitted articles
→ edits or comments
→ rejects or approves
→ schedules publication
→ monitors published content
```

### حالة 6: Admin

```text
Admin logs in
→ configures categories
→ manages users and roles
→ creates subscription packages
→ configures ad placements
→ manages training content
→ monitors system health
```

---

## 26. MVP المطلوب تنفيذه أولاً

### Phase 1 — Foundation

الهدف: جعل المنصة قابلة للتصفح والقراءة.

يجب تنفيذ:

- Auth.
- Users.
- Roles.
- Categories.
- Tags.
- Articles.
- Homepage.
- Category Pages.
- Article Pages.
- Writer Profiles.
- Language support.

---

### Phase 2 — CMS

الهدف: تفعيل غرفة الأخبار.

يجب تنفيذ:

- Writer Registration.
- Writer Dashboard.
- Create Article.
- Drafts.
- Submit for Review.
- Editor Queue.
- Review workflow.
- Comments moderation.
- Media upload.
- Reports / Interviews / Multimedia basics.
- Training basics.

---

### Phase 3 — Monetization

الهدف: تفعيل الإيرادات.

يجب تنفيذ:

- Subscription Packages.
- Payments.
- Premium access.
- Ad placements.
- Newsletter.
- Ad-light experience.

---

### Phase 4 — Optimization

الهدف: تحسين التجربة والنمو.

يجب تنفيذ:

- Search.
- Analytics.
- Most Read.
- Trending.
- Writer ranking.
- Recommendations.
- Personalization.
- Multilingual automation.

---

## 27. قواعد قبول MVP

يعتبر النظام جاهزاً كـ MVP عندما:

1. يستطيع الزائر تصفح الصفحة الرئيسية والأقسام والمقالات.
2. يستطيع المستخدم التسجيل وتسجيل الدخول.
3. يستطيع القارئ حفظ المقالات والتعليق ومتابعة الكاتب.
4. يستطيع الكاتب إنشاء مسودة وإرسالها للمراجعة.
5. يستطيع المحرر قبول أو رفض أو جدولة المحتوى.
6. يستطيع الأدمن إدارة الأقسام والمقالات والكتّاب من Filament.
7. يدعم المقال primary category و secondary categories و tags.
8. يوجد Home structure واضح: ticker, hero, category rows, footer.
9. يوجد Article Page احترافية فيها author card و related stories.
10. توجد بنية الاشتراكات والإعلانات حتى لو لم يتم تشغيل الدفع مباشرة.
11. توجد بنية Training courses و lessons.

---

## 28. ملاحظات تنفيذ للمطور

- لا تستخدم role جديد باسم Verified Writer. استخدم flag.
- لا تجعل الاشتراك يعطي صلاحيات تحريرية.
- لا تسمح بالنشر المباشر من Contributor.
- اجعل كل status عبارة عن Enum.
- افصل منطق الوصول للمحتوى داخل Service أو Middleware.
- اجعل category قابلة للهرمية عبر parent_id.
- اجعل المقال يدعم primary category واحدة و secondary categories متعددة.
- أضف article_revisions حتى يمكن تتبع التعديلات.
- أضف article_views حتى يمكن بناء Trending و Analytics.
- استخدم Soft Deletes للجداول المهمة.
- جهز SEO fields من البداية.
- لا تبني الواجهة وكأنها Magazine فقط، بل Newsroom مع hierarchy واضح.

---

## 29. الخلاصة

Al Shaheen 360 يجب أن يبنى كنظام إخباري متكامل بثلاث طبقات رئيسية:

1. **Content Layer**  
   مقالات، تقارير، مقابلات، وسائط، تدريب.

2. **Editorial Layer**  
   كتاب، مساهمون، محررون، مراجعة، حالات نشر، ملاحظات.

3. **Business Layer**  
   اشتراكات، إعلانات، محتوى Premium، تجربة ad-light.

نجاح المنصة يعتمد على عدم خلط هذه الطبقات مع بعضها، وعلى الحفاظ على مسار تحريري واضح ومنظم من أول يوم.
