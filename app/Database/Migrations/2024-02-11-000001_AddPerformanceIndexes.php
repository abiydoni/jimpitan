<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPerformanceIndexes extends Migration
{
    public function up()
    {
        // Index for tb_iuran
        $this->db->query("ALTER TABLE `tb_iuran` ADD INDEX IF NOT EXISTS `idx_perf_kode_tarif` (`kode_tarif`)");
        $this->db->query("ALTER TABLE `tb_iuran` ADD INDEX IF NOT EXISTS `idx_perf_nikk` (`nikk`)");
        $this->db->query("ALTER TABLE `tb_iuran` ADD INDEX IF NOT EXISTS `idx_perf_tahun_bulan` (`tahun`, `bulan`)");

        // Index for tb_warga
        $this->db->query("ALTER TABLE `tb_warga` ADD INDEX IF NOT EXISTS `idx_perf_nikk` (`nikk`)");
        $this->db->query("ALTER TABLE `tb_warga` ADD INDEX IF NOT EXISTS `idx_perf_hubungan` (`hubungan`)");

        // Index for tb_pengurus_menu
        $this->db->query("ALTER TABLE `tb_pengurus_menu` ADD INDEX IF NOT EXISTS `idx_perf_id_pengurus` (`id_pengurus`)");
    }

    public function down()
    {
        // Dropping indexes might be tricky if we don't know exact names if they pre-existed, 
        // but for this migration we assume we added them.
        $this->db->query("ALTER TABLE `tb_iuran` DROP INDEX `idx_perf_kode_tarif`");
        $this->db->query("ALTER TABLE `tb_iuran` DROP INDEX `idx_perf_nikk`");
        $this->db->query("ALTER TABLE `tb_iuran` DROP INDEX `idx_perf_tahun_bulan`");
        
        $this->db->query("ALTER TABLE `tb_warga` DROP INDEX `idx_perf_nikk`");
        $this->db->query("ALTER TABLE `tb_warga` DROP INDEX `idx_perf_hubungan`");

        $this->db->query("ALTER TABLE `tb_pengurus_menu` DROP INDEX `idx_perf_id_pengurus`");
    }
}
