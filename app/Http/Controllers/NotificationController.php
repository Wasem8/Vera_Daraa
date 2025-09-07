<?php

namespace App\Http\Controllers;

use App\Http\Responses\Response;
use App\Models\Notification;
use GPBMetadata\Google\Api\Auth;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        return Response::Success([
            'notifications' => $user->notifications,
            'unread' => $user->unreadNotifications],'success');
    }

    public function markAsRead($id, Request $request)
    {
        $notification = $request->user()->notifications()->find($id);

        if ($notification) {
            $notification->markAsRead();
           return Response::Success(true,"Notification marked as read");
        }

        return response()->json(['message' => 'Notification not found'], 404);
    }


    public function destroy($id): \Illuminate\Http\JsonResponse
    {
        $notification = auth()->user()->notifications()->findOrFail($id);

        if(isset($notification)) {
            $notification->delete();
            return Response::Success(true,"Notification deleted");
        }
        return Response::Error(false,"Notification not found");
    }
}
