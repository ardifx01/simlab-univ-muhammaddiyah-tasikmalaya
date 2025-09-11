<?php 
namespace App\Models\Data;
use CodeIgniter\Model;
class InventarisModel extends Model
{
	protected $table = 'data_inventaris';
	protected $primaryKey = 'id';
	protected $useAutoIncrement = true;
	protected $allowedFields = [
		'id_lab',
		'nama',
		'deskripsi',
		'code_prefix',
		'code_suffix',
		'code_length',
		'added_by',
		'added_date',
		'updated_by',
		'updated_date'
	];
}