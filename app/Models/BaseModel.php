<?php

namespace App\Models;

use App\Helpers\GlobalHelper;
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

        $this->dbDir = __DIR__ . "/../../_db";
        $this->store = new Store($documentName, $this->dbDir, $configuration);
    }

    public function getStore()
    {
        return $this->store;
    }

    public function insert($data)
    {
        $data['_meta'] = $this->__createTimeStamp();

        return $this->store->insert($data);
    }

    public function insertUnique($data, $unique)
    {
        $found = $this->store->findOneBy([$unique, '=', $data[$unique]]);

        if (!empty($found)) {
            return [];
        }

        return $this->insert($data);
    }

    public function insertMany(array $data)
    {
        foreach ($data as $key => $value) {
            $data[$key]['_meta'] = $this->__createTimeStamp();
        }

        return $this->store->insertMany($data);
    }

    public function insertManyUnique($data, $unique)
    {
        $newData = [];

        $uniqueFilter = [];
        foreach ($data as $key => $value) {
            $uniqueFilter[] = $value[$unique];
        }

        $foundData = $this->store->findBy([$unique, 'IN', $uniqueFilter]);

        foreach ($data as $key => $value) {
            $found = false;
            foreach ($foundData as $k => $v) {
                if ($v[$unique] == $value[$unique]) {
                    $found = true;
                }
            }

            if (!$found) {
                $newData[] = $value;
            }
        }
        if (empty($newData)) {
            return [];
        }

        return $this->insertMany($newData);
    }

    private function __createTimeStamp()
    {
        return [
            'created_at' => date(DateTime::ISO8601),
            'updated_at' => date(DateTime::ISO8601),
            'deleted_at' => NULL,
        ];
    }

    public function getPagination(string $documentName, $param)
    {
        if ($param['limit'] < 1) {
            return [];
        }

        $data = $this->store->findBy($param['criteria'], $param['order']);

        return [
            'page' => (int) $param['page'],
            'per_page' => (int) $param['limit'],
            'total_data' => count($data),
            'total_page' => ceil(count($data) / $param['limit']),
        ];
    }
}
