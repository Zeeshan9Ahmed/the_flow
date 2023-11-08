<?php
namespace App\Contracts;
interface LevelInterface{
    public function allLevels($request);
    public function findlevel($request);
    public function levelScore($request);
    
}