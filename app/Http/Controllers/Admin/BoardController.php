<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Board;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SampleBoardExport; // (Make sure to create this export class)
use App\Imports\BoardImport;       // (Make sure to create this import class)

class BoardController extends Controller
{
    public function index()
    {
        $boards = Board::orderBy('serial', 'asc')->get();
        return view('admin.board.index', compact('boards'));
    }

    public function data(Request $request)
    {
        $query = Board::query();

        if ($request->filled('search')) {
            $query->where('name_en', 'like', $request->search . '%')
                  ->orWhere('name_bn', 'like', $request->search . '%');
        }

        $sort = $request->get('sort', 'serial');
        $direction = $request->get('direction', 'asc');
        $query->orderBy($sort, $direction);

        $data = $query->paginate(10);

        return response()->json([
            'data' => $data->items(),
            'total' => $data->total(),
            'current_page' => $data->currentPage(),
            'last_page' => $data->lastPage(),
            'per_page' => $data->perPage(),
            'from' => $data->firstItem(),
            'to' => $data->lastItem(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name_en' => 'required|string',
            'name_bn' => 'required|string',
        ]);

        Board::create([
            'name_en' => $request->name_en,
            'name_bn' => $request->name_bn,
            'status' => $request->status ?? 1,
            'serial' => (Board::max('serial') ?? 0) + 1,
        ]);

        return redirect()->back()->with('success', 'Board created successfully!');
    }

    public function update(Request $request, $id)
    {
        $board = Board::findOrFail($id);
        $request->validate([
            'name_en' => 'required|string',
            'name_bn' => 'required|string',
        ]);

        $board->update([
            'name_en' => $request->name_en,
            'name_bn' => $request->name_bn,
            'status' => $request->status,
        ]);

        return redirect()->back()->with('success', 'Board updated successfully!');
    }

    public function destroy($id)
    {
        Board::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Board deleted successfully!');
    }

    public function show($id)
    {
        return response()->json(Board::findOrFail($id));
    }

    public function reorder(Request $request)
    {
        foreach ($request->order as $order) {
            Board::where('id', $order['id'])->update(['serial' => $order['position']]);
        }
        return response()->json(['status' => 'success']);
    }

    // Import/Export Methods
    public function downloadSample()
    {
        // Ensure you have created SampleBoardExport class
         return Excel::download(new SampleBoardExport, 'board_sample.xlsx');
        return redirect()->back()->with('error', 'Export class not found yet.');
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv']);
        // Ensure you have created BoardImport class
         Excel::import(new BoardImport, $request->file('file'));
        return redirect()->back()->with('success', 'Boards imported successfully!');
    }
}