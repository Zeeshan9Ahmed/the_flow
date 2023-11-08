<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class GeneresTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('generes')->delete();
        
        \DB::table('generes')->insert(array (
            0 => 
            array (
                'id' => 1,
                'name' => 'pop',
                'image' => 'https://server.appsstaging.com/3346/the-flow/public/genereimages/1658215069.png',
                'created_at' => '2022-07-19 07:17:49',
                'updated_at' => '2022-07-19 07:17:49',
            ),
            1 => 
            array (
                'id' => 2,
                'name' => 'jazz',
                'image' => 'https://server.appsstaging.com/3346/the-flow/public/genereimages/1658215136.png',
                'created_at' => '2022-07-19 07:18:56',
                'updated_at' => '2022-07-19 07:18:56',
            ),
            2 => 
            array (
                'id' => 3,
                'name' => 'indie',
                'image' => 'https://server.appsstaging.com/3346/the-flow/public/genereimages/1658215156.png',
                'created_at' => '2022-07-19 07:19:16',
                'updated_at' => '2022-07-19 07:19:16',
            ),
            3 => 
            array (
                'id' => 4,
                'name' => 'house',
                'image' => 'https://server.appsstaging.com/3346/the-flow/public/genereimages/1658215175.png',
                'created_at' => '2022-07-19 07:19:35',
                'updated_at' => '2022-07-19 07:19:35',
            ),
            4 => 
            array (
                'id' => 5,
                'name' => 'hiphop',
                'image' => 'https://server.appsstaging.com/3346/the-flow/public/genereimages/1658215207.png',
                'created_at' => '2022-07-19 07:20:07',
                'updated_at' => '2022-07-19 07:20:07',
            ),
            5 => 
            array (
                'id' => 6,
                'name' => 'gospel',
                'image' => 'https://server.appsstaging.com/3346/the-flow/public/genereimages/1658215236.png',
                'created_at' => '2022-07-19 07:20:36',
                'updated_at' => '2022-07-19 07:20:36',
            ),
            6 => 
            array (
                'id' => 7,
                'name' => 'folk_music',
                'image' => 'https://server.appsstaging.com/3346/the-flow/public/genereimages/1658215259.png',
                'created_at' => '2022-07-19 07:20:59',
                'updated_at' => '2022-07-19 07:20:59',
            ),
            7 => 
            array (
                'id' => 8,
                'name' => 'country',
                'image' => 'https://server.appsstaging.com/3346/the-flow/public/genereimages/1658215282.png',
                'created_at' => '2022-07-19 07:21:22',
                'updated_at' => '2022-07-19 07:21:22',
            ),
            8 => 
            array (
                'id' => 9,
                'name' => 'blues',
                'image' => 'https://server.appsstaging.com/3346/the-flow/public/genereimages/1658215299.png',
                'created_at' => '2022-07-19 07:21:39',
                'updated_at' => '2022-07-19 07:21:39',
            ),
            9 => 
            array (
                'id' => 10,
                'name' => 'dubstep',
                'image' => 'https://server.appsstaging.com/3346/the-flow/public/genereimages/1658215316.png',
                'created_at' => '2022-07-19 07:21:56',
                'updated_at' => '2022-07-19 07:21:56',
            ),
        ));
        
        
    }
}