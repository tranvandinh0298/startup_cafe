<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayrollsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('payrolls')) {
            return;
        }

        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->date('from_date');
            $table->date('to_date');
            $table->integer('total_hours')->default(0)->comment("Tổng số giờ làm việc trong kỳ lương");
            $table->integer('hourly_rate')->default(0)->comment("Mức lương theo giờ tại thời điểm trả lương");
            $table->integer('total_amount')->default(0)->comment("Tổng số tiền lương trong kỳ");
            $table->enum('status', PAYROLL_STATUSES)->default(PAYROLL_STATUS_PENDING)->comment("Trạng thái thanh toán lương");
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
        Schema::dropIfExists('payrolls');
    }
}
