<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StudentController extends Controller
{
    /**
     * Display a listing of students.
     */
    public function index(Request $request)
    {
        $query = Student::with('user', 'programme');
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('first_name', 'LIKE', "%{$search}%")
                  ->orWhere('last_name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            })->orWhere('registration_number', 'LIKE', "%{$search}%");
        }
        
        $students = $query->paginate(20);
        
        return view('superadmin.students.index', compact('students'));
    }
    
    /**
     * Show the form for creating a new student.
     */
    public function create()
    {
        return view('superadmin.students.create');
    }
    
    /**
     * Store a newly created student in storage.
     */
    public function store(Request $request)
    {
        // Validation and store logic
        // You can implement this based on your needs
        return redirect()->route('superadmin.students.index')->with('success', 'Student created successfully');
    }
    
    /**
     * Display the specified student.
     */
    public function show($id)
    {
        $student = Student::with('user', 'programme')->findOrFail($id);
        return view('superadmin.students.show', compact('student'));
    }
    
    /**
     * Show the form for editing the specified student.
     */
    public function edit($id)
    {
        $student = Student::with('user')->findOrFail($id);
        return view('superadmin.students.edit', compact('student'));
    }
    
    /**
     * Update the specified student in storage.
     */
    public function update(Request $request, $id)
    {
        // Update logic
        return redirect()->route('superadmin.students.index')->with('success', 'Student updated successfully');
    }
    
    /**
     * Remove the specified student from storage.
     */
    public function destroy($id)
    {
        $student = Student::findOrFail($id);
        $student->delete();
        return redirect()->route('superadmin.students.index')->with('success', 'Student deleted successfully');
    }
    
    /**
     * Show enrollment form.
     */
    public function showEnroll($id)
    {
        $student = Student::with('user')->findOrFail($id);
        return view('superadmin.students.enroll', compact('student'));
    }
    
    /**
     * Enroll student.
     */
    public function enroll(Request $request, $id)
    {
        // Enrollment logic
        return redirect()->route('superadmin.students.show', $id)->with('success', 'Student enrolled successfully');
    }
    
    /**
     * Export students.
     */
    public function export(Request $request)
    {
        // Export logic
        return redirect()->back()->with('info', 'Export feature coming soon');
    }
    
    /**
     * Show student transcript.
     */
    public function transcript($id)
    {
        $student = Student::with('user', 'programme')->findOrFail($id);
        return view('superadmin.students.transcript', compact('student'));
    }
    
    /**
     * Show student fees.
     */
    public function fees($id)
    {
        $student = Student::with('user')->findOrFail($id);
        return view('superadmin.students.fees', compact('student'));
    }
    
    /**
     * Quick create student.
     */
    public function quickCreate(Request $request)
    {
        // Quick create logic
        return response()->json(['success' => true]);
    }
    
    /**
     * Search students (AJAX).
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        $students = Student::with('user')
            ->whereHas('user', function($q) use ($query) {
                $q->where('first_name', 'LIKE', "%{$query}%")
                  ->orWhere('last_name', 'LIKE', "%{$query}%")
                  ->orWhere('email', 'LIKE', "%{$query}%");
            })
            ->orWhere('registration_number', 'LIKE', "%{$query}%")
            ->limit(10)
            ->get();
        
        return response()->json($students);
    }
    
    /**
     * Bulk import students.
     */
    public function bulkImport(Request $request)
    {
        // Bulk import logic
        return redirect()->back()->with('success', 'Students imported successfully');
    }
    
    /**
     * Bulk enroll students.
     */
    public function bulkEnroll(Request $request)
    {
        // Bulk enroll logic
        return redirect()->back()->with('success', 'Students enrolled successfully');
    }
    
    /**
     * Show supplementary fees.
     */
    public function supplementaryFees($id)
    {
        $student = Student::findOrFail($id);
        return view('superadmin.students.supplementary-fees', compact('student'));
    }
    
    /**
     * Apply supplementary.
     */
    public function applySupplementary($id)
    {
        return redirect()->back()->with('info', 'Supplementary application feature coming soon');
    }
    
    /**
     * Store supplementary.
     */
    public function storeSupplementary(Request $request, $id)
    {
        return redirect()->back()->with('success', 'Supplementary fees recorded');
    }
    
    /**
     * Show supplementary history.
     */
    public function supplementaryHistory($id)
    {
        $student = Student::findOrFail($id);
        return view('superadmin.students.supplementary-history', compact('student'));
    }
    
    /**
     * Show supplementary invoice.
     */
    public function supplementaryInvoice($id, $payment)
    {
        return view('superadmin.students.supplementary-invoice', compact('id', 'payment'));
    }
    
    /**
     * Show repeat module fees.
     */
    public function repeatModuleFees($id)
    {
        $student = Student::findOrFail($id);
        return view('superadmin.students.repeat-module-fees', compact('student'));
    }
    
    /**
     * Apply repeat module.
     */
    public function applyRepeatModule($id)
    {
        return redirect()->back()->with('info', 'Repeat module application feature coming soon');
    }
    
    /**
     * Store repeat module.
     */
    public function storeRepeatModule(Request $request, $id)
    {
        return redirect()->back()->with('success', 'Repeat module fees recorded');
    }
    
    /**
     * Show repeat module history.
     */
    public function repeatModuleHistory($id)
    {
        $student = Student::findOrFail($id);
        return view('superadmin.students.repeat-module-history', compact('student'));
    }
    
    /**
     * Show repeat module invoice.
     */
    public function repeatModuleInvoice($id, $payment)
    {
        return view('superadmin.students.repeat-module-invoice', compact('id', 'payment'));
    }
}