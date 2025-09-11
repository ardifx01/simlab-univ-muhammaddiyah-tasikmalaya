<?php 
namespace App\Models\Administrator;
use CodeIgniter\Model;
class MenuPermissionModel extends Model
{
	protected $table = 'menu_permissions';
	protected $allowedFields = ['menu_id', 'permission_id'];
}