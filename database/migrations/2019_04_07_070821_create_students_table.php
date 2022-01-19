<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStudentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('students', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('secinstallmentdate');
            $table->string('secinstallmentamount');
            $table->longText('remark')->nullable();
            $table->longText('resume');
            $table->boolean('status')->default(true);
            $table->string('actionstatus')->default(0);

            //inquire
            $table->unsignedBigInteger('inquire_id');

            //user
            $table->unsignedBigInteger('user_id');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('students');
    }
}
