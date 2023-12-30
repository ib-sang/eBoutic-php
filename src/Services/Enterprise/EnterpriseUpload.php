<?php 

namespace App\Services\Enterprise;

use Controllers\Upload;

class EnterpriseUpload extends Upload{

    protected $path='uploads/enterprise';
    protected $format=[
        "thumb"=>[320,180],
        'medium' => [1024, 860]
    ];
}