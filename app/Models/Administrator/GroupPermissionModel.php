<?php 
namespace App\Models\Administrator;
use CodeIgniter\Model;
class GroupPermissionModel extends Model
{
	protected $table = 'auth_groups_permissions';
	protected $allowedFields = ['group_id', 'permission_id'];
}