<?php

namespace App\Controllers;

use App\Models\Administrator\GroupPermissionModel;
use App\Models\Administrator\GroupUserModel;
use App\Models\Administrator\MenuModel;
use App\Models\Administrator\MenuPermissionModel;
use Myth\Auth\Config\Auth as AuthConfig;
use Myth\Auth\Entities\User;
use Myth\Auth\Models\UserModel;
use Myth\Auth\Models\GroupModel;
use Myth\Auth\Models\PermissionModel;

class Administrator extends BaseController
{
	protected $auth;

	/**
	 * @var AuthConfig
	 */
	protected $config;

	/**
	 * @var Session
	 */
	protected $session;

	public function __construct()
	{
		// Most services in this controller require
		// the session to be started - so fire it up!
		$this->session = service('session');

		$this->config = config('Auth');
		$this->auth   = service('authentication');
	}
	
	public function user()
    {
		if(has_permission('userView'))
		{
			$data['title'] = 'Pengaturan Pengguna';
			$data['open'] = 2;
			$data['active'] = 3;
			
			$db = \Config\Database::connect();
			$session = \Config\Services::session();
			
			$data['breadcrumbs'] = $db->query("SELECT name, href FROM menu WHERE id = ".$data['open']." OR id = ".$data['active']." ORDER BY parent_id ASC");
			
			$data['search'] = $session->get('searchUserList');
			$data['start'] = $session->get('startUserList');
			$data['length'] = $session->get('lengthUserList');
			$data['orderCol'] = $session->get('orderColUserList');
			$data['orderDir'] = $session->get('orderDirUserList');
			
			return view('administrator/user', $data);
		}
		else
		{
			return redirect()->back();
		}
    }
	
