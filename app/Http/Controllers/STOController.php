<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Inventory; // Import the Inventory model
use App\Models\ReportSTO;
use App\Models\Part; // Assuming you have a Part model

class STOController extends Controller
{
  public function index()
  {
    $user = Auth::user();
    $inventoryC = new InventoryController();
    return view('STO.index', compact('user'));
  }

  public function show($id)
  {
    $user = Auth::user();
    $inventoryC = new InventoryController();
    $report = ReportSTO::with('inventory')->findOrFail($id);
    return view('STO.index', compact('user', 'report'));
  }

  public function scan(Request $request)
  {
    $request->validate([
      'inventory_id' => 'required|string',
    ]);

    $inventory = Inventory::where('inventory_id', $request->inventory_id)->first();

    if ($inventory) {
      return redirect()->route('sto.form',  $inventory->inventory_id);
    }

    return back()->with('error', 'Inventory not found. Please try again.');
  }

  public function form($inventory_id)
  {
    $inventory = Inventory::where('inventory_id', $inventory_id)->first();
    if ($inventory) {
      $inventoryC = new InventoryController();
      return view('sto.form', compact('inventory'));
    }
    return back()->with('error', 'Inventory not found. Please try again.');
  }

  public function store(Request $request)
  {
    $validatedData = $request->validate([
      'id_inventory' => 'required|exists:inventory,id',
      'inventory_id' => 'required|exists:inventory,inventory_id',
      'issued_date' => 'required|date',
      'prepared_by' => 'required|exists:users,id',
      'status' => 'required|string',
      'qty_per_box' => 'required|integer',
      'qty_box' => 'required|integer',
      'total' => 'required|integer',
      'qty_per_box_2' => 'nullable|integer',
      'qty_box_2' => 'nullable|integer',
      'total_2' => 'nullable|integer',
      'grand_total' => 'required|integer',
      'detail_lokasi' => 'nullable|string', // Add detail_lokasi validation
    ]);

    // Create and save the report
    $reportSTO = ReportSTO::create([
      'inventory_id' => $validatedData['inventory_id'],
      'issued_date' => $validatedData['issued_date'],
      'prepared_by' => $validatedData['prepared_by'],
      'status' => $validatedData['status'],
      'qty_per_box' => $validatedData['qty_per_box'],
      'qty_box' => $validatedData['qty_box'],
      'total' => $validatedData['total'],
      'qty_per_box_2' => $validatedData['qty_per_box_2'],
      'qty_box_2' => $validatedData['qty_box_2'],
      'total_2' => $validatedData['total_2'],
      'grand_total' => $validatedData['grand_total'],
      'detail_lokasi' => $validatedData['detail_lokasi'],
    ]);

    // Redirect back with success message
    return redirect()->route('sto.index')->with('success', "Report STO with Inventory ID {$reportSTO->inventory_id} created successfully.");
  }

  public function storeNew(Request $request)
  {
    $validatedData = $request->validate([
      'inventory_code' => 'nullable|string',
      'issued_date' => 'required|date',
      'prepared_by' => 'required|exists:users,id',
      'part_name' => 'required|string',
      'part_number' => 'required|string',
      'checked_by' => 'nullable|string',
      'status' => 'required|string',
      'qty_per_box' => 'required|integer',
      'qty_box' => 'required|integer',
      'total' => 'required|integer',
      'qty_per_box_2' => 'nullable|integer',
      'qty_box_2' => 'nullable|integer',
      'total_2' => 'nullable|integer',
      'grand_total' => 'required|integer',
    ]);

    $inventory = new Inventory();
    $inventory->inventory_id = $validatedData["inventory_code"];
    $inventory->part_name = $validatedData["part_name"];
    $inventory->part_number = $validatedData["part_number"];
    $inventory->type_package = "-";
    $inventory->customer = "-";
    $inventory->satuan = "-";
    $inventory->status_product = $validatedData["status"];

    $inventory->save();
    $validatedData["id_inventory"] = $inventory->id;

    // Create and save the report
    $reportSTO = ReportSTO::create([
      'inventory_id' => $validatedData['inventory_code'],
      'issued_date' => $validatedData['issued_date'],
      'prepared_by' => $validatedData['prepared_by'],
      'checked_by' => $validatedData['checked_by'],
      'status' => $validatedData['status'],
      'qty_per_box' => $validatedData['qty_per_box'],
      'qty_box' => $validatedData['qty_box'],
      'total' => $validatedData['total'],
      'qty_per_box_2' => $validatedData['qty_per_box_2'],
      'qty_box_2' => $validatedData['qty_box_2'],
      'total_2' => $validatedData['total_2'],
      'grand_total' => $validatedData['grand_total'],
      'detail_lokasi' => $validatedData['detail_lokasi'],
    ]);

    $reportSTO->inventory = $inventory;

    // Redirect back with success message
    return redirect()->route('sto.index')
      ->with('success', "Report STO with Part Number {$inventory->part_number} created successfully.")
      ->with('report', $reportSTO);
  }

  public function showForm(Request $request)
  {
    $user = auth()->user();
    $inventory = null;

    if ($request->has('part_number')) {
      $inventory = Inventory::where('part_number', $request->input('part_number'))->first();
    }

    return view('STO.from', compact('user', 'inventory'));
  }

  public function manage(Request $request, $id)
  {
    // Your manage logic here
  }

  public function search(Request $request)
  {
    $query = $request->input('part_name_number');
    $results = Part::where('part_name', 'LIKE', "%{$query}%")
                    ->orWhere('part_number', 'LIKE', "%{$query}%")
                    ->get();

    return view('STO.search', compact('results'));
  }
}