<?php

namespace App\Services;

use App\Models\Service;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class  ServiceService
{
    public function create(array $data)
    {
        $data['image'] = $this->handleImageUpload($data['image'] ?? null);
        return Service::create($data);
    }

    /**
     * @throws \Throwable
     */
    public function update(array $data, Service $service)
    {
        $data['image'] = $this->handleImageUpload($data['image'] ?? null, $service->image);
        $service->update($data);
        return $service;
    }

    public function delete($serviceId)
    {
        $service = Service::query()->findOrFail($serviceId);
        $service->delete();
        return true;
    }

    private function handleImageUpload(?UploadedFile $image, ?string $oldPath = null): ?string
    {
        if ($image instanceof UploadedFile) {
            if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
            return $image->store('services', 'public');
        }
        return $oldPath;
    }


}
