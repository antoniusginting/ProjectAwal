<?php

namespace App\Http\Controllers;

use App\Models\Sortiran;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Http\Request;
use App\Models\SortiranRecord;
use Filament\Notifications\Notification;

class SortiranController extends Controller
{
    public function printSortiranRecord($id)
    {
        $sortiran = Sortiran::find($id);
        if ($sortiran) {
            SortiranRecord::create([
                "user_id" => auth()->user()->id,
                'sortiran_id' => $id
            ]);

            $pdf = \PDF::loadView('pdf.sortiran',compact('sortiran'));
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
