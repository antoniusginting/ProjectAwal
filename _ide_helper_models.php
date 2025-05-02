<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $id_kontrak
 * @property string $alamat
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Kontrak $kontrak
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AlamatKontrak newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AlamatKontrak newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AlamatKontrak query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AlamatKontrak whereAlamat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AlamatKontrak whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AlamatKontrak whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AlamatKontrak whereIdKontrak($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AlamatKontrak whereUpdatedAt($value)
 */
	class AlamatKontrak extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string|null $jenis
 * @property string|null $no_dryer
 * @property int $id_kapasitas_dryer
 * @property int $id_lumbung_1
 * @property int|null $id_lumbung_2
 * @property int|null $id_lumbung_3
 * @property int|null $id_lumbung_4
 * @property string $operator
 * @property string $jenis_jagung
 * @property string $lumbung_tujuan
 * @property float $rencana_kadar
 * @property float|null $hasil_kadar
 * @property int $total_netto
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\KapasitasDryer $kapasitasdryer
 * @property-read \App\Models\LumbungBasah $lumbung1
 * @property-read \App\Models\LumbungBasah|null $lumbung2
 * @property-read \App\Models\LumbungBasah|null $lumbung3
 * @property-read \App\Models\LumbungBasah|null $lumbung4
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dryer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dryer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dryer query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dryer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dryer whereHasilKadar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dryer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dryer whereIdKapasitasDryer($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dryer whereIdLumbung1($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dryer whereIdLumbung2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dryer whereIdLumbung3($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dryer whereIdLumbung4($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dryer whereJenis($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dryer whereJenisJagung($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dryer whereLumbungTujuan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dryer whereNoDryer($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dryer whereOperator($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dryer whereRencanaKadar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dryer whereTotalNetto($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dryer whereUpdatedAt($value)
 */
	class Dryer extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $nama_kapasitas_dryer
 * @property int $kapasitas_total
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KapasitasDryer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KapasitasDryer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KapasitasDryer query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KapasitasDryer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KapasitasDryer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KapasitasDryer whereKapasitasTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KapasitasDryer whereNamaKapasitasDryer($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KapasitasDryer whereUpdatedAt($value)
 */
	class KapasitasDryer extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $no_kapasitas_lumbung
 * @property int $kapasitas_total
 * @property int $kapasitas_sisa
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KapasitasLumbungBasah newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KapasitasLumbungBasah newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KapasitasLumbungBasah query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KapasitasLumbungBasah whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KapasitasLumbungBasah whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KapasitasLumbungBasah whereKapasitasSisa($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KapasitasLumbungBasah whereKapasitasTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KapasitasLumbungBasah whereNoKapasitasLumbung($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KapasitasLumbungBasah whereUpdatedAt($value)
 */
	class KapasitasLumbungBasah extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $nama_kapasitas_lumbung
 * @property int $kapasitas_total
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KapasitasLumbungKering newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KapasitasLumbungKering newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KapasitasLumbungKering query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KapasitasLumbungKering whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KapasitasLumbungKering whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KapasitasLumbungKering whereKapasitasTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KapasitasLumbungKering whereNamaKapasitasLumbung($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KapasitasLumbungKering whereUpdatedAt($value)
 */
	class KapasitasLumbungKering extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $plat_polisi_terbaru
 * @property string|null $plat_polisi_sebelumnya
 * @property string|null $pemilik
 * @property string|null $nama_supir
 * @property string|null $nama_kernek
 * @property string|null $jenis_mobil
 * @property string|null $status_sp
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kendaraan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kendaraan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kendaraan query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kendaraan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kendaraan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kendaraan whereJenisMobil($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kendaraan whereNamaKernek($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kendaraan whereNamaSupir($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kendaraan wherePemilik($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kendaraan wherePlatPolisiSebelumnya($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kendaraan wherePlatPolisiTerbaru($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kendaraan whereStatusSp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kendaraan whereUpdatedAt($value)
 */
	class Kendaraan extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $status
 * @property string $nama_sup_per
 * @property string|null $plat_polisi
 * @property string|null $nama_barang
 * @property string|null $keterangan
 * @property string|null $jam_masuk
 * @property string|null $jam_keluar
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $user_id
 * @property int|null $nomor_antrian
 * @property int|null $status_selesai
 * @property int|null $status_awal
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KendaraanMasuks newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KendaraanMasuks newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KendaraanMasuks query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KendaraanMasuks whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KendaraanMasuks whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KendaraanMasuks whereJamKeluar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KendaraanMasuks whereJamMasuk($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KendaraanMasuks whereKeterangan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KendaraanMasuks whereNamaBarang($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KendaraanMasuks whereNamaSupPer($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KendaraanMasuks whereNomorAntrian($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KendaraanMasuks wherePlatPolisi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KendaraanMasuks whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KendaraanMasuks whereStatusAwal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KendaraanMasuks whereStatusSelesai($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KendaraanMasuks whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KendaraanMasuks whereUserId($value)
 */
	class KendaraanMasuks extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $nama_supir
 * @property int $kendaraan_id
 * @property int $tonase
 * @property string $tujuan
 * @property string|null $jam_masuk
 * @property string|null $jam_keluar
 * @property int|null $status
 * @property int|null $status_awal
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $user_id
 * @property array<array-key, mixed>|null $foto
 * @property string|null $keterangan
 * @property-read \App\Models\Kendaraan $kendaraan
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KendaraanMuat newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KendaraanMuat newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KendaraanMuat query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KendaraanMuat whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KendaraanMuat whereFoto($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KendaraanMuat whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KendaraanMuat whereJamKeluar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KendaraanMuat whereJamMasuk($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KendaraanMuat whereKendaraanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KendaraanMuat whereKeterangan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KendaraanMuat whereNamaSupir($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KendaraanMuat whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KendaraanMuat whereStatusAwal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KendaraanMuat whereTonase($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KendaraanMuat whereTujuan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KendaraanMuat whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KendaraanMuat whereUserId($value)
 */
	class KendaraanMuat extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $nama
 * @property string|null $npwp
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kontrak newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kontrak newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kontrak query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kontrak whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kontrak whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kontrak whereNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kontrak whereNpwp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kontrak whereUpdatedAt($value)
 */
	class Kontrak extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string|null $jenis
 * @property string|null $no_lp
 * @property string $operator_timbangan
 * @property string $jenis_jagung
 * @property int $jumlah_goni
 * @property int|null $id_lumbung_kering_1
 * @property int|null $berat_1
 * @property int|null $id_lumbung_kering_2
 * @property int|null $berat_2
 * @property int|null $id_lumbung_kering_3
 * @property int|null $berat_3
 * @property int|null $id_lumbung_kering_4
 * @property int|null $berat_4
 * @property int|null $id_lumbung_kering_5
 * @property int|null $berat_5
 * @property int|null $id_lumbung_kering_6
 * @property int|null $berat_6
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaporanPenjualan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaporanPenjualan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaporanPenjualan query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaporanPenjualan whereBerat1($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaporanPenjualan whereBerat2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaporanPenjualan whereBerat3($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaporanPenjualan whereBerat4($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaporanPenjualan whereBerat5($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaporanPenjualan whereBerat6($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaporanPenjualan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaporanPenjualan whereIdLumbungKering1($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaporanPenjualan whereIdLumbungKering2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaporanPenjualan whereIdLumbungKering3($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaporanPenjualan whereIdLumbungKering4($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaporanPenjualan whereIdLumbungKering5($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaporanPenjualan whereIdLumbungKering6($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaporanPenjualan whereJenis($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaporanPenjualan whereJenisJagung($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaporanPenjualan whereJumlahGoni($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaporanPenjualan whereNoLp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaporanPenjualan whereOperatorTimbangan($value)
 */
	class LaporanPenjualan extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string|null $jenis
 * @property string|null $no_lb
 * @property int|null $no_lumbung_basah
 * @property string|null $jenis_jagung
 * @property int|null $id_sortiran_1
 * @property int|null $id_sortiran_2
 * @property int|null $id_sortiran_3
 * @property int|null $id_sortiran_4
 * @property int|null $id_sortiran_5
 * @property int $total_netto
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\KapasitasLumbungBasah|null $kapasitaslumbungbasah
 * @property-read \App\Models\Sortiran|null $sortiran1
 * @property-read \App\Models\Sortiran|null $sortiran2
 * @property-read \App\Models\Sortiran|null $sortiran3
 * @property-read \App\Models\Sortiran|null $sortiran4
 * @property-read \App\Models\Sortiran|null $sortiran5
 * @property-read \App\Models\Sortiran|null $sortiran6
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LumbungBasah newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LumbungBasah newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LumbungBasah query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LumbungBasah whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LumbungBasah whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LumbungBasah whereIdSortiran1($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LumbungBasah whereIdSortiran2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LumbungBasah whereIdSortiran3($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LumbungBasah whereIdSortiran4($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LumbungBasah whereIdSortiran5($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LumbungBasah whereJenis($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LumbungBasah whereJenisJagung($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LumbungBasah whereNoLb($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LumbungBasah whereNoLumbungBasah($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LumbungBasah whereTotalNetto($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LumbungBasah whereUpdatedAt($value)
 */
	class LumbungBasah extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string|null $jenis
 * @property string|null $no_llk
 * @property string $jenis_jagung
 * @property int $id_kapasitas_lumbung_kering
 * @property int $id_dryer
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $id_laporan_penjualan_1
 * @property int|null $berat_1
 * @property int|null $id_laporan_penjualan_2
 * @property int|null $berat_2
 * @property int|null $id_laporan_penjualan_3
 * @property int|null $berat_3
 * @property int|null $id_laporan_penjualan_4
 * @property int|null $berat_4
 * @property int|null $id_laporan_penjualan_5
 * @property int|null $berat_5
 * @property int|null $id_laporan_penjualan_6
 * @property int|null $berat_6
 * @property int|null $id_laporan_penjualan_7
 * @property int|null $berat_7
 * @property int|null $id_laporan_penjualan_8
 * @property int|null $berat_8
 * @property int|null $id_laporan_penjualan_9
 * @property int|null $berat_9
 * @property int|null $id_laporan_penjualan_10
 * @property int|null $berat_10
 * @property int $total_berat
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LumbungKering newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LumbungKering newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LumbungKering query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LumbungKering whereBerat1($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LumbungKering whereBerat10($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LumbungKering whereBerat2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LumbungKering whereBerat3($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LumbungKering whereBerat4($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LumbungKering whereBerat5($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LumbungKering whereBerat6($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LumbungKering whereBerat7($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LumbungKering whereBerat8($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LumbungKering whereBerat9($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LumbungKering whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LumbungKering whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LumbungKering whereIdDryer($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LumbungKering whereIdKapasitasLumbungKering($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LumbungKering whereIdLaporanPenjualan1($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LumbungKering whereIdLaporanPenjualan10($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LumbungKering whereIdLaporanPenjualan2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LumbungKering whereIdLaporanPenjualan3($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LumbungKering whereIdLaporanPenjualan4($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LumbungKering whereIdLaporanPenjualan5($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LumbungKering whereIdLaporanPenjualan6($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LumbungKering whereIdLaporanPenjualan7($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LumbungKering whereIdLaporanPenjualan8($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LumbungKering whereIdLaporanPenjualan9($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LumbungKering whereJenis($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LumbungKering whereJenisJagung($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LumbungKering whereNoLlk($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LumbungKering whereTotalBerat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LumbungKering whereUpdatedAt($value)
 */
	class LumbungKering extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $plat_polisi
 * @property string $jenis_mobil
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mobil newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mobil newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mobil query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mobil whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mobil whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mobil whereJenisMobil($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mobil wherePlatPolisi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mobil whereUpdatedAt($value)
 */
	class Mobil extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string|null $jenis
 * @property string|null $nama_supir
 * @property string|null $no_spb
 * @property string $nama_barang
 * @property string|null $no_container
 * @property string|null $brondolan
 * @property string|null $plat_polisi
 * @property int $bruto
 * @property int|null $tara
 * @property int|null $netto
 * @property string|null $keterangan
 * @property string|null $no_po
 * @property string $jam_masuk
 * @property string|null $jam_keluar
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $id_supplier
 * @property int|null $id_mobil
 * @property int $user_id
 * @property int|null $jumlah_karung
 * @property-read \App\Models\Mobil|null $mobil
 * @property-read \App\Models\Supplier|null $supplier
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pembelian newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pembelian newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pembelian query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pembelian whereBrondolan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pembelian whereBruto($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pembelian whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pembelian whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pembelian whereIdMobil($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pembelian whereIdSupplier($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pembelian whereJamKeluar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pembelian whereJamMasuk($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pembelian whereJenis($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pembelian whereJumlahKarung($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pembelian whereKeterangan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pembelian whereNamaBarang($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pembelian whereNamaSupir($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pembelian whereNetto($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pembelian whereNoContainer($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pembelian whereNoPo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pembelian whereNoSpb($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pembelian wherePlatPolisi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pembelian whereTara($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pembelian whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pembelian whereUserId($value)
 */
	class Pembelian extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property \App\Models\Pembelian|null $pembelian_id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PembelianRecord newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PembelianRecord newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PembelianRecord query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PembelianRecord whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PembelianRecord whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PembelianRecord wherePembelianId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PembelianRecord whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PembelianRecord whereUserId($value)
 */
	class PembelianRecord extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string|null $jenis
 * @property string|null $nama_supir
 * @property string|null $no_spb
 * @property string $nama_barang
 * @property string|null $plat_polisi
 * @property string|null $brondolan
 * @property string|null $bruto
 * @property string|null $tara
 * @property string|null $netto
 * @property string|null $keterangan
 * @property string|null $jam_masuk
 * @property string|null $jam_keluar
 * @property string|null $no_lumbung
 * @property string|null $nama_lumbung
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $id_supplier
 * @property int|null $id_mobil
 * @property string|null $no_container
 * @property int $user_id
 * @property int|null $jumlah_karung
 * @property-read \App\Models\Mobil|null $mobil
 * @property-read \App\Models\Supplier|null $supplier
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Penjualan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Penjualan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Penjualan query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Penjualan whereBrondolan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Penjualan whereBruto($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Penjualan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Penjualan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Penjualan whereIdMobil($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Penjualan whereIdSupplier($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Penjualan whereJamKeluar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Penjualan whereJamMasuk($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Penjualan whereJenis($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Penjualan whereJumlahKarung($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Penjualan whereKeterangan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Penjualan whereNamaBarang($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Penjualan whereNamaLumbung($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Penjualan whereNamaSupir($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Penjualan whereNetto($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Penjualan whereNoContainer($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Penjualan whereNoLumbung($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Penjualan whereNoSpb($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Penjualan wherePlatPolisi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Penjualan whereTara($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Penjualan whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Penjualan whereUserId($value)
 */
	class Penjualan extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property \App\Models\Penjualan|null $penjualan_id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PenjualanRecord newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PenjualanRecord newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PenjualanRecord query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PenjualanRecord whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PenjualanRecord whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PenjualanRecord wherePenjualanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PenjualanRecord whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PenjualanRecord whereUserId($value)
 */
	class PenjualanRecord extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string|null $jenis
 * @property string|null $no_sortiran
 * @property int $id_pembelian
 * @property string|null $no_lumbung
 * @property int|null $total_karung
 * @property int|null $berat_tungkul
 * @property string|null $netto_bersih
 * @property string $kualitas_jagung_1
 * @property string|null $x1_x10_1
 * @property string|null $jumlah_karung_1
 * @property string|null $tonase_1
 * @property string|null $kualitas_jagung_2
 * @property string|null $x1_x10_2
 * @property string|null $jumlah_karung_2
 * @property string|null $tonase_2
 * @property string|null $kualitas_jagung_3
 * @property string|null $x1_x10_3
 * @property string|null $jumlah_karung_3
 * @property string|null $tonase_3
 * @property string|null $kualitas_jagung_4
 * @property string|null $x1_x10_4
 * @property string|null $jumlah_karung_4
 * @property string|null $tonase_4
 * @property string|null $kualitas_jagung_5
 * @property string|null $x1_x10_5
 * @property string|null $jumlah_karung_5
 * @property string|null $tonase_5
 * @property string|null $kualitas_jagung_6
 * @property string|null $x1_x10_6
 * @property string|null $jumlah_karung_6
 * @property string|null $tonase_6
 * @property array<array-key, mixed>|null $foto_jagung_1
 * @property array<array-key, mixed>|null $foto_jagung_2
 * @property array<array-key, mixed>|null $foto_jagung_3
 * @property array<array-key, mixed>|null $foto_jagung_4
 * @property array<array-key, mixed>|null $foto_jagung_5
 * @property array<array-key, mixed>|null $foto_jagung_6
 * @property float $kadar_air
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $user_id
 * @property int|null $status
 * @property-read \App\Models\Pembelian $pembelian
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sortiran newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sortiran newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sortiran query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sortiran whereBeratTungkul($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sortiran whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sortiran whereFotoJagung1($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sortiran whereFotoJagung2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sortiran whereFotoJagung3($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sortiran whereFotoJagung4($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sortiran whereFotoJagung5($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sortiran whereFotoJagung6($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sortiran whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sortiran whereIdPembelian($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sortiran whereJenis($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sortiran whereJumlahKarung1($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sortiran whereJumlahKarung2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sortiran whereJumlahKarung3($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sortiran whereJumlahKarung4($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sortiran whereJumlahKarung5($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sortiran whereJumlahKarung6($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sortiran whereKadarAir($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sortiran whereKualitasJagung1($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sortiran whereKualitasJagung2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sortiran whereKualitasJagung3($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sortiran whereKualitasJagung4($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sortiran whereKualitasJagung5($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sortiran whereKualitasJagung6($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sortiran whereNettoBersih($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sortiran whereNoLumbung($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sortiran whereNoSortiran($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sortiran whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sortiran whereTonase1($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sortiran whereTonase2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sortiran whereTonase3($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sortiran whereTonase4($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sortiran whereTonase5($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sortiran whereTonase6($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sortiran whereTotalKarung($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sortiran whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sortiran whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sortiran whereX1X101($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sortiran whereX1X102($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sortiran whereX1X103($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sortiran whereX1X104($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sortiran whereX1X105($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sortiran whereX1X106($value)
 */
	class Sortiran extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property \App\Models\Sortiran|null $sortiran_id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SortiranRecord newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SortiranRecord newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SortiranRecord query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SortiranRecord whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SortiranRecord whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SortiranRecord whereSortiranId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SortiranRecord whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SortiranRecord whereUserId($value)
 */
	class SortiranRecord extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $nama_supplier
 * @property string|null $jenis_supplier
 * @property string|null $no_ktp
 * @property string|null $npwp
 * @property string|null $no_rek
 * @property string|null $nama_bank
 * @property string|null $atas_nama_bank
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereAtasNamaBank($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereJenisSupplier($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereNamaBank($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereNamaSupplier($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereNoKtp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereNoRek($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereNpwp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereUpdatedAt($value)
 */
	class Supplier extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $id_kontrak
 * @property int $id_kontrak2
 * @property int|null $id_alamat
 * @property int $id_timbangan_tronton
 * @property string $kota
 * @property string|null $po
 * @property int|null $tambah_berat
 * @property int|null $bruto_final
 * @property int|null $netto_final
 * @property string|null $satuan_muatan
 * @property string|null $jenis_mobil
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $user_id
 * @property-read \App\Models\AlamatKontrak|null $alamat
 * @property-read \App\Models\Kontrak $kontrak
 * @property-read \App\Models\Kontrak $kontrak2
 * @property-read \App\Models\TimbanganTronton $timbanganTronton
 * @property-read \App\Models\TimbanganTronton $tronton
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SuratJalan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SuratJalan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SuratJalan query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SuratJalan whereBrutoFinal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SuratJalan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SuratJalan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SuratJalan whereIdAlamat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SuratJalan whereIdKontrak($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SuratJalan whereIdKontrak2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SuratJalan whereIdTimbanganTronton($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SuratJalan whereJenisMobil($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SuratJalan whereKota($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SuratJalan whereNettoFinal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SuratJalan wherePo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SuratJalan whereSatuanMuatan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SuratJalan whereTambahBerat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SuratJalan whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SuratJalan whereUserId($value)
 */
	class SuratJalan extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property \App\Models\SuratJalan|null $suratjalan_id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SuratJalanRecord newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SuratJalanRecord newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SuratJalanRecord query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SuratJalanRecord whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SuratJalanRecord whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SuratJalanRecord whereSuratjalanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SuratJalanRecord whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SuratJalanRecord whereUserId($value)
 */
	class SuratJalanRecord extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $jenis
 * @property string|null $kode
 * @property int $id_timbangan_jual_1
 * @property int|null $id_timbangan_jual_2
 * @property int|null $id_timbangan_jual_3
 * @property int|null $id_timbangan_jual_4
 * @property int|null $id_timbangan_jual_5
 * @property int|null $id_timbangan_jual_6
 * @property int|null $bruto_akhir
 * @property int|null $total_netto
 * @property int|null $tara_awal
 * @property int|null $tambah_berat
 * @property int|null $bruto_final
 * @property int|null $netto_final
 * @property string|null $keterangan
 * @property string|null $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $user_id
 * @property-read \App\Models\Penjualan $penjualan1
 * @property-read \App\Models\Penjualan|null $penjualan2
 * @property-read \App\Models\Penjualan|null $penjualan3
 * @property-read \App\Models\Penjualan|null $penjualan4
 * @property-read \App\Models\Penjualan|null $penjualan5
 * @property-read \App\Models\Penjualan|null $penjualan6
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SuratJalan> $suratJalans
 * @property-read int|null $surat_jalans_count
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimbanganTronton newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimbanganTronton newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimbanganTronton query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimbanganTronton whereBrutoAkhir($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimbanganTronton whereBrutoFinal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimbanganTronton whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimbanganTronton whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimbanganTronton whereIdTimbanganJual1($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimbanganTronton whereIdTimbanganJual2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimbanganTronton whereIdTimbanganJual3($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimbanganTronton whereIdTimbanganJual4($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimbanganTronton whereIdTimbanganJual5($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimbanganTronton whereIdTimbanganJual6($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimbanganTronton whereJenis($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimbanganTronton whereKeterangan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimbanganTronton whereKode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimbanganTronton whereNettoFinal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimbanganTronton whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimbanganTronton whereTambahBerat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimbanganTronton whereTaraAwal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimbanganTronton whereTotalNetto($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimbanganTronton whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimbanganTronton whereUserId($value)
 */
	class TimbanganTronton extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property \App\Models\TimbanganTronton|null $timbangan_id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimbanganTrontonRecord newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimbanganTrontonRecord newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimbanganTrontonRecord query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimbanganTrontonRecord whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimbanganTrontonRecord whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimbanganTrontonRecord whereTimbanganId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimbanganTrontonRecord whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimbanganTrontonRecord whereUserId($value)
 */
	class TimbanganTrontonRecord extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read int|null $roles_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User permission($permissions, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User role($roles, $guard = null, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutPermission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutRole($roles, $guard = null)
 */
	class User extends \Eloquent {}
}

