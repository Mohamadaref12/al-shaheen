<?php

return [
    'brand'   => 'Al Shaheen',
    'footer'  => 'This is an automated message from Al Shaheen. Please do not reply directly to this email.',
    'greeting'=> 'Hello :name,',

    'actions' => [
        'view'     => 'View in dashboard',
        'open'     => 'Open',
        'visit'    => 'Visit Al Shaheen',
    ],

    'staff' => [
        'article_review' => [
            'subject' => 'Article awaiting review',
            'heading' => 'New article in the editorial queue',
            'body'    => '":title" needs editorial review.',
        ],
        'news_review' => [
            'subject' => 'News item awaiting review',
            'heading' => 'News awaiting review',
            'body'    => '":title" is waiting for editorial action.',
        ],
        'opinion_review' => [
            'subject' => 'Opinion awaiting review',
            'heading' => 'Opinion awaiting review',
            'body'    => '":title" is waiting for editorial action.',
        ],
        'comment_pending' => [
            'subject' => 'New comment pending review',
            'heading' => 'Comment moderation needed',
            'body'    => ':author commented on ":title".',
        ],
        'contact_new' => [
            'subject' => 'New contact message',
            'heading' => 'New reader message',
            'body'    => ':name sent a message: ":subject".',
        ],
        'writer_application' => [
            'subject' => 'New writer application',
            'heading' => 'Writer application submitted',
            'body'    => ':name applied to join as a writer.',
        ],
        'submission_review' => [
            'subject' => 'New content submission',
            'heading' => 'Submission awaiting review',
            'body'    => '":title" was submitted for review.',
        ],
    ],

    'writer' => [
        'application_received' => [
            'subject' => 'We received your writer application',
            'heading' => 'Application received',
            'body'    => 'Thank you for applying to write for Al Shaheen. Our editorial team will review your application and get back to you soon.',
        ],
        'application_approved' => [
            'subject' => 'Your writer application was approved',
            'heading' => 'Welcome aboard',
            'body'    => 'Your writer application has been approved. You can now submit stories through your account.',
        ],
        'application_rejected' => [
            'subject' => 'Update on your writer application',
            'heading' => 'Application not approved',
            'body'    => 'We are unable to approve your writer application at this time.:notes',
        ],
        'application_suspended' => [
            'subject' => 'Your writer account was suspended',
            'heading' => 'Account suspended',
            'body'    => 'Your writer account has been suspended.:notes',
        ],
        'verified_granted' => [
            'subject' => 'You are now a verified writer',
            'heading' => 'Verified writer badge granted',
            'body'    => 'Congratulations! Your verified writer badge is now active on Al Shaheen.',
        ],
        'verified_revoked' => [
            'subject' => 'Verified writer badge removed',
            'heading' => 'Verification removed',
            'body'    => 'Your verified writer badge has been removed.:notes',
        ],
        'article_ready' => [
            'subject' => 'Your article was approved',
            'heading' => 'Article ready for publishing',
            'body'    => 'Your article ":title" has been approved and is ready for publishing.',
        ],
        'article_published' => [
            'subject' => 'Your article is now live',
            'heading' => 'Article published',
            'body'    => 'Your article ":title" has been published on Al Shaheen.',
        ],
        'article_rejected' => [
            'subject' => 'Your article needs changes',
            'heading' => 'Article not approved',
            'body'    => 'Your article ":title" was not approved.:notes',
        ],
        'news_published' => [
            'subject' => 'Your news item is now live',
            'heading' => 'News published',
            'body'    => 'Your news item ":title" has been published.',
        ],
        'opinion_published' => [
            'subject' => 'Your opinion piece is now live',
            'heading' => 'Opinion published',
            'body'    => 'Your opinion ":title" has been published.',
        ],
        'submission_approved' => [
            'subject' => 'Your submission was approved',
            'heading' => 'Submission approved',
            'body'    => 'Your submission ":title" has been approved.',
        ],
        'submission_rejected' => [
            'subject' => 'Your submission was not approved',
            'heading' => 'Submission rejected',
            'body'    => 'Your submission ":title" was not approved.:notes',
        ],
    ],

    'reader' => [
        'contact_received' => [
            'subject' => 'We received your message',
            'heading' => 'Message received',
            'body'    => 'Thank you for contacting Al Shaheen. We received your message about ":subject" and will respond as soon as possible.',
        ],
        'contact_replied' => [
            'subject' => 'Reply from Al Shaheen',
            'heading' => 'We replied to your message',
            'body'    => 'Regarding ":subject":\n\n:reply',
        ],
        'comment_pending' => [
            'subject' => 'Your comment is under review',
            'heading' => 'Comment received',
            'body'    => 'Thank you for commenting on ":title". Your comment is pending moderation and will appear once approved.',
        ],
        'comment_approved' => [
            'subject' => 'Your comment was approved',
            'heading' => 'Comment published',
            'body'    => 'Your comment on ":title" has been approved and is now visible.',
        ],
        'comment_rejected' => [
            'subject' => 'Your comment was not approved',
            'heading' => 'Comment not published',
            'body'    => 'Your comment on ":title" was not approved for publication.',
        ],
        'welcome' => [
            'subject' => 'Welcome to Al Shaheen',
            'heading' => 'Welcome',
            'body'    => 'Your account has been created successfully. Explore stories, save articles, and stay informed with Al Shaheen.',
        ],
        'newsletter_subscribed' => [
            'subject' => 'Newsletter subscription confirmed',
            'heading' => 'You are subscribed',
            'body'    => 'You are now subscribed to the Al Shaheen newsletter. Expect editorial highlights and important updates in your inbox.',
        ],
        'newsletter_unsubscribed' => [
            'subject' => 'Newsletter unsubscribed',
            'heading' => 'Subscription cancelled',
            'body'    => 'You have been unsubscribed from the Al Shaheen newsletter.',
        ],
        'password_reset' => [
            'subject' => 'Password reset code',
            'heading' => 'Reset your password',
            'body'    => 'Use this code to reset your password: :code. It expires at :expires.',
        ],
    ],

    'notes_prefix' => "\n\nNotes: :notes",
];
