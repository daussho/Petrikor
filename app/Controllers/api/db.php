<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\BaseModel;

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
}