	public function userList()
    {
		if(has_permission('userView'))
		{
			$db = \Config\Database::connect();
			$session = \Config\Services::session();
			
			//Searching
			
			$search = $db->escapeString($this->request->getPost('search'));
			$session->set('searchUserList', $search);
			
			$sSearch = '';
			
			if(isset($search) && $search != '')
			{
				$sSearch .= "
					AND (
						a.fullname LIKE '%".$search."%'
						OR a.email LIKE '%".$search."%'
						OR a.username LIKE '%".$search."%'
						OR c.name LIKE '%".$search."%'
					)
				";
			}
		
			//Limit
			
			$start = $this->request->getPost("start");
			$session->set('startUserList', $start);
			$length = $this->request->getPost("length");
			$session->set('lengthUserList', $length);
			
			if (isset($start) && $start != '-1' )
			{
				$sLimit = " LIMIT ".intval($start).", ".intval($length);
			}
			
			//Ordering
			
			$orderCol = $this->request->getPost("order[0][column]");
			$session->set('orderColUserList', $orderCol);
			$orderDir = $this->request->getPost("order[0][dir]");
			$session->set('orderDirUserList', $orderDir);
			$columnName = $this->request->getPost("columns[".$orderCol."][name]");
			
			if (isset($columnName) && $columnName != '')
			{
				$sOrder = " ORDER BY ".$columnName." ".$orderDir." ";
			}
			else
			{
				$sOrder = " ORDER BY a.id ASC ";
			}
			
			//Table Row
			
			$s1 = "
				SELECT a.id, a.fullname, a.email, a.username, a.active, a.created_at, a.updated_at, GROUP_CONCAT(c.name SEPARATOR ', ') AS groups
				FROM users a
                LEFT JOIN auth_groups_users b ON(b.user_id = a.id)
                LEFT JOIN auth_groups c ON(b.group_id = c.id)
				WHERE 1=1
			";
			$s1 .= $sSearch;
			$s1 .= " GROUP BY a.id ";
			$s1 .= $sOrder;
			$s1 .= $sLimit;
		
			$sheet1 = $db->query($s1);
			
			$sheet_total = $db->query("
				SELECT a.id FROM users a LEFT JOIN auth_groups_users b ON(b.user_id = a.id) LEFT JOIN auth_groups c ON(b.group_id = c.id) WHERE 1=1
			".$sSearch." GROUP BY a.id ")->getNumRows();
		
			$result = array();
			$i=0;
			$no = (isset($start) ? $start + 1 : 1);
			foreach($sheet1->getResult() as $sheet)
			{
				
				$result[$i]['0'] =	$sheet->id;
				$result[$i]['1'] =	$sheet->fullname;
				$result[$i]['2'] =	$sheet->email;
				$result[$i]['3'] =	$sheet->username;
				$result[$i]['4'] = 	$sheet->groups;
				$result[$i]['5'] =	'
					<button id="'.$sheet->id.'" class="btn btn-flat btn-'.($sheet->active == 1 ? 'primary' : 'danger').' btn-xs activeBtn"
					title="'.($sheet->active == 1 ? 'Nonaktifkan' : 'Aktifan').' pengguna '.$sheet->username.'">'
					.($sheet->active == 1 ? 'Aktif' : 'Nonaktif').'</button>
					<script>
						$(".activeBtn").click(function() {var id = $(this).attr("id");var title = $(this).attr("title");
						swal({title: title+"?", icon: "warning",
						buttons:{cancel: {visible: true, text : "Cancel", className: "btn"}, confirm: {text : "Ya", className : "btn btn-flat btn-warning"}}})
						.then((willActive) => {if (willActive) {$.ajax({url: "/admin/userActive", type: "POST", data: {"id":id}, cache: false});oTable.fnDraw();}});});
					</script>
				';
				$result[$i]['6'] =	$sheet->created_at;
				$result[$i]['7'] =	$sheet->updated_at;
				$result[$i]['8'] =	'
					<div class="text-right" style="width:98px">
						<button id="'.$sheet->id.'" class="btn btn-flat btn-info btn-xs edit" title="edit '.$sheet->username.' user"><i class="fa fa-edit"></i></button>
						<button id="'.$sheet->id.'" class="btn btn-flat btn-warning btn-xs reset" title="atur ulang kata sandi untuk pengguna '.$sheet->username.'"><i class="fa fa-key"></i></button>
						<button id="'.$sheet->id.'" class="btn btn-flat btn-danger btn-xs delete" title="hapus pengguna '.$sheet->username.'"><i class="fa fa-trash"></i></button>
						<script>
						$(".edit").click(function(){var id = $(this).attr("id");window.location.assign("/admin/userEdit/"+id);});
						$(".reset").click(function() {var id = $(this).attr("id");var title = $(this).attr("title");
						swal({title: "Apakah anda yakin akan meng"+title+"?", text: "", icon: "warning",
						buttons:{cancel: {visible: true, text : "Cancel", className: "btn"}, confirm: {text : "Atur ulang", className : "btn btn-flat btn-warning"}}})
						.then((willReset) => {if (willReset) {var id = $(this).attr("id");window.location.assign("userPasswordReset/"+id);}});});
						$(".delete").click(function() {var id = $(this).attr("id");var title = $(this).attr("title");
						swal({title: "Apakah and yakin akan meng"+title+"?", text: "Data yang telah dihapus tidak bisa dikembalikan!", icon: "warning",
						buttons:{cancel: {visible: true, text : "Cancel", className: "btn"}, confirm: {text : "Hapus", className : "btn btn-flat btn-danger"}}})
						.then((willDelete) => {if (willDelete) {$.ajax({url: "/admin/userDelete", type: "POST", data: {"id":id}, cache: false});oTable.fnDraw();}});});
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
	
	public function userAdd()
	{
		if(has_permission('userAdd'))
		{
			$data['title'] = 'Tambah Pengguna';
			$data['open'] = 2;
			$data['active'] = 3;
			
			$db = \Config\Database::connect();
			$session = \Config\Services::session();
			
			$data['breadcrumbs'] = $db->query("SELECT name, href FROM menu WHERE id = ".$data['open']." OR id = ".$data['active']." ORDER BY parent_id ASC");
			$groupList = $db->query("SELECT id, name, description FROM auth_groups ORDER BY description ASC");
			$data['groupList'] = $groupList;
			
			if($this->request->getMethod() == 'POST')
			{
				$users = model(UserModel::class);
		
				// Validate basics first since some password rules rely on these fields
				$rules = config('Validation')->registrationRules ?? [
					'fullname' => ['label' => 'Nama lengkap', 'rules' => 'required'],
					'username' => ['label' => lang('Auth.username'), 'rules' => 'required|alpha_numeric_space|min_length[3]|max_length[30]|is_unique[users.username]'],
					'email'	=> ['label' => lang('Auth.email'), 'rules' => 'required|valid_email|is_unique[users.email]'],
				];
		
				if (!$this->validate($rules)) {
					return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
				}
		
				// Validate passwords since they can only be validated properly here
				$rules = [
					'password' => ['label' => lang('Auth.password'), 'rules' => 'required'],
					'pass_confirm' => ['label' => lang('Auth.repeatPassword'), 'rules' => 'required|matches[password]'],
				];
		
				if (!$this->validate($rules)) {
					return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
				}
		
				// Save the user
				$allowedPostFields = array_merge(['password'], $this->config->validFields, $this->config->personalFields);
				$user			  = new User($this->request->getPost($allowedPostFields));
		
				$user->activate();
		
				// Ensure default group gets assigned if set
				/*if (!empty($this->config->defaultUserGroup)) {
					$users = $users->withGroup($this->config->defaultUserGroup);
				}*/
				
				if (!$users->save($user)) {
					return redirect()->back()->withInput()->with('errors', $users->errors());
				}
				
				$groupModel = model(GroupModel::class);
				$userId = $db->query("SELECT id FROM users ORDER BY id ASC")->getLastRow()->id;
				
				foreach($groupList->getResult() as $gL) {
					$groupId = $this->request->getPost($gL->name);
					
					if(isset($groupId) && $groupId != '') {
						$groupModel->addUserToGroup($userId, $groupId);
					}
				}
		
				// Success!
				return redirect()->to('/admin/users')->with('message', 'Pengguna berhasil ditambahkan!');
			}
			
			return view('administrator/user_add', $data);
		}
		else
		{
			return redirect()->back();
		}
	}
	
	public function userEdit($id=null)
	{
		if(has_permission('userEdit'))
		{
			$data['title'] = 'Edit User';
			$data['open'] = 2;
			$data['active'] = 3;
			
			$db = \Config\Database::connect();
			
			$data['breadcrumbs'] = $db->query("SELECT name, href FROM menu WHERE id = ".$data['open']." OR id = ".$data['active']." ORDER BY parent_id ASC");
			$groupList = $db->query("
				SELECT a.id, a.name, a.description, IF(EXISTS (SELECT b.group_id FROM auth_groups_users b WHERE b.group_id = a.id AND b.user_id = $id), 'checked', '') AS checked
				FROM auth_groups a
				ORDER BY description ASC
			");
			$data['groupList'] = $groupList;
			
			if($this->request->getMethod() == 'POST')
			{
				// Validate basics first since some password rules rely on these fields
				$rules = config('Validation')->registrationRules ?? [
					'fullname' => ['label' => 'Nama lengkap', 'rules' => 'required'],
					'username' => ['label' => lang('Auth.username'), 'rules' => 'required|alpha_numeric_space|min_length[3]|max_length[30]|is_unique[users.username,id,'.$id.']'],
					'email'	=> ['label' => lang('Auth.email'), 'rules' => 'required|valid_email|is_unique[users.email,id,'.$id.']'],
				];
		
				if (!$this->validate($rules)) {
					return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
				}
				
				if (null === $id) {
					return redirect()->back()->with('error', lang('Auth.forgotNoUser'));
				}
				
				$fullname	= $this->request->getPost('fullname');
				$username	= $this->request->getPost('username');
				$email		= $this->request->getPost('email');
				
				$db->query("UPDATE users SET fullname = '$fullname', username = '$username', email = '$email' WHERE id = $id");
				
				$groupModel = model(GroupModel::class);
				
				$db->query("DELETE FROM auth_groups_users WHERE user_id = $id");
				
				foreach($groupList->getResult() as $gL) {
					$groupId = $this->request->getPost($gL->name);
					
					if(isset($groupId) && $groupId != '') {
						$groupModel->addUserToGroup($id, $groupId);
					}
				}
				
				return redirect()->to('/admin/users')->with('message', 'Perubahan berhasil disimpan.');
			}
			
			$data['userRow'] = $db->query("SELECT id, fullname, email, username FROM users WHERE id = $id")->getRow();
			return view('administrator/user_edit', $data);
		}
		else
		{
			return redirect()->back();
		}
	}
	
	public function userPasswordReset($id=null)
	{
		if(has_permission('userEdit'))
		{
			$data['title'] = 'Atur Ulang Kata Sandi';
			$data['open'] = 2;
			$data['active'] = 3;
			
			$db = \Config\Database::connect();
			
			$data['breadcrumbs'] = $db->query("SELECT name, href FROM menu WHERE id = ".$data['open']." OR id = ".$data['active']." ORDER BY parent_id ASC");
			
			$userRow = $db->query("SELECT email FROM users WHERE id = $id")->getRow();
			
			$users = model(UserModel::class);
			
			$user = $users->where('email', $userRow->email)->first();
			
			if (null === $user) {
				return redirect()->back()->with('error', lang('Auth.forgotNoUser'));
			}
			
			if($this->request->getMethod() == 'get')
			{
				$user->generateResetHash();
				$users->save($user);
			}
			
			$resetter = service('resetter');
			
			if($this->request->getMethod() == 'POST')
			{
				if ($this->config->activeResetter === null) {
					return redirect()->to('/admin/users')->with('error', lang('Auth.forgotDisabled'));
				}
		
				// Validate basics first since some password rules rely on these fields
				$rules = config('Validation')->registrationRules ?? [
					'token' => ['label' => 'Token', 'rules' => 'required'],
					'password' => ['label' => lang('Auth.password'), 'rules' => 'required'],
					'pass_confirm' => ['label' => lang('Auth.repeatPassword'), 'rules' => 'required|matches[password]'],
				];
		
				if (!$this->validate($rules)) {
					return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
				}
				
				$user1 = $users->where('email', $userRow->email)->where('reset_hash', $this->request->getPost('token'))->first();
				
				if (null === $user1) {
					return redirect()->back()->with('error', lang('Auth.forgotNoUser'));
				}
				
				// Reset token still valid?
				if (!empty($user1->reset_expires) && time() > $user1->reset_expires->getTimestamp()) {
					return redirect()->back()->withInput()->with('error', lang('Auth.resetTokenExpired'));
				}
				
				// Success! Save the new password, and cleanup the reset hash.
				$user1->password = $this->request->getPost('password');
				$user1->reset_hash = null;
				$user1->reset_at = date('Y-m-d H:i:s');
				$user1->reset_expires = null;
				$user1->force_pass_reset = false;
				$users->save($user1);
				
				return redirect()->to('/admin/users')->with('message', 'Kata sandi telah diatur ulang.');
			}
			
			$data['username'] = $user->username;
			$data['reset_hash'] = $user->reset_hash;
			
			return view('administrator/user_password_reset', $data);
		}
		else
		{
			return redirect()->back();
		}
	}
	
	public function userDelete()
	{
		if(has_permission('userDelete'))
		{
			$id = $this->request->getPost('id');
			
			if($id != 1)
			{
				$db = \Config\Database::connect();
				$db->query("DELETE FROM auth_users_permissions WHERE user_id = $id");
				$db->query("DELETE FROM auth_groups_users WHERE user_id = $id");
				$db->query("DELETE FROM users WHERE id = $id AND id != 1");
			}
			
		}
    }
	
	public function userActive()
	{
		if(has_permission('userEdit'))
		{
			$id = $this->request->getPost('id');
			
			if($id != 1)
			{
				$db = \Config\Database::connect();
				$active_status = $db->query("SELECT active FROM users WHERE id = $id")->getRow()->active;
				$active = ($active_status == 1 ? 0 : 1);
				$db->query("UPDATE users SET active = $active WHERE id = $id AND id != 1");
			}
			
		}
    }
	
	public function group()
    {
		if(has_permission('groupView'))
		{
			$data['title'] = 'Pengaturan Grup';
			$data['open'] = 2;
			$data['active'] = 4;
			
			$db = \Config\Database::connect();
			$session = \Config\Services::session();
			
			$data['breadcrumbs'] = $db->query("SELECT name, href FROM menu WHERE id = ".$data['open']." OR id = ".$data['active']." ORDER BY parent_id ASC");
			
			$data['search'] = $session->get('searchGroupList');
			$data['start'] = $session->get('startGroupList');
			$data['length'] = $session->get('lengthGroupList');
			$data['orderCol'] = $session->get('orderColGroupList');
			$data['orderDir'] = $session->get('orderDirGroupList');
			
			return view('administrator/group', $data);
		}
		else
		{
			return redirect()->back();
		}
    }
	
	public function groupList()
    {
		if(has_permission('groupView'))
		{
			$db = \Config\Database::connect();
			$session = \Config\Services::session();
			
			//Searching
			
			$search = $db->escapeString($this->request->getPost('search'));
			$session->set('searchGroupList', $search);
			
			$sSearch = '';
			
			$sSearch = '';
			
			if(isset($search) && $search != '')
			{
				$sSearch .= " AND (a.name LIKE '%".$search."%' OR a.description LIKE '%".$search."%') ";
			}
		
			//Limit
			
			$start = $this->request->getPost("start");
			$session->set('startGroupList', $start);
			$length = $this->request->getPost("length");
			$session->set('lengthGroupList', $length);
			
			if (isset($start) && $start != '-1' )
			{
				$sLimit = " LIMIT ".intval($start).", ".intval($length);
			}
			
			//Ordering
			
			$orderCol = $this->request->getPost("order[0][column]");
			$session->set('orderColGroupList', $orderCol);
			$orderDir = $this->request->getPost("order[0][dir]");
			$session->set('orderDirGroupList', $orderDir);
			$columnName = $this->request->getPost("columns[".$orderCol."][name]");
			
			if (isset($columnName) && $columnName != '')
			{
				$sOrder = " ORDER BY ".$columnName." ".$orderDir." ";
			}
			else
			{
				$sOrder = " ORDER BY a.id ASC ";
			}
			
			//Table Row
			
			$s1 = "SELECT a.id, a.name, a.description FROM auth_groups a WHERE 1=1 ";
			$s1 .= $sSearch;
			$s1 .= $sOrder;
			$s1 .= $sLimit;
		
			$sheet1 = $db->query($s1);
			
			$sheet_total = $db->query("SELECT a.id FROM auth_groups a WHERE 1=1 ".$sSearch)->getNumRows();
		
			$result = array();
			$i=0;
			$no = (isset($start) ? $start + 1 : 1);
			foreach($sheet1->getResult() as $sheet)
			{
				
				$result[$i]['0'] = $sheet->id;
				$result[$i]['1'] = $sheet->name;
				$result[$i]['2'] = $sheet->description;
				$result[$i]['3'] = '
					<div class="text-right">
						<button id="'.$sheet->id.'" class="btn btn-flat btn-info btn-xs edit" title="edit"><i class="fa fa-edit"></i></button>
						<button id="'.$sheet->id.'" class="btn btn-flat btn-danger btn-xs delete" title="hapus grup '.$sheet->name.'"><i class="fa fa-trash"></i></button>
						<script>
						$(".edit").click(function(){var id = $(this).attr("id");window.location.assign("groupEdit/"+id);});
						$(".delete").click(function() {var id = $(this).attr("id");var title = $(this).attr("title");
						swal({title: "Apakah and yakin akan meng"+title+"?", text: "Data yang telah dihapus tidak bisa dikembalikan!", icon: "warning",
						buttons:{cancel: {visible: true, text : "Cancel", className: "btn"}, confirm: {text : "Hapus", className : "btn btn-flat btn-danger"}}})
						.then((willDelete) => {if (willDelete) {$.ajax({url: "groupDelete", type: "POST", data: {"id":id}, cache: false});oTable.fnDraw();}});});
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
	
	public function groupAdd()
	{
		if(has_permission('groupAdd'))
		{
			$data['title'] = 'Tambah Grup';
			$data['open'] = 2;
			$data['active'] = 4;
			
			$db = \Config\Database::connect();
			
			$data['breadcrumbs'] = $db->query("SELECT name, href FROM menu WHERE id = ".$data['open']." OR id = ".$data['active']." ORDER BY parent_id ASC");
			$permissionList = $db->query("SELECT id, name, description FROM auth_permissions ORDER BY name ASC");
			$data['permissionList'] = $permissionList;
			
			if($this->request->getMethod() == 'POST')
			{
				$validation =  \Config\Services::validation();
				
				$validation->setRules([
					'name' => ['label' => 'Group name', 'rules' => 'required|is_unique[auth_groups.name]']
				]);
				
				$isDataValid = $validation->withRequest($this->request)->run();
				
				if($isDataValid)
				{
					$groupModel = new GroupModel();
					$groupPermissionModel = new GroupPermissionModel();
					
					$groupModel->insert([
						'name'			=> $this->request->getPost('name'),
						'description'	=> $this->request->getPost('description')
					]);
						
					$groupId = $db->query("SELECT id FROM auth_groups ORDER BY id ASC")->getLastRow()->id;
					
					$db->query("DELETE FROM auth_groups_permissions WHERE group_id = ".$groupId);
					
					foreach($permissionList->getResult() as $pL) {
						$permissionId = $this->request->getPost($pL->name);
						
						if(isset($permissionId) && $permissionId != '') {
							$groupPermissionModel->insert(['group_id' => $groupId, 'permission_id' => $permissionId]);
						}
					}
					
					return redirect()->to('/admin/groups')->with('message', 'Grup berhasil ditambahkan!');
				}
				else
				{
					return redirect()->back()->withInput()->with('errors', $validation->getErrors());
				}
			}
			
			return view('administrator/group_add', $data);
		}
		else
		{
			return redirect()->back();
		}
	}
	
	public function groupEdit($id=null)
	{
		if(has_permission('groupEdit'))
		{
			$data['title'] = 'Edit Grup';
			$data['open'] = 2;
			$data['active'] = 4;
			
			$db = \Config\Database::connect();
			
			$data['breadcrumbs'] = $db->query("SELECT name, href FROM menu WHERE id = ".$data['open']." OR id = ".$data['active']." ORDER BY parent_id ASC");
			
			$permissionList = $db->query("
				SELECT a.id, a.name, a.description,
					IF(EXISTS (SELECT b.group_id FROM auth_groups_permissions b WHERE b.group_id = $id AND b.permission_id = a.id), 'checked', '') AS checked
				FROM auth_permissions a
				ORDER BY a.name ASC
			");
			
			$data['permissionList'] = $permissionList;
			
			if($this->request->getMethod() == 'POST')
			{
				$validation =  \Config\Services::validation();
				
				$validation->setRules([
					'nama' => ['label' => 'Group name', 'rules' => 'required|is_unique[auth_groups.name,id,'.$id.']']
				]);
				
				$isDataValid = $validation->withRequest($this->request)->run();
				
				$groupModel = new GroupModel();
				
				if($isDataValid)
				{
					$groupPermissionModel = new GroupPermissionModel();
					
					$groupModel->update($id, [
						'nama'			=> $this->request->getPost('nama'),
						'description'	=> $this->request->getPost('description')
					]);
					
					$db->query("DELETE FROM auth_groups_permissions WHERE group_id = ".$id);
					
					foreach($permissionList->getResult() as $pL) {
						$permissionId = $this->request->getPost($pL->name);
						
						if(isset($permissionId) && $permissionId != '') {
							$groupPermissionModel->insert(['group_id' => $id, 'permission_id' => $permissionId]);
						}
					}
					
					return redirect()->to('/admin/groups')->with('message', 'Perubahan berhasil disimpan!');
				}
				else
				{
					return redirect()->back()->withInput()->with('errors', $validation->getErrors());
				}
			}
			
			$data['groupRow'] = $db->query("SELECT id, name, description FROM auth_groups WHERE id = $id")->getRow();
			return view('administrator/group_edit', $data);
		}
		else
		{
			return redirect()->back();
		}
	}
	
	public function groupDelete()
	{
		if(has_permission('groupDelete'))
		{
			$id = $this->request->getPost('id');
			
			if($id != 1)
			{
				$db = \Config\Database::connect();
				$db->query("DELETE FROM auth_groups_permissions WHERE group_id = $id");
				$db->query("DELETE FROM auth_groups_users WHERE group_id = $id");
				
				$groupModel = new GroupModel();
				$groupModel->delete($id);
			}
		}
    }
	
    public function menu()
    {
		if(has_permission('menuView'))
		{
			$data['title'] = 'Pengaturan Menu';
			$data['open'] = 2;
			$data['active'] = 5;
			
			$db = \Config\Database::connect();
			$session = \Config\Services::session();
			
			$data['breadcrumbs'] = $db->query("SELECT name, href FROM menu WHERE id = ".$data['open']." OR id = ".$data['active']." ORDER BY parent_id ASC");
			
			$data['search'] = $session->get('searchMenuList');
			$data['start'] = $session->get('startMenuList');
			$data['length'] = $session->get('lengthMenuList');
			$data['orderCol'] = $session->get('orderColMenuList');
			$data['orderDir'] = $session->get('orderDirMenuList');
			
			return view('administrator/menu', $data);
		}
		else
		{
			return redirect()->back();
		}
    }
	
	public function menuList()
    {
		if(has_permission('menuView'))
		{
			$db = \Config\Database::connect();
			$session = \Config\Services::session();
			
			//Searching
			
			$search = $db->escapeString($this->request->getPost('search'));
			$session->set('searchMenuList', $search);
			
			$sSearch = '';
			
			if(isset($search) && $search != '')
			{
				$sSearch .= "
					AND (
						a.name LIKE '%".$search."%'
						OR b.name LIKE '%".$search."%'
						OR a.href LIKE '%".$search."%'
						OR a.icon LIKE '%".$search."%'
						OR a.order_number LIKE '%".$search."%'
					)
				";
			}
		
			//Limit
			
			$start = $this->request->getPost("start");
			$session->set('startMenuList', $start);
			$length = $this->request->getPost("length");
			$session->set('lengthMenuList', $length);
			
			if (isset($start) && $start != '-1' )
			{
				$sLimit = " LIMIT ".intval($start).", ".intval($length);
			}
			
			//Ordering
			
			$orderCol = $this->request->getPost("order[0][column]");
			$session->set('orderColMenuList', $orderCol);
			$orderDir = $this->request->getPost("order[0][dir]");
			$session->set('orderDirMenuList', $orderDir);
			$columnName = $this->request->getPost("columns[".$orderCol."][name]");
			
			if (isset($columnName) && $columnName != '')
			{
				$sOrder = " ORDER BY ".$columnName." ".$orderDir." ";
			}
			else
			{
				$sOrder = " ORDER BY COALESCE(a.parent_id, a.id), a.parent_id != 0, a.order_number ";
			}
			
			//Table Row
			
			$s1 = "
				SELECT a.id, a.name, a.parent_id, a.href, a.icon, a.order_number, a.active, b.name as parent_name
				FROM menu a
				LEFT JOIN menu b ON(a.parent_id = b.id)
				WHERE 1=1
			";
			$s1 .= $sSearch;
			$s1 .= $sOrder;
			$s1 .= $sLimit;
		
			$sheet1 = $db->query($s1);
			
			$sheet_total = $db->query("SELECT a.id FROM menu a LEFT JOIN menu b ON(a.parent_id = b.id) WHERE 1=1 ".$sSearch)->getNumRows();
		
			$result = array();
			$i=0;
			$no = (isset($start) ? $start + 1 : 1);
			foreach($sheet1->getResult() as $sheet)
			{
				
				$result[$i]['0'] = $sheet->id;
				$result[$i]['1'] = $sheet->name;
				$result[$i]['2'] = $sheet->href;
				$result[$i]['3'] = $sheet->parent_name;
				$result[$i]['4'] = $sheet->order_number;
				$result[$i]['5'] = $sheet->icon;
				$result[$i]['6'] = ($sheet->active == 1 ? 'Aktif' : 'Tidak aktif');
				$result[$i]['7'] = '
					<div class="text-right">
						<button id="'.$sheet->id.'" class="btn btn-flat btn-info btn-xs edit" title="edit"><i class="fa fa-edit"></i></button>
						<button id="'.$sheet->id.'" class="btn btn-flat btn-danger btn-xs delete" title="hapus menu '.$sheet->name.'"><i class="fa fa-trash"></i></button>
						<script>
						$(".edit").click(function(){var id = $(this).attr("id");window.location.assign("menuEdit/"+id);});
						$(".delete").click(function() {var id = $(this).attr("id");var title = $(this).attr("title");
						swal({title: "Apakah and yakin akan meng"+title+"?", text: "Data yang telah dihapus tidak bisa dikembalikan!", icon: "warning",
						buttons:{cancel: {visible: true, text : "Cancel", className: "btn"}, confirm: {text : "Hapus", className : "btn btn-flat btn-danger"}}})
						.then((willDelete) => {if (willDelete) {$.ajax({url: "/menuDelete", type: "POST", data: {"id":id}, cache: false});oTable.fnDraw();}});});
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
	
	public function menuAdd()
	{
		if(has_permission('menuAdd'))
		{
			$data['title'] = 'Tambah Menu';
			$data['open'] = 2;
			$data['active'] = 5;
			
			$db = \Config\Database::connect();
			
			$data['breadcrumbs'] = $db->query("SELECT name, href FROM menu WHERE id = ".$data['open']." OR id = ".$data['active']." ORDER BY parent_id ASC");
			$data['menuParentList'] = $db->query("SELECT id, name FROM menu WHERE parent_id = 0 ORDER BY order_number ASC");
			$permissionList = $db->query("SELECT id, name, description FROM auth_permissions ORDER BY name ASC");
			$data['permissionList'] = $permissionList;
			
			$validation =  \Config\Services::validation();
			
			$validation->setRules([
				'name' => 'required',
				'href' => 'required'
			]);
			
			$isDataValid = $validation->withRequest($this->request)->run();
			
			if($isDataValid)
			{
				$menuModel = new MenuModel();
				$menuPermissionModel = new MenuPermissionModel();
				
				$menuModel->insert([
					'name'			=> $this->request->getPost('name'),
					'parent_id'		=> $this->request->getPost('parent_id'),
					'href'			=> $this->request->getPost('href'),
					'icon'			=> $this->request->getPost('icon'),
					'order_number'	=> $this->request->getPost('order_number'),
					'active'		=> $this->request->getPost('active')
				]);
				
				$menuId = $db->query("SELECT id FROM menu ORDER BY id ASC")->getLastRow()->id;
				
				$db->query("DELETE FROM menu_permissions WHERE menu_id = ".$menuId);
				
				foreach($permissionList->getResult() as $pL) {
					$permissionId = $this->request->getPost($pL->name);
					
					if(isset($permissionId) && $permissionId != '') {
						$menuPermissionModel->insert(['menu_id' => $menuId, 'permission_id' => $permissionId]);
					}
				}
				
				return redirect()->to('/admin/menus')->with('message', 'Menu berhasil ditambahkan!');
			}
			
			return view('administrator/menu_add', $data);
		}
		else
		{
			return redirect()->back();
		}
	}
	
	public function menuEdit($id=null)
	{
		if(has_permission('menuEdit'))
		{
			$data['title'] = 'Edit Menu';
			$data['open'] = 2;
			$data['active'] = 5;
			
			$db = \Config\Database::connect();
			
			$data['breadcrumbs'] = $db->query("SELECT name, href FROM menu WHERE id = ".$data['open']." OR id = ".$data['active']." ORDER BY parent_id ASC");
			$data['menuParentList'] = $db->query("SELECT id, name FROM menu WHERE parent_id = 0 ORDER BY order_number ASC");
			
			$permissionList = $db->query("
				SELECT a.id, a.name, a.description, IF(EXISTS (SELECT b.menu_id FROM menu_permissions b WHERE b.menu_id = $id AND b.permission_id = a.id), 'checked', '') AS checked
				FROM auth_permissions a
				ORDER BY name ASC
			");
			
			$data['permissionList'] = $permissionList;
			
			$validation =  \Config\Services::validation();
			
			$validation->setRules([
				'name' => 'required',
				'href' => 'required'
			]);
			
			$isDataValid = $validation->withRequest($this->request)->run();
			
			$menuModel = new MenuModel();
			
			if($isDataValid)
			{
				$menuPermissionModel = new MenuPermissionModel();
				
				$menuModel->update($id, [
					'name'			=> $this->request->getPost('name'),
					'parent_id'		=> $this->request->getPost('parent_id'),
					'href'			=> $this->request->getPost('href'),
					'icon'			=> $this->request->getPost('icon'),
					'order_number'	=> $this->request->getPost('order_number'),
					'active'		=> $this->request->getPost('active')
				]);
				
				$db->query("DELETE FROM menu_permissions WHERE menu_id = ".$id);
				
				foreach($permissionList->getResult() as $pL) {
					$permissionId = $this->request->getPost($pL->name);
					
					if(isset($permissionId) && $permissionId != '') {
						$menuPermissionModel->insert(['menu_id' => $id, 'permission_id' => $permissionId]);
					}
				}
				
				return redirect()->to('/admin/menus')->with('message', 'Perubahan berhasil disimpan!');
			}
			
			$data['menuRow'] = $db->query("SELECT id, name, parent_id, href, icon, order_number, active FROM menu WHERE id = $id")->getRow();
			return view('administrator/menu_edit', $data);
		}
		else
		{
			return redirect()->back();
		}
	}
	
	public function menuDelete()
	{
		if(has_permission('menuDelete'))
		{
			$id = $this->request->getPost('id');
			
			$db = \Config\Database::connect();
			$db->query("DELETE FROM menu_permissions WHERE menu_id = $id");
			
			$menuModel = new MenuModel();
			$menuModel->delete($id);
			
		}
    }
	
	public function permission()
    {
		if(has_permission('permissionView'))
		{
			$data['title'] = 'Pengaturan Jenis Izin';
			$data['open'] = 2;
			$data['active'] = 6;
			
			$db = \Config\Database::connect();
			$session = \Config\Services::session();
			
			$data['breadcrumbs'] = $db->query("SELECT name, href FROM menu WHERE id = ".$data['open']." OR id = ".$data['active']." ORDER BY parent_id ASC");
			
			$data['search'] = $session->get('searchPermissionList');
			$data['start'] = $session->get('startPermissionList');
			$data['length'] = $session->get('lengthPermissionList');
			$data['orderCol'] = $session->get('orderColPermissionList');
			$data['orderDir'] = $session->get('orderDirPermissionList');
			
			return view('administrator/permission', $data);
		}
		else
		{
			return redirect()->back();
		}
    }
	
	public function permissionList()
    {
		if(has_permission('permissionView'))
		{
			$db = \Config\Database::connect();
			$session = \Config\Services::session();
			
			//Searching
			
			$search = $db->escapeString($this->request->getPost('search'));
			$session->set('searchPermissionList', $search);
			
			$sSearch = '';
			
			if(isset($search) && $search != '')
			{
				$sSearch .= " AND (a.name LIKE '%".$search."%' OR a.description LIKE '%".$search."%') ";
			}
		
			//Limit
			
			$start = $this->request->getPost("start");
			$session->set('startPermissionList', $start);
			$length = $this->request->getPost("length");
			$session->set('lengthPermissionList', $length);
			
			if (isset($start) && $start != '-1' )
			{
				$sLimit = " LIMIT ".intval($start).", ".intval($length);
			}
			
			//Ordering
			
			$orderCol = $this->request->getPost("order[0][column]");
			$session->set('orderColPermissionList', $orderCol);
			$orderDir = $this->request->getPost("order[0][dir]");
			$session->set('orderDirPermissionList', $orderDir);
			$columnName = $this->request->getPost("columns[".$orderCol."][name]");
			
			if (isset($columnName) && $columnName != '')
			{
				$sOrder = " ORDER BY ".$columnName." ".$orderDir." ";
			}
			else
			{
				$sOrder = " ORDER BY a.id ASC ";
			}
			
			//Table Row
			
			$s1 = "SELECT a.id, a.name, a.description FROM auth_permissions a WHERE 1=1 ";
			$s1 .= $sSearch;
			$s1 .= $sOrder;
			$s1 .= $sLimit;
		
			$sheet1 = $db->query($s1);
			
			$sheet_total = $db->query("SELECT a.id FROM auth_permissions a WHERE 1=1 ".$sSearch)->getNumRows();
		
			$result = array();
			$i=0;
			$no = (isset($start) ? $start + 1 : 1);
			foreach($sheet1->getResult() as $sheet)
			{
				
				$result[$i]['0'] = $sheet->id;
				$result[$i]['1'] = $sheet->name;
				$result[$i]['2'] = $sheet->description;
				$result[$i]['3'] = '
					<div class="text-right">
						<button id="'.$sheet->id.'" class="btn btn-flat btn-info btn-xs edit" title="edit"><i class="fa fa-edit"></i></button>
						<button id="'.$sheet->id.'" class="btn btn-flat btn-danger btn-xs delete" title="hapus izin '.$sheet->name.'"><i class="fa fa-trash"></i></button>
						<script>
						$(".edit").click(function(){var id = $(this).attr("id");window.location.assign("permissionEdit/"+id);});
						$(".delete").click(function() {var id = $(this).attr("id");var title = $(this).attr("title");
						swal({title: "Apakah and yakin akan meng"+title+"?", text: "Data yang telah dihapus tidak bisa dikembalikan!", icon: "warning",
						buttons:{cancel: {visible: true, text : "Cancel", className: "btn"}, confirm: {text : "Hapus", className : "btn btn-flat btn-danger"}}})
						.then((willDelete) => {if (willDelete) {$.ajax({url: "/permissionDelete", type: "POST", data: {"id":id}, cache: false});oTable.fnDraw();}});});
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
	
	public function permissionAdd()
	{
		if(has_permission('permissionAdd'))
		{
			$data['title'] = 'Tambah Jenis Izin';
			$data['open'] = 2;
			$data['active'] = 6;
			
			$db = \Config\Database::connect();
			
			$data['breadcrumbs'] = $db->query("SELECT name, href FROM menu WHERE id = ".$data['open']." OR id = ".$data['active']." ORDER BY parent_id ASC");
			
			if($this->request->getMethod() == 'POST')
			{
				$validation = \Config\Services::validation();
				
				$validation->setRules([
					'name' => ['label' => 'Nama jenis izin', 'rules' => 'required|is_unique[auth_permissions.name]']
				]);
				
				$isDataValid = $validation->withRequest($this->request)->run();
				
				if($isDataValid)
				{
					$permissionModel = new PermissionModel();
					
					$permissionModel->insert([
						'name'			=> $this->request->getPost('name'),
						'description'	=> $this->request->getPost('description')
					]);
					
					return redirect()->to('/admin/permissions')->with('message', 'Jenis izin berhasil ditambahkan!');
				}
				else
				{
					return redirect()->back()->withInput()->with('errors', $validation->getErrors());
				}
			}
			
			return view('administrator/permission_add', $data);
		}
		else
		{
			return redirect()->back();
		}
	}
	
	public function permissionEdit($id=null)
	{
		if(has_permission('permissionEdit'))
		{
			$data['title'] = 'Ubah Jenis Izin';
			$data['open'] = 2;
			$data['active'] = 6;
			
			$db = \Config\Database::connect();
			
			$data['breadcrumbs'] = $db->query("SELECT name, href FROM menu WHERE id = ".$data['open']." OR id = ".$data['active']." ORDER BY parent_id ASC");
			
			if($this->request->getMethod() == 'POST')
			{
				$validation =  \Config\Services::validation();
				
				$validation->setRules([
					'name' => ['label' => 'Nama jenis izin', 'rules' => 'required|is_unique[auth_permissions.name,id,'.$id.']']
				]);
				
				$isDataValid = $validation->withRequest($this->request)->run();
				
				$permissionModel = new PermissionModel();
				
				if($isDataValid)
				{
					$permissionModel->update($id, [
						'name'			=> $this->request->getPost('name'),
						'description'	=> $this->request->getPost('description')
					]);
					
					return redirect()->to('/admin/permissions')->with('message', 'Perubahan berhasil disimpan!');
				}
				else
				{
					return redirect()->back()->withInput()->with('errors', $validation->getErrors());
				}
			}
			
			$data['permissionRow'] = $db->query("SELECT id, name, description FROM auth_permissions WHERE id = $id")->getRow();
			return view('administrator/permission_edit', $data);
		}
		else
		{
			return redirect()->back();
		}
	}
	
	public function permissionDelete()
	{
		if(has_permission('permissionDelete'))
		{
			$id = $this->request->getPost('id');
			
			$db = \Config\Database::connect();
			$db->query("DELETE FROM auth_users_permissions WHERE permission_id = $id");
			$db->query("DELETE FROM auth_groups_permissions WHERE permission_id = $id");
			
			$permissionModel = new PermissionModel();
			$permissionModel->delete($id);
		}
    }
	
}
