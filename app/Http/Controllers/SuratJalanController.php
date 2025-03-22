<?php

namespace App\Http\Controllers;

use PDF;
use App\Models\SuratJalan;
use Illuminate\Http\Request;
use App\Models\SuratJalanRecord;
use Filament\Notifications\Notification;

class SuratJalanController extends Controller
{
    public function printSuratJalanRecord($id)
    {
        $suratjalan = SuratJalan::with(['tronton','kontrak2','kontrak','tronton.penjualan1','alamat.kontrak'])->find($id);
        if($suratjalan)
        {
            SuratJalanRecord::create([
                "user_id" => auth()->user()->id,
                'suratjalan_id' => $id
            ]);
            $pdf = \PDF::loadView('pdf.suratjalan',compact('suratjalan'));
            return $pdf->stream();
        }else
        {
            Notification::make()
            ->title("No No")
            ->danger()
            ->send();
            return redirect()->back();
        }
    }
}
