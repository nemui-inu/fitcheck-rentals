<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ItemImageController extends Controller
{
    /**
     * Save rows first, then write the files once the transaction commits.
     */
    public function store(Request $request, int $item): RedirectResponse
    {
        $item = $request->user()->ownerProfile->items()->findOrFail($item);

        $request->validate([
            'photos' => ['required', 'array', 'max:8'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        /** @var array<int, UploadedFile> $photos */
        $photos = $request->file('photos');

        $uploads = DB::transaction(function () use ($item, $photos) {
            $nextOrder = (int) $item->images()->max('sort_order') + ($item->images()->exists() ? 1 : 0);

            return collect($photos)->map(function (UploadedFile $photo, int $index) use ($item, $nextOrder) {
                $image = $item->images()->create([
                    'path' => "items/{$item->id}/".Str::uuid().'.'.$photo->extension(),
                    'sort_order' => $nextOrder + $index,
                ]);

                return [$image, $photo];
            });
        });

        foreach ($uploads as [$image, $photo]) {
            Storage::disk('public')->putFileAs(dirname($image->path), $photo, basename($image->path));
        }

        return back();
    }

    /**
     * Move a photo one place up or down. The first photo is the cover.
     */
    public function update(Request $request, int $item, int $image): RedirectResponse
    {
        $item = $request->user()->ownerProfile->items()->findOrFail($item);
        $image = $item->images()->findOrFail($image);

        $validated = $request->validate(['direction' => ['required', Rule::in(['up', 'down'])]]);

        $images = $item->images()->get()->all();
        $position = array_search($image->id, array_column($images, 'id'), true);
        $target = $validated['direction'] === 'up' ? $position - 1 : $position + 1;

        if (isset($images[$target])) {
            [$images[$position], $images[$target]] = [$images[$target], $images[$position]];

            DB::transaction(function () use ($images) {
                foreach ($images as $index => $each) {
                    $each->update(['sort_order' => $index]);
                }
            });
        }

        return back();
    }

    public function destroy(Request $request, int $item, int $image): RedirectResponse
    {
        $item = $request->user()->ownerProfile->items()->findOrFail($item);
        $image = $item->images()->findOrFail($image);

        $image->delete();
        Storage::disk('public')->delete($image->path);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Photo removed.')]);

        return back();
    }
}
