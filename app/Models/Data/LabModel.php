<?php 
namespace App\Models\Data;
use CodeIgniter\Model;
class LabModel extends Model
{
	protected $table = 'data_lab';
	protected $primaryKey = 'id';
	protected $useAutoIncrement = true;
	protected $allowedFields = [
		'nama',
		'deskripsi',
		'lantai_awal',
		'jml_lantai',
		'added_by',
		'added_date',
		'updated_by',
		'updated_date'
	];
}