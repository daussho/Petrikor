<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\BaseModel;
use DateTime;
use phpDocumentor\Reflection\Types\Null_;

class DB extends BaseController
{
    public function index()
    {
        return ('hello world');
    }

    public function insert($documentName)
    {
        $model = new BaseModel($documentName);
        $store = $model->getStore();

        $data = json_decode($this->request->getBody(), true);
        $data['_created_at'] = date(DateTime::ISO8601);
        $data['_updated_at'] = date(DateTime::ISO8601);
        $data['_deleted_at'] = NULL;

        $success = $store->insert($data);
        return $this->response->setJSON([
            'data' => $success
        ]);
    }

    public function insertMany($documentName)
    {
        $model = new BaseModel($documentName);
        $store = $model->getStore();

        $data = json_decode($this->request->getBody(), true);

        foreach ($data as $key => $value) {
            $data[$key]['_created_at'] = date(DateTime::ISO8601);
            $data[$key]['_updated_at'] = date(DateTime::ISO8601);
            $data[$key]['_deleted_at'] = NULL;
        }

        $success = $store->insertMany($data);
        return $this->response->setJSON([
            'data' => $success
        ]);
    }

    public function findBy($documentName)
    {
        $model = new BaseModel($documentName);
        $store = $model->getStore();

        $body = json_decode($this->request->getBody(), true);
        $body['criteria'][] = [
            '_deleted_at', '=', NULL
        ];

        $param = [
            'criteria' => $body['criteria'],
            'order' => $body['order'] ?? NULL,
            'limit' => $body['limit'] ?? 25,
            'offset' => $body['offset'] ?? NULL,
        ];

        $data = $store->findBy($param['criteria'], $param['order'], $param['limit'], $param['offset']);

        return $this->response->setJSON([
            'data' => $data,
            'debug' => [
                'param' => $param
            ],
        ]);
    }

    public function updateById($documentName)
    {
        $model = new BaseModel($documentName);
        $store = $model->getStore();

        $body = json_decode($this->request->getBody(), true);

        $body['data']['_updated_at'] = date(DateTime::ISO8601);

        $data = $store->updateById($body['id'], $body['data']);

        return $this->response->setJSON([
            'data' => $data,
        ]);
    }
}
