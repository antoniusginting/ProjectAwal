<?php

namespace App\Http\Controllers;

use App\Models\TimbanganTronton;
use App\Models\TimbanganTrontonRecord;
use Illuminate\Http\Request;

class LaporanPenjualanController extends Controller
{
    public function printLaporanPenjualanRecord($id)
    {
        $laporanpenjualan = TimbanganTronton::find($id);
        if ($laporanpenjualan) {
            TimbanganTrontonRecord::create([
                "user_id" => auth()->user()->id,
                'timbangan_id' => $id
            ]);

            $pdf = \PDF::loadView('pdf.laporanpenjualan',compact('laporanpenjualan'));
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
