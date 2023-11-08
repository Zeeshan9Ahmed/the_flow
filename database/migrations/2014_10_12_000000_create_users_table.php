<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('full_name')->nullable();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->string('phone')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('address')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('state')->nullable();
            $table->string('avatar')->nullable();
            $table->string('cover_image')->nullable();
            $table->integer('genere_id')->nullable();
            // $table->string('social_token')->nullable();
            // $table->enum('social_type',['normal','facebook','google','phone','apple'])->nullable();
            $table->enum('device_type', ['ios','android'])->nullable();
            $table->string('device_token')->nullable();
            $table->enum('is_profile_complete', ['0','1']);
            $table->enum('is_forgot',['0','1'])->nullable();
            $table->enum('is_verified',['0','1'])->nullable();
            $table->string('verification_code')->nullable();
            $table->enum('is_active',['1','0']);
            $table->enum('is_blocked', ['0','1']);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
