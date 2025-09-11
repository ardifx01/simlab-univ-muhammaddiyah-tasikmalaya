<?php 
namespace App\Models\Data;
use CodeIgniter\Model;
class KotakPenyimpananModel extends Model
{
	protected $table = 'data_kotak_penyimpanan';
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