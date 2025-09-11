<?php 
namespace App\Models\Administrator;
use CodeIgniter\Model;
class GroupModel extends Model
{
	protected $table = 'auth_groups';
	protected $primaryKey = 'id';
	protected $useAutoIncrement = true;
	protected $allowedFields = ['name', 'description'];
}