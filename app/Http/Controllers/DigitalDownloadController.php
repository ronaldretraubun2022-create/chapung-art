<?php

namespace App\Http\Controllers;

use App\Models\Artwork;
use App\Services\DigitalDownloadService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DigitalDownloadController extends Controller
{
    public function __invoke(Artwork $artwork, DigitalDownloadService $downloads): StreamedResponse
    {
        abort_unless($downloads->canDownload($artwork, auth()->user()), 403);
        abort_unless($downloads->fileExists($artwork), 404);

        return $downloads->downloadResponse($artwork);
    }
}
