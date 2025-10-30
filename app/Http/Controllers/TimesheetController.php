<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Services\TimesheetGeneratorService;
use Illuminate\Http\Request;

class TimesheetController extends Controller
{
    public function index()
    {
        $employees = Employee::orderBy('full_name')->get();
        return view('timesheets.index', compact('employees'));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        return redirect()->route('timesheets.show', [
            'employee_id' => $request->employee_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);
    }

    public function show(Request $request)
    {
        $employee = Employee::with(['establishment', 'department'])->findOrFail($request->employee_id);
        
        $generator = new TimesheetGeneratorService();
        $data = $generator->generate($employee, $request->start_date, $request->end_date);

        return view('timesheets.show', $data);
    }
}
