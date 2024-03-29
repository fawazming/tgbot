<?php
namespace App\Models;

use CodeIgniter\Model;

class Logs extends Model
{
    protected $table = 'logs';
    protected $primaryKey = 'id';

    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields = ['name','data'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';
}
