<?php

namespace App\Models;

use SleekDB\Store;

class BaseModel
{
    protected $dbDir;
    protected $store;

    function __construct(string $documentName)
    {
        $this->dbDir = __DIR__ . "../../../_db";
        $this->store = new Store($documentName, $this->dbDir);
    }

    public function getStore()
    {
        return $this->store;
    }
}
