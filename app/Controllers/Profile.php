<?php

namespace App\Controllers;

use App\Models\Profile\PhotoModel;
use Myth\Auth\Config\Auth as AuthConfig;
use Myth\Auth\Entities\User;
use Myth\Auth\Models\UserModel;
use Myth\Auth\Models\PermissionModel;

class Profile extends BaseController
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
	
	public function index()
	{
		$id = user_id();
		$data['title'] = 'Profil Pengguna';
		$data['open'] = 0;
		$data['active'] = 0;
		
		$db = \Config\Database::connect();
		
		$data['breadcrumbs'] = $db->query("SELECT name, href FROM menu WHERE id = ".$data['open']." OR id = ".$data['active']." ORDER BY parent_id ASC");
		
		if($this->request->getMethod() == 'POST')
		{
			// Validate basics first since some password rules rely on these fields
			$rules = config('Validation')->registrationRules ?? [
				'fullname' => ['label' => 'Nama lengkap', 'rules' => 'required'],
				'username' => ['label' => lang('Auth.username'), 'rules' => 'required|alpha_numeric|min_length[3]|max_length[30]|is_unique[users.username,id,'.$id.']'],
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
			
			return redirect()->back()->with('message', 'Perubahan telah disimpan.');
		}
		
		return view('profile/index', $data);
	}
	
	public function changePhoto()
	{
		$id = user_id();
		
		$rules = config('Validation')->registrationRules ?? [
			'file' => [
				'label' => 'File',
				'rules' => 'uploaded[file]'
					. '|is_image[file]'
					. '|mime_in[file,image/jpg,image/jpeg,image/gif,image/png,image/webp]'
					. '|max_size[file,100]'
					. '|max_dims[file,128,128]',
			]
		];
		
		if (!$this->validate($rules)) {
			return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
		}
		
		$file = new PhotoModel();
		$dataFile = $this->request->getFile('file');
		
		$fileName = $dataFile->getRandomName();
		$file->update($id, [
			'photo' => $fileName,
		]);
		
		$dataFile->move(WRITEPATH . 'uploads/users_image', $fileName);
		
		return redirect()->back()->with('message', 'Foto profil telah berhasil diubah.');
	}
	
	public function changePassword()
	{
		$data['title'] = 'Ganti Kata Sandi';
		$data['open'] = 0;
		$data['active'] = 0;
		
		$db = \Config\Database::connect();
		$id = user_id();
		
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
				'password' => ['label' => lang('Auth.password'), 'rules' => 'required|strong_password'],
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
			
			return redirect()->to('/profile')->with('message', 'Kata sandi berhasil diubah.');
		}
		
		$data['username'] = $user->username;
		$data['reset_hash'] = $user->reset_hash;
		
		return view('profile/change_password', $data);
	}
	
	
}
