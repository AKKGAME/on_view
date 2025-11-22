<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    // Login form မှာ Phone field ပြောင်းမယ်
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('phone')
                    ->label('Phone Number')
                    ->tel()
                    ->required()
                    ->autocomplete(),
                $this->getPasswordFormComponent(),
                $this->getRememberFormComponent(),
            ])
            ->statePath('data');
    }

    // Credentials စစ်တဲ့အခါ phone နဲ့ စစ်မယ်
    protected function getCredentialsFromFormData(array $data): array
    {
        return [
            'phone' => $data['phone'],
            'password' => $data['password'],
        ];
    }
}