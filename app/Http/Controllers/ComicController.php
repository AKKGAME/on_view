<?php

namespace App\Http\Controllers;

use App\Models\Comic;
use App\Models\ComicChapter;
use App\Models\Transaction; // Premium စစ်ဆေးရန်
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ComicController extends Controller
{
    // 1. GET /api/comics
    // Comic ဇာတ်လမ်းတွဲများ အားလုံးကို ရယူခြင်း
    public function index()
    {
        $comics = Comic::latest()
            ->select('id', 'title', 'slug', 'cover_image', 'is_finished', 'author') // လိုအပ်တာပဲ ယူမယ်
            ->paginate(20); // Pagination သုံးထားသည်

        return response()->json($comics);
    }

    // 2. GET /api/comics/{slug}
    // Comic တစ်ခုချင်းစီ၏ အသေးစိတ်နှင့် Chapter များကို ရယူခြင်း
    public function show($slug)
    {
        $comic = Comic::where('slug', $slug)
            ->with(['chapters' => function ($query) {
                $query->orderBy('chapter_number', 'desc'); // အသစ်ဆုံး Chapter အပေါ်တင်မယ်
            }])
            ->firstOrFail();

        return response()->json($comic);
    }

    // 3. GET /api/comics/chapter/{id}
    // Chapter တစ်ခုကို ဖတ်ခြင်း (Pages ရယူခြင်း)
    public function readChapter(Request $request, $id)
    {
        $chapter = ComicChapter::findOrFail($id);

        // --- PREMIUM CHECK LOGIC ---
        if ($chapter->is_premium) {
            $user = $request->user();
            
            if (!$user) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            // ဝယ်ပြီးသားလား စစ်ဆေးခြင်း
            // Transaction description format: "comic_chapter_{id}"
            $hasUnlocked = Transaction::where('user_id', $user->id)
                ->where('type', 'purchase')
                ->where('description', 'comic_chapter_' . $chapter->id)
                ->exists();

            if (!$hasUnlocked) {
                return response()->json([
                    'message' => 'This chapter is premium. Please unlock it first.',
                    'coin_price' => $chapter->coin_price,
                    'is_locked' => true
                ], 403);
            }
        }
        // ---------------------------

        return response()->json([
            'id' => $chapter->id,
            'title' => $chapter->title,
            'chapter_number' => $chapter->chapter_number,
            // Model မှာ getFullPageUrlsAttribute ရေးထားပြီးဖြစ်လို့ ဒီမှာခေါ်သုံးရုံပါပဲ
            'pages' => $chapter->full_page_urls, 
            'is_locked' => false
        ]);
    }
}