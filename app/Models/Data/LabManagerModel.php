<?php 
namespace App\Models\Data;
use CodeIgniter\Model;
class LabManagerModel extends Model
{
	protected $table = 'data_pengelola';
	protected $allowedFields = [
		'id_lab',
		'id_pengelola'
	];
}