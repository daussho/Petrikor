<?php

namespace App\Models;

use SleekDB\Query;
use SleekDB\Store;

class BaseModel
{
    protected $dbDir;
    protected $store;

    function __construct(string $documentName)
    {
        $configuration = [
            "timeout" => false, // deprecated! Set it to false!
        ];

        $this->dbDir = __DIR__ . "../../../_db";
        $this->store = new Store($documentName, $this->dbDir, $configuration);
    }

    public function getStore()
    {
        return $this->store;
    }
}
