<?php

namespace App\Controllers;

class Dashboard extends BaseController
{
    public function index()
    {
		if(has_permission('dashboardView'))
		{
			$db = \Config\Database::connect();
			
			$data['open'] = 0;
			$data['active'] = 1;
			$data['breadcrumbs'] = $db->query("SELECT name, href FROM menu WHERE id = " . $data['open'] . " OR id = " . $data['active'] . " ORDER BY parent_id ASC");
			$data['title'] = 'Dashboard';
			
			$data['db'] = $db;

			if(has_permission('labView'))
			{
				$qLabs = "
					SELECT a.id, a.nama, a.deskripsi, a.lantai_awal, a.jml_lantai
					FROM data_lab a
					WHERE 1=1
				";

				if(!in_groups('admin')) {
					$qLabs .= " AND IF(EXISTS(SELECT c.id_pengelola FROM data_pengelola c WHERE c.id_lab = a.id AND c.id_pengelola = '" . user_id() . "'), 1, 0) = 1 ";
				}
			}

			if(has_permission('loanedView'))
			{
				$qLoanedMhsItem = " SELECT a.* FROM peminjaman_mhs_item a ";
				$qLoanedMhsKotak = " SELECT a.* FROM peminjaman_mhs_kotak_penyimpanan a ";
				$qLoanedDosenItem = " SELECT a.* FROM peminjaman_dosen_item a ";
				$qLoanedDosenKotak = " SELECT a.* FROM peminjaman_dosen_kotak_penyimpanan a ";

				if(!in_groups('admin'))
				{
					$qLoanedMhsItem .= "
						LEFT JOIN data_item b ON(a.kode_item = b.kode)
						LEFT JOIN data_inventaris c ON(b.id_inventaris = c.id)
						LEFT JOIN data_lab d ON(c.id_lab = d.id)
						LEFT JOIN data_pengelola e ON(e.id_lab = d.id)
						WHERE e.id_pengelola = " . user_id() . "
					";

					$qLoanedMhsKotak .= "
						LEFT JOIN data_daftar_kotak_penyimpanan b ON(a.kode_kotak_penyimpanan = b.kode)
						LEFT JOIN data_kotak_penyimpanan c ON(b.id_kotak_penyimpanan = c.id)
						LEFT JOIN data_lab d ON(c.id_lab = d.id)
						LEFT JOIN data_pengelola e ON(e.id_lab = d.id)
						WHERE e.id_pengelola = " . user_id() . "
					";

					$qLoanedDosenItem .= "
						LEFT JOIN data_item b ON(a.kode_item = b.kode)
						LEFT JOIN data_inventaris c ON(b.id_inventaris = c.id)
						LEFT JOIN data_lab d ON(c.id_lab = d.id)
						LEFT JOIN data_pengelola e ON(e.id_lab = d.id)
						WHERE e.id_pengelola = " . user_id() . "
					";

					$qLoanedDosenKotak .= "
						LEFT JOIN data_daftar_kotak_penyimpanan b ON(a.kode_kotak_penyimpanan = b.kode)
						LEFT JOIN data_kotak_penyimpanan c ON(b.id_kotak_penyimpanan = c.id)
						LEFT JOIN data_lab d ON(c.id_lab = d.id)
						LEFT JOIN data_pengelola e ON(e.id_lab = d.id)
						WHERE e.id_pengelola = " . user_id() . "
					";
				}
				else
				{
					$qLoanedMhsItem .= " WHERE 1=1 ";
					$qLoanedMhsKotak .= " WHERE 1=1 ";
					$qLoanedDosenItem .= " WHERE 1=1 ";
					$qLoanedDosenKotak .= " WHERE 1=1 ";
				}

				$qLoanedMhsItem .= " AND a.ada_kembali = 0 ";
				$qLoanedMhsKotak .= " AND a.ada_kembali = 0 ";
				$qLoanedDosenItem .= " AND a.ada_kembali = 0 ";
				$qLoanedDosenKotak .= " AND a.ada_kembali = 0 ";

				//echo $qLoanedMhsItem; return;

				$loanedMhsItem = $db->query($qLoanedMhsItem)->getNumRows();

				$loanedMhsKotak = $db->query($qLoanedMhsKotak)->getNumRows();

				$loanedDosenItem = $db->query($qLoanedDosenItem)->getNumRows();

				$loanedDosenKotak = $db->query($qLoanedDosenKotak)->getNumRows();

				$data['jmlItemDipinjamkan'] = $loanedMhsItem + $loanedDosenItem;
				$data['jmlKotakDipinjamkan'] = $loanedMhsKotak + $loanedDosenKotak;
			}
			
			return view('dashboard/index', $data);
		}
    }
}
