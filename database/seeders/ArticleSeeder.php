<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Comment;
use App\Models\Tag;
use App\Models\User;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        $this->purgeArticles();

        $writers     = User::whereHas('writer')->orWhereHas('editor')->orWhereHas('admin')->pluck('id')->toArray();
        $primaries   = Category::whereNull('parent_id')->pluck('id')->toArray();
        $secondaries = Category::whereNotNull('parent_id')->pluck('id')->toArray();
        $tagIds      = Tag::pluck('id')->toArray();
        $readers     = User::whereHas('reader')->pluck('id')->toArray();

        if ($writers === [] || $primaries === []) {
            $this->command?->warn('ArticleSeeder skipped: writers or categories are missing.');

            return;
        }

        $statuses = ['published', 'published', 'published', 'draft', 'under_review', 'rejected'];

        foreach ($this->articles() as $index => $data) {
            $status = fake()->randomElement($statuses);

            $article = Article::create([
                'author_id'           => fake()->randomElement($writers),
                'primary_category_id' => fake()->randomElement($primaries),
                'featured_image'      => null,
                'read_time'           => rand(4, 14),
                'is_breaking'         => $status === 'published' && fake()->boolean(12),
                'is_editor_pick'      => $status === 'published' && fake()->boolean(18),
                'editor_pick_order'   => null,
                'status'              => $status,
                'views_count'         => $status === 'published' ? rand(120, 8500) : 0,
                'published_at'        => $status === 'published' ? now()->subDays(rand(1, 90)) : null,
                'submitted_at'        => in_array($status, ['submitted', 'under_review', 'rejected'], true)
                    ? now()->subDays(rand(1, 14))
                    : null,
            ]);

            $slugEn = Str::slug($data['title_en']) ?: 'article-en-' . ($index + 1);
            $slugAr = 'article-ar-' . ($index + 1);

            $article->title_en            = $data['title_en'];
            $article->subtitle_en         = $data['subtitle_en'];
            $article->slug_en             = $slugEn;
            $article->excerpt_en          = $data['excerpt_en'];
            $article->content_en          = $data['content_en'];
            $article->seo_title_en        = $data['title_en'];
            $article->seo_description_en  = $data['excerpt_en'];

            $article->title_ar            = $data['title_ar'];
            $article->subtitle_ar         = $data['subtitle_ar'];
            $article->slug_ar             = $slugAr;
            $article->excerpt_ar          = $data['excerpt_ar'];
            $article->content_ar          = $data['content_ar'];
            $article->seo_title_ar        = $data['title_ar'];
            $article->seo_description_ar  = $data['excerpt_ar'];
            $article->save();

            if ($secondaries !== []) {
                $article->secondaryCategories()->attach(
                    fake()->randomElements($secondaries, rand(0, min(2, count($secondaries))))
                );
            }

            if ($tagIds !== []) {
                $article->tags()->attach(
                    fake()->randomElements($tagIds, rand(1, min(4, count($tagIds))))
                );
            }

            if ($status === 'published' && $readers !== []) {
                $commentCount = rand(1, 6);
                for ($c = 0; $c < $commentCount; $c++) {
                    Comment::create([
                        'user_id'    => fake()->randomElement($readers),
                        'article_id' => $article->id,
                        'body'       => fake()->randomElement([
                            'Great analysis, thank you for this piece.',
                            'Very informative article.',
                            'I would love to read a follow-up on this topic.',
                            'مقال ممتاز وتحليل عميق.',
                            'شكراً على هذا التقرير المهم.',
                            'نتمنى المزيد من التغطية لهذا الموضوع.',
                        ]),
                        'status' => fake()->randomElement(['approved', 'approved', 'pending', 'rejected']),
                    ]);
                }
            }
        }

        Article::query()
            ->where('is_editor_pick', true)
            ->orderByDesc('published_at')
            ->get()
            ->each(fn (Article $article, int $index) => $article->update(['editor_pick_order' => $index + 1]));

        $this->command?->info('ArticleSeeder: ' . count($this->articles()) . ' bilingual articles created.');
    }

    private function purgeArticles(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        DB::table('article_ai_suggestions')->truncate();
        DB::table('article_views')->truncate();
        DB::table('article_revisions')->truncate();
        DB::table('saved_articles')->truncate();
        DB::table('comments')->truncate();
        DB::table('article_tags')->truncate();
        DB::table('article_secondary_categories')->truncate();
        DB::table('article_translations')->truncate();
        DB::table('articles')->truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function articles(): array
    {
        return [
            [
                'title_en'    => 'How Independent Journalism Is Reshaping Public Discourse',
                'subtitle_en' => 'A closer look at editorial independence in the digital age',
                'excerpt_en'  => 'Independent newsrooms are gaining trust by prioritizing accountability, transparency, and community-driven reporting.',
                'content_en'  => "Independent journalism has become one of the most important forces shaping modern public debate. As legacy media models struggle with commercial pressure, smaller editorial teams are building sustainable newsrooms focused on investigative depth and audience trust.\n\nReaders increasingly value outlets that disclose funding sources, correct errors publicly, and engage communities in the reporting process. This shift is not only changing how stories are produced, but also how citizens evaluate information.\n\nFor emerging writers, independence means balancing editorial courage with ethical rigor. The future of journalism depends on newsrooms that can remain free while staying accountable.",
                'title_ar'    => 'كيف يعيد الصحافة المستقلة تشكيل الخطاب العام',
                'subtitle_ar' => 'نظرة على استقلالية التحرير في العصر الرقمي',
                'excerpt_ar'  => 'تحظى غرف الأخبار المستقلة بثقة أكبر بفضل الشفافية والمساءلة والتغطية القريبة من المجتمع.',
                'content_ar'  => "باتت الصحافة المستقلة من أهم القوى المؤثرة في النقاش العام اليوم. ومع تراجع نماذج الإعلام التقليدية تحت ضغوط السوق، تبني فرق تحريرية صغيرة غرف أخبار تركز على التحقيق والعمق وبناء الثقة.\n\nالقراء اليوم يقدّرون المنصات التي تكشف مصادر تمويلها، وتصحح أخطاءها علناً، وتشرك الجمهور في صناعة الخبر. هذا التحول يغيّر ليس فقط طريقة إنتاج المحتوى، بل أيضاً كيفية تقييم المعلومات.\n\nبالنسبة للكتّاب الجدد، الاستقلالية تعني موازنة الشجاعة التحريرية مع الالتزام الأخلاقي. مستقبل الصحافة يعتمد على غرف أخبار قادرة على البقاء حرة ومسؤولة في آن واحد.",
            ],
            [
                'title_en'    => 'Fact-Checking in the Age of Viral Misinformation',
                'subtitle_en' => 'Why verification workflows matter more than ever',
                'excerpt_en'  => 'Newsrooms are adopting structured verification pipelines to counter false narratives before they spread.',
                'content_en'  => "Misinformation spreads faster than ever because social platforms reward emotional content over verified reporting. In response, professional newsrooms are formalizing fact-checking workflows that include source triangulation, metadata analysis, and editorial review.\n\nEffective verification is not a single step; it is a discipline. Reporters document evidence chains, archive web pages, and separate confirmed facts from unverified claims. This protects both audiences and newsroom credibility.\n\nAs audiences become more skeptical, transparent fact-checking can become a competitive advantage rather than a backend process.",
                'title_ar'    => 'التحقق من الحقائق في عصر المعلومات المضللة',
                'subtitle_ar' => 'لماذا أصبحت مسارات التوثيق ضرورية أكثر من أي وقت',
                'excerpt_ar'  => 'تعتمد غرف الأخبار على خطوات تحقق منهجية لمواجهة الروايات المغلوطة قبل انتشارها.',
                'content_ar'  => "تنتشر المعلومات المضللة بسرعة لأن المنصات الرقمية تكافئ المحتوى العاطفي أكثر من الخبر الموثق. لذلك تبني غرف الأخبار مسارات تحقق رسمية تشمل التوثيق المتعدد للمصادر وتحليل البيانات الوصفية ومراجعة التحرير.\n\nالتحقق الفعّال ليس خطوة واحدة بل منهجية. يوثق الصحفيون سلسلة الأدلة، ويؤرشفون الصفحات، ويفصلون بين ما ثبت وما لم يثبت. هذا يحمي الجمهور وسمعة المنصة.\n\nمع تزايد شك الجمهور، يمكن أن يصبح التحقق الشفاف ميزة تنافسية وليس عملاً خلف الكواليس فقط.",
            ],
            [
                'title_en'    => 'The Rise of Data Journalism in Regional Newsrooms',
                'subtitle_en' => 'Turning public datasets into compelling stories',
                'excerpt_en'  => 'Data teams are helping reporters uncover patterns in education, health, and public spending.',
                'content_en'  => "Data journalism is transforming regional reporting by making complex public issues understandable. Reporters combine datasets with human narratives to reveal inequality, budget misuse, and service gaps that traditional coverage might miss.\n\nSuccessful data stories begin with a clear editorial question, not a spreadsheet. Journalists clean data carefully, visualize trends responsibly, and explain limitations to avoid misleading conclusions.\n\nAs open data initiatives expand, newsrooms that invest in data literacy will produce stronger accountability journalism.",
                'title_ar'    => 'صعود صحافة البيانات في غرف الأخبار المحلية',
                'subtitle_ar' => 'تحويل البيانات العامة إلى قصص مؤثرة',
                'excerpt_ar'  => 'فرق البيانات تساعد الصحفيين على كشف أنماط في التعليم والصحة والإنفاق العام.',
                'content_ar'  => "تغيّر صحافة البيانات طريقة التغطية المحلية عبر جعل القضايا المعقدة مفهومة للجمهور. يجمع الصحفيون بين الأرقام والقصص الإنسانية لكشف الفجوات في الخدمات وسوء استخدام الميزانيات.\n\nالقصة البياناتية الناجحة تبدأ بسؤال تحريري واضح لا بجدول بيانات. يجب تنظيف البيانات بعناية، وعرض الاتجاهات بمسؤولية، وشرح حدود التحليل لتجنب الاستنتاجات المضللة.\n\nمع توسع مبادرات البيانات المفتوحة، ستنتج غرف الأخبار الأكثر وعياً بيانات تقارير مساءلة أقوى.",
            ],
            [
                'title_en'    => 'Ethical Interviewing: Listening Beyond the Headline',
                'subtitle_en' => 'Building trust with sources and vulnerable communities',
                'excerpt_en'  => 'Strong interviews depend on consent, context, and respect for the people behind the story.',
                'content_en'  => "Interviewing is one of journalism's most human skills, yet it is often reduced to quick quotes and confrontation. Ethical interviewing prioritizes informed consent, trauma-aware questioning, and accurate representation of context.\n\nReporters should explain how quotes will be used, offer review when appropriate, and avoid re-traumatizing subjects for dramatic effect. This approach builds long-term trust with communities and improves story quality.\n\nThe best interviews do not extract soundbites; they reveal understanding.",
                'title_ar'    => 'المقابلة الصحفية الأخلاقية: الاستماع أبعد من العنوان',
                'subtitle_ar' => 'بناء الثقة مع المصادر والمجتمعات الحساسة',
                'excerpt_ar'  => 'تعتمد المقابلات القوية على الموافقة والسياق واحترام الإنسان خلف القصة.',
                'content_ar'  => "المقابلة من أهم مهارات الصحافة الإنسانية، لكنها أحياناً تُختزل إلى اقتباسات سريعة أو مواجهات إعلامية. المقابلة الأخلاقية تضع الموافقة الواعية والسؤال الحساس للصدمات وتمثيل السياق بدقة في مقدمة العمل.\n\nعلى الصحفي أن يشرح كيف سيُستخدم الكلام، ويمنح فرصة للمراجعة عند الحاجة، ويتجنب إعادة إيذاء المصدر من أجل الدراما. هذا يبني ثقة طويلة المدى مع المجتمعات ويرفع جودة التقرير.\n\nأفضل المقابلات لا تستخرج جملاً لافتة، بل تكشف فهماً أعمق.",
            ],
            [
                'title_en'    => 'Podcasting and the Future of Long-Form Storytelling',
                'subtitle_en' => 'Why audio formats are winning loyal audiences',
                'excerpt_en'  => 'Podcasts allow newsrooms to deepen narratives and reach listeners during daily routines.',
                'content_en'  => "Podcasting has reopened space for long-form storytelling in a media environment dominated by short updates. Audio formats create intimacy, continuity, and stronger audience relationships over time.\n\nNewsroom podcasts succeed when they have a clear editorial identity, consistent production quality, and episodes designed for both depth and accessibility. Distribution strategy matters as much as recording technique.\n\nFor publishers, podcasting is not a side project; it is a core channel for trust-building journalism.",
                'title_ar'    => 'البودكاست ومستقبل السرد الطويل في الإعلام',
                'subtitle_ar' => 'لماذا تفوز الصيغ الصوتية بجمهور مخلص',
                'excerpt_ar'  => 'يتيح البودكاست للصحافة تعميق السرد والوصول للجمهور أثناء روتينه اليومي.',
                'content_ar'  => "أعاد البودكاست مساحة للسرد الطويل في بيئة إعلامية تهيمن عليها التحديثات القصيرة. الصيغة الصوتية تخلق قرباً واستمرارية وعلاقة أقوى مع الجمهور عبر الزمن.\n\nتنجح بودكاستات غرف الأخبار عندما تملك هوية تحريرية واضحة وجودة إنتاج ثابتة وحلقات تجمع بين العمق والسهولة. استراتيجية النشر مهمة بقدر جودة التسجيل.\n\nبالنسبة للناشرين، البودكاست ليس مشروعاً جانبياً بل قناة أساسية لبناء الثقة.",
            ],
            [
                'title_en'    => 'Covering Climate Change with Local Impact',
                'subtitle_en' => 'From global models to neighborhood consequences',
                'excerpt_en'  => 'Climate reporting gains relevance when it connects science to daily life in local communities.',
                'content_en'  => "Climate journalism often fails when it speaks only in global averages. Readers engage more when reporting connects scientific forecasts to local agriculture, water access, urban planning, and public health.\n\nReporters should collaborate with scientists, municipal officials, and affected residents to translate abstract risk into actionable information. Visual storytelling and historical comparison can make trends tangible.\n\nLocal climate coverage is not secondary; it is where policy meets lived experience.",
                'title_ar'    => 'تغطية التغير المناخي بأثر محلي',
                'subtitle_ar' => 'من النماذج العالمية إلى تبعات الحي',
                'excerpt_ar'  => 'تصبح تغطية المناخ أكثر تأثيراً عندما تربط العلم بالحياة اليومية في المجتمعات.',
                'content_ar'  => "تفشل تغطية المناخ عندما تقتصر على المتوسطات العالمية. يتفاعل القراء أكثر عندما يربط التقرير بين التوقعات العلمية والزراعة المحلية والمياه والتخطيط الحضري والصحة العامة.\n\nيجب على الصحفي التعاون مع العلماء والمسؤولين المحليين والمتضررين لتحويل المخاطر المجردة إلى معلومات قابلة للفعل. السرد البصري والمقارنة التاريخية يجعلان الاتجاهات ملموسة.\n\nالتغطية المناخية المحلية ليست ثانوية، بل هي نقطة التقاء السياسات مع تجربة الناس.",
            ],
            [
                'title_en'    => 'Newsroom Diversity as an Editorial Strength',
                'subtitle_en' => 'Representation improves accuracy and audience reach',
                'excerpt_en'  => 'Inclusive newsrooms produce broader coverage and reduce blind spots in reporting.',
                'content_en'  => "Diversity in newsrooms is frequently discussed as a values issue, but its editorial impact is equally practical. Teams with varied backgrounds identify stories earlier, ask better questions, and avoid harmful stereotypes.\n\nInclusive hiring must be paired with inclusive editorial culture: mentorship, fair assignment distribution, and room for dissent in news meetings. Metrics should track not only recruitment but also byline equity and source diversity.\n\nWhen audiences see themselves reflected in coverage, trust grows organically.",
                'title_ar'    => 'التنوع في غرف الأخبار كقوة تحريرية',
                'subtitle_ar' => 'التمثيل يحسّن الدقة ويوسّع الوصول للجمهور',
                'excerpt_ar'  => 'الغرف التحريرية الشاملة تنتج تغطية أوسع وتقلل نقاط العمى في التقارير.',
                'content_ar'  => "يُناقش التنوع في غرف الأخبار غالباً كقيمة أخلاقية، لكن أثره التحريري عملي أيضاً. الفرق المتنوعة تكتشف القصص مبكراً وتطرح أسئلة أدق وتتجنب الصور النمطية الضارة.\n\nيجب أن يرافق التوظيف الشامل ثقافة تحريرية شاملة: إرشاد عادل وتوزيع مهام منصف ومساحة للرأي في الاجتماعات. وينبغي قياس التنوع في التوظيف وفي توزيع التوقيعات وتنوع المصادر.\n\nعندما يرى الجمهور نفسه في التغطية، تنمو الثقة بشكل طبيعي.",
            ],
            [
                'title_en'    => 'Mobile-First Reporting for Modern Audiences',
                'subtitle_en' => 'Designing stories for small screens and fast contexts',
                'excerpt_en'  => 'Mobile audiences need concise structure, strong visuals, and scannable storytelling.',
                'content_en'  => "Most readers now encounter news on mobile devices, often while multitasking. Mobile-first reporting means structuring stories with clear hierarchy, short sections, meaningful visuals, and immediate context in the opening lines.\n\nHeadlines and push notifications should promise value without clickbait. Interactive elements must remain lightweight to protect performance on slower networks.\n\nNewsrooms that design for mobile first improve comprehension across all platforms.",
                'title_ar'    => 'الصحافة الموجهة للهاتف للجمهور الحديث',
                'subtitle_ar' => 'تصميم القصص للشاشات الصغيرة والسياق السريع',
                'excerpt_ar'  => 'جمهور الهاتف يحتاج بنية واضحة وصوراً قوية وسرداً سهل التصفح.',
                'content_ar'  => "يواجه معظم القراء الأخبار عبر الهاتف وغالباً أثناء القيام بمهام أخرى. الصحافة الموجهة للهاتف تعني بنية واضحة وفقرات قصيرة وصوراً مؤثرة وسياقاً فورياً في بداية النص.\n\nيجب أن تعد العناوين والإشعارات بقيمة حقيقية دون إثارة مضللة. والعناصر التفاعلية يجب أن تبقى خفيفة لضمان الأداء على الشبكات البطيئة.\n\nغرف الأخبار التي تبدأ من الهاتف تحسّن الفهم على جميع المنصات.",
            ],
            [
                'title_en'    => 'Investigative Reporting Under Resource Constraints',
                'subtitle_en' => 'How small teams deliver high-impact accountability stories',
                'excerpt_en'  => 'Collaboration, document discipline, and audience support can sustain investigative work.',
                'content_en'  => "Investigative journalism is expensive, but small newsrooms can still produce major impact through collaboration, public records expertise, and disciplined project management.\n\nCross-border partnerships, nonprofit funding, and member-supported models help teams pursue stories that commercial incentives ignore. Editors must protect reporters with legal review and secure data handling.\n\nResource constraints should refine focus, not eliminate ambition.",
                'title_ar'    => 'التحقيقات الصحفية في ظل محدودية الموارد',
                'subtitle_ar' => 'كيف تقدم الفرق الصغيرة قصص مساءلة عالية الأثر',
                'excerpt_ar'  => 'التعاون وإدارة الوثائق ودعم الجمهور يمكن أن يحافظوا على العمل الاستقصائي.',
                'content_ar'  => "الصحافة الاستقصائية مكلفة، لكن غرف الأخبار الصغيرة ما زالت قادرة على إحداث أثر كبير عبر التعاون وخبرة الوثائق العامة وإدارة المشاريع بانضباط.\n\nالشراكات العابرة للحدود والتمويل غير الربحي ونماذج العضوية تساعد الفرق على متابعة القصص التي يتجاهلها منطق السوق. وعلى التحرير حماية الصحفيين قانونياً وتقنياً.\n\nمحدودية الموارد يجب أن تصقل التركيز لا أن تلغي الطموح.",
            ],
            [
                'title_en'    => 'Visual Storytelling Ethics in Conflict Zones',
                'subtitle_en' => 'Balancing witness with dignity and safety',
                'excerpt_en'  => 'Photo and video teams need clear protocols when documenting violence and displacement.',
                'content_en'  => "Visual journalism in conflict zones carries heightened ethical responsibility. Images can expose injustice, but they can also re-victimize subjects if used without care.\n\nNewsrooms should establish consent guidelines, avoid gratuitous imagery, and prioritize safety training for field teams. Captions must provide context, not sensational framing.\n\nPowerful visuals should inform public understanding, not exploit suffering for engagement.",
                'title_ar'    => 'أخلاقيات السرد البصري في مناطق النزاع',
                'subtitle_ar' => 'موازنة الشهادة مع الكرامة والسلامة',
                'excerpt_ar'  => 'فرق الصورة والفيديو تحتاج بروتوكولات واضحة عند توثيق العنف والنزوح.',
                'content_ar'  => "تحمل الصحافة البصرية في مناطق النزاع مسؤولية أخلاقية مضاعفة. الصور قادرة على كشف الظلم، لكنها قد تعيد إيذاء الضحايا إذا استُخدمت دون عناية.\n\nعلى غرف الأخبار وضع إرشادات موافقة وتجنب الصور المستفزة وتدريب الفرق الميدانية على السلامة. يجب أن تقدم التعليقات سياقاً لا إطاراً مثيراً.\n\nالصورة القوية يجب أن تخدم الفهم العام لا استغلال المعاناة من أجل التفاعل.",
            ],
            [
                'title_en'    => 'Building a Sustainable Newsletter Strategy',
                'subtitle_en' => 'Retention beats reach in audience growth',
                'excerpt_en'  => 'Editorial newsletters thrive when they deliver consistent value and clear voice.',
                'content_en'  => "Newsletters have re-emerged as a direct channel between newsrooms and loyal readers. Sustainable growth depends less on viral acquisition and more on retention, relevance, and editorial consistency.\n\nSuccessful newsletters define a promise: daily briefing, investigative follow-up, or curated analysis. Subject lines should reflect substance, and send cadence should match audience expectations.\n\nWhen done well, newsletters become both a trust product and a revenue foundation.",
                'title_ar'    => 'بناء استراتيجية نشرة بريدية مستدامة',
                'subtitle_ar' => 'الاحتفاظ بالجمهور أهم من الوصول الواسع',
                'excerpt_ar'  => 'النشرات البريدية التحريرية تنجح عندما تقدم قيمة ثابتة وهوية واضحة.',
                'content_ar'  => "عادت النشرات البريدية كقناة مباشرة بين غرف الأخبار والقراء المخلصين. النمو المستدام يعتمد على الاحتفاظ بالجمهور والملاءمة والاستمرارية التحريرية أكثر من الانتشار العابر.\n\nالنشرة الناجحة تحدد وعداً واضحاً: موجز يومي أو متابعة تحقيقية أو تحليل منتقى. وعناوين الرسائل يجب أن تعكس المضمون لا الإثارة.\n\nعند تنفيذها جيداً، تصبح النشرة منتج ثقة وقاعدة للإيرادات.",
            ],
            [
                'title_en'    => 'Youth Voices in Civic Media',
                'subtitle_en' => 'How student reporters are shaping local conversations',
                'excerpt_en'  => 'Campus and youth-led media projects are introducing fresh perspectives to public debate.',
                'content_en'  => "Youth-led journalism is expanding through school media labs, campus newsrooms, and community storytelling initiatives. Young reporters bring urgency to issues such as education reform, mental health, and digital rights.\n\nMentorship from professional editors helps students develop verification habits and ethical standards early. Youth voices also help legacy outlets reconnect with younger demographics.\n\nInvesting in young journalists is an investment in future public literacy.",
                'title_ar'    => 'أصوات الشباب في الإعلام المدني',
                'subtitle_ar' => 'كيف يشكل الصحفيون الطلاب النقاش المحلي',
                'excerpt_ar'  => 'مبادرات الإعلام الجامعي والشبابي تقدم زوايا جديدة للنقاش العام.',
                'content_ar'  => "يتوسع الإعلام بقيادة الشباب عبر مختبرات المدارس وغرف الأخبار الجامعية ومبادرات السرد المجتمعي. يجلب الصحفيون الشباب إلحاحاً لقضايا مثل التعليم والصحة النفسية والحقوق الرقمية.\n\nالإرشاد المهني يساعد الطلاب على بناء عادات التحقق والمعايير الأخلاقية مبكراً. كما تساعد أصوات الشباب المنصات التقليدية على إعادة الاتصال بالجيل الأصغر.\n\nالاستثمار في الصحفيين الشباب استثمار في ثقافة المعلومات المستقبلية.",
            ],
            [
                'title_en'    => 'Archives and Memory: Why Old Stories Still Matter',
                'subtitle_en' => 'News archives as tools for accountability',
                'excerpt_en'  => 'Well-maintained archives help reporters connect present events to historical patterns.',
                'content_en'  => "News archives are more than storage; they are institutional memory. Reporters use archival reporting to identify recurring failures, track policy promises, and provide context during breaking news.\n\nDigital preservation requires metadata standards, backup systems, and editorial indexing. Without archive discipline, newsrooms lose investigative leverage.\n\nWhen history is accessible, accountability becomes harder to evade.",
                'title_ar'    => 'الأرشيف والذاكرة: لماذا تبقى القصص القديمة مهمة',
                'subtitle_ar' => 'أرشيف الأخبار أداة للمساءلة',
                'excerpt_ar'  => 'الأرشيف المنظم يساعد الصحفيين على ربط الأحداث الحالية بأنماط تاريخية.',
                'content_ar'  => "أرشيف الأخبار ليس مجرد تخزين بل ذاكرة مؤسسية. يستخدم الصحفيون الأرشيف لرصد الإخفاقات المتكررة وتتبع الوعود السياسية وتقديم سياق أثناء الأخبار العاجلة.\n\nالحفظ الرقمي يتطلب معايير بيانات وصفية ونسخاً احتياطية وفهرسة تحريرية. دون انضباط أرشيفي تفقد غرف الأخبار أدوات التحقيق.\n\nعندما يصبح التاريخ متاحاً، يصعب التهرب من المساءلة.",
            ],
            [
                'title_en'    => 'AI Tools in the Newsroom: Opportunity and Oversight',
                'subtitle_en' => 'Using automation without compromising editorial control',
                'excerpt_en'  => 'Newsrooms are adopting AI for transcription and research while tightening disclosure policies.',
                'content_en'  => "Artificial intelligence is entering newsrooms through transcription, translation support, data analysis, and headline testing. These tools can save time, but they also introduce risks of bias, hallucination, and opaque decision-making.\n\nEditorial policy should define where AI can assist and where human judgment is mandatory. Transparency with audiences about AI-assisted workflows strengthens credibility.\n\nTechnology should amplify reporting standards, not replace them.",
                'title_ar'    => 'أدوات الذكاء الاصطناعي في غرف الأخبار: فرصة ورقابة',
                'subtitle_ar' => 'استخدام الأتمتة دون التفريط بالرقابة التحريرية',
                'excerpt_ar'  => 'تعتمد غرف الأخبار الذكاء الاصطناعي في التفريغ والبحث مع تشديد سياسات الإفصاح.',
                'content_ar'  => "يدخل الذكاء الاصطناعي غرف الأخبار عبر التفريغ ودعم الترجمة وتحليل البيانات واختبار العناوين. هذه الأدوات توفر وقتاً لكنها تحمل مخاطر التحيز والهلوسة واتخاذ قرارات غير شفافة.\n\nيجب أن تحدد السياسة التحريرية أين يساعد الذكاء الاصطناعي وأين يبقى الحكم البشري إلزامياً. الشفافية مع الجمهور حول استخدامه تعزز المصداقية.\n\nالتقنية يجب أن ترفع معايير الصحافة لا أن تحل محلها.",
            ],
            [
                'title_en'    => 'Community Listening Sessions That Improve Coverage',
                'subtitle_en' => 'Editorial meetings beyond the newsroom walls',
                'excerpt_en'  => 'Public listening forums help editors identify under-covered issues and build local trust.',
                'content_en'  => "Community listening sessions allow newsrooms to hear directly from residents about coverage gaps, language needs, and mistrust drivers. These sessions work best when they are recurring, accessible, and followed by visible editorial action.\n\nReporters gain story ideas rooted in lived experience, while editors receive feedback on tone, representation, and accessibility. Documentation of outcomes is essential to avoid performative engagement.\n\nListening is not a one-time event; it is an editorial practice.",
                'title_ar'    => 'جلسات الاستماع المجتمعية لتحسين التغطية',
                'subtitle_ar' => 'اجتماعات تحريرية خارج جدران غرفة الأخبار',
                'excerpt_ar'  => 'منتديات الاستماع العامة تساعد التحرير على رصد القضايا المغفلة وبناء الثقة.',
                'content_ar'  => "تتيح جلسات الاستماع لغرف الأخبار سماع السكان مباشرة حول فجوات التغطية وحاجات اللغة وأسباب انعدام الثقة. تنجح هذه الجلسات عندما تكون دورية ومتاحة ويتبعها إجراء تحريري واضح.\n\nيكتسب الصحفيون أفكار قصص من تجربة الناس، ويحصل التحرير على ملاحظات حول النبرة والتمثيل وسهولة الوصول. توثيق النتائج ضروري لتجنب الاستماع الشكلي.\n\nالاستماع ليس فعالية واحدة بل ممارسة تحريرية مستمرة.",
            ],
            [
                'title_en'    => 'Election Coverage Beyond the Horse Race',
                'subtitle_en' => 'Policy, participation, and local stakes',
                'excerpt_en'  => 'Voters need reporting that explains consequences, not just polling swings.',
                'content_en'  => "Election coverage often over-indexes on polls and personalities while under-explaining policy consequences. Strong civic journalism clarifies what candidates propose, how institutions function, and what outcomes affect daily life.\n\nReporters should verify campaign claims, contextualize statistics, and highlight barriers to participation such as access, misinformation, and procedural complexity.\n\nDemocracy-focused reporting should empower informed decisions, not spectacle.",
                'title_ar'    => 'تغطية الانتخابات بعيداً عن سباق التكهنات',
                'subtitle_ar' => 'السياسات والمشاركة والرهانات المحلية',
                'excerpt_ar'  => 'الناخب يحتاج تقارير تشرح النتائج لا مجرد تقلبات الاستطلاعات.',
                'content_ar'  => "غالباً تركز تغطية الانتخابات على الاستطلاعات والشخصيات أكثر من شرح تبعات السياسات. الصحافة المدنية القوية توضح ما يقترحه المرشحون وكيف تعمل المؤسسات وما الذي يمس الحياة اليومية.\n\nعلى الصحفيين التحقق من ادعاءات الحملات ووضع الأرقام في سياقها وإبراز عوائق المشاركة مثل الوصول والمعلومات المضللة وتعقيد الإجراءات.\n\nالتغطية الانتخابية يجب أن تمكّن قراراً واعياً لا أن تقدم مشهداً إعلامياً فقط.",
            ],
            [
                'title_en'    => 'Freelance Journalists and Fair Compensation',
                'subtitle_en' => 'Rethinking contracts, rates, and editorial support',
                'excerpt_en'  => 'Sustainable publishing requires fair pay and clear rights for independent contributors.',
                'content_en'  => "Freelancers sustain much of modern journalism, yet many face delayed payments, unclear rights, and limited editorial support. Newsrooms that rely on independent contributors must adopt transparent rate cards and timely invoicing.\n\nContracts should specify usage rights, correction policies, and safety provisions for hazardous assignments. Editorial onboarding helps freelancers align with verification standards.\n\nFair compensation is not charity; it is infrastructure for quality reporting.",
                'title_ar'    => 'الصحفيون المستقلون والأجر العادل',
                'subtitle_ar' => 'إعادة التفكير في العقود والأسعار والدعم التحريري',
                'excerpt_ar'  => 'النشر المستدام يتطلب أجراً عادلاً وحقوقاً واضحة للمساهمين المستقلين.',
                'content_ar'  => "يحمل الصحفيون المستقلون جزءاً كبيراً من العمل الصحفي الحديث، لكن كثيرين يواجهون تأخر الدفع وغموض الحقوق ودعماً تحريرياً محدوداً. على غرف الأخبار التي تعتمد عليهم اعتماد شفاف على جداول أسعار وفوترة في الوقت المناسب.\n\nيجب أن تحدد العقود حقوق الاستخدام وسياسات التصحيح وضوابط السلامة للمهام الخطرة. كما يساعد الإدماج التحريري على مواءمة معايير التحقق.\n\nالأجر العادل ليس معونة بل بنية تحتية لجودة التقرير.",
            ],
            [
                'title_en'    => 'Cultural Reporting That Goes Beyond Festivals',
                'subtitle_en' => 'Arts journalism as social commentary',
                'excerpt_en'  => 'Culture desks are covering identity, labor, and public space with greater depth.',
                'content_en'  => "Cultural journalism is expanding beyond event listings and celebrity coverage. Reporters now examine how art, film, literature, and performance reflect social change, economic pressure, and collective memory.\n\nStrong culture reporting connects creative work to policy, migration, gender dynamics, and urban transformation. Critics and feature writers collaborate with news desks to surface under-reported communities.\n\nCulture is not a soft beat; it is a lens on society.",
                'title_ar'    => 'التغطية الثقافية بعيداً عن المهرجانات فقط',
                'subtitle_ar' => 'صحافة الفنون كتعليق اجتماعي',
                'excerpt_ar'  => 'الأقسام الثقافية تتناول الهوية والعمل والفضاء العام بعمق أكبر.',
                'content_ar'  => "تتوسع الصحافة الثقافية خارج أجندة الفعاليات والمشاهير. يبحث الصحفيون اليوم في كيف تعكس الفنون والسينما والأدب التحولات الاجتماعية والضغوط الاقتصادية والذاكرة الجماعية.\n\nالتغطية الثقافية القوية تربط العمل الإبداعي بالسياسات والهجرة وديناميكيات النوع الاجتماعي والتحول الحضري. كما يتعاون النقاد مع أقسام الأخبار لإبراز مجتمعات مغفلة.\n\nالثقافة ليست قسماً ثانوياً بل عدسة على المجتمع.",
            ],
            [
                'title_en'    => 'Safety Protocols for Reporters on Protest Assignments',
                'subtitle_en' => 'Planning, communication, and legal awareness',
                'excerpt_en'  => 'Field safety plans are now essential for covering demonstrations and civil unrest.',
                'content_en'  => "Protest assignments expose reporters to physical risk, detention, and digital targeting. Newsrooms must provide safety training, communication protocols, and legal support before deployment.\n\nTeams should plan exit routes, identify safe contacts, and separate personal from professional devices when possible. Editors need real-time check-in systems and clear authority to pull teams back.\n\nProtecting journalists is a prerequisite for public interest coverage.",
                'title_ar'    => 'بروتوكولات السلامة للصحفيين في تغطية الاحتجاجات',
                'subtitle_ar' => 'التخطيط والتواصل والوعي القانوني',
                'excerpt_ar'  => 'خطط السلامة الميدانية أصبحت ضرورية لتغطية التظاهرات والاضطرابات.',
                'content_ar'  => "تعرض مهام تغطية الاحتجاجات الصحفيين لمخاطر جسدية واحتجاز واستهداف رقمي. على غرف الأخبار توفير تدريب سلامة وبروتوكولات تواصل ودعم قانوني قبل الإرسال.\n\nيجب التخطيط لمسارات الخروج وتحديد جهات اتصال آمنة وفصل الأجهزة الشخصية عن المهنية قدر الإمكان. وعلى التحرير أنظمة متابعة لحظية وسلطة واضحة لسحب الفرق.\n\nحماية الصحفيين شرط أساسي لتغطية المصلحة العامة.",
            ],
            [
                'title_en'    => 'Rebuilding Trust After a Major Correction',
                'subtitle_en' => 'Transparency as a newsroom recovery strategy',
                'excerpt_en'  => 'How publishers can respond to errors without destroying long-term credibility.',
                'content_en'  => "Every newsroom will publish corrections, but the response determines whether trust survives. Effective correction culture includes prompt disclosure, clear explanation of error scope, and accountability without defensiveness.\n\nLeaders should analyze systemic causes, update workflows, and communicate changes publicly. Avoiding correction delays often causes more damage than the original mistake.\n\nTrust is rebuilt through process visibility, not silence.",
                'title_ar'    => 'إعادة بناء الثقة بعد تصحيح كبير',
                'subtitle_ar' => 'الشفافية كاستراتيجية لتعافي غرفة الأخبار',
                'excerpt_ar'  => 'كيف تستجيب المنصات للأخطاء دون تدمير مصداقيتها على المدى الطويل.',
                'content_ar'  => "ستصدر كل غرفة أخبار تصحيحات، لكن طريقة الاستجابة تحدد بقاء الثقة. ثقافة التصحيح الفعالة تشمل الإفصاح السريع وشرح نطاق الخطأ والمساءلة دون دفاعية.\n\nعلى القيادات تحليل الأسباب النظامية وتحديث المسارات وإبلاغ الجمهور بالتغييرات. تأخير التصحيح غالباً يضر أكثر من الخطأ نفسه.\n\nتُعاد الثقة عبر وضوح العملية لا عبر الصمت.",
            ],
            [
                'title_en'    => 'The Business of Membership-Driven News',
                'subtitle_en' => 'Community funding models that protect editorial independence',
                'excerpt_en'  => 'Membership programs work when value, transparency, and engagement are aligned.',
                'content_en'  => "Membership models are helping independent publishers reduce reliance on volatile advertising markets. Success depends on articulating editorial value, delivering member benefits, and maintaining transparency about finances.\n\nNewsrooms should treat members as partners in mission, not just donors. Regular reporting on impact, open editorial Q&A sessions, and member-only briefings can deepen loyalty.\n\nSustainable membership is built on trust, not paywalls alone.",
                'title_ar'    => 'اقتصاد الأخبار القائم على العضوية',
                'subtitle_ar' => 'نماذج تمويل مجتمعية تحمي الاستقلال التحريري',
                'excerpt_ar'  => 'برامج العضوية تنجح عندما تتوافق القيمة والشفافية والتفاعل.',
                'content_ar'  => "تساعد نماذج العضوية الناشرين المستقلين على تقليل الاعتماد على إعلانات متقلبة. النجاح يعتمد على توضيح القيمة التحريرية وتقديم مزايا للأعضاء والشفافية المالية.\n\nيجب التعامل مع الأعضاء كشركاء في المهمة لا كمتبرعين فقط. التقارير الدورية عن الأثر وجلسات الأسئلة التحريرية تعمق الولاء.\n\nالعضوية المستدامة تُبنى على الثقة لا على جدران الدفع وحدها.",
            ],
        ];
    }
}
