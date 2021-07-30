<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Helpers\GlobalHelper;
use App\Models\BaseModel;
use DateTime;

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
        $data['_meta'] = [
            'created_at' => date(DateTime::ISO8601),
            'updated_at' => date(DateTime::ISO8601),
            'deleted_at' => NULL,
        ];

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
            $data[$key]['_meta'] = [
                'created_at' => date(DateTime::ISO8601),
                'updated_at' => date(DateTime::ISO8601),
                'deleted_at' => NULL,
            ];
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
        $query = $this->request->getGet();

        $body['criteria'][] = [
            '_meta.deleted_at', '=', NULL
        ];

        $param = [
            'criteria' => $body['criteria'],
            'order' => $body['order'] ?? ['_id' => 'asc'],
            'limit' => $query['limit'] ?? 25,
            'page' => $query['page'] ?? 1,
        ];
        $param['offset'] = ($param['page'] - 1) * $param['limit'];

        $data = $store->findBy($param['criteria'], $param['order'], $param['limit'], $param['offset']);

        return $this->response->setJSON([
            'data' => $data,
            'pagination' => $this->getPagination($documentName, $param),
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

        $body['data']['_meta']['updated_at'] = date(DateTime::ISO8601);

        $data = $store->updateById($body['id'], $body['data']);

        return $this->response->setJSON([
            'data' => $data,
        ]);
    }

    public function delete($documentName, $id)
    {
        $model = new BaseModel($documentName);
        $store = $model->getStore();

        $data = $store->findById($id);
        $data['_meta']['updated_at'] = date(DateTime::ISO8601);
        $data['_meta']['deleted_at'] = date(DateTime::ISO8601);

        $res = $store->updateById($id, [
            '_meta' => $data['_meta'],
        ]);

        return $this->response->setJSON([
            'data' => $res,
        ]);
    }

    private function getPagination($documentName, $param)
    {
        $model = new BaseModel($documentName);
        $store = $model->getStore();

        $data = $store->findBy($param['criteria'], $param['order']);

        return [
            'page' => (int) $param['page'],
            'per_page' => (int) $param['limit'],
            'total_data' => count($data),
            'total_page' => ceil(count($data) / $param['limit']),
        ];
    }
}
