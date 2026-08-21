<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'mobile' => $this->mobile,
            'alternate_mobile' => $this->alternate_mobile,
            'house' => $this->whenLoaded('house'),
            'block_wing' => $this->block_wing,
            'address' => $this->address,
            'profile_photo' => $this->profile_photo,
            'status' => $this->status,
            'locale' => $this->locale,
            'roles' => $this->whenLoaded('roles', fn () => $this->roles->pluck('name')),
            'family_members' => $this->whenLoaded('familyMembers'),
            'vehicles' => $this->whenLoaded('vehicles'),
            'emergency_contact_name' => $this->emergency_contact_name,
            'emergency_mobile' => $this->emergency_mobile,
        ];
    }
}
