<?php

return [
    'brand' => [
        'eyebrow' => 'Al Shaheen · Newsroom',
    ],

    'navigation' => [
        'dashboard' => 'Dashboard',
        'groups' => [
            'Content'     => 'Content',
            'Users'       => 'Users',
            'Catalog'     => 'Catalog',
            'Training'    => 'Training',
            'Marketing'   => 'Marketing',
            'Monetization'=> 'Monetization',
            'Settings'    => 'Settings',
        ],
    ],

    'pages' => [
        'ai_settings' => 'AI Settings',
    ],

    'resources' => [
        'articles' => [
            'navigation' => 'Articles',
            'label'      => 'Article',
            'plural'     => 'Articles',
        ],
        'news' => [
            'navigation' => 'News',
            'label'      => 'News Item',
            'plural'     => 'News',
        ],
        'opinions' => [
            'navigation' => 'Opinions',
            'label'      => 'Opinion',
            'plural'     => 'Opinions',
        ],
        'comments' => [
            'navigation' => 'Comments',
            'label'      => 'Comment',
            'plural'     => 'Comments',
        ],
        'submissions' => [
            'navigation' => 'Submissions',
            'label'      => 'Submission',
            'plural'     => 'Submissions',
        ],
        'interviews' => [
            'navigation' => 'Interviews',
            'label'      => 'Interview',
            'plural'     => 'Interviews',
        ],
        'reports' => [
            'navigation' => 'Reports',
            'label'      => 'Report',
            'plural'     => 'Reports',
        ],
        'media_items' => [
            'navigation' => 'Multimedia',
            'label'      => 'Media Item',
            'plural'     => 'Media Items',
        ],
        'writers' => [
            'navigation' => 'Writers',
            'label'      => 'Writer',
            'plural'     => 'Writers',
        ],
        'editors' => [
            'navigation' => 'Editors',
            'label'      => 'Editor',
            'plural'     => 'Editors',
        ],
        'readers' => [
            'navigation' => 'Readers',
            'label'      => 'Reader',
            'plural'     => 'Readers',
        ],
        'contributors' => [
            'navigation' => 'Contributors',
            'label'      => 'Contributor',
            'plural'     => 'Contributors',
        ],
        'admins' => [
            'navigation' => 'Admins',
            'label'      => 'Admin',
            'plural'     => 'Admins',
        ],
        'users' => [
            'navigation' => 'Users',
            'label'      => 'User',
            'plural'     => 'Users',
        ],
        'primary_categories' => [
            'navigation' => 'Primary Category',
            'label'      => 'Primary Category',
            'plural'     => 'Primary Categories',
        ],
        'secondary_categories' => [
            'navigation' => 'Secondary Category',
            'label'      => 'Secondary Category',
            'plural'     => 'Secondary Categories',
        ],
        'tags' => [
            'navigation' => 'Tags',
            'label'      => 'Tag',
            'plural'     => 'Tags',
        ],
        'courses' => [
            'navigation' => 'Courses',
            'label'      => 'Course',
            'plural'     => 'Courses',
        ],
        'lessons' => [
            'navigation' => 'Lessons',
            'label'      => 'Lesson',
            'plural'     => 'Lessons',
        ],
        'course_categories' => [
            'navigation' => 'Categories',
            'label'      => 'Course Category',
            'plural'     => 'Categories',
        ],
        'progress' => [
            'navigation' => 'Progress',
            'label'      => 'Progress',
            'plural'     => 'Progress',
        ],
        'contact_messages' => [
            'navigation' => 'Contact Messages',
            'label'      => 'Contact Message',
            'plural'     => 'Contact Messages',
        ],
        'newsletter' => [
            'navigation' => 'Newsletter',
            'label'      => 'Subscriber',
            'plural'     => 'Subscribers',
        ],
        'ads' => [
            'navigation' => 'Ads',
            'label'      => 'Ad',
            'plural'     => 'Ads',
        ],
        'payments' => [
            'navigation' => 'Payments',
            'label'      => 'Payment',
            'plural'     => 'Payments',
        ],
    ],

    'fields' => [
        'title'    => 'Title',
        'headline' => 'Headline',
    ],

    'actions' => [
        'translate_article'              => 'Translate',
        'translate_article_confirm'      => 'Empty fields will be filled automatically from the other language (Arabic ↔ English) using AI.',
        'translate_article_success'        => 'Translation applied (:from → :to). Review the fields, then save.',
        'translate_article_no_source'      => 'Add content in Arabic or English first, then translate.',
        'translate_article_failed'         => 'Translation failed. Try again.',
        'translate_article_nothing_to_fill'  => 'No empty fields to fill in the target language.',
        'translate_article_unavailable'    => 'Enable OpenAI in Admin → AI Settings.',
        'locale_ar'                        => 'Arabic',
        'locale_en'                        => 'English',
    ],

    'dashboard' => [
        'welcome_back'       => 'Welcome back, :name',
        'welcome_back_short' => 'Welcome back',
        'attention_items'    => 'items need your attention',
        'all_caught_up'      => 'All caught up',
        'quick_actions'      => 'Quick actions',
        'at_a_glance'        => 'At a glance',
        'at_a_glance_desc'   => 'Key metrics across articles, news, and community',
        'published'          => 'Published',
        'published_desc'     => ':articles articles · :news news',
        'editorial_queue'    => 'Editorial Queue',
        'editorial_queue_desc'=> ':articles articles · :news news',
        'needs_attention'    => 'Needs Attention',
        'needs_attention_desc'=> ':comments comments · :messages messages',
        'total_views'        => 'Total Views',
        'total_views_desc'   => 'Across published content',
        'actions' => [
            'new_article' => [
                'label'       => 'New Article',
                'description' => 'Create a bilingual story',
            ],
            'new_news' => [
                'label'       => 'New News Item',
                'description' => 'Publish breaking coverage',
            ],
            'review_articles' => [
                'label'       => 'Review Articles',
                'description' => 'Editorial queue',
            ],
            'moderate_comments' => [
                'label'       => 'Moderate Comments',
                'description' => 'Pending approvals',
            ],
            'contact_inbox' => [
                'label'       => 'Contact Inbox',
                'description' => 'Reader messages',
            ],
            'ai_settings' => [
                'label'       => 'AI Settings',
                'description' => 'Translation & GPT',
            ],
        ],
    ],
];
