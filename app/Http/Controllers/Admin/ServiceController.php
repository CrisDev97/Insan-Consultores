<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::query()
            ->orderByDesc('is_active')
            ->orderBy('position')
            ->orderByDesc('id')
            ->paginate(10);

        return view('admin.services.index', compact('services'));
    }

    public function create()
    {
        return view('admin.services.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateService($request);

        DB::transaction(function () use ($request, &$data) {

            // Manejo de imágenes (2)
            $data['image_1_path'] = $this->storeImage($request, 'image_1');
            $data['image_2_path'] = $this->storeImage($request, 'image_2');

            // Lógica posiciones: si entra activo, hacemos espacio
            if ($data['is_active']) {
                Service::where('is_active', true)
                    ->where('position', '>=', $data['position'])
                    ->increment('position');
            }

            Service::create($data);
        });

        return redirect()->route('admin.services.index')->with('success', 'Servicio creado.');
    }

    public function edit(Service $service)
    {
        return view('admin.services.edit', compact('service'));
    }

    public function update(Request $request, Service $service)
    {
        $data = $this->validateService($request);

        $newActive = (bool) $data['is_active'];
        $oldActive = (bool) $service->is_active;

        $newPos = (int) $data['position'];
        $oldPos = (int) $service->position;

        DB::transaction(function () use ($request, $service, &$data, $newActive, $oldActive, $newPos, $oldPos) {

            // Imágenes: si viene nueva, reemplaza y borra anterior
            $this->replaceImageIfUploaded($request, $service, $data, 'image_1', 'image_1_path');
            $this->replaceImageIfUploaded($request, $service, $data, 'image_2', 'image_2_path');

            // ------- LÓGICA DE ORDEN (misma idea banners) -------

            // CASO 1: Activo -> Activo (reordenamiento)
            if ($oldActive && $newActive) {

                if ($newPos !== $oldPos) {
                    // liberamos la posición temporalmente
                    $service->update(['position' => 0]);

                    if ($newPos > $oldPos) {
                        // se mueve hacia abajo: los intermedios retroceden
                        Service::where('is_active', true)
                            ->whereBetween('position', [$oldPos + 1, $newPos])
                            ->decrement('position');
                    } else {
                        // se mueve hacia arriba: los intermedios avanzan
                        Service::where('is_active', true)
                            ->whereBetween('position', [$newPos, $oldPos - 1])
                            ->increment('position');
                    }
                }

                $service->update($data);
                return;
            }

            // CASO 2: Activo -> Inactivo (cerrar hueco)
            if ($oldActive && !$newActive) {
                Service::where('is_active', true)
                    ->where('position', '>', $oldPos)
                    ->decrement('position');

                // conserva su última posición activa (útil si luego lo reactivas)
                $data['position'] = $oldPos;
                $service->update($data);
                return;
            }

            // CASO 3: Inactivo -> Activo (insertar en newPos, correr +1)
            if (!$oldActive && $newActive) {
                Service::where('is_active', true)
                    ->where('position', '>=', $newPos)
                    ->increment('position');

                $service->update($data);
                return;
            }

            // CASO 4: Inactivo -> Inactivo (solo actualiza campos)
            $service->update($data);
        });

        return redirect()->route('admin.services.index')->with('success', 'Servicio actualizado.');
    }

    public function destroy(Service $service)
    {
        DB::transaction(function () use ($service) {

            // Si estaba activo, cerrar hueco
            if ($service->is_active) {
                Service::where('is_active', true)
                    ->where('position', '>', $service->position)
                    ->decrement('position');
            }

            // Borrar imágenes del storage
            if ($service->image_1_path) Storage::disk('public')->delete($service->image_1_path);
            if ($service->image_2_path) Storage::disk('public')->delete($service->image_2_path);

            $service->delete();
        });

        return redirect()->route('admin.services.index')->with('success', 'Servicio eliminado.');
    }

    // ---------------- Helpers ----------------

    private function validateService(Request $request): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],

            // se ingresarán como texto multilinea y luego lo convertimos a array
            'includes_text' => ['nullable', 'string'],
            'objectives_text' => ['nullable', 'string'],

            'sessions_count' => ['nullable', 'integer', 'min:1'],
            'price' => ['nullable', 'numeric', 'min:0'],

            'position' => ['required', 'integer', 'min:1'],
            'is_active' => ['nullable'],

            'image_1' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:4096'],
            'image_2' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:4096'],
        ]);

        return [
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,

            // Convertimos a arrays por líneas
            'includes' => $this->linesToArray($validated['includes_text'] ?? ''),
            'objectives' => $this->linesToArray($validated['objectives_text'] ?? ''),

            'sessions_count' => $validated['sessions_count'] ?? null,
            'price' => $validated['price'] ?? null,

            'position' => (int) $validated['position'],
            'is_active' => $request->boolean('is_active'),

            // paths se llenan en store/update según upload
            'image_1_path' => null,
            'image_2_path' => null,
        ];
    }

    private function linesToArray(string $text): ?array
    {
        $lines = array_values(array_filter(array_map('trim', preg_split("/\r\n|\n|\r/", $text))));
        return count($lines) ? $lines : null;
    }

    private function storeImage(Request $request, string $field): ?string
    {
        if (!$request->hasFile($field)) return null;
        return $request->file($field)->store('services', 'public');
    }

    private function replaceImageIfUploaded(Request $request, Service $service, array &$data, string $uploadField, string $dbField): void
    {
        if (!$request->hasFile($uploadField)) {
            // si no sube, conservar la existente
            $data[$dbField] = $service->$dbField;
            return;
        }

        // borrar anterior
        if ($service->$dbField) {
            Storage::disk('public')->delete($service->$dbField);
        }

        $data[$dbField] = $request->file($uploadField)->store('services', 'public');
    }
}
