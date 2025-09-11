<?php 
namespace App\Models\Administrator;
use CodeIgniter\Model;
class MenuModel extends Model
{
	protected $table = 'menu';
	protected $primaryKey = 'id';
	protected $useAutoIncrement = true;
	protected $allowedFields = ['name', 'parent_id', 'href', 'icon', 'order_number', 'active'];
}