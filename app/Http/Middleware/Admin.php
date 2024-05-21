<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use View;
use Illuminate\Support\Facades\Route;

class Admin
{
    public function handle($request, Closure $next ,$guard = 'admins')
    {
        if(Auth::guard($guard)->check())
        {
            $modules = $this->adminModules(Auth()->user()->hasPermission);
            Auth()->user()->permissions = $this->currentModulePermissions(Auth()->user()->id);

            $menu_items = [];

            $index = 0;
            foreach($modules as  $key => $module)
            {
                
                
                if($module->prefix == 'orders' || $module->prefix == 'contact-us' || $module->prefix == 'emergency-orders' || $module->prefix == 'order-up' || $module->prefix == 'warranty-orders')
                {
                    if(!isset($menu_items['orders']))
                    {
                        $index = 0;
                        $menu_items['orders']['text'] = 'الطلبات';
                        $menu_items['orders']['icon'] = 'fa fa-calendar';
                        $menu_items['orders']['element'][$key] = $module;
                    }
                    else
                    {
                        $index++;
                        $menu_items['orders']['element'][$key] = $module;
                    }
                }
                else if($module->prefix == 'maintenance-report' || $module->prefix == 'bankup-report'
                        || $module->prefix == 'sales-report' || $module->prefix == 'team-report')
                {
                    if(!isset($menu_items['reports']))
                    {
                        $index = 0;
                        $menu_items['reports']['text'] = 'التقارير';
                        $menu_items['reports']['icon'] = 'fa fa-file-excel';
                        $menu_items['reports']['element'][$index] = $module;
                    }
                    else
                    {
                        $index++;
                        $menu_items['reports']['element'][$index] = $module;
                    }
                }
                else if($module->prefix == 'emergency-services' || $module->prefix == 'services' || $module->prefix == 'buildings' || $module->prefix == 'units' || $module->prefix == 'coupons' 
                        || $module->prefix == 'features' || $module->prefix == 'static-pages' || $module->prefix == 'common-questions'
                        || $module->prefix == 'report-problems' || $module->prefix == 'offers' || $module->prefix == 'send-notification')
                {
                    if(!isset($menu_items['manage_content']))
                    {
                        $index = 0;
                        $menu_items['manage_content']['text'] = 'إدارة المحتوى';
                        $menu_items['manage_content']['icon'] = 'fa fa-book';
                        $menu_items['manage_content']['element'][$index] = $module;
                    }
                    else
                    {
                        $index++;
                        if($module->prefix == 'features')
                        {
                            $menu_items['manage_content']['element'][$index] = $module;
                        }
                        else
                        {
                            $menu_items['manage_content']['element'][$index] = $module;
                        }
                    }
                }
                else if($module->prefix == 'settings' || $module->prefix == 'admins' || $module->prefix == 'teams' || $module->prefix = 'users' || $module->prefix == 'logs')
                {
                    if(!isset($menu_items['settings']))
                    {
                        $index = 0;
                        $menu_items['settings']['text'] = 'الإعدادات';
                        $menu_items['settings']['icon'] = 'fa fa-cog';
                        $menu_items['settings']['element'][$index] = $module;
                    }
                    else
                    {
                        $index++;
                        $menu_items['settings']['element'][$index] = $module;
                    }
                }
            }
            View::share('modules', $menu_items);

            $order_notification_link_type = '';
            $ordr_notification_permission = $this->permissionForSelectedModule(Auth()->user()->id, 'orders');
            $notifications =  DB::table('notifications')->where([['user_type', '3'], ['seen', '0']])->orderBy('id', 'DESC')->get();

            if($ordr_notification_permission->can_show == 1 && $ordr_notification_permission->can_edit == 1)
            {
                $order_notification_link_type = 'orders.show';
            }
            else if($ordr_notification_permission->can_show == 1 && $ordr_notification_permission->can_edit == 0)
            {
                $order_notification_link_type = 'orders.show';
            }
            else if($ordr_notification_permission->can_show == 0 && $ordr_notification_permission->can_edit == 1)
            {
                $order_notification_link_type = 'orders.edit';
            }
            else
            {
                $notifications = [];
            }

            View::share('notifications', $notifications);
            View::share('order_notification_link_type', $order_notification_link_type);

            return $next($request);
        }
        return redirect('/admin-panel/login');
    }

    public function adminModules($permissions)
	{
        $permit_modules = [];

        foreach($permissions as $permission)
        {
            $permit_modules[] = $permission->module_id;
        }

        return $modules = DB::table('modules')->whereIn('id', $permit_modules)->orderBy('order', 'ASC')->get();
    }

    public function currentModulePermissions($admin_id)
    {
        if(\Request::route()->getName() != 'home' && \Request::route()->getName() != 'profile' && \Request::route()->getName() != 'sendTestSms'
            && \Request::route()->getName() != 'export-sales-report' && \Request::route()->getName() != 'export-bankup-report'
            && \Request::route()->getName() != 'export-maintenance-report' && \Request::route()->getName() != 'edit-maintenance-report' && \Request::route()->getName() != 'edit-order-up' && \Request::route()->getName() != 'export-team-report' && \Request::route()->getName() != 'sendNotification')
        {
            $prefix = explode('.', \Request::route()->getName())[0];

            $current_modules = DB::table('modules')
                                ->where('prefix', $prefix)
                                ->first();
            $permissions = DB::table('admin_permissions')
                                ->select('can_create', 'can_edit', 'can_show', 'can_delete')
                                ->where([['admin_id', $admin_id], ['module_id', $current_modules->id]])
                                ->first();

            if($permissions)
            {
                $permissions->prefix = $prefix;
            }
            else
            {
                $permissions['can_create'] = 0;
                $permissions['can_edit'] = 0;
                $permissions['can_show'] = 0;
                $permissions['can_delete'] = 0;
                $permissions = (object) $permissions;
            }

            $current_route = Route::getCurrentRoute()->getName();
            $check_method_permission = $this->checkMethodPermission($current_route, $permissions);

            return $permissions;
        }
    }

    public function permissionForSelectedModule($admin_id, $prefix)
    {
        $current_modules = DB::table('modules')
                ->where('prefix', $prefix)
                ->first();

        $permissions = DB::table('admin_permissions')
                ->select('can_create', 'can_edit', 'can_show', 'can_delete')
                ->where([['admin_id', $admin_id], ['module_id', $current_modules->id]])
                ->first();

        if($permissions)
        {
            $permissions->prefix = $prefix;
        }
        else
        {
            $permissions['can_create'] = 0;
            $permissions['can_edit'] = 0;
            $permissions['can_show'] = 0;
            $permissions['can_delete'] = 0;
            $permissions = (object) $permissions;
        }
        return $permissions;
    }

    public function checkMethodPermission($current_route, $permission)
    {
        $method = explode('.', $current_route)[1];

        if($method == 'index' && $permission->can_create == 0 && $permission->can_edit == 0
            && $permission->can_show == 0 && $permission->can_delete == 0)
        {
            header('Location: '.route('home'));
            exit;
        }
        else if(($method == 'create' || $method == 'store') && $permission->can_create == 0)
        {
            header('Location: '.route('home'));
            exit;
        }
        else if($method == 'show' && $permission->can_show == 0)
        {
            header('Location: '.route('home'));
            exit;
        }
        else if(($method == 'edit' || $method == 'update') && $permission->can_edit == 0)
        {
            header('Location: '.route('home'));
            exit;
        }
        else if($method == 'destroy' && $permission->can_delete == 0)
        {
            header('Location: '.route('home'));
            exit;
        }
    }

}
