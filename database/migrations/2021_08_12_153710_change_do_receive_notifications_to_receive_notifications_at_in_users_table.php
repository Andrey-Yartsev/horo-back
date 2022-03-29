<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeDoReceiveNotificationsToReceiveNotificationsAtInUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('do_receive_notifications');
            $table->time('receive_notifications_at')->nullable()->after('interests');
            $table->smallInteger('utc_offset')->nullable()->after('receive_notifications_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('receive_notifications_at');
            $table->dropColumn('utc_offset');
            $table->boolean('do_receive_notifications')->nullable()->after('interests');
        });
    }
}
