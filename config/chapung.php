<?php

$validEmail = static fn (?string $email): string => filter_var(trim((string) $email), FILTER_VALIDATE_EMAIL)
    ? trim((string) $email)
    : '';

$adminEmails = array_values(array_filter(array_map(
    static fn (string $email): string => mb_strtolower($validEmail($email)),
    explode(',', (string) env('ADMIN_EMAILS', 'admin@chapungart.com')),
)));

return [
    'admin_emails' => $adminEmails,

    'emails' => [
        'admin' => $validEmail(env('MAIL_FROM_ADDRESS', 'admin@chapungart.com')) ?: 'admin@chapungart.com',
        'info' => $validEmail(env('INFO_EMAIL', 'info@chapungart.com')) ?: 'info@chapungart.com',
        'gallery' => $validEmail(env('GALLERY_EMAIL', 'gallery@chapungart.com')) ?: 'gallery@chapungart.com',
        'news' => $validEmail(env('NEWS_EMAIL', 'news@chapungart.com')) ?: 'news@chapungart.com',
        'media' => $validEmail(env('MEDIA_EMAIL', 'media@chapungart.com')) ?: 'media@chapungart.com',
        'support' => $validEmail(env('SUPPORT_EMAIL', 'support@chapungart.com')) ?: 'support@chapungart.com',
        'finance' => $validEmail(env('FINANCE_EMAIL', 'finance@chapungart.com')) ?: 'finance@chapungart.com',
        'contact' => $validEmail(env('CONTACT_EMAIL', 'contact@chapungart.com')) ?: 'contact@chapungart.com',
    ],

    'email_labels' => [
        'admin' => 'Admin',
        'info' => 'Info',
        'gallery' => 'Gallery',
        'news' => 'News',
        'media' => 'Media',
        'support' => 'Support',
        'finance' => 'Finance',
        'contact' => 'Contact',
    ],
];
