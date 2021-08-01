<?php

namespace App\Models;

use DateTime;
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

    public function insert($data, string $unique = NULL)
    {
        $data['_meta'] = $this->__createTimeStamp();

        if (!empty($unique)) {
            $found = $this->store->findOneBy([$unique, '=', $data[$unique]]);

            if (!empty($found)) {
                return false;
            }
        }

        return $this->store->insert($data);
    }

    private function __createTimeStamp()
    {
        return [
            'created_at' => date(DateTime::ISO8601),
            'updated_at' => date(DateTime::ISO8601),
            'deleted_at' => NULL,
        ];
    }
}
