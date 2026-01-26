<?php

namespace App\Http\Controllers;

use App\Models\Comic;
use App\Models\ComicChapter;
use App\Models\Transaction; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ComicController extends Controller
{
    // 1. GET /api/comics
    public function index()
    {
        $comics = Comic::latest()
            ->select('id', 'title', 'slug', 'cover_image', 'is_finished', 'author')
            ->paginate(10);

        return response()->json($comics);
    }

    // 2. GET /api/comics/{slug}
    public function show($slug)
    {
        $comic = Comic::where('slug', $slug)
            ->with(['chapters' => function ($query) {
                $query->orderBy('chapter_number', 'desc');
            }])
            ->firstOrFail();

        return response()->json($comic);
    }

    // 3. GET /api/comics/chapter/{id}
    public function readChapter(Request $request, $id)
    {
        $chapter = ComicChapter::findOrFail($id);
        $user = $request->user();

        // 🟢 1. VIP CHECK (အရေးကြီးဆုံး)
        // User ရှိပြီး Premium သက်တမ်းကျန်သေးရင် ဝယ်စရာမလိုဘဲ တန်းပေးဖတ်မယ်
        if ($user && $user->is_premium) {
            return $this->successResponse($chapter);
        }

        // 🟢 2. FREE CHAPTER CHECK
        // Chapter က Premium မဟုတ်ရင် (Free ဆိုရင်) ပေးဖတ်မယ်
        if (!$chapter->is_premium) {
            return $this->successResponse($chapter);
        }

        // --- ဒီအောက်ရောက်ရင် Premium Chapter ဖြစ်လို့ Login ရှိမှရမယ် ---
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        // 🟢 3. PURCHASED CHECK
        // User က ဒီ Chapter ကို Coin နဲ့ ဝယ်ထားပြီးသားလား စစ်မယ်
        $hasUnlocked = Transaction::where('user_id', $user->id)
            ->where('description', 'comic_chapter_' . $chapter->id) // Format ကိုက်ညီပါစေ
            ->exists();

        if ($hasUnlocked) {
            return $this->successResponse($chapter);
        }

        // 🔴 4. LOCKED (ဘာမှမဝင်ရင် Lock ချမယ်)
        return response()->json([
            'success' => false,
            'error' => 'locked', // Flutter က ဒီ keyword ကိုစစ်ပြီး Dialog ပြမှာပါ
            'message' => 'This chapter is premium. Please unlock it.',
            'coin_price' => $chapter->coin_price,
        ], 403);
    }

    // Helper Function: Code ထပ်မရေးရအောင် ခွဲထုတ်ထားခြင်း
    private function successResponse($chapter)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $chapter->id,
                'title' => $chapter->title,
                'chapter_number' => $chapter->chapter_number,
                'pages' => $chapter->full_page_urls, // Accessor from Model
                'is_locked' => false
            ]
        ]);
    }
}