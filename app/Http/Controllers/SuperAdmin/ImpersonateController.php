<?php
// app/Http/Controllers/SuperAdmin/ImpersonateController.php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ImpersonateLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ImpersonateController extends Controller
{
    const IMPERSONATION_RULES = [
        'SuperAdmin' => ['*'],
        'Director' => ['Head_of_Department', 'Deputy_Principal_Academics', 'Tutor', 'Student'],
        'Principal' => ['Head_of_Department', 'Deputy_Principal_Academics', 'Tutor', 'Student'],
        'Deputy_Principal_Academics' => ['Tutor', 'Student'],
        'Head_of_Department' => ['Student'],
        'Dean_of_Students' => ['Student'],
        'Examination_Officer' => ['Student']
    ];
    
    private function canImpersonate(User $admin, User $target): bool
    {
        if ($admin->id === $target->id) {
            return false;
        }
        
        $adminRole = $admin->getRoleNames()->first();
        $targetRole = $target->getRoleNames()->first();
        
        if ($adminRole === 'SuperAdmin') {
            return true;
        }
        
        $higherRoles = ['SuperAdmin', 'Director', 'Principal'];
        if (in_array($targetRole, $higherRoles)) {
            return false;
        }
        
        $allowedRoles = self::IMPERSONATION_RULES[$adminRole] ?? [];
        
        if (in_array('*', $allowedRoles)) {
            return true;
        }
        
        if (!in_array($targetRole, $allowedRoles)) {
            return false;
        }
        
        if ($adminRole === 'Head_of_Department') {
            $studentDepartment = $target->student?->department_id;
            $hodDepartment = $admin->department_id;
            return $studentDepartment && $studentDepartment === $hodDepartment;
        }
        
        return true;
    }

    
    
    private function getDashboardRoute(User $user): string
    {
        $role = $user->getRoleNames()->first();
        
        $routes = [
            'SuperAdmin' => 'superadmin.dashboard',
            'Director' => 'director.dashboard',
            'Principal' => 'principal.dashboard',
            'Deputy_Principal_Academics' => 'dp-academics.dashboard',
            'Deputy_Principal_Administration' => 'dp-admin.dashboard',
            'Head_of_Department' => 'hod.dashboard',
            'Tutor' => 'tutor.dashboard',
            'Examination_Officer' => 'examination.dashboard',
            'Dean_of_Students' => 'dean-students.dashboard',
            'Admission_Officer' => 'admission.dashboard',
            'Records_Officer' => 'records.dashboard',
            'Secretary' => 'secretary.dashboard',
            'Financial_Controller' => 'finance.dashboard',
            'Accountant' => 'accountant.dashboard',
            'Procurement_Officer' => 'procurement.dashboard',
            'ICT_Manager' => 'ict.dashboard',
            'HR_Manager' => 'hr.dashboard',
            'Librarian' => 'library.dashboard',
            'Estate_Manager' => 'estate.dashboard',
            'PR_Marketing_Officer' => 'pr.dashboard',
            'Quality_Assurance_Manager' => 'qa.dashboard',
            'Student' => 'student.dashboard',
            'Applicant' => 'applicant.dashboard',
        ];
        
        return $routes[$role] ?? 'superadmin.dashboard';
    }
    
   /**
 * Start impersonating a user
 */
public function start(Request $request, $userId)
{
    try {
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        
        $admin = Auth::user();
        $targetUser = User::find($userId);
        
        if (!$targetUser) {
            return redirect()->back()->with('error', 'User not found');
        }
        
        // Check if admin can impersonate
        $adminRole = $admin->getRoleNames()->first();
        if ($adminRole !== 'SuperAdmin') {
            return redirect()->back()->with('error', 'Only SuperAdmin can impersonate');
        }
        
        // Store original admin info
        session()->put('impersonate_admin_id', $admin->id);
        session()->put('impersonate_admin_name', $admin->first_name . ' ' . $admin->last_name);
        session()->put('impersonating', true);
        session()->put('original_user_name', $admin->first_name . ' ' . $admin->last_name);
        
        // IMPORTANT: Logout admin properly
        Auth::logout();
        session()->regenerate();
        
        // Login as target user
        Auth::login($targetUser);
        session()->regenerate();
        
        // Get target role and redirect to their dashboard
        $targetRole = $targetUser->getRoleNames()->first();
        
        $redirectUrl = match($targetRole) {
            'Student' => '/student/dashboard',
    'Tutor' => '/tutor/dashboard',
    'Financial_Controller' => '/finance/dashboard',
    'Head_of_Department' => '/hod/dashboard',
    'Director' => '/director/dashboard',
    'Principal' => '/principal/dashboard',
    'Deputy_Principal_Academics' => '/dp-academics/dashboard',
    'Dean_of_Students' => '/dean-students/dashboard',
    'Examination_Officer' => '/exam/dashboard',
    'Admission_Officer' => '/admission/dashboard',
    'Records_Officer' => '/records/dashboard',
    'Secretary' => '/secretary/dashboard',
    'Accountant' => '/accountant/dashboard',
    'Procurement_Officer' => '/procurement/dashboard',
    'ICT_Manager' => '/ict/dashboard',
    'HR_Manager' => '/hr/dashboard',
    'Librarian' => '/library/dashboard',
    'Estate_Manager' => '/estate/dashboard',
    'PR_Marketing_Officer' => '/pr/dashboard',
    'Quality_Assurance_Manager' => '/qa/dashboard',
    'Applicant' => '/applicant/dashboard',
    default => '/dashboard'
};
        
        return redirect($redirectUrl);
        
    } catch (\Exception $e) {
        Log::error('Impersonation error: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
    }
}
  /**
 * Stop impersonating
 */
public function stop(Request $request)
{
    Log::info('Stop impersonation called');
    
    try {
        $adminId = session()->get('impersonate_admin_id');
        $logId = session()->get('impersonate_log_id');
        
        if (!$adminId) {
            session()->flush();
            return redirect('/login');
        }
        
        $admin = User::find($adminId);
        
        if (!$admin) {
            session()->flush();
            return redirect('/login')
                ->with('error', 'Original admin account not found');
        }
        
        // Update log
        if ($logId) {
            try {
                $log = ImpersonateLog::find($logId);
                if ($log && !$log->stopped_at) {
                    $log->update([
                        'stopped_at' => now(),
                        'duration_seconds' => now()->diffInSeconds($log->impersonated_at ?? now())
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Failed to update log: ' . $e->getMessage());
            }
        }
        
        // Clear all session data
        $request->session()->flush();
        $request->session()->regenerate();
        
        // Login as original admin
        Auth::login($admin);
        $request->session()->regenerate();
        
        Log::info('Stop impersonation successful', ['admin_id' => $adminId]);
        
        // DIRECT REDIRECT TO USERS PAGE USING URL - NOT ROUTE NAME
        return redirect('/superadmin/users')
            ->with('success', 'Returned to your account. You can now impersonate another user.');
            
    } catch (\Exception $e) {
        Log::error('Stop impersonation error: ' . $e->getMessage());
        session()->flush();
        return redirect('/login');
    }
}
    
    public static function isImpersonating(): bool
    {
        return session()->has('impersonate_admin_id') && session()->get('impersonating') === true;
    }
    
    public static function getImpersonationInfo(): ?object
    {
        if (!self::isImpersonating()) {
            return null;
        }
        
        return (object) [
            'admin_name' => session()->get('impersonate_admin_name'),
            'admin_role' => session()->get('impersonate_admin_role'),
            'target_name' => session()->get('impersonate_target_name')
        ];
    }
}