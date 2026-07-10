@include('errors.layout', [
    'code' => 429,
    'title' => 'Terlalu Banyak Permintaan',
    'message' => 'Permintaan Anda terlalu cepat. Silakan beri jeda sebentar sebelum mencoba kembali.',
])
