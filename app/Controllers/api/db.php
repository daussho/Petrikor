<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\BaseModel;
use phpDocumentor\Reflection\Types\Null_;

class DB extends BaseController
{
    public function index()
    {
        return view('welcome_message');
    }

    public function insert($documentName)
    {
        $model = new BaseModel($documentName);
        $store = $model->getStore();

        $data = json_decode($this->request->getBody(), true);

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

        $param = [
            'criteria' => $body['criteria'] ?? [],
            'order' => $body['order'] ?? NULL,
            'limit' => $body['limit'] ?? NULL,
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
}
