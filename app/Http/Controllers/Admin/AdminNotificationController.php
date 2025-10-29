<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AdminNotification;
use App\Models\User;

class AdminNotificationController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 20);
        $type = $request->get('type');
        $unread_only = $request->get('unread_only', false);

        $query = AdminNotification::query()->orderBy('created_at', 'desc');

        if ($type) {
            $query->where('type', $type);
        }

        if ($unread_only) {
            $query->where('is_read', false);
        }

        $notifications = $query->paginate($perPage);
        $unreadCount = AdminNotification::where('is_read', false)->count();

        return view('admin.admin-notifications.index', compact('notifications', 'unreadCount'));
    }

    public function markAsRead(Request $request, $id)
    {
        $notification = AdminNotification::findOrFail($id);
        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notification marquée comme lue'
        ]);
    }

    public function markAllAsRead(Request $request)
    {
        AdminNotification::unread()->update([
            'is_read' => true,
            'read_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Toutes les notifications marquées comme lues'
        ]);
    }

    public function getUnreadCount()
    {
        return response()->json([
            'success' => true,
            'unread_count' => AdminNotification::unread()->count()
        ]);
    }

    public function getPendingUsers(Request $request)
    {
        $perPage = $request->get('per_page', 20);
        
        // Récupérer tous les utilisateurs avec statut inactif ou expiré
        $pendingUsers = User::whereIn('account_status', ['inactive', 'expired'])
                    ->orderBy('created_at', 'desc')
                    ->paginate($perPage);
        
        $totalPending = User::where('account_status', 'inactive')->count();

        // Retourner la vue même s'il n'y a pas d'utilisateurs en attente
        return view('admin.users.pending', compact('pendingUsers', 'totalPending'));
    }

    public function activateUser(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        
        if ($user->isAccountActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Le compte est déjà actif'
            ], 400);
        }

        // Manually activate account
        $user->activateAccount();
        
        // Give welcome bonus if not already claimed
        if (!$user->welcome_bonus_claimed) {
            $user->update([
                'balance' => $user->balance + 2000,
                'welcome_bonus_claimed' => true
            ]);
            
            // Create welcome bonus transaction
            $user->transactions()->create([
                'type' => 'bonus',
                'amount' => 2000,
                'description' => 'Bonus de bienvenue - Activation manuelle par admin',
                'status' => 'completed',
                'meta' => json_encode([
                    'bonus_type' => 'welcome_bonus_manual',
                    'activated_by' => 'admin'
                ])
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Compte activé avec succès',
            'user' => $user->fresh()
        ]);
    }

    public function deactivateUser(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        
        $user->deactivateAccount();

        return response()->json([
            'success' => true,
            'message' => 'Compte désactivé avec succès',
            'user' => $user->fresh()
        ]);
    }
}
