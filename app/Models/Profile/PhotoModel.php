<?php 
namespace App\Models\Profile;
use CodeIgniter\Model;

class PhotoModel extends Model
{
	protected $DBGroup			= 'default';
	protected $table			= 'users';
	protected $primaryKey		= 'id';
	protected $returnType		= 'object';
	protected $useTimestamps	= true;
	protected $allowedFields	= ['photo'];
}