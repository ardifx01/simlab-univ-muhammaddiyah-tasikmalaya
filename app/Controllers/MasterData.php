<?php

namespace App\Controllers;

use App\Models\Masterdata\RoomModel;

class MasterData extends BaseController
{
	public function student()
    {
		if(has_permission('masterDataStudentsView'))
		{
			$data['title'] = 'Students';
			$data['open'] = 7;
			$data['active'] = 8;
			
			$db = \Config\Database::connect();
			$session = \Config\Services::session();
			
			$data['breadcrumbs'] = $db->query("SELECT name, href FROM menu WHERE id = ".$data['open']." OR id = ".$data['active']." ORDER BY parent_id ASC");
			
			$data['search'] = $session->get('searchStudentList');
			$data['start'] = $session->get('startStudentList');
			$data['length'] = $session->get('lengthStudentList');
			$data['orderCol'] = $session->get('orderColStudentList');
			$data['orderDir'] = $session->get('orderDirStudentList');
			
			return view('masterdata/students', $data);
		}
		else
		{
			return redirect()->back();
		}
    }
	
	public function studentList()
    {
		if(has_permission('masterDataStudentsView'))
		{
			$db = \Config\Database::connect();
			$session = \Config\Services::session();
			
			//Searching
			
			$search = $db->escapeString($this->request->getPost('search'));
			$session->set('searchStudentList', $search);
			
			$sSearch = '';
			
			if(isset($search) && $search != '')
			{
				$sSearch .= " AND (a.nim LIKE '%".$search."%' OR a.name LIKE '%".$search."%' OR a.major LIKE '%".$search."%' OR a.yoe LIKE '%".$search."%') ";
			}
		
			//Limit
			
			$start = $this->request->getPost("start");
			$session->set('startStudentList', $start);
			$length = $this->request->getPost("length");
			$session->set('lengthStudentList', $length);
			
			if (isset($start) && $start != '-1' )
			{
				$sLimit = " LIMIT ".intval($start).", ".intval($length);
			}
			
			//Ordering
			
			$orderCol = $this->request->getPost("order[0][column]");
			$session->set('orderColStudentList', $orderCol);
			$orderDir = $this->request->getPost("order[0][dir]");
			$session->set('orderDirStudentList', $orderDir);
			$columnName = $this->request->getPost("columns[".$orderCol."][name]");
			
			if (isset($columnName) && $columnName != '')
			{
				$sOrder = " ORDER BY ".$columnName." ".$orderDir." ";
			}
			else
			{
				$sOrder = " ORDER BY a.major ASC, a.yoe DESC, a.name ASC ";
			}
			
			//Table Row
			
			$s1 = "SELECT a.id, a.nim, a.name, a.major, a.yoe FROM ref_student a WHERE 1=1 ";
			$s1 .= $sSearch;
			$s1 .= $sOrder;
			$s1 .= $sLimit;
		
			$sheet1 = $db->query($s1);
			
			$sheet_total = $db->query("SELECT a.id FROM ref_student a WHERE 1=1 ".$sSearch)->getNumRows();
		
			$result = array();
			$i=0;
			$no = (isset($start) ? $start + 1 : 1);
			
			foreach($sheet1->getResult() as $sheet)
			{
				$result[$i]['0'] = $no;
				$result[$i]['1'] = $sheet->nim;
				$result[$i]['2'] = $sheet->name;
				$result[$i]['3'] = $sheet->major;
				$result[$i]['4'] = $sheet->yoe;
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
	
	public function studentSync()
    {
		if(has_permission('masterDataStudentsSync'))
		{
			$db = \Config\Database::connect();
			
			$ch = curl_init('https://siakad.umtas.ac.id/live/token');
			
			$data = array(
				'grant_type' => 'client_credentials',
				'client_id' => 'mawaumtas',
				'client_secret' => 'umt@$m4w@'
			);
			
			$payload = json_encode($data);
			
			curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			
			$tokens = curl_exec($ch);
			$tokens = json_decode($tokens,TRUE);
			
			curl_close($ch);
			
			$auth = "Authorization: Bearer ".$tokens['access_token'];;
			
			$ch2 = curl_init('https://siakad.umtas.ac.id/live/datamhsmawa?showpage=1&limit=1000000');
			
			curl_setopt($ch2, CURLOPT_HTTPHEADER, array('Content-Type:application/json', $auth));
			curl_setopt($ch2, CURLOPT_RETURNTRANSFER, TRUE);
			
			$mhss = curl_exec($ch2);
			$mhss = json_decode($mhss,TRUE);
			
			curl_close($ch2);
			
			/*
			echo '<pre>';
			print_r($mhss);
			echo '</pre>';
			*/
			
			foreach($mhss['data'] as $mhs)
			{
				$nim = substr($mhs['nim'], 0, 11);
				$nim_row = $db->query("SELECT nim FROM ref_student WHERE nim = '".$nim."'")->getNumRows();
				
				if($nim_row == 0)
				{
					$db->query("
						INSERT INTO ref_student
						SET
							nim = '".$nim."',
							name = '".$db->escapeString($mhs['nama'])."',
							major = '".$mhs['namaprodi']."',
							yoe = '".substr($mhs['thnmasuk'], 0, 4)."'
					");
				}
				else
				{
					$db->query("
						UPDATE ref_student
						SET
							name = '".$db->escapeString($mhs['nama'])."',
							major = '".$mhs['namaprodi']."',
							yoe = '".substr($mhs['thnmasuk'], 0, 4)."'
						WHERE nim = '".$nim."'
					");
				}
			}
			
			return redirect()->to('/masterdata/students')->with('message', 'Sinkronisasi telah berhasil!');
		}
	}
	
	public function dosen()
    {
		if(has_permission('masterDataDosenView'))
		{
			$data['title'] = 'Dosen';
			$data['open'] = 7;
			$data['active'] = 17;
			
			$db = \Config\Database::connect();
			$session = \Config\Services::session();
			
			$data['breadcrumbs'] = $db->query("SELECT name, href FROM menu WHERE id = ".$data['open']." OR id = ".$data['active']." ORDER BY parent_id ASC");
			
			$data['search'] = $session->get('searchDosenList');
			$data['start'] = $session->get('startDosenList');
			$data['length'] = $session->get('lengthDosenList');
			$data['orderCol'] = $session->get('orderColDosenList');
			$data['orderDir'] = $session->get('orderDirDosenList');
			
			return view('masterdata/dosen', $data);
		}
		else
		{
			return redirect()->back();
		}
    }
	
	public function dosenList()
    {
		if(has_permission('masterDataDosenView'))
		{
			$db = \Config\Database::connect();
			$session = \Config\Services::session();
			
			//Searching
			
			$search = $db->escapeString($this->request->getPost('search'));
			$session->set('searchDosenList', $search);
			
			$sSearch = '';
			
			if(isset($search) && $search != '')
			{
				$sSearch .= " AND (a.nidn LIKE '%".$search."%' OR a.nama_sdm LIKE '%".$search."%') ";
			}
		
			//Limit
			
			$start = $this->request->getPost("start");
			$session->set('startDosenList', $start);
			$length = $this->request->getPost("length");
			$session->set('lengthDosenList', $length);
			
			if (isset($start) && $start != '-1' )
			{
				$sLimit = " LIMIT ".intval($start).", ".intval($length);
			}
			
			//Ordering
			
			$orderCol = $this->request->getPost("order[0][column]");
			$session->set('orderColDosenList', $orderCol);
			$orderDir = $this->request->getPost("order[0][dir]");
			$session->set('orderDirDosenList', $orderDir);
			$columnName = $this->request->getPost("columns[".$orderCol."][nama_sdm]");
			
			if (isset($columnName) && $columnName != '')
			{
				$sOrder = " ORDER BY ".$columnName." ".$orderDir." ";
			}
			else
			{
				$sOrder = " ORDER BY a.nama_sdm ASC ";
			}
			
			//Table Row
			
			$s1 = "SELECT a.nidn, a.nama_sdm, a.jenis_sdm FROM ref_dosen a WHERE 1=1 ";
			$s1 .= $sSearch;
			$s1 .= $sOrder;
			$s1 .= $sLimit;
		
			$sheet1 = $db->query($s1);
			
			$sheet_total = $db->query("SELECT a.id FROM ref_dosen a WHERE 1=1 ".$sSearch)->getNumRows();
		
			$result = array();
			$i=0;
			$no = (isset($start) ? $start + 1 : 1);
			
			foreach($sheet1->getResult() as $sheet)
			{
				$result[$i]['0'] = $no;
				$result[$i]['1'] = $sheet->nidn;
				$result[$i]['2'] = $sheet->nama_sdm;
				$result[$i]['3'] = $sheet->jenis_sdm;
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
	
	public function dosenSync()
    {
		if(has_permission('masterDataDosenSync'))
		{
			$db = \Config\Database::connect();
			$session = \Config\Services::session();
			
			$login = curl_init(env('SISTER_API_URL') . '/authorize');
			
			$data = array(
				'username' => env('SISTER_DEVELOPER_USERNAME'),
				'password' => env('SISTER_DEVELOPER_PASSWORD'),
				'id_pengguna' => env('SISTER_DEVELOPER_ID_PENGGUNA')
			);
			
			$payload = json_encode($data);
			
			curl_setopt($login, CURLOPT_POSTFIELDS, $payload);
			curl_setopt($login, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
			curl_setopt($login, CURLOPT_RETURNTRANSFER, TRUE);
			
			$tokens	= curl_exec($login);
			$info	= curl_getinfo($login);
			$tokens	= json_decode($tokens,TRUE);
			
			curl_close($login);
			
			$http_code = $info["http_code"];
			
			if($http_code != 200)
			{
				return redirect()->to('/masterdata/dosen')->with('error', 'Error ' . $http_code);
			}
			else
			{
				$token	= $tokens['token'];
				$session->set('sisterSyncToken', $token);
				
				$auth	= "Authorization: Bearer ".$token;
				$ch		= curl_init(env('SISTER_API_URL') . '/referensi/sdm');
				
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json', $auth));
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
				
				$data = curl_exec($ch);
				$data = json_decode($data,TRUE);
				
				curl_close($ch);
				
				foreach($data as $row)
				{
					$rows = $db->query("SELECT id FROM ref_dosen WHERE nidn = '" . $row['nidn'] . "'")->getNumRows();
					
					$nama_sdm	= trim($db->escapeString($row['nama_sdm']));
					$nidn		= trim($row['nidn']);
					$jenis_sdm	= $row['jenis_sdm'];
					
					if($rows == 0)
					{
						$db->query("
							INSERT INTO ref_dosen
							SET
								nama_sdm = '$nama_sdm',
								nidn = '$nidn',
								jenis_sdm = '$jenis_sdm'
						");
					}
					else
					{
						$db->query("
							UPDATE ref_dosen
							SET
								nama_sdm = '$nama_sdm',
								jenis_sdm = '$jenis_sdm'
							WHERE nidn = '$nidn'
						");
					}
				}
				//dd($data);
				return redirect()->to('/masterdata/dosen')->with('message', 'Sinkronisasi telah berhasil!');
			}
		}
	}
	
}
