<?php

namespace App\Imports;

use App\Models\Api\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class ExcelImport implements ToCollection
{
    // $newUser = new \App\Models\Api\User();
    /**
    * @param Collection $collection
    */
   public function collection(Collection $rows)
    {
        $skippedFirstRow = false;
        foreach ($rows as $row) {
            // dd($row['3']);
            if (!$skippedFirstRow) {
            $skippedFirstRow = true;
            continue; // Skip the first row
            }
           User::create([
               'id' => $row[0],
               'full_name' => $row['1'],
                'role_name' => $row['2'],
                'role_id' => $row['3'],
                'organ_id' => $row['4'] === "NULL" ? null : (int) $row[4],
                'mobile' => $row['5'],
                'email' => $row['6'],
                'bio' => $row['7'],
                'password' => $row['8'], // You might need to hash the password
                'google_id' => $row['9'],
                'facebook_id' => $row['10'],
                'remember_token' => $row['11'],
                'logged_count' => $row['12'],
                'verified' => $row['13'],
                'financial_approval' => $row['14'],
                'installment_approval' => $row['15'],
                'enable_installments' => $row['16'],
                'disable_cashback' => $row['17'],
                'enable_registration_bonus' => $row['18'],
                'registration_bonus_amount' => $row['19'] === "NULL" ? null : (float) $row[19],
                'avatar' => $row['20'],
                'avatar_settings' => $row['21']=== "NULL" ? null : (float) $row[21],
                'cover_img' => $row['22'],
                'headline' => $row['23'],
                'about' => $row['24'],
                'address' => $row['25'],
                'country_id' => $row['26']=== "NULL" ? null : (int) $row[26],
                'province_id' => $row[27] === "NULL" ? null : (int) $row[27],
                'city_id' => $row[28] === "NULL" ? null : (int) $row[28],
                'district_id' => $row[29] === "NULL" ? null : (int) $row[29],
                'location' => $row['30'] === "NULL" ? null : (int) $row[30],
                'level_of_training' => $row['31']=== "NULL" ? null : (int) $row[31],
                'meeting_type' => $row['32'],
                'status' => $row['33'],
                'access_content' => $row['34'],
                'language' => $row['35'],
                'currency' => $row['36'],
                'timezone' => $row['37'],
                'newsletter' => $row['38'],
                'public_message' => $row['39'],
                'identity_scan' => $row['40'],
                'certificate' => $row['41'],
                'commission' => $row['42']=== "NULL" ? null : (int) $row[42],
                'affiliate' => $row['43'],
                'can_create_store' => $row['44'],
                'ban' => $row['45'],
                'ban_start_at' => $row['46']=== "NULL" ? null : (int) $row[46],
                'ban_end_at' => $row['47']=== "NULL" ? null : (int) $row[47],
                'offline' => $row['48'],
                'offline_message' => $row['49'],
                'created_at' => $row['50'],
                'updated_at' => $row['51']=== "NULL" ? null : (int) $row[51],
                'deleted_at' => $row['52']=== "NULL" ? null : (int) $row[52],
               ]);
        }
        //  $importedUsers = $rows->map(function ($row) {
           
        //  $user = new User([
        //         'id' => $row['id'],
        //         'full_name' => $row['full_name'],
        //         'role_name' => $row['role_name'],
        //         'role_id' => $row['role_id'],
        //         'organ_id' => $row['organ_id'],
        //         'mobile' => $row['mobile'],
        //         'email' => $row['email'],
        //         'bio' => $row['bio'],
        //         'password' => $row['password'], // You might need to hash the password
        //         'google_id' => $row['google_id'],
        //         'facebook_id' => $row['facebook_id'],
        //         'remember_token' => $row['remember_token'],
        //         'logged_count' => $row['logged_count'],
        //         'verified' => $row['verified'],
        //         'financial_approval' => $row['financial_approval'],
        //         'installment_approval' => $row['installment_approval'],
        //         'enable_installments' => $row['enable_installments'],
        //         'disable_cashback' => $row['disable_cashback'],
        //         'enable_registration_bonus' => $row['enable_registration_bonus'],
        //         'registration_bonus_amount' => $row['registration_bonus_amount'],
        //         'avatar' => $row['avatar'],
        //         'avatar_settings' => $row['avatar_settings'],
        //         'cover_img' => $row['cover_img'],
        //         'headline' => $row['headline'],
        //         'about' => $row['about'],
        //         'address' => $row['address'],
        //         'country_id' => $row['country_id'],
        //         'province_id' => $row['province_id'],
        //         'city_id' => $row['city_id'],
        //         'district_id' => $row['district_id'],
        //         'location' => $row['location'],
        //         'level_of_training' => $row['level_of_training'],
        //         'meeting_type' => $row['meeting_type'],
        //         'status' => $row['status'],
        //         'access_content' => $row['access_content'],
        //         'language' => $row['language'],
        //         'currency' => $row['currency'],
        //         'timezone' => $row['timezone'],
        //         'newsletter' => $row['newsletter'],
        //         'public_message' => $row['public_message'],
        //         'identity_scan' => $row['identity_scan'],
        //         'certificate' => $row['certificate'],
        //         'commission' => $row['commission'],
        //         'affiliate' => $row['affiliate'],
        //         'can_create_store' => $row['can_create_store'],
        //         'ban' => $row['ban'],
        //         'ban_start_at' => $row['ban_start_at'],
        //         'ban_end_at' => $row['ban_end_at'],
        //         'offline' => $row['offline'],
        //         'offline_message' => $row['offline_message'],
        //         'created_at' => $row['created_at'],
        //         'updated_at' => $row['updated_at'],
        //         'deleted_at' => $row['deleted_at'],
        //     ]);
        //      $user->save();
        //      return $user;
        //  });
        //   return $importedUsers;
        }
         public function map($row): array
    {
        return [
            0 => $row[0] ];
    }
    }

