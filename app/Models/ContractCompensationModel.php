<?php

namespace App\Models;

use CodeIgniter\Model;

class ContractCompensationModel extends Model
{
    protected $table            = 'contract_compensations';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'employee_id', 'contract_id', 'client_id', 'basic_salary',
        'masa_kerja_tahun', 'masa_kerja_bulan', 'masa_kerja_hari',
        'multiplier', 'nilai_kompensasi', 'nilai_kompensasi_final',
        'status', 'ditetapkan_oleh', 'ditetapkan_pada',
        'disetujui_oleh', 'disetujui_pada', 'catatan',
        'tgl_mulai_kerja', 'tgl_akhir_kontrak'
    ];
    protected $useTimestamps    = true;

    /**
     * Menghitung nilai kompensasi kontrak berdasarkan masa kerja.
     * 
     * Rumus:
     * - Jika >= 1 tahun: min(tahun, 5) * basic_salary
     * - Jika < 1 tahun: (bulan / 12) * basic_salary
     * - Jika < 1 bulan: (hari / standard_days) / 12 * basic_salary
     */
    public function calculateCompensation($basicSalary, $startDate, $endDate, $standardDays = 22, $actualDaysWorked = null)
    {
        if (empty($startDate) || empty($endDate) || $basicSalary <= 0) {
            return [
                'tahun' => 0,
                'bulan' => 0,
                'hari' => 0,
                'multiplier' => 0.0,
                'nilai' => 0.0
            ];
        }

        $start = new \DateTime($startDate);
        $end = new \DateTime($endDate);
        
        // Add 1 day to end date so it is inclusive
        $endClone = clone $end;
        $endClone->modify('+1 day');
        
        $diff = $start->diff($endClone);
        
        $years = $diff->y;
        $months = $diff->m;
        $days = $diff->d;

        $multiplier = 0.0;
        $nilai = 0.0;

        if ($years >= 1) {
            // >= 1 tahun: multiplier adalah jumlah tahun (maksimum 5x)
            // Sisa bulan dihitung proporsional dari tahun tersebut (misalnya 1 tahun 3 bulan = 1.25x)
            // Rumus dasar: tahun + (bulan / 12)
            $totalMonths = ($years * 12) + $months;
            $rawMultiplier = $totalMonths / 12;
            $multiplier = min($rawMultiplier, 5.0);
            $nilai = $multiplier * $basicSalary;
        } else {
            // < 1 tahun
            if ($months > 0) {
                // Diatas atau sama dengan 1 bulan
                $rawMultiplier = $months / 12;
                // Proporsional sisa hari
                if ($days > 0) {
                    $rawMultiplier += ($days / $standardDays) / 12;
                }
                $multiplier = min($rawMultiplier, 5.0);
                $nilai = $multiplier * $basicSalary;
            } else {
                // < 1 bulan (hanya hari kerja)
                // Jika total_hari_kerja_sebulan tidak null, gunakan actualDaysWorked
                $daysToUse = ($actualDaysWorked !== null) ? $actualDaysWorked : $days;
                
                $rawMultiplier = ($daysToUse / $standardDays) / 12;
                $multiplier = min($rawMultiplier, 5.0);
                $nilai = $multiplier * $basicSalary;
            }
        }

        // Round multiplier to 4 decimal places, and nilai to 2 decimal places
        return [
            'tahun' => $years,
            'bulan' => $months,
            'hari' => $days,
            'multiplier' => round($multiplier, 4),
            'nilai' => round($nilai, 2)
        ];
    }
}
