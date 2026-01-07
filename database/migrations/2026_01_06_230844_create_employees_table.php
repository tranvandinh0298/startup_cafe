<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('employees')) {
            return;
        }

        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->unique();
            $table->enum('role', EMPLOYEE_ROLES)->default(EMPLOYEE_ROLE_WAITER);
            $table->integer('hourly_rate')->default(0)->comment("Mức lương theo giờ");
            $table->enum('status', RECORD_STATUS)->default(RECORD_STATUS_ACTIVE)->comment("Trạng thái nhân viên");
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employees');
    }
}
