<?php
// database/migrations/2024_01_01_000005_create_clients_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('company_name')->nullable();
            $table->string('tax_number')->nullable();
            $table->json('billing_address');
            $table->json('shipping_address')->nullable();
            $table->enum('client_type', ['individual', 'business'])->default('individual');
            $table->decimal('credit_limit', 10, 2)->default(0);
            $table->decimal('current_balance', 10, 2)->default(0);
            $table->enum('payment_terms', ['immediate', 'net_15', 'net_30', 'net_60'])->default('immediate');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['client_type', 'is_active']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('clients');
    }
};
