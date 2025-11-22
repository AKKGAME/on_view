<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Oracle extends Component
{
    public $userInput = ''; 
    public $messages = []; // Chat History ကို သိမ်းမယ့် Array
    public $isLoading = false;

    public function mount()
    {
        // စစချင်း Welcome Message
        $this->messages[] = [
            'role' => 'model', 
            'text' => 'Greetings, Traveler. I am the Oracle. What anime wisdom do you seek today?'
        ];
    }

    public function askGemini()
    {
        // ၁။ Input စစ်ဆေးခြင်း
        if (empty(trim($this->userInput))) {
            return;
        }

        // ၂။ User မေးတဲ့စာကို UI မှာ ပြခြင်း
        $this->messages[] = ['role' => 'user', 'text' => $this->userInput];
        
        // Loading ပြမယ်၊ Input ကို ရှင်းမယ်
        $this->isLoading = true;
        $this->userInput = ''; 

        try {
            $apiKey = env('GEMINI_API_KEY');
            
            // * သင်တောင်းဆိုထားသည့် URL အသစ် (gemini-2.5-flash) *
            $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}";

            // ၃။ Chat History ကို API ပို့ရန် ပြင်ဆင်ခြင်း
            $contents = [];
            foreach ($this->messages as $msg) {
                $contents[] = [
                    'role' => $msg['role'],
                    'parts' => [['text' => $msg['text']]]
                ];
            }

            // ၄။ System Instruction (Oracle Persona & Language Rules)
            // နောက်ဆုံး User မေးတဲ့စာမှာ ပေါင်းထည့်ပါမယ် (ဒါမှ စကားပြောရှည်သွားလည်း မမေ့တော့ပါ)
            $instruction = "
            [SYSTEM INSTRUCTION: 
            1. You are the Anime Oracle.
            2. Context: We are having a continuous conversation about Anime.
            3. Rule: If I ask in Burmese, YOU MUST REPLY IN BURMESE.
            4. Rule: If I ask in English, reply in English.
            5. Style: Use emojis ✨, 🔮, 🧙‍♂️, 🧙‍♀️, 🧙, 🧚‍♂️, 🧛, 🎩, 📜, ⚗️, 🧪, 🗝️, ⚡, 🔥, 🗡️ and keep it concise.]";

            // Array ရဲ့ နောက်ဆုံးအခန်း (User နောက်ဆုံးပို့လိုက်သောစာ) ထဲသို့ Instruction ပေါင်းထည့်ခြင်း
            $lastIndex = count($contents) - 1;
            $contents[$lastIndex]['parts'][0]['text'] .= $instruction;

            // ၅။ API သို့ Request ပို့ခြင်း
            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->post($url, [
                    'contents' => $contents
                ]);

            // ၆။ Response ကို စစ်ဆေးခြင်း
            if ($response->successful()) {
                $data = $response->json();
                $reply = $data['candidates'][0]['content']['parts'][0]['text'] ?? 'The vision is clouded... (No Reply)';
                
                // Gemini အဖြေကို Chat ထဲထည့်ခြင်း
                $this->messages[] = ['role' => 'model', 'text' => $reply];
            } else {
                // Error တက်ခဲ့ရင် (ဥပမာ Model name မှားနေရင်)
                $this->messages[] = ['role' => 'model', 'text' => "API Error: " . $response->body()];
            }

        } catch (\Exception $e) {
            Log::error($e);
            $this->messages[] = ['role' => 'model', 'text' => 'Connection Error occurred.'];
        } finally {
            $this->isLoading = false;
            $this->dispatch('scroll-to-bottom'); 
        }
    }

    public function render()
    {
        return view('livewire.oracle');
    }
}