<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Circulation extends BaseController
{
	public function loan()
	{
		if(has_permission('lend'))
		{
			$data['title'] = 'Peminjaman';
			$data['open'] = 12;
			$data['active'] = 13;
			
			$db = \Config\Database::connect();
			$session = \Config\Services::session();
			
			$data['breadcrumbs'] = $db->query("SELECT name, href FROM menu WHERE id = ".$data['open']." OR id = ".$data['active']." ORDER BY parent_id ASC");

			$idLab = $this->request->getGet('id_lab');
			$idPeminjam = $this->request->getGet('id_peminjam');
			
			if(null === $idLab || $idLab == '') {
				$qLabs = "SELECT id, nama, deskripsi FROM data_lab ";
				if(!in_groups('admin')) {
					$qLabs .= " WHERE IF(EXISTS(SELECT c.id_pengelola FROM data_pengelola c WHERE c.id_lab = data_lab.id AND c.id_pengelola = '" . user_id() . "'), 1, 0) = 1 ";
				}
				$labs = $db->query($qLabs);
				if($labs->getNumRows() == 1) {
					return redirect()->to('/circulation/loan?id_lab=' . $labs->getRow()->id);
				}
				$data['labs'] = $labs;
				return view('circulation/loan_lab', $data);
			}

			$data['idLab'] = $idLab;
			$data['namaLab'] = $db->query("SELECT deskripsi FROM data_lab WHERE id = $idLab ORDER BY deskripsi ASC")->getRow()->deskripsi;

			if(null === $idPeminjam || $idPeminjam == '') {
				return view('circulation/loan_user', $data);
			}

			$idPeminjam = $this->request->getGet('id_peminjam');

			$mhs_row = $db->query("SELECT * FROM ref_student WHERE nim = '$idPeminjam'");
			if($mhs_row->getNumRows() != 0) {
				$mhs = $mhs_row->getRow();
				$data['id_peminjam'] = $mhs->nim;
				$data['nama'] = $mhs->name;
				return view('circulation/loan_item', $data);
			}
			
			$dosen_row = $db->query("SELECT * FROM ref_dosen WHERE nidn = '$idPeminjam'");
			if($dosen_row->getNumRows() != 0) {
				$dosen = $dosen_row->getRow();
				$data['id_peminjam'] = $dosen->nidn;
				$data['nama'] = $dosen->nama_sdm;
				return view('circulation/loan_item', $data);
			}
			
			return redirect()->back()->withInput()->with('error', 'Mahasiswa / Dosen tidak ditemukan');
		}
	}

	public function lend()
	{
		if(has_permission('lend')) {
			$validation = \Config\Services::validation();
			
			$validation->setRules([
				'idLab' => [
					'label' => 'ID Lab',
					'rules' => 'required|numeric',
				],
				'idPeminjam' => [
					'label' => 'ID Peminjam',
					'rules' => 'required',
				],
				'targetTglKembali' => [
					'label' => 'Tanggal Kembali',
					'rules' => 'required',
				],
			]);
			
			$isDataValid = $validation->withRequest($this->request)->run();
			
			if($isDataValid) {
				$db = \Config\Database::connect();

				$idLab = $this->request->getPost('idLab');
				$idPeminjam = $this->request->getPost('idPeminjam');
				$kodeItem = $this->request->getPost('kodeItem');
				$kodeKotak = $this->request->getPost('kodeKotak');
				$targetTglKembali = $this->request->getPost('targetTglKembali');

				if(!in_groups('admin')) {
					$labs = $db->query("SELECT * FROM data_pengelola WHERE id_lab = $idLab AND id_pengelola = " . user_id());
					if($labs->getNumRows() == 0) {
						return $this->response
	                        ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
	                        ->setContentType('text/plain')
	                        ->setBody('Anda tidak memiliki hak akses untuk lab ini');
					}
				}

				$mhs = $db->query("SELECT * FROM ref_student WHERE nim = '$idPeminjam'");
				$dosen = $db->query("SELECT * FROM ref_dosen WHERE nidn = '$idPeminjam'");

				if($mhs->getNumRows() != 0) {
					$peminjamanMhs = $db->table('peminjaman_mhs');

					$peminjamanMhs->insert([
						'id_lab' => $idLab,
						'nim' => $idPeminjam,
						'tgl_pinjam' => date('Y-m-d H:i:s'),
						'target_tgl_kembali' => $targetTglKembali . ' ' . date('H:i:s'),
					]);

					$idPeminjamanMhs = $db->insertID();

					if(is_array($kodeItem)) {
						$itemBatch = [];

						foreach($kodeItem as $kode) {
							$itemBatch[] = [
								'id_peminjaman_mhs' => $idPeminjamanMhs,
								'kode_item' => $kode
							];
						}

						$peminjamanMhsItem = $db->table('peminjaman_mhs_item');
						$peminjamanMhsItem->insertBatch($itemBatch);
					}

					if(is_array($kodeKotak)) {
						$kotakBatch = [];

						foreach($kodeKotak as $kode)
						{
							$kotakBatch[] = [
								'id_peminjaman_mhs' => $idPeminjamanMhs,
								'kode_kotak_penyimpanan' => $kode
							];
						}

						$peminjamanMhsKotak = $db->table('peminjaman_mhs_kotak_penyimpanan');
						$peminjamanMhsKotak->insertBatch($kotakBatch);
					}
				}

				if($dosen->getNumRows() != 0) {
					$peminjamanDosen = $db->table('peminjaman_dosen');

					$peminjamanDosen->insert([
						'id_lab' => $idLab,
						'nidn' => $idPeminjam,
						'tgl_pinjam' => date('Y-m-d H:i:s'),
						'target_tgl_kembali' => $targetTglKembali . ' ' . date('H:i:s'),
					]);

					$idPeminjamanDosen = $db->insertID();

					if(is_array($kodeItem)) {
						$itemBatch = [];

						foreach($kodeItem as $kode)
						{
							$itemBatch[] = [
								'id_peminjaman_dosen' => $idPeminjamanDosen,
								'kode_item' => $kode
							];
						}

						$peminjamanDosenItem = $db->table('peminjaman_dosen_item');
						$peminjamanDosenItem->insertBatch($itemBatch);
					}

					if(is_array($kodeKotak)) {
						$kotakBatch = [];

						foreach($kodeKotak as $kode) {
							$kotakBatch[] = [
								'id_peminjaman_dosen' => $idPeminjamanDosen,
								'kode_kotak_penyimpanan' => $kode
							];
						}

						$peminjamanDosenKotak = $db->table('peminjaman_dosen_kotak_penyimpanan');
						$peminjamanDosenKotak->insertBatch($kotakBatch);
					}

					
				}
			} else {
				return $this->response
                        ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                        ->setJSON($validation->getErrors());
			}
		}
	}
	
    public function loaned()
    {
		if(has_permission('loanedView')) {
			$data['title'] = 'Data Barang Yang Dipinjam';
			$data['open'] = 12;
			$data['active'] = 15;
			
			$db = \Config\Database::connect();
			$session = \Config\Services::session();
			
			$data['breadcrumbs'] = $db->query("SELECT name, href FROM menu WHERE id = ".$data['open']." OR id = ".$data['active']." ORDER BY parent_id ASC");
			
			$data['searchPeminjamanMhs'] = $session->get('searchPeminjamanMhs');
			$data['startPeminjamanMhs'] = $session->get('startPeminjamanMhs');
			$data['lengthPeminjamanMhs'] = $session->get('lengthPeminjamanMhs');
			$data['orderColPeminjamanMhs'] = $session->get('orderColPeminjamanMhs');
			$data['orderDirPeminjamanMhs'] = $session->get('orderDirPeminjamanMhs');
			
			$data['searchPeminjamanDosen'] = $session->get('searchPeminjamanDosen');
			$data['startPeminjamanDosen'] = $session->get('startPeminjamanDosen');
			$data['lengthPeminjamanDosen'] = $session->get('lengthPeminjamanDosen');
			$data['orderColPeminjamanDosen'] = $session->get('orderColPeminjamanDosen');
			$data['orderDirPeminjamanDosen'] = $session->get('orderDirPeminjamanDosen');
			
			$data['tab'] = $session->get('loanedTab');
			
			return view('circulation/loaned', $data);
		} else {
			return redirect()->back();
		}
    }
	
	public function loanedMhsList()
    {
		if(has_permission('loanedView'))
		{
			$db = \Config\Database::connect();
			$session = \Config\Services::session();
			
			//Searching
			
			$search = $db->escapeString($this->request->getPost('search'));
			$status = $this->request->getPost('status');

			$session->set('searchPeminjamanMhsList', $search);
			
			$sSearch = '';
			
			if(isset($search) && $search != '') {
				$sSearch .= " AND (a.nim LIKE '%".$search."%' OR b.name LIKE '%".$search."%' OR b.major LIKE '%".$search."%') ";
			}
		
			//Limit
			
			$start = $this->request->getPost("start");
			$session->set('startPeminjamanMhsList', $start);
			$length = $this->request->getPost("length");
			$session->set('lengthPeminjamanMhsList', $length);
			
			if (isset($start) && $start != '-1' ) {
				$sLimit = " LIMIT ".intval($start).", ".intval($length);
			}
			
			//Table Row
			
			$s1 = "
				SELECT a.id, a.nim, a.id_lab, b.name, b.major, a.tgl_pinjam, a.target_tgl_kembali, a.tgl_kembali
				FROM peminjaman_mhs a
				LEFT JOIN ref_student b ON(a.nim = b.nim)
				LEFT JOIN data_lab c ON(a.id_lab = c.id)
				LEFT JOIN data_pengelola d ON(d.id_lab = c.id)
				WHERE 1=1
			";

			if(!in_groups('admin')) {
				$s1 .= " AND d.id_pengelola = " . user_id();
			}

			if($status == 'loaned') {
				$s1 .= " AND tgl_kembali IS NULL";
			}

			$s1 .= $sSearch;
			$s1 .= " ORDER BY tgl_pinjam DESC ";
			$s1 .= $sLimit;
		
			$sheet1 = $db->query($s1);
			
			$sheet_total = $db->query("
				SELECT a.id
				FROM peminjaman_mhs a
				LEFT JOIN ref_student b ON(a.nim = b.nim)
				WHERE 1=1
			".$sSearch)->getNumRows();
		
			$result = array();
			$i=0;
			$no = (isset($start) ? $start + 1 : 1);
			
			foreach($sheet1->getResult() as $sheet)
			{
				if($sheet->tgl_kembali === null || $sheet->tgl_kembali == '') {
					$rincian = '<a href="/circulation/return?id_lab=' . $sheet->id_lab . '&id_peminjam=' . $sheet->nim . '" class="btn btn-primary">Rincian</a>';
				} else {
					$rincian = '<a href="/circulation/historyDetail/mhs/' . $sheet->id . '" class="btn btn-primary">Rincian</a>';
				}
				$result[$i]['0'] = $no;
				$result[$i]['1'] = $sheet->nim;
				$result[$i]['2'] = $sheet->name;
				$result[$i]['3'] = $sheet->major;
				$result[$i]['4'] = $sheet->tgl_pinjam;
				$result[$i]['5'] = $sheet->target_tgl_kembali;
				$result[$i]['6'] = $sheet->tgl_kembali;
				$result[$i]['7'] = $rincian;
				$i++;
				$no++;
			}
			
			$data = $result;
			$results = array(
				"iTotalRecords" => ($sheet_total),
				"iTotalDisplayRecords" => ($sheet_total),
				"aaData"=>$data
			);
				
			echo json_encode($results);
		}
    }

    public function loanedMhsItemList()
    {
    	if(has_permission('lend'))
    	{
    		$db = \Config\Database::connect();
			$session = \Config\Services::session();
			
			//Searching
			
			$idLab = $this->request->getPost('id_lab');
			$idPeminjam = $this->request->getPost('id_peminjam');
			$selesai = $this->request->getPost('selesai');
			$idPeminjaman = $this->request->getPost('id_peminjaman');
			
			//Table Row
			
			$s1 = "
				SELECT a.kode_item, a.ada_kembali, b.kode_kotak_penyimpanan, c.nama
				FROM peminjaman_mhs_item a
				LEFT JOIN data_item b ON(a.kode_item = b.kode)
				LEFT JOIN data_inventaris c ON(b.id_inventaris = c.id)
				LEFT JOIN peminjaman_mhs d ON(a.id_peminjaman_mhs = d.id)
				LEFT JOIN data_lab e ON(d.id_lab = e.id)
				LEFT JOIN data_pengelola f ON(f.id_lab = e.id)
				WHERE d.nim = '$idPeminjam'
			";

			if(!in_groups('admin')) {
				$s1 .= " AND f.id_pengelola = " . user_id();
			}

			if($idLab != '') {
				$s1 .= " AND d.id_lab = '$idLab' ";
			}

			if($selesai == 0) {
				$s1 .= " AND a.ada_kembali = 0 ";
			}	

			if($idPeminjaman != '') {
				$s1 .= " AND d.id = '$idPeminjaman' ";
			}

			$s1 .= " ORDER BY c.nama ASC ";
		
			$sheet1 = $db->query($s1);
			
			$sheet_total = $db->query("SELECT a.id FROM peminjaman_mhs a WHERE 1=1 ")->getNumRows();
		
			$result = array();
			$i=0;
			$no = (isset($start) ? $start + 1 : 1);
			
			foreach($sheet1->getResult() as $sheet)
			{
				if($sheet->ada_kembali == 0) {
					$ada = '<input type="checkbox" name="kodeItem[]" value="' . $sheet->kode_item . '" style="transform: scale(2);">';
				} else {
					$ada = '<input type="checkbox" style="transform: scale(2);" checked disabled>';
				}
				$result[$i]['0'] = $no;
				$result[$i]['1'] = $sheet->nama;
				$result[$i]['2'] = $sheet->kode_item;
				$result[$i]['3'] = $sheet->kode_kotak_penyimpanan;
				$result[$i]['4'] = $ada;
				$i++;
				$no++;
			}
			
			$data = $result;
			$results = array(
				"iTotalRecords" => ($sheet_total),
				"iTotalDisplayRecords" => ($sheet_total),
				"aaData"=>$data
			);
				
			echo json_encode($results);
    	}
    }

    public function loanedMhsKotakList()
    {
    	if(has_permission('lend'))
    	{
    		$db = \Config\Database::connect();
			$session = \Config\Services::session();
			
			//Searching
			
			$idLab = $this->request->getPost('id_lab');
			$idPeminjam = $this->request->getPost('id_peminjam');
			$selesai = $this->request->getPost('selesai');
			$idPeminjaman = $this->request->getPost('id_peminjaman');
			
			//Table Row
			
			$s1 = "
				SELECT a.kode_kotak_penyimpanan, a.ada_kembali, c.nama
				FROM peminjaman_mhs_kotak_penyimpanan a
				LEFT JOIN data_daftar_kotak_penyimpanan b ON(a.kode_kotak_penyimpanan = b.kode)
				LEFT JOIN data_kotak_penyimpanan c ON(b.id_kotak_penyimpanan = c.id)
				LEFT JOIN peminjaman_mhs d ON(a.id_peminjaman_mhs = d.id)
				LEFT JOIN data_lab e ON(d.id_lab = e.id)
				LEFT JOIN data_pengelola f ON(f.id_lab = e.id)
				WHERE d.nim = '$idPeminjam'
			";

			if(!in_groups('admin')) {
				$s1 .= " AND f.id_pengelola = " . user_id();
			}

			if($idLab != '') {
				$s1 .= " AND d.id_lab = '$idLab' ";
			}

			if($selesai == 0) {
				$s1 .= " AND a.ada_kembali = 0 ";
			}

			if($idPeminjaman != '') {
				$s1 .= " AND d.id = '$idPeminjaman' ";
			}

			$s1 .= " ORDER BY c.nama ASC ";
		
			$sheet1 = $db->query($s1);
			
			$sheet_total = $db->query("SELECT a.id FROM peminjaman_mhs a WHERE 1=1 ")->getNumRows();
		
			$result = array();
			$i=0;
			$no = (isset($start) ? $start + 1 : 1);
			
			foreach($sheet1->getResult() as $sheet)
			{
				if($sheet->ada_kembali == 0) {
					$ada = '<input type="checkbox" name="kodeKotak[]" value="' . $sheet->kode_kotak_penyimpanan . '" style="transform: scale(2);">';
				} else {
					$ada = '<input type="checkbox" style="transform: scale(2);" checked disabled>';
				}
				$result[$i]['0'] = $no;
				$result[$i]['1'] = $sheet->nama;
				$result[$i]['2'] = $sheet->kode_kotak_penyimpanan;
				$result[$i]['3'] = $ada;
				$i++;
				$no++;
			}
			
			$data = $result;
			$results = array(
				"iTotalRecords" => ($sheet_total),
				"iTotalDisplayRecords" => ($sheet_total),
				"aaData"=>$data
			);
				
			echo json_encode($results);
    	}
    }
	
	public function loanedDosenList()
    {
		if(has_permission('loanedView'))
		{
			$db = \Config\Database::connect();
			$session = \Config\Services::session();
			
			//Searching
			
			$search = $db->escapeString($this->request->getPost('search'));
			$status = $this->request->getPost('status');

			$session->set('searchPeminjamanMhsList', $search);
			
			$sSearch = '';
			
			if(isset($search) && $search != '')
			{
				$sSearch .= " AND (a.nidn LIKE '%".$search."%' OR b.nama_sdm LIKE '%".$search."%') ";
			}
		
			//Limit
			
			$start = $this->request->getPost("start");
			$session->set('startPeminjamanMhsList', $start);
			$length = $this->request->getPost("length");
			$session->set('lengthPeminjamanMhsList', $length);
			
			if (isset($start) && $start != '-1' )
			{
				$sLimit = " LIMIT ".intval($start).", ".intval($length);
			}
			
			//Table Row
			
			$s1 = "
				SELECT a.id, a.nidn, a.id_lab, b.nama_sdm, a.tgl_pinjam, a.target_tgl_kembali, a.tgl_kembali
				FROM peminjaman_dosen a
				LEFT JOIN ref_dosen b ON(a.nidn = b.nidn)
				LEFT JOIN data_lab c ON(a.id_lab = c.id)
				LEFT JOIN data_pengelola d ON(d.id_lab = c.id)
				WHERE 1=1
			";

			if(!in_groups('admin')) {
				$s1 .= " AND d.id_pengelola = " . user_id();
			}

			if($status == 'loaned') {
				$s1 .= " AND tgl_kembali IS NULL";
			}

			$s1 .= $sSearch;
			$s1 .= " ORDER BY tgl_pinjam DESC ";
			$s1 .= $sLimit;
		
			$sheet1 = $db->query($s1);
			
			$sheet_total = $db->query("
				SELECT a.id
				FROM peminjaman_dosen a
				LEFT JOIN ref_dosen b ON(a.nidn = b.nidn)
				WHERE 1=1
			 ".$sSearch)->getNumRows();
		
			$result = array();
			$i=0;
			$no = (isset($start) ? $start + 1 : 1);
			
			foreach($sheet1->getResult() as $sheet)
			{
				if($sheet->tgl_kembali === null || $sheet->tgl_kembali == '') {
					$rincian = '<a href="/circulation/return?id_lab=' . $sheet->id_lab . '&id_peminjam=' . $sheet->nidn . '" class="btn btn-primary">Rincian</a>';
				} else {
					$rincian = '<a href="/circulation/historyDetail/dosen/' . $sheet->id . '" class="btn btn-primary">Rincian</a>';
				}
				$result[$i]['0'] = $no;
				$result[$i]['1'] = $sheet->nidn;
				$result[$i]['2'] = $sheet->nama_sdm;
				$result[$i]['3'] = $sheet->tgl_pinjam;
				$result[$i]['4'] = $sheet->target_tgl_kembali;
				$result[$i]['5'] = $sheet->tgl_kembali;
				$result[$i]['6'] = $rincian;
				$i++;
				$no++;
			}
			
			$data = $result;
			$results = array(
				"iTotalRecords" => ($sheet_total),
				"iTotalDisplayRecords" => ($sheet_total),
				"aaData"=>$data);
				
			echo json_encode($results);
		}
    }

    public function loanedDosenItemList()
    {
    	if(has_permission('lend'))
    	{
    		$db = \Config\Database::connect();
			$session = \Config\Services::session();
			
			//Searching
			
			$idLab = $this->request->getPost('id_lab');
			$idPeminjam = $this->request->getPost('id_peminjam');
			$selesai = $this->request->getPost('selesai');
			$idPeminjaman = $this->request->getPost('id_peminjaman');
			
			//Table Row
			
			$s1 = "
				SELECT a.kode_item, a.ada_kembali, b.kode_kotak_penyimpanan, c.nama
				FROM peminjaman_dosen_item a
				LEFT JOIN data_item b ON(a.kode_item = b.kode)
				LEFT JOIN data_inventaris c ON(b.id_inventaris = c.id)
				LEFT JOIN peminjaman_dosen d ON(a.id_peminjaman_dosen = d.id)
				LEFT JOIN data_lab e ON(d.id_lab = e.id)
				LEFT JOIN data_pengelola f ON(f.id_lab = e.id)
				WHERE d.nidn = '$idPeminjam'
			";

			if(!in_groups('admin')) {
				$s1 .= " AND f.id_pengelola = " . user_id();
			}

			if($idLab != '') {
				$s1 .= " AND d.id_lab = '$idLab' ";
			}

			if($selesai == 0) {
				$s1 .= " AND a.ada_kembali = 0 ";
			}

			if($idPeminjaman != '') {
				$s1 .= " AND d.id = '$idPeminjaman' ";
			}

			$s1 .= " ORDER BY c.nama ASC ";
		
			$sheet1 = $db->query($s1);
			
			$sheet_total = $db->query("SELECT a.id FROM peminjaman_dosen a WHERE 1=1 ")->getNumRows();
		
			$result = array();
			$i=0;
			$no = (isset($start) ? $start + 1 : 1);
			
			foreach($sheet1->getResult() as $sheet)
			{
				if($sheet->ada_kembali == 0) {
					$ada = '<input type="checkbox" name="kodeItem[]" value="' . $sheet->kode_item . '" style="transform: scale(2);">';
				} else {
					$ada = '<input type="checkbox" style="transform: scale(2);" checked disabled>';
				}
				$result[$i]['0'] = $no;
				$result[$i]['1'] = $sheet->nama;
				$result[$i]['2'] = $sheet->kode_item;
				$result[$i]['3'] = $sheet->kode_kotak_penyimpanan;
				$result[$i]['4'] = $ada;
				$i++;
				$no++;
			}
			
			$data = $result;
			$results = array(
				"iTotalRecords" => ($sheet_total),
				"iTotalDisplayRecords" => ($sheet_total),
				"aaData"=>$data
			);
				
			echo json_encode($results);
    	}
    }

    public function loanedDosenKotakList()
    {
    	if(has_permission('lend'))
    	{
    		$db = \Config\Database::connect();
			$session = \Config\Services::session();
			
			//Searching
			
			$idLab = $this->request->getPost('id_lab');
			$idPeminjam = $this->request->getPost('id_peminjam');
			$selesai = $this->request->getPost('selesai');
			$idPeminjaman = $this->request->getPost('id_peminjaman');
			
			//Table Row
			
			$s1 = "
				SELECT a.kode_kotak_penyimpanan, a.ada_kembali, c.nama
				FROM peminjaman_dosen_kotak_penyimpanan a
				LEFT JOIN data_daftar_kotak_penyimpanan b ON(a.kode_kotak_penyimpanan = b.kode)
				LEFT JOIN data_kotak_penyimpanan c ON(b.id_kotak_penyimpanan = c.id)
				LEFT JOIN peminjaman_dosen d ON(a.id_peminjaman_dosen = d.id)
				LEFT JOIN data_lab e ON(d.id_lab = e.id)
				LEFT JOIN data_pengelola f ON(f.id_lab = e.id)
				WHERE d.nidn = '$idPeminjam'
			";

			if(!in_groups('admin')) {
				$s1 .= " AND f.id_pengelola = " . user_id();
			}

			if($idLab != '') {
				$s1 .= " AND d.id_lab = '$idLab' ";
			}

			if($selesai == 0) {
				$s1 .= " AND a.ada_kembali = 0 ";
			}

			if($idPeminjaman != '') {
				$s1 .= " AND d.id = '$idPeminjaman' ";
			}

			$s1 .= " ORDER BY c.nama ASC ";
		
			$sheet1 = $db->query($s1);
			
			$sheet_total = $db->query("SELECT a.id FROM peminjaman_dosen a WHERE 1=1 ")->getNumRows();
		
			$result = array();
			$i=0;
			$no = (isset($start) ? $start + 1 : 1);
			
			foreach($sheet1->getResult() as $sheet)
			{
				if($sheet->ada_kembali == 0) {
					$ada = '<input type="checkbox" name="kodeKotak[]" value="' . $sheet->kode_kotak_penyimpanan . '" style="transform: scale(2);">';
				} else {
					$ada = '<input type="checkbox" style="transform: scale(2);" checked disabled>';
				}
				$result[$i]['0'] = $no;
				$result[$i]['1'] = $sheet->nama;
				$result[$i]['2'] = $sheet->kode_kotak_penyimpanan;
				$result[$i]['3'] = $ada;
				$i++;
				$no++;
			}
			
			$data = $result;
			$results = array(
				"iTotalRecords" => ($sheet_total),
				"iTotalDisplayRecords" => ($sheet_total),
				"aaData"=>$data
			);
				
			echo json_encode($results);
    	}
    }

    public function getItem()
    {
    	if(has_permission('lend'))
    	{
    		$db = \Config\Database::connect();

    		$p = $this->request->getPost();
    		$kode = $p['kode'];

    		$peminjamanMhsItem = $db->query("SELECT * FROM peminjaman_mhs_item WHERE kode_item = '$kode' AND ada_kembali = 0");
    		$peminjamanMhsKotak = $db->query("SELECT * FROM peminjaman_mhs_kotak_penyimpanan WHERE kode_kotak_penyimpanan = '$kode' AND ada_kembali = 0");
    		$peminjamanDosenItem = $db->query("SELECT * FROM peminjaman_dosen_item WHERE kode_item = '$kode' AND ada_kembali = 0");
    		$peminjamanDosenKotak = $db->query("SELECT * FROM peminjaman_dosen_kotak_penyimpanan WHERE kode_kotak_penyimpanan = '$kode' AND ada_kembali = 0");
			
			if($peminjamanMhsItem->getNumRows() == 0 && $peminjamanMhsKotak->getNumRows() == 0 && $peminjamanDosenItem->getNumRows() == 0 && $peminjamanDosenKotak->getNumRows() == 0)
			{
	    		$item = $db->query("SELECT * FROM data_item WHERE kode = '$kode'");
	    		$kotak = $db->query("SELECT * FROM data_daftar_kotak_penyimpanan WHERE kode = '$kode'");

	    		if($item->getNumRows() != 0)
	    		{
	    			$idInventaris = $item->getRow()->id_inventaris;
	    			$row = $db->query("SELECT nama FROM data_inventaris WHERE id = '$idInventaris'")->getRow();

	    			$response = array(
	    				'jenis' => 'item',
	    				'nama' => $row->nama,
	    			);

	    			echo json_encode($response);
	    		}
	    		elseif ($kotak->getNumRows() != 0) {
	    			$idKotakPenyimpanan = $kotak->getRow()->id_kotak_penyimpanan;
	    			$rowKotak = $db->query("SELECT nama FROM data_kotak_penyimpanan WHERE id = '$idKotakPenyimpanan'")->getRow();
	    			$itemKotak = $db->table("data_item");
	    			$itemKotak->select('data_inventaris.nama, data_item.kode');
	    			$itemKotak->join('data_inventaris', 'data_item.id_inventaris = data_inventaris.id', 'left');
	    			$itemKotak->where('data_item.kode_kotak_penyimpanan', $kode);

	    			$response = array(
	    				'jenis' => 'kotak',
	    				'nama' => $rowKotak->nama,
	    				'item' => $itemKotak->get()->getResultArray()
	    			);
	    			
	    			echo json_encode($response);
	    		}
	    		else
	    		{
	    			$response = array(
	    				'jenis' => 'error',
	    				'nama' => 'Item atau kotak tidak ditemukan di sistem',
	    			);

	    			echo json_encode($response);
	    		}
	    	}
	    	else
	    	{
	    		$response = array(
    				'jenis' => 'error',
    				'nama' => 'Item atau kotak sedang dipinjam danm belum dikembalikan.',
    			);
    			
    			echo json_encode($response);
	    	}
    	}
    }
	
	public function return()
	{
		if(has_permission('lend'))
		{
			$data['title'] = 'Pengembalian';
			$data['open'] = 12;
			$data['active'] = 14;
			
			$db = \Config\Database::connect();
			$session = \Config\Services::session();
			
			$data['breadcrumbs'] = $db->query("SELECT name, href FROM menu WHERE id = ".$data['open']." OR id = ".$data['active']." ORDER BY parent_id ASC");

			$idLab = $this->request->getGet('id_lab');
			$idPeminjam = $this->request->getGet('id_peminjam');
			
			if(null === $idLab || $idLab == '') {
				$qLabs = "SELECT id, nama, deskripsi FROM data_lab ";
				if(!in_groups('admin')) {
					$qLabs .= " WHERE IF(EXISTS(SELECT c.id_pengelola FROM data_pengelola c WHERE c.id_lab = data_lab.id AND c.id_pengelola = '" . user_id() . "'), 1, 0) = 1 ";
				}
				$labs = $db->query($qLabs);
				if($labs->getNumRows() == 1) {
					return redirect()->to('/circulation/return?id_lab=' . $labs->getRow()->id);
				}
				$data['labs'] = $labs;
				return view('circulation/loan_lab', $data);
			}

			$data['idLab'] = $idLab;
			$data['namaLab'] = $db->query("SELECT deskripsi FROM data_lab WHERE id = $idLab ORDER BY deskripsi ASC")->getRow()->deskripsi;
			
			if(null === $this->request->getGet('id_peminjam')) {
				return view('circulation/loan_user', $data);
			}

			$mhs_row = $db->query("SELECT * FROM ref_student WHERE nim = '$idPeminjam'");
			
			if($mhs_row->getNumRows() != 0) {
				$mhs = $mhs_row->getRow();
				$data['id_peminjam'] = $mhs->nim;
				$data['nama'] = $mhs->name;
				$data['url_item'] = '/circulation/loanedMhsItemList';
				$data['url_kotak'] = '/circulation/loanedMhsKotakList';
				return view('circulation/return_item', $data);
			}
			
			$dosen_row = $db->query("SELECT * FROM ref_dosen WHERE nidn = '$idPeminjam'");
			
			if($dosen_row->getNumRows() != 0) {
				$dosen = $dosen_row->getRow();
				$data['id_peminjam'] = $dosen->nidn;
				$data['nama'] = $dosen->nama_sdm;
				$data['url_item'] = '/circulation/loanedDosenItemList';
				$data['url_kotak'] = '/circulation/loanedDosenKotakList';
				return view('circulation/return_item', $data);
			}
			
			return redirect()->back()->withInput()->with('error', 'Mahasiswa / Dosen tidak ditemukan');
		}
	}

	public function returnAction()
	{
		if(has_permission('lend')) {
			$db = \Config\Database::connect();
			$idPeminjam = $this->request->getPost('idPeminjam');
			$kodeItem = $this->request->getPost('kodeItem');
        	$kodeKotak = $this->request->getPost('kodeKotak');

        	$mhsRow = $db->query("SELECT * FROM ref_student WHERE nim = '$idPeminjam'");
        	$dosenRow = $db->query("SELECT * FROM ref_dosen WHERE nidn = '$idPeminjam'");

        	$tgl_kembali = date('Y-m-d H:i:s');

        	$id_peminjaman = array();

        	if($mhsRow->getNumRows() != 0) {
        		$builder = $db->table('peminjaman_mhs');
        		$builder->where('nim', $idPeminjam);

        		if (is_array($kodeItem)) {
        			$builderItem = $db->table('peminjaman_mhs_item');
        			$builderItem->whereIn('kode_item', $kodeItem);

        			foreach($builder->get()->getResult() as $row) {
        				$builderItemUpdate = clone $builderItem;
        				$builderItemUpdate->where('id_peminjaman_mhs', $row->id)->update([
        					'ada_kembali' => 1,
        					'tgl_kembali' => $tgl_kembali
        				]);
        			}

        			foreach($builderItem->get()->getResult() as $row) {
        				if(!in_array($row->id_peminjaman_mhs, $id_peminjaman)) {
        					array_push($id_peminjaman, $row->id_peminjaman_mhs);
        				}
        			}
	        	}

	        	if (is_array($kodeKotak)) {
	        		$builderKotak = $db->table('peminjaman_mhs_kotak_penyimpanan');
        			$builderKotak->whereIn('kode_kotak_penyimpanan', $kodeKotak);

        			foreach($builder->get()->getResult() as $row) {
        				$builderKotakUpdate = clone $builderKotak;
        				$builderKotakUpdate->where('id_peminjaman_mhs', $row->id)->update([
        					'ada_kembali' => 1,
        					'tgl_kembali' => $tgl_kembali
        				]);
        			}
	        	}
        	}

        	if($dosenRow->getNumRows() != 0) {
        		$builder = $db->table('peminjaman_dosen');
        		$builder->where('nidn', $idPeminjam);

        		if (is_array($kodeItem)) {
        			$builderItem = $db->table('peminjaman_dosen_item');
        			$builderItem->whereIn('kode_item', $kodeItem);
        			
        			foreach($builder->get()->getResult() as $row) {
        				$builderItemUpdate = clone $builderItem;
        				$builderItemUpdate->where('id_peminjaman_dosen', $row->id)->update([
        					'ada_kembali' => 1,
        					'tgl_kembali' => $tgl_kembali
        				]);
        			}

        			foreach($builderItem->get()->getResult() as $row) {
        				if(!in_array($row->id_peminjaman_dosen, $id_peminjaman)) {
        					array_push($id_peminjaman, $row->id_peminjaman_dosen);
        				}
        			}
	        	}

	        	if (is_array($kodeKotak)) {
	        		$builderKotak = $db->table('peminjaman_dosen_kotak_penyimpanan');
        			$builderKotak->whereIn('kode_kotak_penyimpanan', $kodeKotak);
        			
        			foreach($builder->get()->getResult() as $row) {
        				$builderKotakUpdate = clone $builderKotak;
        				$builderKotakUpdate->where('id_peminjaman_dosen', $row->id)->update([
        					'ada_kembali' => 1,
        					'tgl_kembali' => $tgl_kembali
        				]);
        			}
	        	}
        	}
	        if(count($id_peminjaman) != 0) {
		       	$builder->whereIn('id', $id_peminjaman);
		       	$builder->update(['tgl_kembali' => $tgl_kembali]);
		    }
		}
	}

    public function loanHistory()
    {
		if(has_permission('loanHistoryView'))
		{
			$data['title'] = 'Riwayat Peminjaman';
			$data['open'] = 12;
			$data['active'] = 16;
			
			$db = \Config\Database::connect();
			$session = \Config\Services::session();
			
			$data['breadcrumbs'] = $db->query("SELECT name, href FROM menu WHERE id = ".$data['open']." OR id = ".$data['active']." ORDER BY parent_id ASC");
			
			$data['searchRiwayatPeminjamanMhs'] = $session->get('searchRiwayatPeminjamanMhs');
			$data['startRiwayatPeminjamanMhs'] = $session->get('startRiwayatPeminjamanMhs');
			$data['lengthRiwayatPeminjamanMhs'] = $session->get('lengthRiwayatPeminjamanMhs');
			$data['orderColRiwayatPeminjamanMhs'] = $session->get('orderColRiwayatPeminjamanMhs');
			$data['orderDirRiwayatPeminjamanMhs'] = $session->get('orderDirRiwayatPeminjamanMhs');
			
			$data['searchRiwayatPeminjamanDosen'] = $session->get('searchRiwayatPeminjamanDosen');
			$data['startRiwayatPeminjamanDosen'] = $session->get('startRiwayatPeminjamanDosen');
			$data['lengthRiwayatPeminjamanDosen'] = $session->get('lengthRiwayatPeminjamanDosen');
			$data['orderColRiwayatPeminjamanDosen'] = $session->get('orderColRiwayatPeminjamanDosen');
			$data['orderDirRiwayatPeminjamanDosen'] = $session->get('orderDirRiwayatPeminjamanDosen');
			
			$data['tab'] = $session->get('loanedTab');
			
			return view('circulation/loan_history', $data);
		}
		else
		{
			return redirect()->back();
		}
    }

    public function loanHistoryDetailMhs($idPeminjaman)
	{
		if(has_permission('lend'))
		{
			$data['title'] = 'Riwayat Peminjaman';
			$data['open'] = 12;
			$data['active'] = 16;
			
			$db = \Config\Database::connect();
			$session = \Config\Services::session();

			$data['breadcrumbs'] = $db->query("SELECT name, href FROM menu WHERE id = ".$data['open']." OR id = ".$data['active']." ORDER BY parent_id ASC");

			$qPeminjaman = $db->query("
				SELECT a.nim, b.nama as nama_lab, b.id AS id_lab
				FROM peminjaman_dosen a
				LEFT JOIN data_lab b ON(a.id_lab = b.id)
				WHERE a.id = '$idPeminjaman'
			");

			if($qPeminjaman->getNumRows() == 0) {
				throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
			}

			$peminjaman = $qPeminjaman->getRow();

			$idPeminjam = $peminjaman->nidn;

			$peminjam = $db->query("
				SELECT b.nama_sdm
				FROM peminjaman_dosen a
				LEFT JOIN ref_dosen b ON(a.nim = b.nim)
				WHERE a.nim = $idPeminjam
			")->getRow();

			$data['idPeminjaman'] = $idPeminjaman;
			$data['namaLab'] = $peminjaman->nama_lab;
			$data['nama'] = $peminjam->nama_sdm;
			$data['id_peminjam'] = $idPeminjam;
			$data['idLab'] = $peminjaman->id_lab;
			$data['url_item'] = '/circulation/loanedDosenItemList';
			$data['url_kotak'] = '/circulation/loanedDosenKotakList';

			return view('circulation/loan_history_detail', $data);
		}
	}

	public function loanHistoryDetailDosen($idPeminjaman)
	{
		if(has_permission('lend'))
		{
			$data['title'] = 'Riwayat Peminjaman';
			$data['open'] = 12;
			$data['active'] = 16;
			
			$db = \Config\Database::connect();
			$session = \Config\Services::session();

			$data['breadcrumbs'] = $db->query("SELECT name, href FROM menu WHERE id = ".$data['open']." OR id = ".$data['active']." ORDER BY parent_id ASC");

			$qPeminjaman = $db->query("
				SELECT a.nidn, b.nama as nama_lab, b.id AS id_lab
				FROM peminjaman_dosen a
				LEFT JOIN data_lab b ON(a.id_lab = b.id)
				WHERE a.id = '$idPeminjaman'
			");

			if($qPeminjaman->getNumRows() == 0) {
				throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
			}

			$peminjaman = $qPeminjaman->getRow();

			$idPeminjam = $peminjaman->nidn;

			$peminjam = $db->query("
				SELECT b.nama_sdm
				FROM peminjaman_dosen a
				LEFT JOIN ref_dosen b ON(a.nidn = b.nidn)
				WHERE a.nidn = $idPeminjam
			")->getRow();

			$data['idPeminjaman'] = $idPeminjaman;
			$data['namaLab'] = $peminjaman->nama_lab;
			$data['nama'] = $peminjam->nama_sdm;
			$data['id_peminjam'] = $idPeminjam;
			$data['idLab'] = $peminjaman->id_lab;
			$data['url_item'] = '/circulation/loanedDosenItemList';
			$data['url_kotak'] = '/circulation/loanedDosenKotakList';

			return view('circulation/loan_history_detail', $data);
		}
	}
}
