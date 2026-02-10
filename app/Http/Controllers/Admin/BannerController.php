<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class BannerController extends Controller
{
    public function index()
    {
        $active = Banner::query()
            ->where('is_active', true)
            ->orderBy('position')
            ->get();

        $inactive = Banner::query()
            ->where('is_active', false)
            ->orderByDesc('id')
            ->get();

        return view('admin.banners.index', compact('active', 'inactive'));
    }

    public function create()
    {
        $maxPos = (int) Banner::where('is_active', true)->max('position');
        return view('admin.banners.create', compact('maxPos'));
    }

    public function store(Request $request)
    {
        $maxPos = (int) Banner::where('is_active', true)->max('position');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'image' => ['required', 'file', 'mimes:png,jpg,jpeg', 'max:5120'],
            'is_active' => ['required', Rule::in(['0', '1'])],
            'position' => ['nullable', 'integer', 'min:1', 'max:' . max(1, $maxPos + 1)],
        ]);

        $isActive = $data['is_active'] === '1';

        $path = $request->file('image')->store('banners', 'public');

        DB::transaction(function () use ($data, $isActive, $path, $maxPos) {

            if ($isActive) {
                // si no envía posición -> al final
                $pos = (int) ($data['position'] ?? ($maxPos + 1));

                // corre +1 desde pos hacia adelante
                Banner::where('is_active', true)
                    ->where('position', '>=', $pos)
                    ->increment('position', 1);

                Banner::create([
                    'name' => $data['name'],
                    'image_path' => $path,
                    'is_active' => true,
                    'position' => $pos,
                ]);
            } else {
                // inactivo: sin posición
                Banner::create([
                    'name' => $data['name'],
                    'image_path' => $path,
                    'is_active' => false,
                    'position' => null,
                ]);
            }
        });

        return redirect()->route('admin.banners.index')->with('ok', 'Banner creado.');
    }

    public function edit(Banner $banner)
    {
        $maxPos = (int) Banner::where('is_active', true)->max('position');
        return view('admin.banners.edit', compact('banner', 'maxPos'));
    }

    public function update(Request $request, Banner $banner)
    {
        $maxPos = (int) Banner::where('is_active', true)->max('position');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'image' => ['nullable', 'file', 'mimes:png,jpg,jpeg', 'max:5120'],
            'is_active' => ['required', Rule::in(['0', '1'])],
            'position' => ['nullable', 'integer', 'min:1', 'max:' . max(1, $maxPos + 1)],
        ]);

        $newIsActive = $data['is_active'] === '1';
        $oldIsActive = (bool) $banner->is_active;
        $oldPos = $banner->position; // puede ser null
        $newPos = $data['position'] ?? null;

        DB::transaction(function () use ($request, $data, $banner, $newIsActive, $oldIsActive, $oldPos, $newPos) {

            // 1) Imagen (opcional)
            if ($request->hasFile('image')) {
                if ($banner->image_path && Storage::disk('public')->exists($banner->image_path)) {
                    Storage::disk('public')->delete($banner->image_path);
                }
                $banner->image_path = $request->file('image')->store('banners', 'public');
            }

            // 2) Nombre
            $banner->name = $data['name'];

            // 3) Cambios de estado/posición
            if ($oldIsActive && !$newIsActive) {
                // ACTIVO -> INACTIVO
                // cierra hueco (-1 para los que están adelante)
                Banner::where('is_active', true)
                    ->where('position', '>', $oldPos)
                    ->decrement('position', 1);

                $banner->is_active = false;
                $banner->position = null;
            }
            elseif (!$oldIsActive && $newIsActive) {
                // INACTIVO -> ACTIVO
                // si no manda posición: al final (después del máximo actual)
                $maxPosNow = (int) Banner::where('is_active', true)->max('position');
                $pos = (int) ($newPos ?? ($maxPosNow + 1));

                // corre +1 desde pos
                Banner::where('is_active', true)
                    ->where('position', '>=', $pos)
                    ->increment('position', 1);

                $banner->is_active = true;
                $banner->position = $pos;
            }
            elseif ($oldIsActive && $newIsActive) {
                // ACTIVO -> ACTIVO (posible cambio de posición)
                // si no manda posición, mantener
                $pos = (int) ($newPos ?? $oldPos);

                if ($pos !== (int)$oldPos) {
                    if ($pos > (int)$oldPos) {
                        // mover hacia abajo: los intermedios retroceden
                        Banner::where('is_active', true)
                            ->where('id', '!=', $banner->id)
                            ->whereBetween('position', [(int)$oldPos + 1, $pos])
                            ->decrement('position', 1);
                    } else {
                        // mover hacia arriba: los intermedios avanzan
                        Banner::where('is_active', true)
                            ->where('id', '!=', $banner->id)
                            ->whereBetween('position', [$pos, (int)$oldPos - 1])
                            ->increment('position', 1);
                    }

                    $banner->position = $pos;
                }

                $banner->is_active = true;
            }
            else {
                // INACTIVO -> INACTIVO (solo nombre/imagen)
                $banner->is_active = false;
                $banner->position = null;
            }

            $banner->save();
        });

        return redirect()->route('admin.banners.index')->with('ok', 'Banner actualizado.');
    }

    public function destroy(Banner $banner)
    {
        DB::transaction(function () use ($banner) {
            if ($banner->is_active && $banner->position !== null) {
                $oldPos = $banner->position;

                Banner::where('is_active', true)
                    ->where('position', '>', $oldPos)
                    ->decrement('position', 1);
            }

            if ($banner->image_path && Storage::disk('public')->exists($banner->image_path)) {
                Storage::disk('public')->delete($banner->image_path);
            }

            $banner->delete();
        });

        return redirect()->route('admin.banners.index')->with('ok', 'Banner eliminado.');
    }
}
