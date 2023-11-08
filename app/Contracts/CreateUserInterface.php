<?php
namespace App\Contracts;
interface CreateUserInterface extends BaseInterface {
    // public function save($request);
    public function completeProfile($request);
}