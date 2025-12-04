<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification; // Laravel ၏ Notification Model
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * 1. GET /notifications
     * User ရဲ့ Notifications အားလုံးကို (ဖတ်ပြီး/မဖတ်ရသေး) ရယူသည်
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // 💡 Notes: $request->user()->notifications() သည် DatabaseNotification query builder ကို ပြန်ပေးသည်
        $notifications = $request->user()->notifications()
            ->latest()
            ->take(50) // နောက်ဆုံး ၅၀ ကိုသာ ယူပါ
            ->get();

        // Flutter UI ကို လိုအပ်တဲ့ data format (title, message, type) ဖြင့် ပြန်ပို့ပေးသည်
        return response()->json($notifications->map(function ($notify) {
            // Notifications များ၏ `data` column ထဲက key များကို တိုက်ရိုက်ယူသည်
            return [
                'id' => $notify->id,
                'title' => $notify->data['title'] ?? 'Notification',
                'message' => $notify->data['message'] ?? 'New activity.',
                'type' => $notify->data['type'] ?? 'info', // success, error, info
                'read_at' => $notify->read_at,
                // Timezone ချိန်ညှိမှုအတွက် ISO String ပြန်ပို့သည်
                'created_at' => $notify->created_at->toIso8601String(), 
                // Flutter UI အတွက် diffForHumans() ကို Server ကနေ တွက်ပြီး ပို့ပေးသည် (Optional)
                'created_at_human' => $notify->created_at->diffForHumans(), 
            ];
        }));
    }
    
    // 💡 Note: /notifications/unread-count route ကို routes/api.php မှာ closure ဖြင့် ရေးထားပြီးဖြစ်သည်။

    /**
     * 2. POST /notifications/read/{id}
     * Notification တစ်ခုကို ဖတ်ပြီးအဖြစ် မှတ်သားသည်
     * @param Request $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsRead(Request $request, string $id)
    {
        // User ရဲ့ notifications များထဲကမှ ID ကို စစ်ဆေးပြီး ရှာဖွေသည်
        $notification = $request->user()->notifications()->where('id', $id)->first();

        if ($notification) {
            $notification->markAsRead(); // Laravel ၏ built-in method
            return response()->json(['message' => 'Marked as read'], 200);
        }

        return response()->json(['message' => 'Notification not found'], 404);
    }

    /**
     * 3. POST /notifications/clear-all
     * Notifications အားလုံးကို ဖျက်ပစ်သည်
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function clearAll(Request $request)
    {
        // User ရဲ့ notifications အားလုံးကို Database မှ delete လုပ်သည်
        $request->user()->notifications()->delete(); 
        
        return response()->json(['message' => 'All notifications cleared'], 200);
    }

    /**
     * 4. DELETE /notifications/{id}
     * Notification တစ်ခုကို ဖျက်ပစ်သည်
     * @param Request $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request, string $id)
    {
        $notification = $request->user()->notifications()->where('id', $id)->first();

        if ($notification) {
            $notification->delete();
            return response()->json(null, 204); // 204 No Content for successful deletion
        }

        return response()->json(['message' => 'Notification not found'], 404);
    }
}