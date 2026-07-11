<?php

use App\Mail\ContactInquiryReceived;
use App\Mail\AdminNotificationCreated;
use App\Mail\PaymentNotificationReceived;
use App\Models\AdminNotification;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Mail;

function contactPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Maria Contact',
        'email' => 'maria@example.com',
        'department' => 'contact',
        'subject' => 'Artwork Inquiry',
        'message' => 'Saya ingin bertanya tentang koleksi Chapung Art.',
    ], $overrides);
}

test('mail department configuration is available for production mailboxes', function () {
    $departments = config('chapung.emails');

    expect(array_keys($departments))->toBe([
        'admin',
        'info',
        'gallery',
        'news',
        'media',
        'support',
        'finance',
        'contact',
    ]);

    foreach ($departments as $key => $address) {
        expect($address)->toBe("{$key}@chapungart.com")
            ->and(filter_var($address, FILTER_VALIDATE_EMAIL))->not->toBeFalse();
    }
});

test('admin emails support comma separated addresses from centralized config', function () {
    config(['chapung.admin_emails' => ['admin@chapungart.com', 'support@chapungart.com']]);

    expect(config('chapung.admin_emails'))->toBe([
        'admin@chapungart.com',
        'support@chapungart.com',
    ]);
});

test('contact page renders department mailbox options', function () {
    $this->withSession(['locale' => 'en'])
        ->get(route('contact'))
        ->assertOk()
        ->assertSee('Contact Form')
        ->assertSee('Admin')
        ->assertSee('Info')
        ->assertSee('Gallery')
        ->assertSee('News')
        ->assertSee('Media')
        ->assertSee('Support')
        ->assertSee('Finance')
        ->assertSee('Contact')
        ->assertSee('0813-4400-1427')
        ->assertSee('https://wa.me/6281344001427', false)
        ->assertSee('0813-9226-9774')
        ->assertSee('https://wa.me/6281392269774', false)
        ->assertSee('JL SESATE NO 242 RT.007 RW.002 BAMBU PEMALI')
        ->assertSee('MERAUKE MERAUKE, KAB. 99616');
});

test('contact form sends mail to selected department mailbox', function (string $department, string $address) {
    Mail::fake();

    $this->post(route('contact.send'), contactPayload([
        'department' => $department,
        'subject' => ucfirst($department).' request',
    ]))->assertRedirect();

    Mail::assertSent(ContactInquiryReceived::class, function (ContactInquiryReceived $mail) use ($department, $address): bool {
        return $mail->hasTo($address)
            && $mail->payload['department'] === $department
            && $mail->payload['email'] === 'maria@example.com';
    });
})->with([
    ['admin', 'admin@chapungart.com'],
    ['info', 'info@chapungart.com'],
    ['gallery', 'gallery@chapungart.com'],
    ['news', 'news@chapungart.com'],
    ['media', 'media@chapungart.com'],
    ['support', 'support@chapungart.com'],
    ['finance', 'finance@chapungart.com'],
    ['contact', 'contact@chapungart.com'],
]);

test('contact form sends default inquiry to contact mailbox using configured sender', function () {
    Mail::fake();

    $this->post(route('contact.send'), contactPayload())->assertRedirect();

    Mail::assertSent(ContactInquiryReceived::class, function (ContactInquiryReceived $mail): bool {
        return $mail->hasTo('contact@chapungart.com')
            && $mail->hasFrom('admin@chapungart.com');
    });
});

test('payment notification is sent to finance mailbox', function () {
    $order = Order::create([
        'customer_name' => 'Finance Customer',
        'customer_email' => 'finance-customer@example.com',
        'customer_phone' => '6281234567890',
        'subtotal' => 250000,
        'discount_total' => 0,
        'shipping_total' => 0,
        'status' => 'pending',
        'payment_status' => 'unpaid',
    ]);

    Mail::fake();

    Payment::create([
        'order_id' => $order->id,
        'payment_method' => 'bank_transfer',
        'amount' => 250000,
        'status' => 'pending',
    ]);

    Mail::assertSent(PaymentNotificationReceived::class, function (PaymentNotificationReceived $mail): bool {
        return $mail->hasTo('finance@chapungart.com')
            && $mail->hasFrom('admin@chapungart.com');
    });
});

test('admin notification is sent to configured admin emails', function () {
    config(['chapung.admin_emails' => ['admin@chapungart.com', 'support@chapungart.com']]);
    Mail::fake();

    AdminNotification::create([
        'title' => 'Audit notification',
        'message' => 'Admin notification mail test.',
        'type' => 'system',
        'url' => url('/admin'),
    ]);

    Mail::assertSent(AdminNotificationCreated::class, function (AdminNotificationCreated $mail): bool {
        return $mail->hasTo('admin@chapungart.com')
            && $mail->hasTo('support@chapungart.com')
            && $mail->hasFrom('admin@chapungart.com');
    });
});

test('contact form rejects unknown mailbox and does not send mail', function () {
    Mail::fake();

    $this->from(route('contact'))
        ->post(route('contact.send'), contactPayload(['department' => 'unknown']))
        ->assertRedirect(route('contact'))
        ->assertSessionHasErrors('department');

    Mail::assertNothingSent();
});

test('env example documents every production mailbox address', function () {
    $env = parse_ini_file(base_path('.env.example'), false, INI_SCANNER_RAW) ?: [];

    expect($env['MAIL_MAILER'] ?? null)->toBe('smtp')
        ->and($env['MAIL_HOST'] ?? null)->toBe('mail.chapungart.com')
        ->and($env['MAIL_PORT'] ?? null)->toBe('465')
        ->and($env['MAIL_USERNAME'] ?? null)->toBe('admin@chapungart.com')
        ->and($env['MAIL_PASSWORD'] ?? null)->toBe('')
        ->and($env['MAIL_ENCRYPTION'] ?? null)->toBe('ssl')
        ->and($env['MAIL_FROM_ADDRESS'] ?? null)->toBe('admin@chapungart.com')
        ->and($env['ADMIN_EMAILS'] ?? null)->toBe('admin@chapungart.com')
        ->and($env['INFO_EMAIL'] ?? null)->toBe('info@chapungart.com')
        ->and($env['GALLERY_EMAIL'] ?? null)->toBe('gallery@chapungart.com')
        ->and($env['NEWS_EMAIL'] ?? null)->toBe('news@chapungart.com')
        ->and($env['MEDIA_EMAIL'] ?? null)->toBe('media@chapungart.com')
        ->and($env['SUPPORT_EMAIL'] ?? null)->toBe('support@chapungart.com')
        ->and($env['FINANCE_EMAIL'] ?? null)->toBe('finance@chapungart.com')
        ->and($env['CONTACT_EMAIL'] ?? null)->toBe('contact@chapungart.com');
});

test('mail configuration does not use private gmail addresses', function () {
    $files = [
        base_path('.env.example'),
        config_path('chapung.php'),
        config_path('mail.php'),
        config_path('services.php'),
    ];

    $content = collect($files)
        ->map(fn (string $file): string => file_get_contents($file) ?: '')
        ->implode("\n");

    expect($content)->not->toContain('ronaldretraubun2022@gmail.com')
        ->and($content)->not->toContain('noreply@chapungart.com')
        ->and($content)->not->toContain('gmail.com');
});
