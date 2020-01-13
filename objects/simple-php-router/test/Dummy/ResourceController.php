<?php
class ResourceController implements \Pecee\Controllers\IResourceController
{

    public function index()
    {
        echo 'index';
    }

    public function show($id)
    {
        echo 'show ' . $id;
    }

    public function store()
    {
        echo 'store';
    }

    public function create()
    {
        echo 'create';
    }

    public function edit($id)
    {
        echo 'edit ' . $id;
    }

    public function update($id)
    {
        echo 'update ' . $id;
    }

    public function destroy($id)
    {
        echo 'destroy ' . $id;
    }
}