<?php

namespace App\Http\Controllers;

use App\Models\ControlItem;
use App\Models\ControlSection;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Admin: kelola bagian (A, B, C, ...) dan item pada form "Kontrol Server & Jaringan".
 * Perubahan hanya berlaku untuk form BARU; form yang sudah dibuat tidak berubah.
 */
class ControlItemController extends Controller
{
    public function index()
    {
        return view('control-items.index', [
            'sections' => ControlSection::with('items')->orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }

    public function storeSection(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:5', 'unique:control_sections,code'],
            'title' => ['required', 'string', 'max:255'],
        ], [], ['code' => 'Kode', 'title' => 'Judul bagian']);

        ControlSection::create($data + [
            'merge_notes' => $request->boolean('merge_notes'),
            'sort_order' => ((int) ControlSection::max('sort_order')) + 10,
        ]);

        return back()->with('success', 'Bagian berhasil ditambahkan.');
    }

    public function updateSection(Request $request, ControlSection $section)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:5', Rule::unique('control_sections', 'code')->ignore($section->id)],
            'title' => ['required', 'string', 'max:255'],
            'sort_order' => ['required', 'integer', 'between:0,65000'],
        ], [], ['code' => 'Kode', 'title' => 'Judul bagian', 'sort_order' => 'Urutan']);

        $section->update($data + ['merge_notes' => $request->boolean('merge_notes')]);

        return back()->with('success', 'Bagian berhasil diperbarui.');
    }

    public function destroySection(ControlSection $section)
    {
        $section->delete(); // item ikut terhapus; form lama tidak terpengaruh (snapshot)

        return back()->with('success', 'Bagian beserta itemnya berhasil dihapus.');
    }

    public function storeItem(Request $request, ControlSection $section)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ], [], ['name' => 'Nama item']);

        $section->items()->create($data + [
            'sort_order' => ((int) $section->items()->max('sort_order')) + 10,
            'is_active' => true,
        ]);

        return back()->with('success', 'Item berhasil ditambahkan.');
    }

    public function updateItem(Request $request, ControlItem $item)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sort_order' => ['required', 'integer', 'between:0,65000'],
        ], [], ['name' => 'Nama item', 'sort_order' => 'Urutan']);

        $item->update($data + ['is_active' => $request->boolean('is_active')]);

        return back()->with('success', 'Item berhasil diperbarui.');
    }

    public function destroyItem(ControlItem $item)
    {
        $item->delete();

        return back()->with('success', 'Item berhasil dihapus.');
    }
}
