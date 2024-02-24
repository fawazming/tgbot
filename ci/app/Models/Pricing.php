<?php
namespace App\Models;

use CodeIgniter\Model;

class Pricing extends Model
{
    protected $table = 'pricing';
    protected $primaryKey = 'id';

    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = ['name','code','c_price','s_price'];

    protected $useTimestamps = false;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';
}
