<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LaporanLumbung;
use App\Models\LaporanLumbungRecord;
use Filament\Notifications\Notification;

class LaporanLumbungController extends Controller
{
    public function printLaporanLumbungRecord($id)
    {
        $laporanlumbung = LaporanLumbung::with(['dryers'])->find($id);
        if ($laporanlumbung) {
            LaporanLumbungRecord::create([
                "user_id" => auth()->user()->id,
                'laporan_lumbung_id' => $id
            ]);
            $pdf = \PDF::loadView('pdf.laporanlumbung', compact('laporanlumbung'));
            return $pdf->stream();
        } else {
            Notification::make()
                ->title("No No")
                ->danger()
                ->send();
            return redirect()->back();
        }
    }
}
