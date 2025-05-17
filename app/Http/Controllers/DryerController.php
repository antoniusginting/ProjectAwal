<?php

namespace App\Http\Controllers;

use App\Models\Dryer;
use App\Models\DryerRecord;
use Illuminate\Http\Request;
use Filament\Notifications\Notification;

class DryerController extends Controller
{
    public function printDryerRecord($id)
    {
        $dryer = Dryer::find($id);
        if ($dryer) {
            DryerRecord::create([
                "user_id" => auth()->user()->id,
                'dryer_id' => $id
            ]);

            $pdf = \PDF::loadView('pdf.dryer',compact('dryer'));
            return $pdf->stream();
        }else{
            Notification::make()
            ->title("No No")
            ->danger()
            ->send();
            return redirect()->back();
        }
    }
}
