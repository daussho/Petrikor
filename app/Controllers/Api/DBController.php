<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Helpers\GlobalHelper;
use App\Models\BaseModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use DateTime;
use Psr\Log\LoggerInterface;
use SleekDB\Store;

class DBController extends BaseController
{
    private $query;
    private $body;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->query = $this->request->getGet();
        $this->body = $this->request->getJSON(true);
    }

    public function index()
    {
        return ('hello world');
    }

    public function insert($documentName)
    {
        $model = new BaseModel($documentName);

        $success = $model->insert($this->body, $this->query['unique'] ?? '');

        if (!$success) {
            $this->response->setStatusCode(400);
            return $this->response->setJSON([
                'message' => "Duplicate data, key: {$this->query['unique']}, value: {$this->body[$this->query['unique']]}",
                'data' => false,
            ]);
        }

        return $this->response->setJSON([
            'data' => $success
        ]);
    }

    public function insertMany($documentName)
    {
        $model = new BaseModel($documentName);
        $store = $model->getStore();

        $data = json_decode($this->request->getBody(), true);

        if (!empty($this->query['unique'])) {
            $success = $this->_insertManyUnique($store, $this->query['unique']);

            return $this->response->setJSON([
                'data' => $success,
                'legnth' => count($success),
            ]);
        }

        foreach ($data as $key => $value) {
            $data[$key]['_meta'] = [
                'created_at' => date(DateTime::ISO8601),
                'updated_at' => date(DateTime::ISO8601),
                'deleted_at' => NULL,
            ];
        }

        $success = $store->insertMany($data);
        return $this->response->setJSON([
            'data' => $success,
            'legnth' => count($success),
        ]);
    }

    private function _insertManyUnique(Store $store, string $unique)
    {
        $respose = [];

        foreach ($this->body as $key => $value) {
            $found = $store->findOneBy([$unique, '=', $value[$unique]]);

            if (empty($found)) {
                $value['_meta'] = [
                    'created_at' => date(DateTime::ISO8601),
                    'updated_at' => date(DateTime::ISO8601),
                    'deleted_at' => NULL,
                ];

                $respose[] = $store->insert($value);
            }
        }

        return $respose;
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
            'limit' => !empty($query['limit']) ? ($query['limit'] > 0 ? $query['limit'] : NULL) : 25,
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
        if ($param['limit'] < 1) {
            return [];
        }

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
