<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use Illuminate\Http\Request;
use App\Models\PenjualanRecord;
use Filament\Notifications\Notification;

class PenjualanController extends Controller
{
    public function printPenjualanRecord($id)
    {
        $penjualan = Penjualan::find($id);
        if ($penjualan) {
            PenjualanRecord::create([
                "user_id" => auth()->user()->id,
                'penjualan_id' => $id
            ]);

            $pdf = \PDF::loadView('pdf.penjualan',compact('penjualan'));
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
