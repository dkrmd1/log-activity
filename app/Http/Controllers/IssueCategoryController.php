<?php

namespace App\Http\Controllers;

use App\Http\Requests\IssueCategoryRequest;
use App\Models\Issue;
use App\Models\IssueCategory;

class IssueCategoryController extends Controller
{
    public function index()
    {
        $categories = IssueCategory::orderBy('name')->get();

        return view('issue-categories.index', compact('categories'));
    }

    public function store(IssueCategoryRequest $request)
    {
        IssueCategory::create($request->validated());

        return back()->with('success', 'Kategori baru berhasil ditambahkan.');
    }

    public function update(IssueCategoryRequest $request, IssueCategory $issue_category)
    {
        $oldName = $issue_category->name;
        $newName = $request->validated('name');

        $issue_category->update(['name' => $newName]);

        // Supaya data isu yang sudah ada tetap konsisten kalau nama kategorinya diganti.
        if ($oldName !== $newName) {
            Issue::where('category', $oldName)->update(['category' => $newName]);
        }

        return back()->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(IssueCategory $issue_category)
    {
        $usedCount = Issue::where('category', $issue_category->name)->count();

        if ($usedCount > 0) {
            return back()->with('error', "Kategori ini masih dipakai di {$usedCount} data isu, tidak bisa dihapus. Ganti dulu kategori data tersebut, atau edit namanya saja.");
        }

        $issue_category->delete();

        return back()->with('success', 'Kategori berhasil dihapus.');
    }
}
