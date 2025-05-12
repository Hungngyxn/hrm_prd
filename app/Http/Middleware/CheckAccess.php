<?php

namespace App\Http\Middleware;

use App\Models\Access;
use App\Models\Menu;
use Closure;
use Illuminate\Http\Request;

class CheckAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $name = explode(".", $request->route()->getName())[0];

        if ($name == "user") {
            $name = "user";
        } else if ($name == "roles") {
            $name = "role";
        } else if ($name == "orders") {
            $name = "order";
        } else if ($name == "account") {
            $name = "account";   
        } else if ($name == "shops") {
            $name = "shop";   
        }else if ($name == "profile") {
            $name = "account";
        }else if ($name == "sku") {
            $name = "sku";
        }

        $menuId = Menu::whereName($name)->first()->id;
        $accessType = Access::where([
            ["menu_id",'=', $menuId],
            ["role_id",'=', auth()->user()->role_id],
        ])->first()->status;

        if($accessType < 1) {
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}
