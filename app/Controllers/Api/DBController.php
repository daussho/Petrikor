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

    public function insert(string $documentName)
    {
        $model = new BaseModel($documentName);

        // Non unique insert
        if (empty($this->query['unique'])) {
            $success = $model->insert($this->body);
            return $this->response->setJSON([
                'data' => $success
            ]);
        }

        // Unique insert
        $success = $model->insertUnique($this->body, $this->query['unique']);

        if (empty($success)) {
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

    public function insertMany(string $documentName)
    {
        $model = new BaseModel($documentName);

        $data = $this->body;

        if (empty($this->query['unique'])) {
            $success = $model->insertMany($data);
        } else {
            $success = $model->insertManyUnique($data, $this->query['unique']);
        }

        return $this->response->setJSON([
            'data' => $success,
            'length' => count($success),
        ]);
    }

    public function findBy(string $documentName)
    {
        $model = new BaseModel($documentName);
        $store = $model->getStore();

        $body = $this->body;
        $query = $this->query;

        $pagination = $query['pagination'] ?? 0;

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
            'pagination' => $pagination == 1 ? $model->getPagination($documentName, $param) : [],
            'debug' => [
                'param' => $param
            ],
        ]);
    }

    public function updateById(string $documentName)
    {
        $model = new BaseModel($documentName);
        $store = $model->getStore();

        $body = json_decode($this->request->getBody(), true);

        $body['data']['_meta.updated_at'] = date(DateTime::ISO8601);

        $data = $store->updateById((int) $body['id'], $body['data']);

        return $this->response->setJSON([
            'data' => $data
        ]);
    }

    public function delete(string $documentName, $id)
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
}
