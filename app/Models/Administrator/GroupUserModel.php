<?php 
namespace App\Models\Administrator;
use CodeIgniter\Model;
class GroupUserModel extends Model
{
	protected $table = 'auth_groups_users ';
	protected $allowedFields = ['group_id', 'user_id'];
}