<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class UserTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('users')->delete();
        
        \DB::table('users')->insert(array (
            0 => 
            array (
                'id' => 1,
                'username' => 'admin',
                'name' => 'Admin',
                'contact_number' => '9876543210',
                'address' => NULL,
                'email' => 'admin@admin.com',
                'password' => bcrypt('12345678'),
                'email_verified_at' => NULL,
                'user_type' => 'admin',
                'player_id' => NULL,
                'remember_token' => NULL,
                'last_notification_seen' => NULL,
                'status' => 1,
                'current_team_id' => NULL,
                'profile_photo_path' => NULL,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => NULL,
                'deleted_at' => NULL,
            )
        ));
        
        
    }
}