{{ '<?php' }}


use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMailboxSetupTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		//
        Schema::create('mail_accounts', function($table)
        {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->string('host_name');
            $table->integer('host_port')->unsigned();
            $table->enum('host_protocol', array("imap","pop3"));
            $table->string('email');
            $table->string('password');
            $table->integer('message_count')->unsigned();
            $table->integer('unseen_count')->unsigned();
            $table->timestamps();
        });

        Schema::create('mail_addresses',function($table){
            $table->increments('id');
            $table->string('address')->unique();
            $table->string('name');
            $table->integer('user_id')->unsigned();
        });

        Schema::create('mail_entities', function($table){
            $table->increments('id');
            $table->integer('account_id')->unsigned();
            $table->integer('mail_id')->unsigned()->nullable();
            $table->timestamp('date');
            $table->string('subject');
            $table->string('from_name');
            $table->string('from_address');
            $table->text('text_plain');
            $table->text('text_html');
            $table->string('uid')->nullable();
            $table->boolean('flagged')->default(false);
            $table->boolean('seen')->default(false);
            $table->boolean('answered')->default(false);
            $table->boolean('deleted')->default(false);
            $table->boolean('sent')->default(false);
            $table->foreign('account_id')->references('id')->on('mail_accounts');
            $table->timestamps();
        });

        Schema::create('to_addresses', function($table){
            $table->increments('id');
            $table->integer('mail_entity_id')->unsigned();
            $table->integer('mail_address_id')->unsigned();
            $table->foreign('mail_entity_id')->references('id')->on('mail_entities')->onDelete('cascade');
            $table->foreign('mail_address_id')->references('id')->on('mail_addresses')->onDelete('cascade');
        });

        Schema::create('cc_addresses', function($table){
            $table->increments('id');
            $table->integer('mail_entity_id')->unsigned();
            $table->integer('mail_address_id')->unsigned();
            $table->foreign('mail_entity_id')->references('id')->on('mail_entities')->onDelete('cascade');
            $table->foreign('mail_address_id')->references('id')->on('mail_addresses')->onDelete('cascade');
        });

        Schema::create('reply_addresses', function($table){
            $table->increments('id');
            $table->integer('mail_entity_id')->unsigned();
            $table->integer('mail_address_id')->unsigned();
            $table->foreign('mail_entity_id')->references('id')->on('mail_entities')->onDelete('cascade');
            $table->foreign('mail_address_id')->references('id')->on('mail_addresses')->onDelete('cascade');
        });

        Schema::create('mail_attachments', function($table){
            $table->increments('id');
            $table->integer('entity_id')->unsigned();
            $table->string('name');
            $table->string('file_path');
            $table->string('ext_name');
            $table->foreign('entity_id')->references('id')->on('mail_entities')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('mail_address_book', function($table){
            $table->increments('id');
            $table->integer('mail_account_id')->unsigned();
            $table->integer('mail_address_id')->unsigned();
            $table->foreign('mail_account_id')->references('id')->on('mail_accounts')->onDelete('cascade');
            $table->foreign('mail_address_id')->references('id')->on('mail_addresses')->onDelete('cascade');
        });



	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{

        Schema::table('mail_entities', function(Blueprint $table) {
            $table->dropForeign('mail_entities_account_id_foreign');
        });

        Schema::table('to_addresses', function(Blueprint $table) {
            $table->dropForeign('to_addresses_mail_entity_id_foreign');
            $table->dropForeign('to_addresses_mail_address_id_foreign');
        });

        Schema::table('cc_addresses', function(Blueprint $table) {
            $table->dropForeign('cc_addresses_mail_entity_id_foreign');
            $table->dropForeign('cc_addresses_mail_address_id_foreign');
        });

        Schema::table('reply_addresses', function(Blueprint $table) {
            $table->dropForeign('reply_addresses_mail_entity_id_foreign');
            $table->dropForeign('reply_addresses_mail_address_id_foreign');
        });

        Schema::table('mail_attachments', function(Blueprint $table) {
            $table->dropForeign('mail_attachments_entity_id_foreign');
        });

        Schema::table('mail_address_book', function(Blueprint $table) {
            $table->dropForeign('mail_address_book_mail_account_id_foreign');
            $table->dropForeign('mail_address_book_mail_address_id_foreign');
        });
		//
        Schema::drop('mail_accounts');
        Schema::drop('mail_addresses');
        Schema::drop('mail_entities');
        Schema::drop('to_addresses');
        Schema::drop('cc_addresses');
        Schema::drop('reply_addresses');
        Schema::drop('mail_attachments');
        Schema::drop('mail_address_book');
	}

}
