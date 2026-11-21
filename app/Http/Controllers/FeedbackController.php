<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\Feedback;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeedbackController extends Controller
{
    public function store(Request $request, Complaint $complaint): RedirectResponse
    {
        abort_unless((int) $complaint->user_id === (int) $request->user()->id, 403);
        abort_unless($complaint->status === 'selesai', 422, 'Feedback hanya untuk laporan selesai.');
        abort_if($complaint->feedback()->exists(), 422, 'Feedback sudah diberikan.');

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        Feedback::create([
            'complaint_id' => $complaint->id,
            'user_id' => $request->user()->id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
        ]);

        ActivityLogger::log('feedback_given', "Feedback tiket {$complaint->ticket_number}: {$validated['rating']}/5", Complaint::class, $complaint->id, [
            'rating' => $validated['rating'],
        ], $request);

        return back()->with('success', 'Terima kasih atas penilaian Anda.');
    }

    public function index(): View
    {
        abort_unless(auth()->user()->isRole('admin', 'pimpinan'), 403);

        $feedbacks = Feedback::with(['complaint.category', 'user'])
            ->latest()->paginate(12);

        return view('feedbacks.index', [
            'feedbacks' => $feedbacks,
            'rata' => round((float) Feedback::avg('rating'), 1),
            'distribusi' => Feedback::selectRaw('rating, COUNT(*) as total')->groupBy('rating')->orderBy('rating')->pluck('total', 'rating'),
        ]);
    }
}
