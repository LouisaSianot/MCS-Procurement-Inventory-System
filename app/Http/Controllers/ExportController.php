<?php

namespace App\Http\Controllers;

use App\Models\ExportRequest;
use Illuminate\Support\Facades\Storage;

class ExportController extends Controller
{
    public function download(ExportRequest $exportRequest)
    {
        abort_unless($exportRequest->user_id === auth()->id(), 403);
        abort_unless($exportRequest->status === ExportRequest::STATUS_COMPLETED && $exportRequest->path, 404);

        return Storage::disk('local')->download($exportRequest->path, $exportRequest->filename);
    }
}
