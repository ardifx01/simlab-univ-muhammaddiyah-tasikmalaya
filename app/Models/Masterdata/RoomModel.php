<?php 
namespace App\Models\Masterdata;
use CodeIgniter\Model;
class RoomModel extends Model
{
	protected $table = 'ref_room';
	protected $primaryKey = 'id';
	protected $useAutoIncrement = true;
	protected $allowedFields = ['name'];
}