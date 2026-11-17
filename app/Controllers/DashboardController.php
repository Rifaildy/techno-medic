<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class DashboardController extends BaseController
{
    // ─────────────────────────────────────────────
    //  GET /dashboard
    // ─────────────────────────────────────────────
    public function index(): string
    {
        $db    = \Config\Database::connect();
        $today = date('Y-m-d');

        // ── Statistik Utama ──────────────────────
        $totalPatients = $db->table('patients')
                            ->where('deleted_at', null)
                            ->countAllResults();

        $totalVisitsToday = $db->table('registrations')
                               ->where('registration_date', $today)
                               ->where('deleted_at', null)
                               ->countAllResults();

        $totalWaiting = $db->table('registrations')
                           ->where('registration_date', $today)
                           ->where('status', 'MENUNGGU')
                           ->where('deleted_at', null)
                           ->countAllResults();

        $totalExamining = $db->table('registrations')
                             ->where('registration_date', $today)
                             ->where('status', 'DIPERIKSA')
                             ->where('deleted_at', null)
                             ->countAllResults();

        $totalDone = $db->table('registrations')
                        ->where('registration_date', $today)
                        ->where('status', 'SELESAI')
                        ->where('deleted_at', null)
                        ->countAllResults();

        // ── Kunjungan per Poliklinik Hari Ini ───
        $visitsByPoly = $db->table('registrations r')
                           ->select('p.name as poly_name, COUNT(r.id) as total')
                           ->join('polyclinics p', 'p.id = r.polyclinic_id')
                           ->where('r.registration_date', $today)
                           ->where('r.deleted_at', null)
                           ->groupBy('r.polyclinic_id')
                           ->orderBy('total', 'DESC')
                           ->get()
                           ->getResultArray();

        // ── Antrean Aktif Hari Ini ───────────────
        $activeQueue = $db->table('registrations r')
                          ->select('r.registration_number, r.queue_number, r.status, r.complaint,
                                    pat.name as patient_name, pat.no_rm,
                                    doc.name as doctor_name,
                                    pol.name as poly_name')
                          ->join('patients pat', 'pat.id = r.patient_id')
                          ->join('doctors doc', 'doc.id = r.doctor_id')
                          ->join('polyclinics pol', 'pol.id = r.polyclinic_id')
                          ->where('r.registration_date', $today)
                          ->whereIn('r.status', ['MENUNGGU', 'DIPERIKSA'])
                          ->where('r.deleted_at', null)
                          ->orderBy('r.queue_number', 'ASC')
                          ->get()
                          ->getResultArray();

        return view('dashboard/index', [
            'title'           => 'Dashboard',
            'totalPatients'   => $totalPatients,
            'totalVisitsToday'=> $totalVisitsToday,
            'totalWaiting'    => $totalWaiting,
            'totalExamining'  => $totalExamining,
            'totalDone'       => $totalDone,
            'visitsByPoly'    => $visitsByPoly,
            'activeQueue'     => $activeQueue,
        ]);
    }
}
