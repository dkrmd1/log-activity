<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->query('type', 'isu');
        abort_unless(array_key_exists($type, Category::TYPES), 404);

        $categories = Category::ofType($type)->ordered($type)->get();

        // Jumlah data yang memakai tiap kategori (untuk info & mengunci tombol hapus).
        $usage = $categories->mapWithKeys(fn (Category $c) => [$c->id => $c->usageQuery()->count()]);

        return view('categories.index', [
            'type' => $type,
            'types' => Category::TYPES,
            'categories' => $categories,
            'usage' => $usage,
        ]);
    }

    public function store(CategoryRequest $request)
    {
        $category = Category::create($request->validated());

        return redirect()
            ->route('categories.index', ['type' => $category->type])
            ->with('success', "\"{$category->name}\" berhasil ditambahkan.");
    }

    public function update(CategoryRequest $request, Category $category)
    {
        $oldName = $category->name;
        $newName = $request->validated('name');

        DB::transaction(function () use ($category, $oldName, $newName) {
            // Ganti nama massal dulu (selagi nama lama masih ada), supaya data lama tetap konsisten.
            // Pakai query builder -> tidak memicu riwayat status.
            if ($oldName !== $newName) {
                $category->usageQuery()->update([$this->columnFor($category->type) => $newName]);
            }

            $category->update(['name' => $newName]);
        });

        return redirect()
            ->route('categories.index', ['type' => $category->type])
            ->with('success', 'Nama berhasil diperbarui.');
    }

    public function destroy(Category $category)
    {
        $used = $category->usageQuery()->count();

        if ($used > 0) {
            return redirect()
                ->route('categories.index', ['type' => $category->type])
                ->with('error', "\"{$category->name}\" masih dipakai di {$used} data, tidak bisa dihapus. Ganti dulu data tersebut, atau edit namanya saja.");
        }

        $category->delete();

        return redirect()
            ->route('categories.index', ['type' => $category->type])
            ->with('success', 'Berhasil dihapus.');
    }

    protected function columnFor(string $type): string
    {
        return $type === 'backup' ? 'jenis' : 'category';
    }
}
