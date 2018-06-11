<?php
namespace Pecee\Controllers;

interface IResourceController
{

    /**
     * @return void
     */
    public function index();

    /**
     * @param mixed $id
     * @return void
     */
    public function show($id);

    /**
     * @return void
     */
    public function store();

    /**
     * @return void
     */
    public function create();

    /**
     * View
     * @param mixed $id
     * @return void
     */
    public function edit($id);

    /**
     * @param mixed $id
     * @return void
     */
    public function update($id);

    /**
     * @param mixed $id
     * @return void
     */
    public function destroy($id);

}