<?php

namespace App\Services\Afiliate;

use App\Models\User;
use App\Models\Afiliate;
use Illuminate\Support\Facades\Hash;

class AfiliateService
{
    public function getAfiliateInfo($afiliateId)
    {
        $afiliate = Afiliate::findOrFail($afiliateId);

        return [
            'user_name' => $afiliate->user->name,
            'user_email' => $afiliate->user->email,
            'user_id' => $afiliate->user->id,
            'afiliate_id' => $afiliate->id,
            'afiliate_status' => $afiliate->status,
        ];
    }

    public function updateAfiliateAndUser($afiliateId, array $data)
    {
        $afiliate = Afiliate::findOrFail($afiliateId);
        $user = $afiliate->user;

        if (isset($data['name'])) {
            $user->name = $data['name'];
        }
        if (isset($data['email'])) {
            $user->email = $data['email'];
        }
        if (isset($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        if (isset($data['status'])) {
            $user->afiliate->id = $data['status'];
        }

        $user->save();

        return [
            'user_id' => $user->id,
            'afiliate_id' => $afiliate->id,
            'status' => $afiliate->status,
            'name' => $user->name,
            'email' => $user->email,
            'password' => $data['password'] ?? 'Password not updated',
        ];
    }

    public function updateAfiliateStatus($afiliateId, $status)
    {
        $afiliate = Afiliate::findOrFail($afiliateId);
        $afiliate->status = $status;
        $afiliate->save();

        return [
            'afiliate_id' => $afiliate->id,
            'status' => $afiliate->status,
            'message' => 'Afiliate status updated successfully.',
        ];
    }

    public function getAfiliatesByStatus($status, $perPage = 5)
    {
        $query = $status === 'all' ? Afiliate::query() : Afiliate::where('status', $status);

        $afiliates = $query->paginate($perPage);

        return [
            'data' => $afiliates->map(function ($afiliate) {
                $user = $afiliate->user;
                return [
                    'user_id' => $user->id ?? null,
                    'afiliate_id' => $afiliate->id,
                    'status' => $afiliate->status,
                    'name' => $user->name ?? null,
                    'email' => $user->email ?? null,
                ];
            }),
            'pagination' => [
                'current_page' => $afiliates->currentPage(),
                'last_page' => $afiliates->lastPage(),
                'per_page' => $afiliates->perPage(),
                'total' => $afiliates->total(),
            ],
        ];
    }
}
