// src/Core/Http/Middleware/EnforceOwnership.php
<?php

namespace App\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class EnforceOwnership
{
    public function handle(Request $request, Closure $next, string $modelClass)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
                'error_code' => 'UNAUTHENTICATED'
            ], 401);
        }

        // Log the ownership enforcement attempt
        Log::debug('EnforceOwnership middleware processing', [
            'user_id' => $user->id,
            'model_class' => $modelClass,
            'method' => $request->method(),
            'route' => $request->route()->getName(),
            'has_id' => $request->route('id') !== null
        ]);

        // For index operations, filter by ownership
        if ($request->isMethod('GET') && !$request->route('id')) {
            if (!$user->hasPermission('admin.all')) {
                $request->merge(['user_id' => $user->id]);

                Log::debug('Filtering index request by user_id', [
                    'user_id' => $user->id,
                    'model_class' => $modelClass
                ]);
            }
        }

        // For create operations, set user_id
        if ($request->isMethod('POST')) {
            $request->merge(['user_id' => $user->id]);

            Log::debug('Setting user_id for create operation', [
                'user_id' => $user->id,
                'model_class' => $modelClass
            ]);
        }

        // For operations on specific resources, check ownership
        if ($request->route('id') && in_array($request->method(), ['GET', 'PUT', 'PATCH', 'DELETE'])) {
            $model = $modelClass::find($request->route('id'));

            if (!$model) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resource not found',
                    'error_code' => 'NOT_FOUND'
                ], 404);
            }

            // Check if user can access this resource
            if (!$user->hasPermission('admin.all') && !$user->isOwnerOf($model)) {
                Log::warning('User attempted to access resource they do not own', [
                    'user_id' => $user->id,
                    'model_id' => $model->id,
                    'model_class' => $modelClass,
                    'method' => $request->method()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to access this resource',
                    'error_code' => 'FORBIDDEN'
                ], 403);
            }
        }

        return $next($request);
    }
}
