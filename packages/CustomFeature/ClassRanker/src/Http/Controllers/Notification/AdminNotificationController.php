<?php
;
namespace CustomFeature\ClassRanker\Http\Controllers\Notification;

use CustomFeature\ClassRanker\DataGrids\NotificationDataGrid;
use CustomFeature\ClassRanker\Models\Notification;
use CustomFeature\ClassRankerApi\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Webkul\Admin\Http\Controllers\Controller;

class AdminNotificationController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    /**
     * Display listing.
     */
    public function index()
    {
        if (request()->ajax()) {
            return app(NotificationDataGrid::class)->toJson();
        }
 
        return view('class_ranker::notifications.index');
    }

    /**
     * Create page.
     */
    public function create()
    {
        return view('class_ranker::notifications.create');
    }

    /**
     * Store notification.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'         => ['required', 'string', 'max:255'],
            'text'          => ['nullable', 'string'],
            'redirect_type' => ['required', 'in:internal,external'],
            'link'          => ['nullable', 'string', 'max:1000'],
            'path'          => ['nullable', 'string', 'max:500'],
            'params'        => ['nullable'],
            'image'         => ['nullable'],
        ]);

        $data = [
            'title'         => $validated['title'],
            'text'          => $validated['text'] ?? null,
            'redirect_type' => $validated['redirect_type'],
            'link'          => $validated['link'] ?? null,
            'path'          => $validated['path'] ?? null,
        ];

        if ($request->filled('params')) {
            $data['params'] = json_decode(
                $request->params,
                true
            );
        }

        if ($request->hasFile('image')) {
            $image = $request->file('image');

            if (is_array($image)) {
                $image = $image[0];
            }

            if ($image instanceof \Illuminate\Http\UploadedFile) {
                $data['image'] = $image->store('notifications', 'public');
            }
        }

        Notification::create($data);

        session()->flash(
            'success',
            'Notification created successfully.'
        );

        return redirect()->route(
            'admin.notifications.index'
        );
    }

    /**
     * Edit page.
     */
    public function edit(int $id)
    {
        $notification = Notification::findOrFail($id);

        return view(
            'class_ranker::notifications.edit',
            compact('notification')
        );
    }

    /**
     * Update notification.
     */
    public function update(Request $request, int $id)
    {
        $notification = Notification::findOrFail($id);

        $validated = $request->validate([
            'title'         => ['required', 'string', 'max:255'],
            'text'          => ['nullable', 'string'],
            'redirect_type' => ['required', 'in:internal,external'],
            'link'          => ['nullable', 'string', 'max:1000'],
            'path'          => ['nullable', 'string', 'max:500'],
            'params'        => ['nullable'],
            'image'         => ['nullable'],
        ]);

        $data = [
            'title'         => $validated['title'],
            'text'          => $validated['text'] ?? null,
            'redirect_type' => $validated['redirect_type'],
            'link'          => $validated['link'] ?? null,
            'path'          => $validated['path'] ?? null,
        ];

        if ($request->filled('params')) {
            $data['params'] = json_decode(
                $request->params,
                true
            );
        } else {
            $data['params'] = null;
        }

        if ($request->hasFile('image')) {
            if (
                $notification->image
                && Storage::disk('public')->exists($notification->image)
            ) {
                Storage::disk('public')->delete(
                    $notification->image
                );
            }

            $data['image'] = $request
                ->file('image')
                ->store('notifications', 'public');
        }

        $notification->update($data);

        session()->flash(
            'success',
            'Notification updated successfully.'
        );

        return redirect()->route(
            'admin.notifications.index'
        );
    }

    /**
     * Delete notification.
     */
    public function destroy(int $id)
    {
        $notification = Notification::findOrFail($id);

        if (
            $notification->image
            && Storage::disk('public')->exists($notification->image)
        ) {
            Storage::disk('public')->delete(
                $notification->image
            );
        }

        $notification->delete();

        session()->flash(
            'success',
            'Notification deleted successfully.'
        );

        return redirect()->route(
            'admin.notifications.index'
        );
    }

    public function sendToAll(int $id)
    {
        $notification = Notification::findOrFail($id);

        // 1. increment count
        $notification->increment('count');

        // 2. prepare FCM payload
        $dataPayload = [
            'redirect_type' => $notification->redirect_type,
            'path'          => $notification->path,
            'link'          => $notification->link,
            'image'         => $notification->image_url,
        ];

        // merge params if exists
        if (!empty($notification->params)) {
            $dataPayload = array_merge(
                $dataPayload,
                $notification->params
            );
        }

        // ensure all values are string (FCM requirement)
        $dataPayload = array_map('strval', $dataPayload);

        // 3. send via queue service
        $this->notificationService->sendToAllCustomersQueued(
            $notification->title,
            $notification->text,
            $dataPayload
        );

        return response()->json([
            'success' => true,
            'message' => 'Notification queued successfully',
        ]);
    }
}