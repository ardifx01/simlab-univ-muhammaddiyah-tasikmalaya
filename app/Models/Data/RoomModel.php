<?php 
namespace App\Models\Data;
use CodeIgniter\Model;
class RoomModel extends Model
{
	protected $table = 'data_room';
	protected $primaryKey = 'id';
	protected $useAutoIncrement = true;
	protected $allowedFields = [
		'id_lab',
		'nama',
		'deskripsi',
		'lantai',
		'added_by',
		'added_date',
		'updated_by',
		'updated_date'
	];
}