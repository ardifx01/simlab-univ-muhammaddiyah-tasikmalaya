<?php

namespace App\Controllers;

use Picqer;
use Dompdf\Dompdf;

use App\Models\Data\LabModel;
use App\Models\Data\RoomModel;
use App\Models\Data\InventarisModel;
use App\Models\Data\KotakPenyimpananModel;
use App\Models\Data\LabManagerModel;

class Data extends BaseController
{
	public function labs()
    {
		if(has_permission('labView'))
		{
			$data['title'] = 'Data Laboraturium';
			$data['open'] = 9;
			$data['active'] = 10;
			
			$db = \Config\Database::connect();
			$session = \Config\Services::session();
			
			$data['breadcrumbs'] = $db->query("SELECT name, href FROM menu WHERE id = ".$data['open']." OR id = ".$data['active']." ORDER BY parent_id ASC");
			
			$data['search'] = $session->get('searchDataLabList');
			$data['start'] = $session->get('startDataLabList');
			$data['length'] = $session->get('lengthDataLabList');
			$data['orderCol'] = $session->get('orderColDataLabList');
			$data['orderDir'] = $session->get('orderDirDataLabList');
			
			return view('data/labs', $data);
		}
		else
		{
			return redirect()->back();
		}
    }
	
	public function labList()
    {
		if(has_permission('labView'))
		{
			$db = \Config\Database::connect();
			$session = \Config\Services::session();
			
			//Searching
			
			$search = $db->escapeString($this->request->getPost('search'));
			$session->set('searchDataLabList', $search);
			
			$sSearch = '';
			
			if(isset($search) && $search != '')
			{
				$sSearch .= " AND (a.nama LIKE '%".$search."%' OR a.deskripsi LIKE '%".$search."%') ";
			}
			
			//Limit
			
			$start = $this->request->getPost("start");
			$session->set('startDataLabList', $start);
			$length = $this->request->getPost("length");
			$session->set('lengthDataLabList', $length);
			
			if (isset($start) && $start != '-1' )
			{
				$sLimit = " LIMIT ".intval($start).", ".intval($length);
			}
			
			//Ordering
			
			$orderCol = $this->request->getPost("order[0][column]");
			$session->set('orderColDataLabList', $orderCol);
			$orderDir = $this->request->getPost("order[0][dir]");
			$session->set('orderDirDataLabList', $orderDir);
			$columnName = $this->request->getPost("columns[".$orderCol."][name]");
			
			if (isset($columnName) && $columnName != '')
			{
				$sOrder = " ORDER BY ".$columnName." ".$orderDir." ";
			}
			else
			{
				$sOrder = " ORDER BY a.nama ASC, a.deskripsi ASC ";
			}
			
			//Table Row
			
			$s1 = "
				SELECT a.id, a.nama, a.deskripsi, a.lantai_awal, a.jml_lantai, COUNT(b.id) AS jml_ruangan
				FROM data_lab a
				LEFT JOIN data_room b ON(b.id_lab = a.id)
				WHERE 1=1
			";
			if(!in_groups('admin')) {
				$s1 .= " AND IF(EXISTS(SELECT c.id_pengelola FROM data_pengelola c WHERE c.id_lab = a.id AND c.id_pengelola = '".user_id()."'), 1, 0) = 1 ";
			}
			
			$s1 .= $sSearch;
			$s1 .= " GROUP BY b.id_lab ";
			$s1 .= $sOrder;
			$s1 .= $sLimit;
		
			$sheet1 = $db->query($s1);
			
			$sheet_total = $db->query("SELECT a.id FROM data_lab a WHERE 1=1 ".$sSearch)->getNumRows();
		
			$result = array();
			$i=0;
			$no = (isset($start) ? $start + 1 : 1);
			foreach($sheet1->getResult() as $sheet)
			{
				$inventaris_button = '<button id="'.$sheet->id.'" class="btn btn-success inventaris" title="Inventaris"><i class="fa fa-folder"></i> Inventaris</button>';
				$rooms_button = '<button id="'.$sheet->id.'" class="btn btn-primary rooms" title="Ruangan"><i class="fa fa-hotel"></i> Ruangan</button>';
				
				if(has_permission('labEdit'))
				{
					$edit_button = '<button id="'.$sheet->id.'" class="btn btn-info edit" title="edit"><i class="fa fa-edit"></i></button>';
					$managers_button = '<button id="'.$sheet->id.'" class="btn btn-warning manager" title="Pengelola"><i class="fa fa-users"></i></button>';
				}
				else
				{
					$edit_button = '';
					$managers_button = '';
				}
				
				if(has_permission('labDelete'))
				{
					$delete_button = '<button id="'.$sheet->id.'" class="btn btn-danger delete" title="hapus data lab '.$sheet->nama.'"><i class="fa fa-trash"></i></button>';
				}
				else
				{
					$delete_button = '';
				}
				
				$result[$i]['0'] = $no;
				$result[$i]['1'] = $sheet->nama;
				$result[$i]['2'] = $sheet->deskripsi;
				$result[$i]['3'] = $sheet->lantai_awal;
				$result[$i]['4'] = $sheet->jml_lantai;
				$result[$i]['5'] = $sheet->jml_ruangan;
				$result[$i]['6'] = '
					<div class="text-right">
						'.$inventaris_button.' '.$rooms_button.' '.$managers_button.' '.$edit_button.' '.$delete_button.'
						<script>
						$(".inventaris").click(function(){var id = $(this).attr("id");window.location.assign("/data/inventaris/"+id);});
						$(".rooms").click(function(){var id = $(this).attr("id");window.location.assign("/data/rooms/"+id);});
						$(".manager").click(function(){var id = $(this).attr("id");window.location.assign("/data/labManagers/"+id);});
						$(".edit").click(function(){var id = $(this).attr("id");window.location.assign("/data/labEdit/"+id);});
						$(".delete").click(function() {var id = $(this).attr("id");var title = $(this).attr("title");
						swal({title: "Apakah anda yakin akan meng"+title+"?", text: "Semua data akan dihapus dan tidak dapat dikembalikan!", icon: "warning",
						buttons:{cancel: {visible: true, text : "Batal", className: "btn" },
						confirm: {text : "Hapus", className : "btn btn-danger"}}}).then((willDelete) => {if (willDelete) {
						$.ajax({url: "/data/labDelete", type: "POST", data: {"id":id}, cache: false});oTable.fnDraw();}});});
						</script>
					</div>
				';
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
	
	public function labAdd()
	{
		if(has_permission('labAdd'))
		{
			$data['title'] = 'Tambah Data';
			$data['open'] = 9;
			$data['active'] = 10;
			
			$db = \Config\Database::connect();
			
			$data['breadcrumbs'] = $db->query("SELECT name, href FROM menu WHERE id = ".$data['open']." OR id = ".$data['active']." ORDER BY parent_id ASC");
			
			if($this->request->getMethod() == 'POST')
			{
				$validation = \Config\Services::validation();
				
				$validation->setRules([
					'nama' => ['label' => 'Nama', 'rules' => 'required']
				]);
				
				$isDataValid = $validation->withRequest($this->request)->run();
				
				if($isDataValid)
				{
					$labModel = new LabModel();
					
					$labModel->insert([
						'nama'				=> $this->request->getPost('nama'),
						'deskripsi'			=> $this->request->getPost('deskripsi'),
						'lantai_awal'		=> $this->request->getPost('lantai_awal'),
						'jml_lantai'		=> $this->request->getPost('jml_lantai'),
						'added_by'			=> user_id(),
						'added_date'		=> date('Y-m-d H:i:s'),
						'updated_by'		=> user_id(),
						'updated_date'		=> date('Y-m-d H:i:s')
					]);
					
					return redirect()->to('/data/labs')->with('message', 'Data telah berhasil ditambahkan!');
				}
				else
				{
					return redirect()->back()->withInput()->with('errors', $validation->getErrors());
				}
			}
			
			return view('data/lab_add', $data);
		}
		else
		{
			return redirect()->back();
		}
	}
	
	public function labEdit($id = null)
	{
		if(has_permission('labEdit'))
		{
			$data['title'] = 'Edit Data';
			$data['open'] = 9;
			$data['active'] = 10;
			
			$db = \Config\Database::connect();
			
			$data['breadcrumbs'] = $db->query("SELECT name, href FROM menu WHERE id = ".$data['open']." OR id = ".$data['active']." ORDER BY parent_id ASC");
			
			if($this->request->getMethod() == 'POST')
			{
				$validation =  \Config\Services::validation();
				
				$validation->setRules([
					'nama' => ['label' => 'Nama', 'rules' => 'required']
				]);
				
				$isDataValid = $validation->withRequest($this->request)->run();
				
				$labModel = new LabModel();
				
				if($isDataValid)
				{
					$labModel->update($id, [
						'nama'				=> $this->request->getPost('nama'),
						'deskripsi'			=> $this->request->getPost('deskripsi'),
						'lantai_awal'		=> $this->request->getPost('lantai_awal'),
						'jml_lantai'		=> $this->request->getPost('jml_lantai'),
						'added_by'			=> user_id(),
						'added_date'		=> date('Y-m-d H:i:s'),
						'updated_by'		=> user_id(),
						'updated_date'		=> date('Y-m-d H:i:s')
					]);
					
					return redirect()->to('/data/labs')->with('message', 'Perubahan berhasil disimpan!');
				}
				else
				{
					return redirect()->back()->withInput()->with('errors', $validation->getErrors());
				}
			}
			
			$data['labRow'] = $db->query("SELECT id, nama, deskripsi, lantai_awal, jml_lantai FROM data_lab WHERE id = $id")->getRow();
			
			return view('data/lab_edit', $data);
		}
		else
		{
			return redirect()->back();
		}
	}
	
	public function labDelete()
	{
		if(has_permission('labDelete'))
		{
			$id = $this->request->getPost('id');
			
			$db = \Config\Database::connect();
			
			$db->query("DELETE FROM data_room WHERE id_lab = $id");
			$db->query("DELETE FROM data_pengelola WHERE id_lab = $id");
			$data_inventaris = $db->query("SELECT id, id_lab FROM data_inventaris WHERE id_lab = $id");
			
			foreach($data_inventaris->getResult() as $r) {
				$db->query("DELETE FROM data_item WHERE id_inventaris = $r->id");
			}
			
			$labModel = new LabModel();
			$labModel->delete($id);
		}
    }
	
	public function rooms($id_lab = null)
    {
		$db = \Config\Database::connect();
		
		$pengelola = $db->query("SELECT id_pengelola FROM data_pengelola WHERE id_lab = '$id_lab' AND id_pengelola = ".user_id())->getNumRows();
		
		if(has_permission('roomView') && ($pengelola != 0 || in_groups('admin')))
		{
			$data['title'] = 'Data Ruangan';
			$data['open'] = 9;
			$data['active'] = 10;
			
			$session = \Config\Services::session();
			
			$data['breadcrumbs'] = $db->query("SELECT name, href FROM menu WHERE id = ".$data['open']." OR id = ".$data['active']." ORDER BY parent_id ASC");
			
			if(!isset($id_lab) || $id_lab == null || $id_lab == '')
			{
				$data['search'] = $session->get('searchDataLabList');
				$data['start'] = $session->get('startDataLabList');
				$data['length'] = $session->get('lengthDataLabList');
				$data['orderCol'] = $session->get('orderColDataLabList');
				$data['orderDir'] = $session->get('orderDirDataLabList');
				
				return view('data/labs', $data);
			}
			
			else
			{
				$data['search'] = $session->get('searchDataRoomList'.$id_lab);
				$data['start'] = $session->get('startDataRoomList'.$id_lab);
				$data['length'] = $session->get('lengthDataRoomList'.$id_lab);
				$data['orderCol'] = $session->get('orderColDataRoomList'.$id_lab);
				$data['orderDir'] = $session->get('orderDirDataRoomList'.$id_lab);
				$data['id_lab'] = $id_lab;
				$data['nama_lab'] = $db->query("SELECT nama FROM data_lab WHERE id = '$id_lab'")->getRow()->nama;
				
				return view('data/rooms', $data);
			}
		}
		else
		{
			return redirect()->back();
		}
    }
	
	public function roomList()
    {
		$db = \Config\Database::connect();
		
		$id_lab = $db->escapeString($this->request->getPost('id_lab'));
		$pengelola = $db->query("SELECT id_pengelola FROM data_pengelola WHERE id_lab = '$id_lab' AND id_pengelola = ".user_id())->getNumRows();
		
		if(has_permission('roomView') && ($pengelola != 0 || in_groups('admin')))
		{
			$session = \Config\Services::session();
			
			$id_lab = $db->escapeString($this->request->getPost('id_lab'));
			
			//Searching
			
			$search = $db->escapeString($this->request->getPost('search'));
			$session->set('searchDataRoomList'.$id_lab, $search);
			
			$sSearch = '';
			
			if(isset($search) && $search != '')
			{
				$sSearch .= " AND (a.nama LIKE '%".$search."%' OR a.deskripsi LIKE '%".$search."%') ";
			}
			
			//Limit
			
			$start = $this->request->getPost("start");
			$session->set('startDataRoomList'.$id_lab, $start);
			$length = $this->request->getPost("length");
			$session->set('lengthDataRoomList'.$id_lab, $length);
			
			if (isset($start) && $start != '-1' )
			{
				$sLimit = " LIMIT ".intval($start).", ".intval($length);
			}
			
			//Ordering
			
			$orderCol = $this->request->getPost("order[0][column]");
			$session->set('orderColDataRoomList'.$id_lab, $orderCol);
			$orderDir = $this->request->getPost("order[0][dir]");
			$session->set('orderDirDataRoomList'.$id_lab, $orderDir);
			$columnName = $this->request->getPost("columns[".$orderCol."][name]");
			
			if (isset($columnName) && $columnName != '')
			{
				$sOrder = " ORDER BY ".$columnName." ".$orderDir." ";
			}
			else
			{
				$sOrder = " ORDER BY a.lantai ASC, a.nama ASC ";
			}
			
			//Table Row
			
			$s1 = "	SELECT a.id, a.id_lab, a.nama, a.deskripsi, a.lantai
					FROM data_room a
                    LEFT JOIN data_lab b ON(a.id_lab = b.id)
					WHERE a.id_lab = $id_lab
			";
			$s1 .= $sSearch;
			$s1 .= $sOrder;
			$s1 .= $sLimit;
		
			$sheet1 = $db->query($s1);
			
			$sheet_total = $db->query("SELECT a.id FROM data_room a LEFT JOIN data_lab b ON(a.id_lab = b.id) WHERE a.id_lab = $id_lab")->getNumRows();
		
			$result = array();
			$i=0;
			$no = (isset($start) ? $start + 1 : 1);
			foreach($sheet1->getResult() as $sheet)
			{
				if(has_permission('roomEdit'))
				{
					$edit_button = '<button id="'.$sheet->id.'" class="btn btn-info btn-xs edit" title="edit"><i class="fa fa-edit"></i></button>';
				}
				else
				{
					$edit_button = '';
				}
				
				if(has_permission('roomDelete'))
				{
					$delete_button = '<button id="'.$sheet->id.'" class="btn btn-danger btn-xs delete" title="hapus data ruangan '.$sheet->nama.'"><i class="fa fa-trash"></i></button>';
				}
				else
				{
					$delete_button = '';
				}
				
				$result[$i]['0'] = $no;
				$result[$i]['1'] = $sheet->nama;
				$result[$i]['2'] = $sheet->deskripsi;
				$result[$i]['3'] = $sheet->lantai;
				$result[$i]['4'] = '
					<div class="text-right">
						'.$edit_button.' '.$delete_button.'
						<script>
						$(".edit").click(function(){var id = $(this).attr("id");window.location.assign("/data/roomEdit/'.$sheet->id_lab.'/"+id);});
						$(".delete").click(function() {var id = $(this).attr("id");var title = $(this).attr("title");
						swal({title: "Apakah anda yakin akan meng"+title+"?", text: "Semua data akan dihapus dan tidak dapat dikembalikan!", icon: "warning",
						buttons:{cancel: {visible: true, text : "Batal", className: "btn" },
						confirm: {text : "Hapus", className : "btn btn-danger"}}}).then((willDelete) => {if (willDelete) {
						$.ajax({url: "/data/roomDelete", type: "POST", data: {"id":id}, cache: false});oTable.fnDraw();}});});
						</script>
					</div>
				';
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
	
	public function roomAdd($id_lab = null)
	{
		$db = \Config\Database::connect();
		
		$pengelola = $db->query("SELECT id_pengelola FROM data_pengelola WHERE id_lab = '$id_lab' AND id_pengelola = ".user_id())->getNumRows();
		
		if(has_permission('roomAdd') && ($pengelola != 0 || in_groups('admin')))
		{
			$data['title'] = 'Tambah Data';
			$data['open'] = 9;
			$data['active'] = 10;
			
			$data['breadcrumbs'] = $db->query("SELECT name, href FROM menu WHERE id = ".$data['open']." OR id = ".$data['active']." ORDER BY parent_id ASC");
			
			if($this->request->getMethod() == 'POST')
			{
				$validation =  \Config\Services::validation();
				
				$validation->setRules([
					'nama' => ['label' => 'Nama', 'rules' => 'required']
				]);
				
				$isDataValid = $validation->withRequest($this->request)->run();
				
				if($isDataValid)
				{
					$roomModel = new RoomModel();
					
					$roomModel->insert([
						'id_lab'			=> $id_lab,
						'nama'				=> $this->request->getPost('nama'),
						'deskripsi'			=> $this->request->getPost('deskripsi'),
						'lantai'			=> $this->request->getPost('lantai'),
						'added_by'			=> user_id(),
						'added_date'		=> date('Y-m-d H:i:s'),
						'updated_by'		=> user_id(),
						'updated_date'		=> date('Y-m-d H:i:s')
					]);
					
					return redirect()->to('/data/rooms/'.$id_lab)->with('message', 'Data telah berhasil ditambahkan!');
				}
				else
				{
					return redirect()->back()->withInput()->with('errors', $validation->getErrors());
				}
			}
			
			$data['nama_lab'] = $db->query("SELECT nama FROM data_lab WHERE id = '$id_lab'")->getRow()->nama;
			$data['roomRow'] = $db->query("SELECT lantai_awal, jml_lantai FROM data_lab WHERE id = '$id_lab'")->getRow();
			return view('data/room_add', $data);
		}
		else
		{
			return redirect()->back();
		}
	}
	
	public function roomEdit($id_lab = null, $id = null)
	{
		$db = \Config\Database::connect();
		
		$pengelola = $db->query("SELECT id_pengelola FROM data_pengelola WHERE id_lab = '$id_lab' AND id_pengelola = ".user_id())->getNumRows();
		
		if(has_permission('roomEdit') && ($pengelola != 0 || in_groups('admin')))
		{
			$data['title'] = 'Edit Data';
			$data['open'] = 9;
			$data['active'] = 10;
			
			$db = \Config\Database::connect();
			
			$data['breadcrumbs'] = $db->query("SELECT name, href FROM menu WHERE id = ".$data['open']." OR id = ".$data['active']." ORDER BY parent_id ASC");
			
			if($this->request->getMethod() == 'POST')
			{
				$validation =  \Config\Services::validation();
				
				$validation->setRules([
					'nama' => ['label' => 'Nama', 'rules' => 'required']
				]);
				
				$isDataValid = $validation->withRequest($this->request)->run();
				
				$roomModel = new RoomModel();
				
				if($isDataValid)
				{
					$roomModel->update($id, [
						'id_lab'			=> $id_lab,
						'nama'				=> $this->request->getPost('nama'),
						'deskripsi'			=> $this->request->getPost('deskripsi'),
						'lantai'			=> $this->request->getPost('lantai'),
						'added_by'			=> user_id(),
						'added_date'		=> date('Y-m-d H:i:s'),
						'updated_by'		=> user_id(),
						'updated_date'		=> date('Y-m-d H:i:s')
					]);
					
					return redirect()->to('/data/rooms/'.$id_lab)->with('message', 'Perubahan berhasil disimpan!');
				}
				else
				{
					return redirect()->back()->withInput()->with('errors', $validation->getErrors());
				}
			}
			
			$data['nama_lab'] = $db->query("SELECT nama FROM data_lab WHERE id = '$id_lab'")->getRow()->nama;
			$data['roomRow'] = $db->query("	SELECT a.id, a.id_lab, a.nama, a.deskripsi, a.lantai, b.lantai_awal, b.jml_lantai
											FROM data_room a
											LEFT JOIN data_lab b ON(a.id_lab = b.id)
											WHERE a.id = $id
			")->getRow();
			
			return view('data/room_edit', $data);
		}
		else
		{
			return redirect()->back();
		}
	}
	
	public function roomDelete()
	{
		$db = \Config\Database::connect();
		
		$pengelola = $db->query("SELECT id_pengelola FROM data_pengelola WHERE id_lab = '$id_lab' AND id_pengelola = ".user_id())->getNumRows();
		
		if(has_permission('roomDelete') && ($pengelola != 0 || in_groups('admin')))
		{
			$id = $this->request->getPost('id');
			
			$roomModel = new RoomModel();
			$roomModel->delete($id);
		}
    }
	
	public function inventaris($id_lab = null)
    {
		$db = \Config\Database::connect();
		
		$pengelola = $db->query("SELECT id_pengelola FROM data_pengelola WHERE id_lab = '$id_lab' AND id_pengelola = ".user_id())->getNumRows();
		
		if(has_permission('inventarisView') && ($pengelola != 0 || in_groups('admin')))
		{
			$data['title'] = 'Data Inventaris';
			$data['open'] = 9;
			$data['active'] = 10;
			
			$session = \Config\Services::session();
			
			$data['breadcrumbs'] = $db->query("SELECT name, href FROM menu WHERE id = ".$data['open']." OR id = ".$data['active']." ORDER BY parent_id ASC");
			
			$data['searchInventaris'] = $session->get('searchDataInventarisList'.$id_lab);
			$data['startInventaris'] = $session->get('startDataInventarisList'.$id_lab);
			$data['lengthInventaris'] = $session->get('lengthDataInventarisList'.$id_lab);
			$data['orderColInventaris'] = $session->get('orderColDataInventarisList'.$id_lab);
			$data['orderDirInventaris'] = $session->get('orderDirDataInventarisList'.$id_lab);
			
			$data['searchItem'] = $session->get('searchDataItemList'.$id_lab);
			$data['startItem'] = $session->get('startDataItemList'.$id_lab);
			$data['lengthItem'] = $session->get('lengthDataItemList'.$id_lab);
			$data['orderColItem'] = $session->get('orderColDataItemList'.$id_lab);
			$data['orderDirItem'] = $session->get('orderDirDataItemList'.$id_lab);
			
			$data['searchKotakPenyimpanan'] = $session->get('searchDataKotakPenyimpananList'.$id_lab);
			$data['startKotakPenyimpanan'] = $session->get('startDataKotakPenyimpananList'.$id_lab);
			$data['lengthKotakPenyimpanan'] = $session->get('lengthDataKotakPenyimpananList'.$id_lab);
			$data['orderColKotakPenyimpanan'] = $session->get('orderColDataKotakPenyimpananList'.$id_lab);
			$data['orderDirKotakPenyimpanan'] = $session->get('orderDirDataKotakPenyimpananList'.$id_lab);
			
			$data['searchDaftarKotakPenyimpanan'] = $session->get('searchDataDaftarKotakPenyimpananList'.$id_lab);
			$data['startDaftarKotakPenyimpanan'] = $session->get('startDataDaftarKotakPenyimpananList'.$id_lab);
			$data['lengthDaftarKotakPenyimpanan'] = $session->get('lengthDataDaftarKotakPenyimpananList'.$id_lab);
			$data['orderColDaftarKotakPenyimpanan'] = $session->get('orderColDataDaftarKotakPenyimpananList'.$id_lab);
			$data['orderDirDaftarKotakPenyimpanan'] = $session->get('orderDirDataDaftarKotakPenyimpananList'.$id_lab);
			
			$data['tab'] = $session->get('tab');
			$data['id_lab'] = $id_lab;
			$data['nama_lab'] = $db->query("SELECT nama FROM data_lab WHERE id = '$id_lab'")->getRow()->nama;
			
			return view('data/inventaris', $data);
		}
		else
		{
			return redirect()->back();
		}
    }
	
	public function inventarisList()
    {
		$db = \Config\Database::connect();
		
		$id_lab = $db->escapeString($this->request->getPost('id_lab'));
		$pengelola = $db->query("SELECT id_pengelola FROM data_pengelola WHERE id_lab = '$id_lab' AND id_pengelola = ".user_id())->getNumRows();
		
		if(has_permission('inventarisView') && ($pengelola != 0 || in_groups('admin')))
		{
			$session = \Config\Services::session();
			
			$id_lab = $db->escapeString($this->request->getPost('id_lab'));
			
			//Searching
			
			$search = $db->escapeString($this->request->getPost('search'));
			$session->set('searchDataInventarisList'.$id_lab, $search);
			
			$sSearch = '';
			
			if(isset($search) && $search != '')
			{
				$sSearch .= " AND (a.nama LIKE '%".$search."%' OR a.deskripsi LIKE '%".$search."%' OR a.code_prefix LIKE '%".$search."%' OR a.code_suffix LIKE '%".$search."%') ";
			}
			
			//Limit
			
			$start = $this->request->getPost("start");
			$session->set('startDataInventarisList'.$id_lab, $start);
			$length = $this->request->getPost("length");
			$session->set('lengthDataInventarisList'.$id_lab, $length);
			
			if (isset($start) && $start != '-1' )
			{
				$sLimit = " LIMIT ".intval($start).", ".intval($length);
			}
			
			//Ordering
			
			$orderCol = $this->request->getPost("order[0][column]");
			$session->set('orderColDataInventarisList'.$id_lab, $orderCol);
			$orderDir = $this->request->getPost("order[0][dir]");
			$session->set('orderDirDataInventarisList'.$id_lab, $orderDir);
			$columnName = $this->request->getPost("columns[".$orderCol."][name]");
			
			if (isset($columnName) && $columnName != '')
			{
				$sOrder = " ORDER BY ".$columnName." ".$orderDir." ";
			}
			else
			{
				$sOrder = " ORDER BY a.nama ASC ";
			}
			
			//Table Row
			
			$s1 = "
				SELECT a.id, a.id_lab, a.nama, a.deskripsi, a.code_prefix, a.code_suffix, a.code_length, COUNT(c.id_inventaris) AS jml_item
				FROM data_inventaris a
				LEFT JOIN data_lab b ON(a.id_lab = b.id)
				LEFT JOIN data_item c ON(c.id_inventaris = a.id)
				WHERE a.id_lab = $id_lab
			";
			$s1 .= $sSearch;
			$s1 .= " GROUP BY a.id ";
			$s1 .= $sOrder;
			$s1 .= $sLimit;
		
			$sheet1 = $db->query($s1);
			
			$sheet_total = $db->query("SELECT a.id FROM data_inventaris a LEFT JOIN data_lab b ON(a.id_lab = b.id) WHERE a.id_lab = $id_lab".$sSearch)->getNumRows();
		
			$result = array();
			$i=0;
			$no = (isset($start) ? $start + 1 : 1);
			foreach($sheet1->getResult() as $sheet)
			{
				if(has_permission('inventarisEdit'))
				{
					$edit_button = '<button id="'.$sheet->id.'" class="btn btn-info btn-xs edit-inventaris" title="edit"><i class="fa fa-edit"></i></button>';
				}
				else
				{
					$edit_button = '';
				}
				
				if(has_permission('inventarisDelete'))
				{
					$delete_button = '<button id="'.$sheet->id.'" class="btn btn-danger btn-xs delete-inventaris" title="hapus data inventaris '.$sheet->nama.'"><i class="fa fa-trash"></i></button>';
				}
				else
				{
					$delete_button = '';
				}
				
				$result[$i]['0'] = $no;
				$result[$i]['1'] = $sheet->nama;
				$result[$i]['2'] = $sheet->deskripsi;
				$result[$i]['3'] = $sheet->code_prefix;
				$result[$i]['4'] = $sheet->code_suffix;
				$result[$i]['5'] = $sheet->code_length;
				$result[$i]['6'] = $sheet->jml_item;
				$result[$i]['7'] = '
					<div class="text-right">
						'.$edit_button.' '.$delete_button.'
						<script>
						$(".edit-inventaris").click(function(){var id = $(this).attr("id");window.location.assign("/data/inventarisEdit/'.$sheet->id_lab.'/"+id);});
						$(".delete-inventaris").click(function() {var id = $(this).attr("id");var title = $(this).attr("title");
						swal({title: "Apakah anda yakin akan meng"+title+"?", text: "Semua data akan dihapus dan tidak dapat dikembalikan!", icon: "warning",
						buttons:{cancel: {visible: true, text : "Batal", className: "btn" }, confirm: {text : "Hapus", className : "btn btn-danger"}}})
						.then((willDelete) => {if (willDelete) {$.ajax({url: "/data/inventarisDelete", type: "POST", data: {"id":id, "id_lab":'.$sheet->id_lab.'},
						success: function(data){oTable.fnStandingRedraw();}, cache: false});}});});
						</script>
					</div>
				';
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
	
	public function inventarisAdd($id_lab = null)
	{
		$db = \Config\Database::connect();
		
		$pengelola = $db->query("SELECT id_pengelola FROM data_pengelola WHERE id_lab = '$id_lab' AND id_pengelola = ".user_id())->getNumRows();
		
		if(has_permission('inventarisAdd') && ($pengelola != 0 || in_groups('admin')))
		{
			$data['title'] = 'Tambah Data';
			$data['open'] = 9;
			$data['active'] = 10;
			
			$data['breadcrumbs'] = $db->query("SELECT name, href FROM menu WHERE id = ".$data['open']." OR id = ".$data['active']." ORDER BY parent_id ASC");
			
			if($this->request->getMethod() == 'POST')
			{
				$validation =  \Config\Services::validation();
				
				$validation->setRules([
					'nama' => ['label' => 'Nama', 'rules' => 'required']
				]);
				
				$isDataValid = $validation->withRequest($this->request)->run();
				
				if($isDataValid)
				{
					$p = $this->request->getPost();
					$inventaris_check = $db->query("SELECT id FROM data_inventaris WHERE code_prefix = '" . $p['code_prefix'] . "'")->getNumRows();
					$inventaris_check = $inventaris_check + $db->query("SELECT * FROM data_item WHERE kode LIKE '" . $p['code_prefix'] . "%'")->getNumRows();
					$kotak_penyimpanan_check = $db->query("SELECT id FROM data_kotak_penyimpanan WHERE code_prefix = '" . $p['code_prefix'] . "'")->getNumRows();
					$kotak_penyimpanan_check = $kotak_penyimpanan_check + $db->query("SELECT * FROM data_daftar_kotak_penyimpanan WHERE kode LIKE '" . $p['code_prefix'] . "%'")->getNumRows();
					$inventarisModel = new InventarisModel();
					
					if($inventaris_check == 0 && $kotak_penyimpanan_check == 0)
					{
						$inventarisModel->insert([
							'id_lab'			=> $p['id_lab'],
							'nama'				=> $p['nama'],
							'deskripsi'			=> $p['deskripsi'],
							'code_prefix'		=> $p['code_prefix'],
							'code_suffix'		=> $p['code_suffix'],
							'code_length'		=> $p['code_length'],
							'added_by'			=> user_id(),
							'added_date'		=> date('Y-m-d H:i:s'),
							'updated_by'		=> user_id(),
							'updated_date'		=> date('Y-m-d H:i:s')
						]);
						
						return redirect()->to('/data/inventaris/'.$id_lab)->with('message', 'Data telah berhasil ditambahkan!');
					}
					else
					{
						return redirect()->back()->withInput()->with('error', 'Awalan kode sudah terpakai!');
					}
				}
				else
				{
					return redirect()->back()->withInput()->with('errors', $validation->getErrors());
				}
			}
			
			$data['db'] = $db;
			$data['lab'] = $db->query("SELECT id, nama FROM data_lab WHERE id = '$id_lab'")->getRow();
			
			return view('data/inventaris_add', $data);
		}
		else
		{
			return redirect()->back();
		}
	}
	
	public function inventarisEdit($id_lab = null, $id = null)
	{
		$db = \Config\Database::connect();
		
		$pengelola = $db->query("SELECT id_pengelola FROM data_pengelola WHERE id_lab = '$id_lab' AND id_pengelola = ".user_id())->getNumRows();
		
		if(has_permission('inventarisEdit') && ($pengelola != 0 || in_groups('admin')))
		{
			$data['title'] = 'Edit Data';
			$data['open'] = 9;
			$data['active'] = 10;
			
			$db = \Config\Database::connect();
			
			$data['breadcrumbs'] = $db->query("SELECT name, href FROM menu WHERE id = ".$data['open']." OR id = ".$data['active']." ORDER BY parent_id ASC");
			
			if($this->request->getMethod() == 'POST')
			{
				$validation =  \Config\Services::validation();
				
				$validation->setRules([
					'nama' => ['label' => 'Nama', 'rules' => 'required']
				]);
				
				$isDataValid = $validation->withRequest($this->request)->run();
				
				$inventarisModel = new InventarisModel();
				
				if($isDataValid)
				{
					$inventarisModel->update($id, [
						'id_lab'			=> $this->request->getPost('id_lab'),
						'nama'				=> $this->request->getPost('nama'),
						'deskripsi'			=> $this->request->getPost('deskripsi'),
						'code_prefix'		=> $this->request->getPost('code_prefix'),
						'code_suffix'		=> $this->request->getPost('code_suffix'),
						'code_length'		=> $this->request->getPost('code_length'),
						'added_by'			=> user_id(),
						'added_date'		=> date('Y-m-d H:i:s'),
						'updated_by'		=> user_id(),
						'updated_date'		=> date('Y-m-d H:i:s')
					]);
					
					return redirect()->to('/data/inventaris/'.$id_lab)->with('message', 'Perubahan berhasil disimpan!');
				}
				else
				{
					return redirect()->back()->withInput()->with('errors', $validation->getErrors());
				}
			}
			$data['db'] = $db;
			$data['lab'] = $db->query("SELECT id, nama FROM data_lab WHERE id = '$id_lab'")->getRow();
			$data['nama_lab'] = $db->query("SELECT nama FROM data_lab WHERE id = '$id_lab'")->getRow()->nama;
			
			$data['inventarisRow'] = $db->query("	SELECT a.id, a.id_lab, a.nama, a.deskripsi, a.code_prefix, a.code_suffix, a.code_length
													FROM data_inventaris a
													LEFT JOIN data_lab b ON(a.id_lab = b.id)
													WHERE a.id = $id
			")->getRow();
			
			return view('data/inventaris_edit', $data);
		}
		else
		{
			return redirect()->back();
		}
	}
	
	public function inventarisDelete()
	{
		$db = \Config\Database::connect();
		
		$id_lab = $this->request->getPost('id_lab');
		
		$pengelola = $db->query("SELECT id_pengelola FROM data_pengelola WHERE id_lab = '$id_lab' AND id_pengelola = ".user_id())->getNumRows();
		
		if(has_permission('inventarisDelete') && ($pengelola != 0 || in_groups('admin')))
		{
			$id = $this->request->getPost('id');
			
			$db->query("DELETE FROM data_item WHERE id_inventaris = $id");
			
			$inventarisModel = new InventarisModel();
			$inventarisModel->delete($id);
		}
    }
	
	public function inventarisTab()
	{
		$db = \Config\Database::connect();
		
		if(has_permission('inventarisView'))
		{
			$session	= \Config\Services::session();
			$tab		= $this->request->getPost('tab');
			$session->set('tab', $tab);
		}
	}
	
	public function itemList()
    {
		$db = \Config\Database::connect();
		
		$id_lab = $db->escapeString($this->request->getPost('id_lab'));
		
		$pengelola = $db->query("SELECT id_pengelola FROM data_pengelola WHERE id_lab = '$id_lab' AND id_pengelola = ".user_id())->getNumRows();
		
		if(has_permission('inventarisView') && ($pengelola != 0 || in_groups('admin')))
		{
			$session = \Config\Services::session();
			
			$id_lab = $db->escapeString($this->request->getPost('id_lab'));
			
			//Searching
			
			$search = $db->escapeString($this->request->getPost('search'));
			$session->set('searchDataItemList'.$id_lab, $search);
			
			$sSearch = '';
			
			if(isset($search) && $search != '')
			{
				$sSearch .= "AND (a.kode LIKE '%".$search."%' OR b.nama LIKE '%".$search."%')";
			}
			
			//Limit
			
			$start = $this->request->getPost("start");
			$session->set('startDataItemList'.$id_lab, $start);
			$length = $this->request->getPost("length");
			$session->set('lengthDataItemList'.$id_lab, $length);
			
			if (isset($start) && $start != '-1' )
			{
				$sLimit = " LIMIT ".intval($start).", ".intval($length);
			}
			
			//Ordering
			
			$orderCol = $this->request->getPost("order[0][column]");
			$session->set('orderColDataItemList'.$id_lab, $orderCol);
			$orderDir = $this->request->getPost("order[0][dir]");
			$session->set('orderDirDataItemList'.$id_lab, $orderDir);
			$columnName = $this->request->getPost("columns[".$orderCol."][name]");
			
			if (isset($columnName) && $columnName != '')
			{
				$sOrder = " ORDER BY ".$columnName." ".$orderDir." ";
			}
			else
			{
				$sOrder = " ORDER BY b.nama ASC, a.kode ASC ";
			}
			
			//Table Row
			
			$s1 = "
				SELECT a.kode, a.id_inventaris, b.nama, a.keterangan, c.kode AS kode_kotak_penyimpanan
				FROM data_item a
				LEFT JOIN data_inventaris b ON(a.id_inventaris = b.id)
				LEFT JOIN data_daftar_kotak_penyimpanan c ON(a.kode_kotak_penyimpanan = c.kode)
				WHERE b.id_lab = $id_lab
			";
			$s1 .= $sSearch;
			$s1 .= $sOrder;
			$s1 .= $sLimit;
		
			$sheet1 = $db->query($s1);
			
			$sheet_total = $db->query("
				SELECT a.kode
				FROM data_item a
				LEFT JOIN data_inventaris b ON(a.id_inventaris = b.id)
				WHERE b.id_lab = $id_lab
			".$sSearch)->getNumRows();
		
			$result = array();
			$i=0;
			$no = (isset($start) ? $start + 1 : 1);
			foreach($sheet1->getResult() as $sheet)
			{
				if(has_permission('inventarisEdit'))
				{
					$edit_button = '
						<button id="edit-'.str_replace('.', '-', ($sheet->id_inventaris.'-'.$sheet->kode)).'" class="btn btn-info btn-xs" title="tambah keterangan">
							<i class="fa fa-edit"></i>
						</button>
					';
				}
				else
				{
					$edit_button = '';
				}
				
				if(has_permission('inventarisDelete'))
				{
					$delete_button = '
						<button id="delete-'.str_replace('.', '-', ($sheet->id_inventaris.'-'.$sheet->kode)).'"
						class="btn btn-danger btn-xs" title="hapus data item '.$sheet->kode.'">
							<i class="fa fa-trash"></i>
						</button>
					';
				}
				else
				{
					$delete_button = '';
				}
				
				$result[$i]['0'] = '
					<input id="input-kode-'.str_replace('.', '-', $sheet->kode).'" type="checkbox" class="kode-item" name="kode[]" value="'.$sheet->kode.'">
					<input id="input-nama-'.str_replace('.', '-', $sheet->kode).'" type="checkbox" class="nama-item" name="nama[]" value="'.$sheet->nama.'" hidden>
				';
				$result[$i]['1'] = $sheet->kode;
				$result[$i]['2'] = $sheet->nama;
				$result[$i]['3'] = $sheet->kode_kotak_penyimpanan;
				$result[$i]['4'] = $sheet->keterangan;
				$result[$i]['5'] = '
					<div class="text-right">
						'.$edit_button.' '.$delete_button.'
						<script>
						$("#edit-'.str_replace('.', '-', ($sheet->id_inventaris.'-'.$sheet->kode)).'").click(function(){
						var id = $(this).attr("id");window.location.assign("/data/itemEdit/'.urlencode($sheet->kode).'");});
						$("#delete-'.str_replace('.', '-', ($sheet->id_inventaris.'-'.$sheet->kode)).'").click(function(){var title = $(this).attr("title");
						swal({title: "Apakah anda yakin akan meng"+title+"?", text: "Semua data akan dihapus dan tidak dapat dikembalikan!", icon: "warning",
						buttons:{cancel: {visible: true, text : "Batal", className: "btn" }, confirm: {text : "Hapus", className : "btn btn-danger"}}})
						.then((willDelete) => {if (willDelete) {$.ajax({url: "/data/itemDelete", type: "POST", data: {"id_lab":'.$id_lab.', "kode":"'.$sheet->kode.'"},
						success: function(data){oTable2.fnStandingRedraw();}, cache: false});}});});$("#input-kode-'.str_replace('.', '-', $sheet->kode).'").change(function(){
						$("#input-nama-'.str_replace('.', '-', $sheet->kode).'").prop("checked", $(this).prop("checked"));});
						</script>
					</div>
				';
				$i++;
				$no++;
			}
			
			$data = $result;
			$results = array(
				"iTotalRecords" => ($sheet_total),
				"iTotalDisplayRecords" => ($sheet_total),
				"aaData"=> $data
			);
				
			echo json_encode($results);
		}
    }
	
	public function itemAdd($id_lab = null)
	{
		$db = \Config\Database::connect();
		
		$pengelola = $db->query("SELECT id_pengelola FROM data_pengelola WHERE id_lab = '$id_lab' AND id_pengelola = ".user_id())->getNumRows();
		
		if(has_permission('inventarisAdd') && ($pengelola != 0 || in_groups('admin')))
		{
			$data['title'] = 'Tambah Item';
			$data['open'] = 9;
			$data['active'] = 10;
			
			$data['breadcrumbs'] = $db->query("SELECT name, href FROM menu WHERE id = ".$data['open']." OR id = ".$data['active']." ORDER BY parent_id ASC");
			
			if($this->request->getMethod() == 'POST')
			{
				$validation =  \Config\Services::validation();
				
				$validation->setRules([
					'id_inventaris' => ['label' => 'Inventaris', 'rules' => 'required'],
					'jml_item' => ['label' => 'Jumlah Item', 'rules' => 'required']
				]);
				
				$isDataValid = $validation->withRequest($this->request)->run();
				
				if($isDataValid)
				{
					$id_inventaris	= $this->request->getPost('id_inventaris');
					$jml_item		= $this->request->getPost('jml_item');
					$added_by		= user_id();
					$added_date		= date('Y-m-d H:i:s');
					$updated_by		= user_id();
					$updated_date	= date('Y-m-d H:i:s');
					
					$inventaris		= $db->query("SELECT code_prefix, code_suffix, code_length FROM data_inventaris WHERE id = $id_inventaris")->getRow();
					
					$items = $db->query("SELECT kode FROM data_item ORDER BY kode ASC")->getResultArray();
					
					$item_code = array_map (
						function($value){
							return $value['kode'];
						},
						$items
					);
					
					$x = 1;
					$n = 1;
					
					$insert = "
						INSERT INTO data_item (id_inventaris,  kode,  added_by, added_date, update_by, update_date) VALUES
					";
					
					while($x <= $jml_item)
					{
						$kode = $inventaris->code_prefix.(sprintf("%0".$inventaris->code_length."d", ($n))).$inventaris->code_suffix;
						
						if(in_array($kode, $item_code))
						{
							$x = $x;
						}
						else
						{
							$insert .= "(".$id_inventaris.", '".$kode."', ".user_id().", '".date('Y-m-d H:i:s')."', ".user_id().", '".date('Y-m-d H:i:s')."')";
							
							if($x != $jml_item)
							{
								$insert .=",";
							}
							
							$x++;
						}
						$n++;
					}
					
					$db->query($insert);
					
					return redirect()->to('/data/inventaris/'.$id_lab)->with('message', 'Data telah berhasil ditambahkan!');
				}
				else
				{
					return redirect()->back()->withInput()->with('errors', $validation->getErrors());
				}
			}
			
			$data['db'] = $db;
			$data['lab'] = $db->query("SELECT id, nama FROM data_lab WHERE id = '$id_lab'")->getRow();
			
			return view('data/item_add', $data);
		}
		else
		{
			return redirect()->back();
		}
	}
	
	public function itemEdit($kode = null)
	{
		$db = \Config\Database::connect();
		
		$pengelola = $db->query("
			SELECT a.id_pengelola
			FROM data_pengelola a
			LEFT JOIN data_inventaris b ON(b.id_lab = a.id_lab)
			LEFT JOIN data_item c ON(c.id_inventaris = b.id)
			WHERE c.kode = '$kode'
            AND a.id_pengelola = ".user_id()
		)->getNumRows();
		
		if(has_permission('inventarisEdit') && ($pengelola != 0 || in_groups('admin')))
		{
			$data['title'] = 'Edit Item';
			$data['open'] = 9;
			$data['active'] = 10;
			
			$data['breadcrumbs'] = $db->query("SELECT name, href FROM menu WHERE id = ".$data['open']." OR id = ".$data['active']." ORDER BY parent_id ASC");
			
			$item = $db->query("
				SELECT a.kode, a.keterangan, b.nama, c.nama AS nama_lab, c.id AS id_lab
				FROM data_item a
				LEFT JOIN data_inventaris b ON(a.id_inventaris = b.id)
				LEFT JOIN data_lab c ON(b.id_lab = c.id)
				WHERE a.kode = '$kode'
			")->getRow();
			
			if($this->request->getMethod() == 'POST')
			{
				$keterangan	= $this->request->getPost('keterangan');
				
				$db->query("UPDATE data_item SET keterangan = '$keterangan' WHERE kode = '$kode'");
				
				return redirect()->to('/data/inventaris/'.$item->id_lab)->with('message', 'Data telah berhasil diubah!');
			}
			
			$data['item'] = $item;
			
			return view('data/item_edit', $data);
		}
		else
		{
			return redirect()->back();
		}
	}
	
	public function itemDelete()
	{
		$db		= \Config\Database::connect();
		
		$id_lab	= $this->request->getPost('id_lab');
		$kode	= $this->request->getPost('kode');
		
		$pengelola = $db->query("SELECT id_pengelola FROM data_pengelola WHERE id_lab = '$id_lab' AND id_pengelola = ".user_id())->getNumRows();
		
		if(has_permission('inventarisDelete') && ($pengelola != 0 || in_groups('admin')))
		{
			$db->query("DELETE FROM data_item WHERE kode = $kode");
		}
    }
	
	public function itemsDelete()
	{
		$db		= \Config\Database::connect();
		
		$id_lab	= $this->request->getPost('id_lab');
		$kode	= $this->request->getPost('kode');
		
		$pengelola = $db->query("SELECT id_pengelola FROM data_pengelola WHERE id_lab = '$id_lab' AND id_pengelola = ".user_id())->getNumRows();
		
		if(has_permission('inventarisDelete') && ($pengelola != 0 || in_groups('admin')))
		{
			if(isset($kode))
			{
				$q = "DELETE FROM data_item ";
				$i = 0;
				
				foreach($kode as $row)
				{
					if($i == 0)
					{
						$q .= " WHERE kode = '$row' ";
					}
					else
					{
						$q .= " OR kode = '$row' ";
					}
				$i++;
				}
				$db->query($q);
			}
		}
    }
	
	public function itemGetInventaris()
	{
		$db = \Config\Database::connect();
		
		if(has_permission('inventarisAdd'))
		{
			$id_inventaris	= $this->request->getPost('id_inventaris');
			
			if($id_inventaris != '')
			{
				$inventaris		= $db->query("SELECT nama, code_length FROM data_inventaris WHERE id = $id_inventaris")->getRow();
				$jml_item		= $db->query("SELECT kode FROM data_item WHERE id_inventaris = $id_inventaris")->getNumRows();
				
				$code_length	= str_replace(0, 9, (sprintf("%0".$inventaris->code_length."d", (9))));
				$max			= $code_length - $jml_item;
			
				if($max > 0)
				{
					echo'
						<div class="col-sm-3"><label class="font-weight-bold">Jumlah Item</label></div>
						<div class="col-sm-9">
							<input type="number" min="0" max="'.$max.'"
							step="1" class="form-control" name="jml_item" id="jml_item" placeholder="Jumlah item yang akan ditambahkan">
						</div>
				';
				}
				else
				{
					echo '
						<div class="col-sm-3"></div><div class="col-sm-9"><p class="text-danger font-weight-bold">Jumlah item "'.$inventaris->nama.'" sudah penuh</label></p>
					';
				}
			}
			else
			{
				echo '
					<div class="col-sm-3"><label class="font-weight-bold">Jumlah Item</label></div>
					<div class="col-sm-9">
						<input type="number" class="form-control" name="jml_item" id="jml_item" placeholder="Jumlah item yang akan ditambahkan" disabled>
					</div>
				';
			}
		}
	}
	
	public function kotakPenyimpananList()
    {
		$db = \Config\Database::connect();
		
		$id_lab = $db->escapeString($this->request->getPost('id_lab'));
		$pengelola = $db->query("SELECT id_pengelola FROM data_pengelola WHERE id_lab = '$id_lab' AND id_pengelola = ".user_id())->getNumRows();
		
		if(has_permission('inventarisView') && ($pengelola != 0 || in_groups('admin')))
		{
			$session = \Config\Services::session();
			
			$id_lab = $db->escapeString($this->request->getPost('id_lab'));
			
			//Searching
			
			$search = $db->escapeString($this->request->getPost('search'));
			$session->set('searchDataKotakPenyimpananList'.$id_lab, $search);
			
			$sSearch = '';
			
			if(isset($search) && $search != '')
			{
				$sSearch .= " AND (a.nama LIKE '%".$search."%' OR a.deskripsi LIKE '%".$search."%' OR a.code_prefix LIKE '%".$search."%' OR a.code_suffix LIKE '%".$search."%') ";
			}
			
			//Limit
			
			$start = $this->request->getPost("start");
			$session->set('startDataKotakPenyimpananList'.$id_lab, $start);
			$length = $this->request->getPost("length");
			$session->set('lengthDataKotakPenyimpananList'.$id_lab, $length);
			
			if (isset($start) && $start != '-1' )
			{
				$sLimit = " LIMIT ".intval($start).", ".intval($length);
			}
			
			//Ordering
			
			$orderCol = $this->request->getPost("order[0][column]");
			$session->set('orderColDataKotakPenyimpananList'.$id_lab, $orderCol);
			$orderDir = $this->request->getPost("order[0][dir]");
			$session->set('orderDirDataKotakPenyimpananList'.$id_lab, $orderDir);
			$columnName = $this->request->getPost("columns[".$orderCol."][name]");
			
			if (isset($columnName) && $columnName != '')
			{
				$sOrder = " ORDER BY ".$columnName." ".$orderDir." ";
			}
			else
			{
				$sOrder = " ORDER BY a.nama ASC ";
			}
			
			//Table Row
			
			$s1 = "
				SELECT a.id, a.id_lab, a.nama, a.deskripsi, a.code_prefix, a.code_suffix, a.code_length, COUNT(c.id_kotak_penyimpanan) AS jml_item
				FROM data_kotak_penyimpanan a
				LEFT JOIN data_lab b ON(a.id_lab = b.id)
				LEFT JOIN data_daftar_kotak_penyimpanan c ON(c.id_kotak_penyimpanan = a.id)
				WHERE a.id_lab = $id_lab
			";
			$s1 .= $sSearch;
			$s1 .= " GROUP BY a.id ";
			$s1 .= $sOrder;
			$s1 .= $sLimit;
		
			$sheet1 = $db->query($s1);
			
			$sheet_total = $db->query("SELECT a.id FROM data_kotak_penyimpanan a LEFT JOIN data_lab b ON(a.id_lab = b.id) WHERE a.id_lab = $id_lab".$sSearch)->getNumRows();
		
			$result = array();
			$i=0;
			$no = (isset($start) ? $start + 1 : 1);
			foreach($sheet1->getResult() as $sheet)
			{
				if(has_permission('inventarisEdit'))
				{
					$edit_button = '<button id="'.$sheet->id.'" class="btn btn-info btn-xs edit-kotak_penyimpanan" title="edit"><i class="fa fa-edit"></i></button>';
					$isi_kotak_button = '<button id="isi-'.$sheet->id.'" class="btn btn-success btn-xs" title="isi kotak"><i class="fa fa-list"></i></button>';
				}
				else
				{
					$edit_button = '';
					$isi_kotak_button = '';
				}
				
				if(has_permission('inventarisDelete'))
				{
					$delete_button = '
						<button id="'.$sheet->id.'" class="btn btn-danger btn-xs delete-kotak_penyimpanan" title="hapus data kotak penyimpanan '.$sheet->nama.'">
							<i class="fa fa-trash"></i>
						</button>
					';
				}
				else
				{
					$delete_button = '';
				}
				
				$result[$i]['0'] = $no;
				$result[$i]['1'] = $sheet->nama;
				$result[$i]['2'] = $sheet->deskripsi;
				$result[$i]['3'] = $sheet->code_prefix;
				$result[$i]['4'] = $sheet->code_suffix;
				$result[$i]['5'] = $sheet->code_length;
				$result[$i]['6'] = $sheet->jml_item;
				$result[$i]['7'] = '
					<div class="text-right">
						' . $isi_kotak_button . ' ' . $edit_button . ' ' . $delete_button.'
						<script>
						$(".edit-kotak_penyimpanan").click(function(){var id = $(this).attr("id");window.location.assign("/data/kotakPenyimpananEdit/'.$sheet->id_lab.'/"+id);});
						$("#isi-'.$sheet->id.'").click(function(){var id = $(this).attr("id");window.location.assign("/data/isiKotak/'.$sheet->id.'/");});
						$(".delete-kotak_penyimpanan").click(function() {var id = $(this).attr("id");var title = $(this).attr("title");
							swal({title: "Apakah anda yakin akan meng"+title+"?", text: "Semua data akan dihapus dan tidak dapat dikembalikan!", icon: "warning",
							buttons:{cancel: {visible: true, text : "Batal", className: "btn" }, confirm: {text : "Hapus", className : "btn btn-danger"}}})
							.then((willDelete) => {if (willDelete) {$.ajax({url: "/data/kotakPenyimpananDelete", type: "POST",
							data: {"id":id, "id_lab":'.$sheet->id_lab.'}, success: function(data){oTable3.fnStandingRedraw();}, cache: false});}});});
						</script>
					</div>
				';
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
	
	public function kotakPenyimpananAdd($id_lab = null)
	{
		$db = \Config\Database::connect();
		
		$pengelola = $db->query("SELECT id_pengelola FROM data_pengelola WHERE id_lab = '$id_lab' AND id_pengelola = ".user_id())->getNumRows();
		
		if(has_permission('inventarisAdd') && ($pengelola != 0 || in_groups('admin')))
		{
			$data['title'] = 'Tambah Data';
			$data['open'] = 9;
			$data['active'] = 10;
			
			$data['breadcrumbs'] = $db->query("SELECT name, href FROM menu WHERE id = ".$data['open']." OR id = ".$data['active']." ORDER BY parent_id ASC");
			
			if($this->request->getMethod() == 'POST')
			{
				$validation =  \Config\Services::validation();
				
				$validation->setRules([
					'nama' => ['label' => 'Nama', 'rules' => 'required']
				]);
				
				$isDataValid = $validation->withRequest($this->request)->run();
				
				if($isDataValid)
				{
					$p = $this->request->getPost();
					$inventaris_check = $db->query("SELECT id FROM data_inventaris WHERE code_prefix = '" . $p['code_prefix'] . "'")->getNumRows();
					$inventaris_check = $inventaris_check + $db->query("SELECT * FROM data_item WHERE kode LIKE '" . $p['code_prefix'] . "%'")->getNumRows();
					$kotak_penyimpanan_check = $db->query("SELECT id FROM data_kotak_penyimpanan WHERE code_prefix = '" . $p['code_prefix'] . "'")->getNumRows();
					$kotak_penyimpanan_check = $kotak_penyimpanan_check + $db->query("SELECT * FROM data_daftar_kotak_penyimpanan WHERE kode LIKE '" . $p['code_prefix'] . "%'")->getNumRows();
					$kotakPenyimpananModel = new KotakPenyimpananModel();
					
					if($inventaris_check == 0 && $kotak_penyimpanan_check == 0)
					{
						$kotakPenyimpananModel->insert([
							'id_lab'			=> $p['id_lab'],
							'nama'				=> $p['nama'],
							'deskripsi'			=> $p['deskripsi'],
							'code_prefix'		=> $p['code_prefix'],
							'code_suffix'		=> $p['code_suffix'],
							'code_length'		=> $p['code_length'],
							'added_by'			=> user_id(),
							'added_date'		=> date('Y-m-d H:i:s'),
							'updated_by'		=> user_id(),
							'updated_date'		=> date('Y-m-d H:i:s')
						]);
						
						return redirect()->to('/data/inventaris/'.$id_lab)->with('message', 'Data telah berhasil ditambahkan!');
					}
					else
					{
						return redirect()->back()->withInput()->with('error', 'Awalan kode kotak penyimpanan tidak boleh duplikat dengan awalan kode item!');
					}
				}
				else
				{
					return redirect()->back()->withInput()->with('errors', $validation->getErrors());
				}
			}
			
			$data['db'] = $db;
			$data['lab'] = $db->query("SELECT id, nama FROM data_lab WHERE id = '$id_lab'")->getRow();
			
			return view('data/kotak_penyimpanan_add', $data);
		}
		else
		{
			return redirect()->back();
		}
	}
	
	public function kotakPenyimpananEdit($id_lab = null, $id = null)
	{
		$db = \Config\Database::connect();
		
		$pengelola = $db->query("SELECT id_pengelola FROM data_pengelola WHERE id_lab = '$id_lab' AND id_pengelola = ".user_id())->getNumRows();
		
		if(has_permission('inventarisEdit') && ($pengelola != 0 || in_groups('admin')))
		{
			$data['title'] = 'Edit Data';
			$data['open'] = 9;
			$data['active'] = 10;
			
			$db = \Config\Database::connect();
			
			$data['breadcrumbs'] = $db->query("SELECT name, href FROM menu WHERE id = ".$data['open']." OR id = ".$data['active']." ORDER BY parent_id ASC");
			
			if($this->request->getMethod() == 'POST')
			{
				$validation =  \Config\Services::validation();
				
				$validation->setRules([
					'nama' => ['label' => 'Nama', 'rules' => 'required']
				]);
				
				$isDataValid = $validation->withRequest($this->request)->run();
				
				$kotakPenyimpananModel = new KotakPenyimpananModel();
				
				if($isDataValid)
				{
					$kotakPenyimpananModel->update($id, [
						'id_lab'			=> $this->request->getPost('id_lab'),
						'nama'				=> $this->request->getPost('nama'),
						'deskripsi'			=> $this->request->getPost('deskripsi'),
						'code_prefix'		=> $this->request->getPost('code_prefix'),
						'code_suffix'		=> $this->request->getPost('code_suffix'),
						'code_length'		=> $this->request->getPost('code_length'),
						'added_by'			=> user_id(),
						'added_date'		=> date('Y-m-d H:i:s'),
						'updated_by'		=> user_id(),
						'updated_date'		=> date('Y-m-d H:i:s')
					]);
					
					return redirect()->to('/data/inventaris/'.$id_lab)->with('message', 'Perubahan berhasil disimpan!');
				}
				else
				{
					return redirect()->back()->withInput()->with('errors', $validation->getErrors());
				}
			}
			$data['db'] = $db;
			$data['lab'] = $db->query("SELECT id, nama FROM data_lab WHERE id = '$id_lab'")->getRow();
			$data['nama_lab'] = $db->query("SELECT nama FROM data_lab WHERE id = '$id_lab'")->getRow()->nama;
			
			$data['KPRow'] = $db->query("	SELECT a.id, a.id_lab, a.nama, a.deskripsi, a.code_prefix, a.code_suffix, a.code_length
													FROM data_kotak_penyimpanan a
													LEFT JOIN data_lab b ON(a.id_lab = b.id)
													WHERE a.id = $id
			")->getRow();
			
			return view('data/kotak_penyimpanan_edit', $data);
		}
		else
		{
			return redirect()->back();
		}
	}
	
	public function kotakPenyimpananDelete()
	{
		$db = \Config\Database::connect();
		
		$id_lab = $this->request->getPost('id_lab');
		
		$pengelola = $db->query("SELECT id_pengelola FROM data_pengelola WHERE id_lab = '$id_lab' AND id_pengelola = ".user_id())->getNumRows();
		
		if(has_permission('inventarisDelete') && ($pengelola != 0 || in_groups('admin')))
		{
			$id = $this->request->getPost('id');
			
			$kotak_penyimpanan	=	$db->query("SELECT kode FROM data_daftar_kotak_penyimpanan WHERE id_kotak_penyimpanan = $id")->getResult();
			
			foreach($kotak_penyimpanan as $row)
			{
				$db->query("UPDATE data_item SET kode_kotak_penyimpanan = NULL WHERE kode_kotak_penyimpanan = '" . $row->kode . "'");
			}
			
			$db->query("DELETE FROM data_daftar_kotak_penyimpanan WHERE id_kotak_penyimpanan = $id");
			$db->query("DELETE FROM rel_inventaris_kotak_penyimpanan WHERE id_kotak_penyimpanan = $id");
			
			$kotakPenyimpananModel = new KotakPenyimpananModel();
			$kotakPenyimpananModel->delete($id);
		}
    }
	
	public function isiKotak($id_kotak_penyimpanan = null)
    {
		$db = \Config\Database::connect();
		
		if(has_permission('inventarisView'))
		{
			$data['title'] = 'Data Isi Kotak';
			$data['open'] = 9;
			$data['active'] = 10;
			
			$session = \Config\Services::session();
			
			$data['breadcrumbs'] = $db->query("SELECT name, href FROM menu WHERE id = ".$data['open']." OR id = ".$data['active']." ORDER BY parent_id ASC");
			
			$data['orderCol'] = $session->get('orderColIsiKotakList');
			$data['orderDir'] = $session->get('orderDirIsiKotakList');
			
			$data['tab'] = $session->get('tab');
			$data['db'] = $db;
			$data['id_kotak_penyimpanan'] = $id_kotak_penyimpanan;
			$data['kotak'] = $db->query("SELECT nama, id_lab FROM data_kotak_penyimpanan WHERE id = $id_kotak_penyimpanan")->getRow();
			
			return view('data/isi_kotak', $data);
		}
		else
		{
			return redirect()->back();
		}
    }

    public function isiKotakList()
    {
		$db = \Config\Database::connect();
		
		$id_kotak_penyimpanan = $db->escapeString($this->request->getPost('id_kotak_penyimpanan'));
		
		if(has_permission('inventarisView'))
		{
			$session = \Config\Services::session();
			
			//Ordering
			
			$orderCol = $this->request->getPost("order[0][column]");
			$session->set('orderColIsiKotakList', $orderCol);
			$orderDir = $this->request->getPost("order[0][dir]");
			$session->set('orderDirIsiKotakList', $orderDir);
			$columnName = $this->request->getPost("columns[".$orderCol."][name]");
			
			if (isset($columnName) && $columnName != '')
			{
				$sOrder = " ORDER BY ".$columnName." ".$orderDir." ";
			}
			else
			{
				$sOrder = " ORDER BY b.nama ASC ";
			}
			
			//Table Row
			
			$s1 = "
				SELECT a.id_inventaris, a.id_kotak_penyimpanan, a.jml, b.nama
				FROM rel_inventaris_kotak_penyimpanan a
				LEFT JOIN data_inventaris b ON(a.id_inventaris = b.id)
				WHERE a.id_kotak_penyimpanan = $id_kotak_penyimpanan
			";
			$s1 .= $sOrder;
		
			$sheet1 = $db->query($s1);
			
			$sheet_total = $db->query("
				SELECT a.id_inventaris
				FROM rel_inventaris_kotak_penyimpanan a
				LEFT JOIN data_inventaris b ON(a.id_inventaris = b.id)
				WHERE a.id_kotak_penyimpanan = $id_kotak_penyimpanan
			")->getNumRows();
		
			$result = array();
			$i = 0;
			$no = (isset($start) ? $start + 1 : 1);
			foreach($sheet1->getResult() as $sheet)
			{
				if(has_permission('inventarisDelete'))
				{
					$delete_button = '
						<button id="del-'.$sheet->id_inventaris.'" class="btn btn-danger btn-xs" title="hapus data inventaris '.$sheet->nama.'">
							<i class="fa fa-trash"></i>
						</button>
					';
				}
				else
				{
					$delete_button = '';
				}
				
				$result[$i]['0'] = $no;
				$result[$i]['1'] = $sheet->nama;
				$result[$i]['2'] = $sheet->jml;
				$result[$i]['3'] = '
					<div class="text-right">
						'.$delete_button.'
						<script>
						$("#del-'.$sheet->id_inventaris.'").click(function() {var title = $(this).attr("title");
						swal({title: "Apakah anda yakin akan meng"+title+"?", text: "Semua data akan dihapus dan tidak dapat dikembalikan!", icon: "warning",
						buttons:{cancel: {visible: true, text : "Batal", className: "btn" }, confirm: {text : "Hapus", className : "btn btn-danger"}}})
						.then((willDelete) => {if (willDelete) {$.ajax({url: "/data/isiKotakDelete", type: "POST", data: {
						"id_inventaris":' . $sheet->id_inventaris . ', "id_kotak_penyimpanan":' . $sheet->id_kotak_penyimpanan . '},
						success: function(data){oTable.fnStandingRedraw();}, cache: false});}});});
						</script>
					</div>
				';
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
	
	public function isiKotakItem($kode_kotak)
    {
		$db = \Config\Database::connect();
		
		if(has_permission('inventarisView'))
		{
			$kotak = $db->query("
				SELECT b.nama, a.kode, a.keterangan
				FROM data_daftar_kotak_penyimpanan a
				LEFT JOIN  data_kotak_penyimpanan b ON(a.id_kotak_penyimpanan = b.id)
				WHERE a.kode = '$kode_kotak'
			");
			if($kotak->getNumRows() != 0)
			{
				$data['title'] = 'Data Isi Kotak';
				$data['open'] = 9;
				$data['active'] = 10;
				
				$session = \Config\Services::session();
				
				$data['breadcrumbs'] = $db->query("SELECT name, href FROM menu WHERE id = ".$data['open']." OR id = ".$data['active']." ORDER BY parent_id ASC");
				
				$data['start'] = $session->get('startIsiKotakItemList');
				$data['length'] = $session->get('lengthIsiKotakItemList');
				$data['orderCol'] = $session->get('orderColIsiKotakItemList');
				$data['orderDir'] = $session->get('orderDirIsiKotakItemList');
				
				$data['tab'] = $session->get('tab');
				$data['db'] = $db;
				$data['kode_kotak'] = $kode_kotak;
				$data['kotak'] = $kotak->getRow();
				
				return view('data/isi_kotak_item', $data);
			}
			else
			{
				return redirect()->back();
			}
		}
		else
		{
			return redirect()->back();
		}
    }

    public function isiKotakItemList()
    {	
		if(has_permission('inventarisView'))
		{
			$session = \Config\Services::session();

			$db = \Config\Database::connect();
			
			$kode_kotak = $db->escapeString($this->request->getPost('kode_kotak'));
			
			//Ordering
			
			$orderCol = $this->request->getPost("order[0][column]");
			$session->set('orderColIsiKotakItemList', $orderCol);
			$orderDir = $this->request->getPost("order[0][dir]");
			$session->set('orderDirIsiKotakItemList', $orderDir);
			$columnName = $this->request->getPost("columns[".$orderCol."][name]");
			
			if (isset($columnName) && $columnName != '')
			{
				$sOrder = " ORDER BY ".$columnName." ".$orderDir." ";
			}
			else
			{
				$sOrder = " ORDER BY b.nama ASC ";
			}
			
			//Table Row
			
			$s1 = "
				SELECT a.kode, b.nama
				FROM data_item a
				LEFT JOIN data_inventaris b ON(a.id_inventaris = b.id)
				WHERE a.kode_kotak_penyimpanan = '$kode_kotak'
			";
			$s1 .= $sOrder;
		
			$sheet1 = $db->query($s1);
			
			$sheet_total = $db->query("
				SELECT a.kode, b.nama
				FROM data_item a
				LEFT JOIN data_inventaris b ON(a.id_inventaris = b.id)
				WHERE a.kode_kotak_penyimpanan = '$kode_kotak'
			")->getNumRows();
		
			$result = array();
			$i = 0;
			$no = (isset($start) ? $start + 1 : 1);
			foreach($sheet1->getResult() as $sheet)
			{
				$result[$i]['0'] = $no;
				$result[$i]['1'] = $sheet->nama;
				$result[$i]['2'] = $sheet->kode;
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
	
	public function isiKotakDelete()
    {
		if(has_permission('inventarisDelete'))
		{
			$db = \Config\Database::connect();
			
			$id_inventaris = $db->escapeString($this->request->getPost('id_inventaris'));
			$id_kotak_penyimpanan = $db->escapeString($this->request->getPost('id_kotak_penyimpanan'));
			
			$db->query("
				DELETE FROM rel_inventaris_kotak_penyimpanan
				WHERE id_inventaris = $id_inventaris
				AND id_kotak_penyimpanan = $id_kotak_penyimpanan
			");
		}
    }
	
	public function isiKotakAdd()
    {
		if(has_permission('inventarisAdd'))
		{
			$db = \Config\Database::connect();
			
			$id_inventaris = $db->escapeString($this->request->getPost('id_inventaris'));
			$id_kotak_penyimpanan = $db->escapeString($this->request->getPost('id_kotak_penyimpanan'));
			$jml = $db->escapeString($this->request->getPost('jml'));
			
			if(
				isset($id_inventaris)
				&& isset($id_kotak_penyimpanan)
				&& isset($jml)
				&& $id_inventaris != ''
				&& $id_kotak_penyimpanan != ''
				&& $jml != ''
			)
			{
				$isi_kotak = $db->query("
					SELECT id_inventaris, jml
					FROM rel_inventaris_kotak_penyimpanan
					WHERE id_inventaris = $id_inventaris
					AND id_kotak_penyimpanan = $id_kotak_penyimpanan
				");
				
				if($isi_kotak->getNumRows() == 0)
				{
					$db->query("
						INSERT INTO rel_inventaris_kotak_penyimpanan
						SET
							id_inventaris			= $id_inventaris,
							id_kotak_penyimpanan	= $id_kotak_penyimpanan,
							jml						= $jml
					");
				}
				else
				{
					$db->query("
						UPDATE rel_inventaris_kotak_penyimpanan
						SET jml = " . ($isi_kotak->getRow()->jml + $jml) . "
						WHERE id_inventaris = $id_inventaris
						AND id_kotak_penyimpanan = $id_kotak_penyimpanan
					");
				}
			}
		}
    }
	
	public function labManagers($id_lab = null)
    {
		if(has_permission('labManagerView'))
		{
			$data['title'] = 'Data Pengelola Laboraturium';
			$data['open'] = 9;
			$data['active'] = 10;
			
			$db = \Config\Database::connect();
			$session = \Config\Services::session();
			
			$data['breadcrumbs'] = $db->query("SELECT name, href FROM menu WHERE id = ".$data['open']." OR id = ".$data['active']." ORDER BY parent_id ASC");
			
			if(!isset($id_lab) || $id_lab == null || $id_lab == '')
			{
				$data['search'] = $session->get('searchDataLabList');
				$data['start'] = $session->get('startDataLabList');
				$data['length'] = $session->get('lengthDataLabList');
				$data['orderCol'] = $session->get('orderColDataLabList');
				$data['orderDir'] = $session->get('orderDirDataLabList');
				
				return view('data/labs', $data);
			}
			
			else
			{
				$data['db'] = $db;
				$data['search'] = $session->get('searchDataLabManagerList'.$id_lab);
				$data['start'] = $session->get('startDataLabManagerList'.$id_lab);
				$data['length'] = $session->get('lengthDataLabManagerList'.$id_lab);
				$data['orderCol'] = $session->get('orderColDataLabManagerList'.$id_lab);
				$data['orderDir'] = $session->get('orderDirDataLabManagerList'.$id_lab);
				$data['id_lab'] = $id_lab;
				$data['nama_lab'] = $db->query("SELECT nama FROM data_lab WHERE id = '$id_lab'")->getRow()->nama;
				
				return view('data/lab_managers', $data);
			}
		}
		else
		{
			return redirect()->back();
		}
    }
	
	public function labManagerList()
    {
		if(has_permission('labManagerView'))
		{
			$db = \Config\Database::connect();
			$session = \Config\Services::session();
			
			$id_lab = $db->escapeString($this->request->getPost('id_lab'));
			
			//Searching
			
			$search = $db->escapeString($this->request->getPost('search'));
			$session->set('searchDataLabManagerList'.$id_lab, $search);
			
			$sSearch = '';
			
			if(isset($search) && $search != '')
			{
				$sSearch .= " AND (b.fullname LIKE '%".$search."%'";
			}
			
			//Limit
			
			$start = $this->request->getPost("start");
			$session->set('startDataLabManagerList'.$id_lab, $start);
			$length = $this->request->getPost("length");
			$session->set('lengthDataLabManagerList'.$id_lab, $length);
			
			if (isset($start) && $start != '-1' )
			{
				$sLimit = " LIMIT ".intval($start).", ".intval($length);
			}
			
			//Ordering
			
			$orderCol = $this->request->getPost("order[0][column]");
			$session->set('orderColDataLabManagerList'.$id_lab, $orderCol);
			$orderDir = $this->request->getPost("order[0][dir]");
			$session->set('orderDirDataLabManagerList'.$id_lab, $orderDir);
			$columnName = $this->request->getPost("columns[".$orderCol."][name]");
			
			if (isset($columnName) && $columnName != '')
			{
				$sOrder = " ORDER BY ".$columnName." ".$orderDir." ";
			}
			else
			{
				$sOrder = " ORDER BY b.fullname ASC ";
			}
			
			//Table Row
			
			$s1 = "
				SELECT a.id_lab, a.id_pengelola, b.fullname AS nama
				FROM data_pengelola a
				LEFT JOIN users b ON(a.id_pengelola = b.id)
				WHERE a.id_lab = $id_lab
			";
			$s1 .= $sSearch;
			$s1 .= $sOrder;
			$s1 .= $sLimit;
		
			$sheet1 = $db->query($s1);
			
			$sheet_total = $db->query("SELECT a.id_pengelola FROM data_pengelola a WHERE a.id_lab = $id_lab")->getNumRows();
		
			$result = array();
			$i=0;
			$no = (isset($start) ? $start + 1 : 1);
			foreach($sheet1->getResult() as $sheet)
			{
				if(has_permission('labManagerDelete'))
				{
					$delete_button = '
						<button id="'.$sheet->id_lab.'-'.$sheet->id_pengelola.'" class="btn btn-danger btn-xs delete" title="hapus '.$sheet->nama.' dari daftar pengelola">
							<i class="fa fa-times"></i>
						</button>
					';
				}
				else
				{
					$delete_button = '';
				}
				
				$result[$i]['0'] = $no;
				$result[$i]['1'] = $sheet->nama;
				$result[$i]['2'] = '
					<div class="text-right">
						'.$delete_button.'
						<script>
						$(".delete").click(function() {var id = $(this).attr("id");var title = $(this).attr("title");
						swal({title: "Apakah anda yakin akan meng"+title+"?", text: "Semua data akan dihapus dan tidak dapat dikembalikan!", icon: "warning",
						buttons:{cancel: {visible: true, text : "Batal", className: "btn" },
						confirm: {text : "Hapus", className : "btn btn-danger"}}}).then((willDelete) => {if (willDelete) {$.ajax({url: "/data/labManagerDelete",
						type: "POST", data: {"id_lab":'.$sheet->id_lab.', "id_pengelola":'.$sheet->id_pengelola.'}, cache: false});oTable.fnDraw();}});});
						</script>
					</div>
				';
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
	
	public function labManagerAdd()
	{
		if(has_permission('labManagerAdd'))
		{
			$id_lab			= $this->request->getPost('id_lab');
			$id_pengelola	= $this->request->getPost('id_pengelola');
			
			$db = \Config\Database::connect();
			$validation =  \Config\Services::validation();
			
			$validation->setRules([
				'id_pengelola' => [
					'label' => 'Pengelola',
					'rules' => 'required'
				]
			]);
			
			$isDataValid = $validation->withRequest($this->request)->run();
			
			$isNoData = $db->query("SELECT id_pengelola FROM data_pengelola WHERE id_lab = $id_lab AND id_pengelola = $id_pengelola")->getNumRows();
			
			if($isDataValid && $isNoData == 0)
			{
				$labManagerModel = new LabManagerModel();
				
				$labManagerModel->insert([
					'id_lab'			=> $id_lab,
					'id_pengelola'		=> $id_pengelola
				]);
			}
		}
	}
	
	public function labManagerDelete()
	{
		if(has_permission('labManagerDelete'))
		{
			$db = \Config\Database::connect();
			
			$id_lab			= $this->request->getPost('id_lab');
			$id_pengelola	= $this->request->getPost('id_pengelola');
			
			$db->query("DELETE FROM data_pengelola WHERE id_lab = '$id_lab' AND id_pengelola = '$id_pengelola'");
		}
    }
	
	public function daftarKotakPenyimpananList()
    {
		$db = \Config\Database::connect();
		
		$id_lab = $db->escapeString($this->request->getPost('id_lab'));
		
		$pengelola = $db->query("SELECT id_pengelola FROM data_pengelola WHERE id_lab = '$id_lab' AND id_pengelola = ".user_id())->getNumRows();
		
		if(has_permission('inventarisView') && ($pengelola != 0 || in_groups('admin')))
		{
			$session = \Config\Services::session();
			
			$id_lab = $db->escapeString($this->request->getPost('id_lab'));
			
			//Searching
			
			$search = $db->escapeString($this->request->getPost('search'));
			$session->set('searchDataDaftarKotakPenyimpananList'.$id_lab, $search);
			
			$sSearch = '';
			
			if(isset($search) && $search != '')
			{
				$sSearch .= "AND (a.kode LIKE '%".$search."%' OR b.nama LIKE '%".$search."%')";
			}
			
			//Limit
			
			$start = $this->request->getPost("start");
			$session->set('startDataDaftarKotakPenyimpananList'.$id_lab, $start);
			$length = $this->request->getPost("length");
			$session->set('lengthDataDaftarKotakPenyimpananList'.$id_lab, $length);
			
			if (isset($start) && $start != '-1' )
			{
				$sLimit = " LIMIT ".intval($start).", ".intval($length);
			}
			
			//Ordering
			
			$orderCol = $this->request->getPost("order[0][column]");
			$session->set('orderColDataDaftarKotakPenyimpananList'.$id_lab, $orderCol);
			$orderDir = $this->request->getPost("order[0][dir]");
			$session->set('orderDirDataDaftarKotakPenyimpananList'.$id_lab, $orderDir);
			$columnName = $this->request->getPost("columns[".$orderCol."][name]");
			
			if (isset($columnName) && $columnName != '')
			{
				$sOrder = " ORDER BY ".$columnName." ".$orderDir." ";
			}
			else
			{
				$sOrder = " ORDER BY b.nama ASC, a.kode ASC ";
			}
			
			//Table Row
			
			$s1 = "
				SELECT a.kode, a.id_kotak_penyimpanan, b.nama, a.keterangan
				FROM data_daftar_kotak_penyimpanan a
				LEFT JOIN data_kotak_penyimpanan b ON(a.id_kotak_penyimpanan = b.id)
				WHERE b.id_lab = $id_lab
			";
			$s1 .= $sSearch;
			$s1 .= $sOrder;
			$s1 .= $sLimit;
		
			$sheet1 = $db->query($s1);
			
			$sheet_total = $db->query("
				SELECT a.kode
				FROM data_daftar_kotak_penyimpanan a
				LEFT JOIN data_kotak_penyimpanan b ON(a.id_kotak_penyimpanan = b.id)
				WHERE b.id_lab = $id_lab
			".$sSearch)->getNumRows();
		
			$result = array();
			$i=0;
			$no = (isset($start) ? $start + 1 : 1);
			foreach($sheet1->getResult() as $sheet)
			{
				$isi_kotak_button = '<button id="isi-'.str_replace('.', '-', $sheet->kode).'" class="btn btn-success btn-xs" title="isi kotak"><i class="fa fa-list"></i></button>';
				if(has_permission('inventarisEdit'))
				{
					$edit_button = '
						<button id="edit-'.str_replace('.', '-', ($sheet->id_kotak_penyimpanan.'-'.$sheet->kode)).'" class="btn btn-info btn-xs" title="tambah keterangan">
							<i class="fa fa-edit"></i>
						</button>
					';
				}
				else
				{
					$edit_button = '';
				}
				
				if(has_permission('inventarisDelete'))
				{
					$delete_button = '
						<button id="delete-'.str_replace('.', '-', ($sheet->id_kotak_penyimpanan.'-'.$sheet->kode)).'"
						class="btn btn-danger btn-xs" title="hapus data daftarKotakPenyimpanan '.$sheet->kode.'">
							<i class="fa fa-trash"></i>
						</button>
					';
				}
				else
				{
					$delete_button = '';
				}
				
				$result[$i]['0'] = '
					<input id="input-kode-'.str_replace('.', '-', $sheet->kode).'" type="checkbox" class="kode-kotak" name="kode[]" value="'.$sheet->kode.'">
					<input id="input-nama-'.str_replace('.', '-', $sheet->kode).'" type="checkbox" class="nama-kotak" name="nama[]" value="'.$sheet->nama.'" hidden>
				';
				$result[$i]['1'] = $sheet->kode;
				$result[$i]['2'] = $sheet->nama;
				$result[$i]['3'] = $sheet->keterangan;
				$result[$i]['4'] = '
					<div class="text-right">
						' . $isi_kotak_button . $edit_button . $delete_button . '
						<script>
						$("#isi-'.str_replace('.', '-', $sheet->kode).'").click(function(){var id = $(this).attr("id");
							window.location.assign("/data/isiKotakItem/'.urlencode($sheet->kode).'/");});
						$("#edit-'.str_replace('.', '-', ($sheet->id_kotak_penyimpanan.'-'.$sheet->kode)).'").click(function(){
							var id = $(this).attr("id");window.location.assign("/data/daftarKotakPenyimpananEdit/'.urlencode($sheet->kode).'");});
						$("#delete-'.str_replace('.', '-', ($sheet->id_kotak_penyimpanan.'-'.$sheet->kode)).'").click(function(){var title = $(this).attr("title");
							swal({title: "Apakah anda yakin akan meng"+title+"?", text: "Semua data akan dihapus dan tidak dapat dikembalikan!", icon: "warning",
							buttons:{cancel: {visible: true, text : "Batal", className: "btn" }, confirm: {text : "Hapus", className : "btn btn-danger"}}})
							.then((willDelete) => {if (willDelete) {$.ajax({url: "/data/daftarKotakPenyimpananDelete", type: "POST", data: {"id_lab":'.$id_lab.', "kode":"'.$sheet->kode.'"},
							success: function(data){oTable4.fnStandingRedraw();}, cache: false});}});});$("#input-kode-'.str_replace('.', '-', $sheet->kode).'").change(function(){
							$("#input-nama-'.str_replace('.', '-', $sheet->kode).'").prop("checked", $(this).prop("checked"));});
						</script>
					</div>
				';
				$i++;
				$no++;
			}
			
			$data = $result;
			$results = array(
				"iTotalRecords" => ($sheet_total),
				"iTotalDisplayRecords" => ($sheet_total),
				"aaData"=> $data
			);
				
			echo json_encode($results);
		}
    }
	
	public function daftarKotakPenyimpananAdd($id_lab = null)
	{
		function in_array_r($item , $array){
			return preg_match('/"'.preg_quote($item, '/').'"/i' , json_encode($array));
		}
		
		$db = \Config\Database::connect();
		
		//cek izin
		$pengelola = $db->query("SELECT id_pengelola FROM data_pengelola WHERE id_lab = '$id_lab' AND id_pengelola = ".user_id())->getNumRows();
		
		if(has_permission('inventarisAdd') && ($pengelola != 0 || in_groups('admin')))
		{
			$data['title'] = 'Tambah Kotak Penyimpanan';
			$data['open'] = 9;
			$data['active'] = 10;
			
			$data['breadcrumbs'] = $db->query("SELECT name, href FROM menu WHERE id = ".$data['open']." OR id = ".$data['active']." ORDER BY parent_id ASC");
			
			if($this->request->getMethod() == 'POST')
			{
				$validation =  \Config\Services::validation();
				
				$validation->setRules([
					'id_kotak_penyimpanan' => ['label' => 'Inventaris', 'rules' => 'required'],
					'jml_kotak_penyimpanan' => ['label' => 'Jumlah Kotak Penyimpanan', 'rules' => 'required']
				]);
				
				$isDataValid = $validation->withRequest($this->request)->run();
				
				if($isDataValid)
				{
					$id_kotak_penyimpanan			=	$this->request->getPost('id_kotak_penyimpanan');
					$jml_kotak_penyimpanan			=	$this->request->getPost('jml_kotak_penyimpanan');
					$added_by						=	user_id();
					$added_date						=	date('Y-m-d H:i:s');
					$updated_by						=	user_id();
					$updated_date					=	date('Y-m-d H:i:s');
					
					$kotak_penyimpanan				=	$db->query("
															SELECT code_prefix, code_suffix, code_length
															FROM data_kotak_penyimpanan
															WHERE id = $id_kotak_penyimpanan
														")->getRow();
					
					$daftar_kotak_penyimpanan		=	$db->query("
															SELECT kode
															FROM data_daftar_kotak_penyimpanan
															ORDER BY kode ASC
														")->getResultArray();
					
					$daftar_kotak_penyimpanan_code	=	array_map (
						function($value){
							return $value['kode'];
						},
						$daftar_kotak_penyimpanan
					);
					
					$jml_isi_kotak = $db->query("
						SELECT SUM(a.jml) AS jml
						FROM rel_inventaris_kotak_penyimpanan a
						LEFT JOIN data_inventaris b ON(a.id_inventaris = b.id)
						WHERE a.id_kotak_penyimpanan = $id_kotak_penyimpanan
						GROUP BY a.id_kotak_penyimpanan
					")->getRow()->jml;
					
					$isi_kotaks = $db->query("
						SELECT a.id_inventaris, a.jml, b.code_prefix, b.code_suffix, b.code_length, b.nama AS nama
						FROM rel_inventaris_kotak_penyimpanan a
						LEFT JOIN data_inventaris b ON(a.id_inventaris = b.id)
						WHERE a.id_kotak_penyimpanan = $id_kotak_penyimpanan
					");
					
					$lowest = $db->query("
						SELECT a.kode AS kode, c.nama AS nama,
						COALESCE(
							SUM(
								CASE WHEN (
									a.kode_kotak_penyimpanan IS NULL
						 		) THEN 1
								ELSE 0
								END
							), 0
					 	) AS jml,
						b.jml AS jml_target
						FROM data_item a
						LEFT JOIN rel_inventaris_kotak_penyimpanan b ON(b.id_inventaris = a.id_inventaris)
						LEFT JOIN data_inventaris c ON(a.id_inventaris = c.id)
						WHERE b.id_kotak_penyimpanan = $id_kotak_penyimpanan
						GROUP BY a.id_inventaris  
						ORDER BY jml ASC
					")->getRow();
					
					$x = 1;
					$n = 1;
					$ok = 0;
					
					$q_array = array();
					
					if(($jml_kotak_penyimpanan * $lowest->jml_target) <= $lowest->jml)
					{
						while($x <= $jml_kotak_penyimpanan)
						{
							$kode_kotak_penyimpanan = $kotak_penyimpanan->code_prefix.(sprintf("%0".$kotak_penyimpanan->code_length."d", ($n))).$kotak_penyimpanan->code_suffix;
							$n2 = 0;
							
							if(!in_array($kode_kotak_penyimpanan, $daftar_kotak_penyimpanan_code))
							{
								foreach($isi_kotaks->getResult() as $isi_kotak)
								{
									$x3 = 1;
									$n3 = 1;
									
									while($x3 <= $isi_kotak->jml)
									{
										$n4 = 1;
										$x4 = 0;
										while($x4 == 0)
										{
											$kode_item	=	$isi_kotak->code_prefix . (sprintf("%0" . $isi_kotak->code_length."d", $n4)) . $isi_kotak->code_suffix;
											
											if(!in_array_r($kode_item, $q_array))
											{
												$item_tersedia = $db->query("
													SELECT kode
													FROM data_item
													WHERE kode = '$kode_item'
													AND kode_kotak_penyimpanan IS NULL
												")->getNumRows();
												
												if($item_tersedia == 1)
												{
													$q_array[$kode_kotak_penyimpanan][] = $kode_item;
													$n2++;
													$x4++;
													$x3++;
												}
											}
											$n4++;
										}
										$n3++;
									}
								}
								$x++;
							}
							
							if($n2 == $jml_isi_kotak)
							{
								$ok++;
							}
							else
							{
								unset($q_array[$kode_kotak_penyimpanan]);
							}
							
							$n++;
						}
					}
					else
					{
						return redirect()->back()->withInput()->with('error', 'Jumlah ' . $lowest->nama . ' tidak memenuhi');
					}
					
					foreach($q_array as $key1 => $kodes)
					{
						$q1 = "INSERT INTO data_daftar_kotak_penyimpanan (id_kotak_penyimpanan, kode, added_by, added_date, update_by, update_date) VALUES" . PHP_EOL;
						$q1 .= "($id_kotak_penyimpanan, '$key1', ".user_id().", '".date('Y-m-d H:i:s')."', ".user_id().", '".date('Y-m-d H:i:s')."')" . PHP_EOL;
						
						$q2 = "UPDATE data_item" . PHP_EOL . "SET kode_kotak_penyimpanan = '$key1'" . PHP_EOL;
						
						foreach($kodes as $key2 => $kode)
						{
							if($key2 == 0)
							{
								$q2 .= "WHERE kode = '$kode'" . PHP_EOL;
							}
							else
							{
								$q2 .= "OR kode = '$kode'" . PHP_EOL;
							}
						}
						
						$q2 .= PHP_EOL;
						
						$db->query($q1);
						$db->query($q2);
						
					}
					
					if($ok != 0)
					{
						return redirect()->to('/data/inventaris/'.$id_lab)->with('message', $ok . ' Data telah berhasil ditambahkan!');
					}
					else
					{
						return redirect()->back()->withInput()->with('error', 'Data gagal ditambahkan');
					}
				}
				else
				{
					return redirect()->back()->withInput()->with('errors', $validation->getErrors());
				}
			}
			
			$data['db'] = $db;
			$data['lab'] = $db->query("SELECT id, nama FROM data_lab WHERE id = '$id_lab'")->getRow();
			
			return view('data/daftar_kotak_penyimpanan_add', $data);
		}
		else
		{
			return redirect()->back();
		}
	}
	
	public function daftarKotakPenyimpananEdit($kode = null)
	{
		$db = \Config\Database::connect();
		
		$pengelola = $db->query("
			SELECT a.id_pengelola
			FROM data_pengelola a
			LEFT JOIN data_kotak_penyimpanan b ON(b.id_lab = a.id_lab)
			LEFT JOIN data_daftar_kotak_penyimpanan c ON(c.id_kotak_penyimpanan = b.id)
			WHERE c.kode = '$kode'
            AND a.id_pengelola = ".user_id()
		)->getNumRows();
		
		if(has_permission('inventarisEdit') && ($pengelola != 0 || in_groups('admin')))
		{
			$data['title'] = 'Edit Daftar Kotak Penyimpanan';
			$data['open'] = 9;
			$data['active'] = 10;
			
			$data['breadcrumbs'] = $db->query("SELECT name, href FROM menu WHERE id = ".$data['open']." OR id = ".$data['active']." ORDER BY parent_id ASC");
			
			$kotakPenyimpanan = $db->query("
				SELECT a.kode, a.keterangan, b.nama, c.nama AS nama_lab, c.id AS id_lab
				FROM data_daftar_kotak_penyimpanan a
				LEFT JOIN data_kotak_penyimpanan b ON(a.id_kotak_penyimpanan = b.id)
				LEFT JOIN data_lab c ON(b.id_lab = c.id)
				WHERE a.kode = '$kode'
			")->getRow();
			
			if($this->request->getMethod() == 'POST')
			{
				$keterangan	= $this->request->getPost('keterangan');
				
				$db->query("UPDATE data_daftar_kotak_penyimpanan SET keterangan = '$keterangan' WHERE kode = '$kode'");
				
				return redirect()->to('/data/inventaris/'.$kotakPenyimpanan->id_lab)->with('message', 'Data telah berhasil diubah!');
			}
			
			$data['kotakPenyimpanan'] = $kotakPenyimpanan;
			
			return view('data/daftar_kota_penyimpanan_edit', $data);
		}
		else
		{
			return redirect()->back();
		}
	}
	
	public function daftarKotakPenyimpananDelete()
	{
		$db		= \Config\Database::connect();
		
		$id_lab	= $this->request->getPost('id_lab');
		$kode	= $this->request->getPost('kode');
		
		$pengelola = $db->query("SELECT id_pengelola FROM data_pengelola WHERE id_lab = '$id_lab' AND id_pengelola = ".user_id())->getNumRows();
		
		if(has_permission('inventarisDelete') && ($pengelola != 0 || in_groups('admin')))
		{
			$db->query("UPDATE data_item SET kode_kotak_penyimpanan = NULL WHERE kode_kotak_penyimpanan = '$kode'");
			$db->query("DELETE FROM data_daftar_kotak_penyimpanan WHERE kode = '$kode'");
		}
    }
	
	public function daftarKotakPenyimpanansDelete()
	{
		$db		= \Config\Database::connect();
		
		$id_lab	= $this->request->getPost('id_lab');
		$kode	= $this->request->getPost('kode');
		
		$pengelola = $db->query("SELECT id_pengelola FROM data_pengelola WHERE id_lab = '$id_lab' AND id_pengelola = ".user_id())->getNumRows();
		
		if(has_permission('inventarisDelete') && ($pengelola != 0 || in_groups('admin')))
		{
			if(isset($kode))
			{
				$q1 = "UPDATE data_item SET kode_kotak_penyimpanan = NULL";
				$q2 = "DELETE FROM data_daftar_kotak_penyimpanan ";
				$i = 0;
				
				foreach($kode as $row)
				{
					if($i == 0)
					{
						$q1 .= " WHERE kode_kotak_penyimpanan = '$row'";
						$q2 .= " WHERE kode = '$row' ";
					}
					else
					{
						$q1 .= " OR kode_kotak_penyimpanan = '$row'";
						$q2 .= " OR kode = '$row' ";
					}
				$i++;
				}
				
				$db->query($q1);
				$db->query($q2);
			}
		}
    }
	
	public function daftarKotakPenyimpananGetInventaris()
	{
		$db = \Config\Database::connect();
		
		if(has_permission('inventarisAdd'))
		{
			$id_kotak_penyimpanan	= $this->request->getPost('id_kotak_penyimpanan');
			
			if($id_kotak_penyimpanan != '')
			{
				$inventaris				= $db->query("SELECT nama, code_length FROM data_kotak_penyimpanan WHERE id = $id_kotak_penyimpanan")->getRow();
				$jml_kotak_penyimpanan	= $db->query("SELECT kode FROM data_daftar_kotak_penyimpanan WHERE id_kotak_penyimpanan = $id_kotak_penyimpanan")->getNumRows();
				
				$code_length	= str_replace(0, 9, (sprintf("%0".$inventaris->code_length."d", (9))));
				$max			= $code_length - $jml_kotak_penyimpanan;
			
				if($max > 0)
				{
					echo'
						<div class="col-sm-3"><label class="font-weight-bold">Jumlah Kotak Penyimpanan</label></div>
						<div class="col-sm-9">
							<input type="number" min="0" max="'.$max.'"
							step="1" class="form-control" name="jml_kotak_penyimpanan" id="jml_kotak_penyimpanan" placeholder="Jumlah kotak penyimpanan yang akan ditambahkan">
						</div>
				';
				}
				else
				{
					echo '
						<div class="col-sm-3"></div><div class="col-sm-9">
							<p class="text-danger font-weight-bold">Jumlah kotak penyimpanan "'.$inventaris->nama.'" sudah penuh</label>
						</p>
					';
				}
			}
			else
			{
				echo '
					<div class="col-sm-3"><label class="font-weight-bold">Jumlah Kotak Penyimpanan</label></div>
					<div class="col-sm-9">
						<input type="number" class="form-control" name="jml_kotak_penyimpanan" id="jml_kotak_penyimpanan" placeholder="Jumlah item yang akan ditambahkan" disabled>
					</div>
				';
			}
		}
	}
	
	public function generateItemBarcode()
    {
		$session = \Config\Services::session();
		
		if($this->request->getMethod() == 'POST')
		{
			$db = \Config\Database::connect();
			
			$id_lab = $db->escapeString($this->request->getPost('id_lab'));
			$kode	= $this->request->getPost('kode');
			$nama	= $this->request->getPost('nama');
			
			$pengelola = $db->query("SELECT id_pengelola FROM data_pengelola WHERE id_lab = '$id_lab' AND id_pengelola = ".user_id())->getNumRows();
			
			if(has_permission('inventarisView') && ($pengelola != 0 || in_groups('admin')))
			{
				$generator = new Picqer\Barcode\BarcodeGeneratorSVG();
				
				if(isset($kode))
				{
					$color = '#000000';
					$content = '';
					$i = 0;
					
					foreach($kode as $row)
					{
						$barcode = $generator->getBarcode($row, $generator::TYPE_CODE_128, 1, 30, $color);
						$img = '
							<div style="display: inline-block; margin: 0px 10px 10px 0px;">
								<div style="text-align: center; font-size:9px; margin:auto; width:100px; line-height:1;">'.$nama[$i].'</div>
								<img src="data:image/png;base64,'. base64_encode($barcode).'" />
								<div style="text-align: center; font-size:9px;">'.$row.'</div>
							</div>
						';
						$content .= $img;
						if($i == 74) {
							$content .= '<div class="page-break"></div>';
							$i = -1;
						}
						$i ++;
					}
					
					$session->set('content', $content);
				}
				else
				{
					$session->set('content');
				}
			}
		}
		else
		{
			$session->get('content');
			
			$this->response->setHeader("Content-Type", "application/pdf");
			$data['content'] = $session->get('content');
			
			$dompdf = new Dompdf();
			$html = view('data/barcode', $data);
			$dompdf->set_paper('A4', 'portrait');
			$dompdf->loadHtml($html);
			$dompdf->render();
			$dompdf->stream('barcodes.pdf', [ 'Attachment' => false ]);
			
			$session->set('content');
		}
    }

    public function generateKotakBarcode()
    {
		$session = \Config\Services::session();
		
		if($this->request->getMethod() == 'POST')
		{
			$db = \Config\Database::connect();
			
			$id_lab = $db->escapeString($this->request->getPost('id_lab'));
			$kode	= $this->request->getPost('kode');
			$nama	= $this->request->getPost('nama');
			
			$pengelola = $db->query("SELECT id_pengelola FROM data_pengelola WHERE id_lab = '$id_lab' AND id_pengelola = ".user_id())->getNumRows();
			
			if(has_permission('inventarisView') && ($pengelola != 0 || in_groups('admin')))
			{
				$generator = new Picqer\Barcode\BarcodeGeneratorSVG();
				
				if(isset($kode))
				{
					$color = '#000000';
					$content = '';
					$i = 0;
					$n = 1;
					
					foreach($kode as $row)
					{
						$barcode = $generator->getBarcode($row, $generator::TYPE_CODE_128, 2.5, 60, $color);
						$img = '
							<div style="display: inline-block; margin: 0px 10px 10px 0px;">
								<div style="text-align: center; font-size:12px; margin:auto; width:250px; line-height:1;">'.$nama[$i].'</div>
								<img src="data:image/png;base64,'. base64_encode($barcode).'" />
								<div style="text-align: center; font-size:12px;">'.$row.'</div>
							</div>
						';
						$content .= $img;
						if($n == 20) {
							$content .= '<div class="page-break"></div>';
							$n = 0;
						}
						$i ++;
						$n ++;
					}
					
					$session->set('content', $content);
				}
				else
				{
					$session->set('content');
				}
			}
		}
		else
		{
			$session->get('content');
			
			$this->response->setHeader("Content-Type", "application/pdf");
			$data['content'] = $session->get('content');
			
			$dompdf = new Dompdf();
			$html = view('data/barcode', $data);
			$dompdf->set_paper('A4', 'portrait');
			$dompdf->loadHtml($html);
			$dompdf->render();
			$dompdf->stream('barcodes.pdf', [ 'Attachment' => false ]);
			
			$session->set('content');
		}
    }
	
}
