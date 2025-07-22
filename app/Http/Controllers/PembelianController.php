<?php

namespace App\Http\Controllers;

use PDF;
use App\Models\Pembelian;
use Illuminate\Http\Request;
use App\Models\PembelianRecord;
use Filament\Notifications\Notification;

class PembelianController extends Controller
{
    public function printPembelianRecord($id)
    {
        $pembelian = Pembelian::find($id);
        if ($pembelian) {
            PembelianRecord::create([
                "user_id" => auth()->user()->id,
                'pembelian_id' => $id
            ]);

            $pdf = PDF::loadView('pdf.pembelian', compact('pembelian'));
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
