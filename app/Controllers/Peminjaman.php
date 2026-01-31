<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PeminjamanModel;
use App\Models\BarangModel;
use App\Models\KasSubModel;
use App\Models\CoaModel;

class Peminjaman extends BaseController
{
    protected $peminjamanModel;
    protected $barangModel;
    protected $kasSubModel;
    protected $coaModel;

    public function __construct()
    {
        $this->peminjamanModel = new PeminjamanModel();
        $this->barangModel = new BarangModel();
        $this->kasSubModel = new KasSubModel();
        $this->coaModel = new CoaModel();
    }

    public function index()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        // Join to get barang name
        $peminjaman = $this->peminjamanModel
            ->select('tb_peminjaman.*, tb_barang.nama as nama_barang, tb_barang.kode_brg')
            ->join('tb_barang', 'tb_barang.kode = tb_peminjaman.barang_id')
            ->orderBy('id', 'DESC')
            ->findAll();
        
        // Get all barang for dropdown
        $barangList = $this->barangModel->orderBy('nama', 'ASC')->findAll();

        $data = [
            'title' => 'Peminjaman Barang',
            'peminjaman' => $peminjaman,
            'barangList' => $barangList
        ];

        return view('peminjaman/index', $data);
    }

    public function store()
    {
        $role = session()->get('role');
        $allowed = ['s_admin', 'admin', 'pengurus'];
        if (!in_array($role, $allowed)) {
             return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $barangId = $this->request->getPost('barang_id');
        $jumlah = (int) $this->request->getPost('jumlah');

        // Check Stock First
        $barang = $this->barangModel->find($barangId);
        if (!$barang) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Barang tidak ditemukan']);
        }

        if ($barang['jumlah'] < $jumlah) {
             return $this->response->setJSON(['status' => 'error', 'message' => 'Stok tidak mencukupi']);
        }

        $data = [
            'barang_id'     => $barangId,
            'nama_peminjam' => $this->request->getPost('nama_peminjam'),
            'jumlah'        => $jumlah,
            'tanggal_pinjam'=> date('Y-m-d'),
            'status'        => 'dipinjam',
            'keterangan'    => $this->request->getPost('keterangan')
        ];

        if ($this->peminjamanModel->save($data)) {
            // Reduce Stock
            $newStock = $barang['jumlah'] - $jumlah;
            $this->barangModel->update($barangId, ['jumlah' => $newStock]);

            log_activity('PINJAM_BARANG', "Peminjaman {$barang['nama']} oleh {$data['nama_peminjam']} ({$jumlah} unit)");
            return $this->response->setJSON(['status' => 'success', 'message' => 'Peminjaman berhasil dicatat']);
        }

        return $this->response->setJSON([
            'status' => 'error', 
            'message' => 'Gagal menyimpan data',
            'errors' => $this->peminjamanModel->errors()
        ]);
    }

    public function returnItem()
    {
        $role = session()->get('role');
        $allowed = ['s_admin', 'admin', 'pengurus'];
        if (!in_array($role, $allowed)) {
             return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $id = $this->request->getPost('id');
        $input_kembali = (int) $this->request->getPost('jumlah_kembali');
        $keterangan = $this->request->getPost('keterangan');
        $is_ganti_rugi = $this->request->getPost('ganti_rugi') === 'on'; // Checkbox
        $nominal_ganti = (int) str_replace(['.', ','], '', $this->request->getPost('nominal_uang'));

        $peminjaman = $this->peminjamanModel->find($id);

        if (!$peminjaman || $peminjaman['status'] === 'kembali') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Data tidak valid atau seluruh barang sudah dikembalikan']);
        }

        // Calculate Totals
        $current_total_returned = (int) $peminjaman['jumlah_kembali'];
        $total_borrowed = (int) $peminjaman['jumlah'];
        $remaining = $total_borrowed - $current_total_returned;

        // Validation
        if ($input_kembali < 0) {
             return $this->response->setJSON(['status' => 'error', 'message' => 'Jumlah kembali tidak boleh minus']);
        }
        if ($input_kembali > $remaining) {
            return $this->response->setJSON(['status' => 'error', 'message' => "Jumlah melebihi sisa peminjaman (Sisa: $remaining)"]);
        }

        // Logic split: Normal Return vs Compensation Check
        if ($is_ganti_rugi && $input_kembali < $remaining) {
            // Case: Partial/Zero return BUT checked "Ganti Uang" -> Force Close
            if ($nominal_ganti <= 0) {
                 return $this->response->setJSON(['status' => 'error', 'message' => 'Nominal ganti rugi wajib diisi jika dicentang']);
            }

            // 1. Calculate stats
            $barang_kembali_fisik = $input_kembali;
            $barang_hilang = $remaining - $input_kembali;
            
            // 2. Prepare Update Data
            $new_total_returned = $current_total_returned + $barang_kembali_fisik; // Only count physical items? Or count them as 'returned' logically?
            // User requested: "Sisa barang diganti uang". So physically they are NOT returned to stock.
            // But status should be 'kembali'.
            
            $dateNow = date('d-m-Y');
            $historyNote = "[$dateNow: Kembali Fisik $barang_kembali_fisik unit. Hilang/Ganti: $barang_hilang unit. Nominal: Rp " . number_format($nominal_ganti,0,',','.') . ". Catatan: $keterangan]";
            $finalKeterangan = $peminjaman['keterangan'] . ' ' . $historyNote;

            $updateData = [
                'id' => $id,
                'jumlah_kembali' => $new_total_returned, // Updated logic: Keep track of physical returns
                'keterangan' => $finalKeterangan,
                'status' => 'kembali', // Force Close
                'tanggal_kembali' => date('Y-m-d'),
                'nominal_ganti_rugi' => $nominal_ganti
            ];
            
            // 3. Create Journal Entry (Kas Sub)
            // Cari COA 'Pendapatan Lain' or code starting with '4' (Pendapatan)
            // Fallback to a safe default if not found
            $coa = $this->coaModel->like('name', 'Pendapatan Lain', 'both')->first();
            if(!$coa) $coa = $this->coaModel->like('code', '4', 'after')->first(); // Grab any income account
            $coaCode = $coa ? $coa['code'] : '4000'; // Default fallback

            $barang = $this->barangModel->find($peminjaman['barang_id']);
            $nama_barang = $barang['nama'] ?? 'Barang ID '.$peminjaman['barang_id'];

            $jurnalData = [
                'date_trx' => date('Y-m-d H:i:s'),
                'coa_code' => $coaCode, 
                'desc_trx' => "Ganti Rugi Inventaris ($nama_barang) - {$peminjaman['nama_peminjam']}",
                'debet'    => $nominal_ganti, 
                'kredit'   => 0,
                'reff'     => 'INV' // Requested Fixed Reff
            ];
            
            // Transaction
            $this->peminjamanModel->transStart();
            $this->peminjamanModel->save($updateData);
            $this->kasSubModel->save($jurnalData);

            // Restore Stock (Only Physical)
            if ($barang_kembali_fisik > 0) {
                 $newStock = $barang['jumlah'] + $barang_kembali_fisik;
                 $this->barangModel->update($peminjaman['barang_id'], ['jumlah' => $newStock]);
            }
            $this->peminjamanModel->transComplete();

            if ($this->peminjamanModel->transStatus() === FALSE) {
                 return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal memproses transaksi ganti rugi']);
            }
            
            log_activity('GANTI_RUGI_BARANG', "Ganti Rugi ID: {$id}. Fisik: {$barang_kembali_fisik}. Hilang: {$barang_hilang}. Nominal: {$nominal_ganti}");
            return $this->response->setJSON(['status' => 'success', 'message' => 'Peminjaman ditutup dengan Ganti Rugi']);

        } else {
            // Case: Normal Return (Existing Logic)
            if ($input_kembali <= 0) {
                 return $this->response->setJSON(['status' => 'error', 'message' => 'Jumlah kembali minimal 1']);
            }

            $new_total_returned = $current_total_returned + $input_kembali;
            $is_fully_returned = ($new_total_returned >= $total_borrowed);
            
            $dateNow = date('d-m-Y');
            $historyNote = "[$dateNow: Kembali $input_kembali unit. ";
            if (!empty($keterangan)) $historyNote .= "Catatan: $keterangan. ";
            $historyNote .= "]";
            
            $finalKeterangan = $peminjaman['keterangan'] . ' ' . $historyNote;

            $updateData = [
                'id' => $id,
                'jumlah_kembali' => $new_total_returned,
                'keterangan' => $finalKeterangan
            ];

            if ($is_fully_returned) {
                $updateData['status'] = 'kembali';
                $updateData['tanggal_kembali'] = date('Y-m-d');
            }

            if ($this->peminjamanModel->save($updateData)) {
                // Restore Stock
                $barang = $this->barangModel->find($peminjaman['barang_id']);
                if ($barang) {
                    $newStock = $barang['jumlah'] + $input_kembali;
                    $this->barangModel->update($peminjaman['barang_id'], ['jumlah' => $newStock]);
                }

                $message = $is_fully_returned ? 'Seluruh barang berhasil dikembalikan' : "Berhasil mengembalikan $input_kembali unit. Masih ada sisa " . ($total_borrowed - $new_total_returned);
                
                log_activity('KEMBALI_BARANG', "Pengembalian ID: {$id}. Item ini: {$input_kembali}. Total Kembali: {$new_total_returned}/{$total_borrowed}");
                return $this->response->setJSON(['status' => 'success', 'message' => $message]);
            }
             return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal update status']);
        }
    }
    
    public function delete()
    {
        $role = session()->get('role');
        $allowed = ['s_admin', 'admin']; 
        if (!in_array($role, $allowed)) {
             return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }
        
        $id = $this->request->getPost('id');
        if($this->peminjamanModel->delete($id)){
             return $this->response->setJSON(['status' => 'success', 'message' => 'Data dihapus']);
        }
         return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal hapus']);
    }
}
