<?php
/**
 * View : Export Excel - Laporan Stok
 * --------------------------------------------------------------
 * Letak file : app/views/laporan/excel.php
 * Dipanggil  : Laporan::exportExcel() SAJA (tanpa layout/header, tanpa layout/footer,
 *              tanpa sidebar/navbar) -- file ini menjadi seluruh isi dokumen yang diunduh.
 *
 * Variabel dari controller:
 *   $data['user']           -> data session admin yang mengekspor (untuk sub-header)
 *   $data['waktu_export']   -> waktu file dibuat (d/m/Y H:i)
 *   $data['filter']         -> (tidak dipakai langsung; sudah diringkas jadi label_periode/label_kategori)
 *   $data['label_periode']  -> teks periode, mis. "01/09/2026 s.d. 20/09/2026" atau "semua tanggal"
 *   $data['label_kategori'] -> nama kategori terpilih atau "Semua kategori"
 *   $data['stok']           -> daftar barang: kode_barang, nama_barang, nama_kategori, satuan, stok, stok_minimum
 *   $data['total_masuk']    -> total kuantitas barang masuk pada rentang tanggal
 *   $data['total_keluar']   -> total kuantitas barang keluar pada rentang tanggal
 *   $data['stok_kritis']    -> jumlah jenis barang dengan stok <= stok minimum
 *
 * File ini SENGAJA berupa tabel HTML polos ber-<style> inline (bukan halaman Bootstrap):
 * Excel membaca file .xls dari controller sebagai HTML, dan hanya mengenali gaya
 * yang ditulis inline atau lewat elemen <style>, bukan class Bootstrap/CDN eksternal.
 */

/** @var array $data */

$e = function ($nilai) {
    return htmlspecialchars((string) $nilai, ENT_QUOTES, 'UTF-8');
};

// Format angka Indonesia: 1234 -> 1.234 (tanpa pemisah desimal, karena semua kolom di sini bilangan bulat)
$angka = function ($nilai) {
    return number_format((int) $nilai, 0, ',', '.');
};

$steel = '#1F4E5F';
$amber = '#F2A93B';
$hijau = '#1E7E34';
$merah = '#B42318';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Stok Gudang</title>
    <!--[if gte mso 9]>
    <xml>
        <x:ExcelWorkbook>
            <x:ExcelWorksheets>
                <x:ExcelWorksheet>
                    <x:Name>Laporan Stok</x:Name>
                    <x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions>
                </x:ExcelWorksheet>
            </x:ExcelWorksheets>
        </x:ExcelWorkbook>
    </xml>
    <![endif]-->
    <style>
        table { border-collapse: collapse; font-family: Calibri, Arial, sans-serif; font-size: 12px; }
        td, th { border: 1px solid #B9C2C7; padding: 4px 8px; }

        .judul { font-size: 16px; font-weight: bold; color: <?= $steel; ?>; }
        .sub { font-size: 12px; color: #5B6B75; }

        .kartu-label { font-size: 11px; color: #5B6B75; }
        .kartu-angka { font-size: 15px; font-weight: bold; color: <?= $steel; ?>; }

        .th-tabel {
            background: <?= $steel; ?>;
            color: #FFFFFF;
            font-weight: bold;
            text-align: center;
        }

        .angka { text-align: right; mso-number-format: "#,##0"; }
        .tengah { text-align: center; }

        .status-aman { background: #D6F0DC; color: <?= $hijau; ?>; font-weight: bold; text-align: center; }
        .status-kritis { background: #FBE0DD; color: <?= $merah; ?>; font-weight: bold; text-align: center; }
    </style>
</head>

<body>
    <table>

        <!-- ===================== JUDUL ===================== -->
        <tr>
            <td colspan="7" class="judul">LAPORAN STOK &amp; INVENTORY GUDANG - A.K.M.A.L INVENTORY</td>
        </tr>

        <!-- ===================== SUB-HEADER ===================== -->
        <tr>
            <td colspan="7" class="sub">Periode transaksi: <?= $e($data['label_periode']); ?></td>
        </tr>
        <tr>
            <td colspan="7" class="sub">Kategori: <?= $e($data['label_kategori']); ?></td>
        </tr>
        <tr>
            <td colspan="7" class="sub">Diekspor oleh: <?= $e($data['user']['nama']); ?> pada <?= $e($data['waktu_export']); ?></td>
        </tr>

        <!-- baris kosong sebagai pemisah -->
        <tr><td colspan="7" style="border: none;">&nbsp;</td></tr>

        <!-- ===================== 3 ANGKA RINGKASAN ===================== -->
        <tr>
            <td colspan="2" class="kartu-label">Total Barang Masuk (unit)</td>
            <td colspan="2" class="kartu-label">Total Barang Keluar (unit)</td>
            <td colspan="3" class="kartu-label">Jumlah Barang Stok Kritis</td>
        </tr>
        <tr>
            <td colspan="2" class="kartu-angka"><?= $angka($data['total_masuk']); ?></td>
            <td colspan="2" class="kartu-angka"><?= $angka($data['total_keluar']); ?></td>
            <td colspan="3" class="kartu-angka"><?= $angka($data['stok_kritis']); ?></td>
        </tr>

        <!-- baris kosong sebagai pemisah -->
        <tr><td colspan="7" style="border: none;">&nbsp;</td></tr>

        <!-- ===================== TABEL UTAMA ===================== -->
        <tr>
            <th class="th-tabel">No</th>
            <th class="th-tabel">Kode Barang</th>
            <th class="th-tabel">Nama Barang</th>
            <th class="th-tabel">Kategori</th>
            <th class="th-tabel">Stok Saat Ini</th>
            <th class="th-tabel">Stok Minimum</th>
            <th class="th-tabel">Status</th>
        </tr>

        <?php if (empty($data['stok'])) : ?>
            <tr>
                <td colspan="7" class="tengah">Tidak ada barang pada kategori ini.</td>
            </tr>
        <?php else : ?>
            <?php $no = 1; ?>
            <?php foreach ($data['stok'] as $b) : ?>
                <?php $kritis = ((int) $b['stok'] <= (int) $b['stok_minimum']); ?>
                <tr>
                    <td class="tengah"><?= $no++; ?></td>
                    <td><?= $e($b['kode_barang']); ?></td>
                    <td><?= $e($b['nama_barang']); ?></td>
                    <td><?= $e($b['nama_kategori']); ?></td>
                    <td class="angka"><?= $angka($b['stok']); ?> <?= $e($b['satuan']); ?></td>
                    <td class="angka"><?= $angka($b['stok_minimum']); ?></td>
                    <td class="<?= $kritis ? 'status-kritis' : 'status-aman'; ?>">
                        <?= $kritis ? 'Kritis' : 'Aman'; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>

    </table>
</body>

</html>
