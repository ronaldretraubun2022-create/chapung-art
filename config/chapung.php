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

    'address' => "JL SESATE NO 242 RT.007 RW.002 BAMBU PEMALI\nMERAUKE MERAUKE, KAB. 99616",

    'contact_phone' => '0813-4400-1427',
    'contact_whatsapp' => '6281344001427',

    'contact_numbers' => [
        [
            'label' => 'Admin 1',
            'phone' => '0813-4400-1427',
            'whatsapp' => '6281344001427',
        ],
        [
            'label' => 'Admin 2',
            'phone' => '0813-9226-9774',
            'whatsapp' => '6281392269774',
        ],
    ],

    'bank_accounts' => [
        [
            'bank' => 'BANK PAPUA',
            'account_number' => '4000202029294',
            'account_name' => 'CHAPUNG ART',
        ],
        [
            'bank' => 'Bank BCA',
            'account_number' => '8316008181',
            'account_name' => 'Vara Diah Kirana',
        ],
    ],

    'cart' => [
        'shipping_estimates' => [
            'pickup' => [
                'label' => 'Ambil di Chapung Art Merauke',
                'amount' => 0,
            ],
            'merauke' => [
                'label' => 'Kurir Merauke',
                'amount' => 25000,
            ],
            'papua' => [
                'label' => 'Papua Selatan / Papua',
                'amount' => 60000,
            ],
            'indonesia' => [
                'label' => 'Indonesia',
                'amount' => 125000,
            ],
        ],
        'coupons' => [
            'CHAPUNG10' => [
                'label' => 'Chapung 10%',
                'type' => 'percent',
                'value' => 10,
                'max_discount' => 150000,
                'min_subtotal' => 500000,
            ],
            'PAPUA50' => [
                'label' => 'Papua Collector',
                'type' => 'fixed',
                'value' => 50000,
                'max_discount' => 50000,
                'min_subtotal' => 250000,
            ],
        ],
    ],

    'checkout' => [
        'payment_methods' => [
            'bank_transfer' => [
                'label' => 'Transfer Bank',
                'description' => 'Transfer ke rekening resmi Chapung Art setelah order dibuat.',
            ],
            'manual_confirmation' => [
                'label' => 'Konfirmasi Admin',
                'description' => 'Tim Chapung Art menghubungi Anda untuk konfirmasi pembayaran.',
            ],
            'cod_merauke' => [
                'label' => 'Bayar di Merauke',
                'description' => 'Pembayaran dilakukan saat pengambilan atau pengantaran area Merauke.',
            ],
        ],
    ],

    'digital_download' => [
        'max_kb' => (int) env('DIGITAL_DOWNLOAD_MAX_KB', 51200),
        'allowed_mime_types' => [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'image/webp',
        ],
        'extensions' => [
            'application/pdf' => 'pdf',
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ],
    ],

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
