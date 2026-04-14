<?php

namespace App\Http\Responses;

use Filament\Http\Responses\Auth\Contracts\LoginResponse as Responsable;
use Illuminate\Support\Facades\Auth;

class LoginResponse implements Responsable
{
    public function toResponse($request)
    {
        // Mendapatkan ID panel yang sedang digunakan untuk login
        $panelId = filament()->getCurrentPanel()->getId();

        // Arahkan ke URL utama dari panel tersebut
        return redirect()->to(filament()->getPanel($panelId)->getUrl());
    }
}
