<?php

namespace App\Services\Afiliate;

use App\Models\User;
use App\Models\Afiliate;
use Illuminate\Support\Facades\Hash;

class AfiliateService
{
    // هيكل موحد للردود
    private function formatResponse($afiliate, $user = null, $message = '', $additionalData = [])
    {
        $response = [
            'afiliate' => [
                'id' => $afiliate->id,
                'status' => $afiliate->status,
            ],
            'user' => [
                'id' => $user ? $user->id : $afiliate->user->id,
                'name' => $user ? $user->name : $afiliate->user->name,
                'email' => $user ? $user->email : $afiliate->user->email,
            ],
            'message' => $message,
        ];

        return array_merge($response, $additionalData);
    }

    public function getAfiliateInfo($afiliateId)
    {
        $afiliate = Afiliate::findOrFail($afiliateId);

        return $this->formatResponse($afiliate, null, 'Afiliate info retrieved successfully');
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
            $afiliate->status = $data['status']; // تصحيح: كان هناك خطأ هنا حيث كان يتم تعيينه لـ user->afiliate->id
            $afiliate->save();
        }

        $user->save();

        $additionalData = [];
        if (isset($data['password'])) {
            $additionalData['password_updated'] = true;
        }

        return $this->formatResponse($afiliate, $user, 'Afiliate and user updated successfully', $additionalData);
    }

    public function updateAfiliateStatus($afiliateId, $status)
    {
        $afiliate = Afiliate::findOrFail($afiliateId);
        $afiliate->status = $status;
        $afiliate->save();

        return $this->formatResponse($afiliate, null, 'Afiliate status updated successfully');
    }

    public function getAfiliatesByStatus($status, $perPage = 5)
    {
        $query = $status === 'all' ? Afiliate::query() : Afiliate::where('status', $status);
        $afiliates = $query->paginate($perPage);

        $formattedAfiliates = $afiliates->map(function ($afiliate) {
            return $this->formatResponse($afiliate, $afiliate->user);
        });

        return [
            'data' => $formattedAfiliates,
            'pagination' => [
                'current_page' => $afiliates->currentPage(),
                'last_page' => $afiliates->lastPage(),
                'per_page' => $afiliates->perPage(),
                'total' => $afiliates->total(),
            ],
            'message' => 'Afiliates retrieved successfully',
        ];
    }
}
