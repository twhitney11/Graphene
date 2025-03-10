<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Page;
use App\User;
use App\Group;
use App\AppVersion;
use App\AppInstance;
use Illuminate\Http\Request;
use App\Libraries\Templater;
use App\Libraries\CustomAuth;

class PageController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth')->except('show', 'run');    
        
        $this->customAuth = new CustomAuth();     
    }
    
    public function list_all_pages(Request $request) {
        if (Auth::user()->site_admin) {
            $pages = Page::select('id','group_id','name','slug','icon','order','unlisted','device','public')->whereHas('group', function($q){
                $q->where('site_id','=',config('app.site')->id);
            })->orderBy('group_id','order')->get();
        } else {
            $pages = Page::select('id','group_id','name','slug','icon','order','unlisted','device','public')->whereHas('group', function($q){
                $q->where('site_id','=',config('app.site')->id)->whereIn('id',Auth::user()->content_admin_groups);
            })->orderBy('group_id','order')->get();
        }
        return $pages;
    }

    public function show(Page $page)
    {
        if (!$page->public) {
            $this->authorize('get_data', $page);
        } 
        return $page;
    }

    public function create(Request $request)
    {
        $page = new Page($request->all());
        $page->save();
        return $page;
    }

    public function update(Request $request, Page $page)
    {
        $data = $request->all();
        if(isset($data['content'])){
            $data['content'] = json_decode($data['content']);
        }        
        if(isset($data['mobile_order'])){
            $data['mobile_order'] = json_decode($data['mobile_order']);
        }       
        if(isset($data['groups'])){
            $data['groups'] = json_decode($data['groups']);
        }
        $page->update($data);
        return $page;
    }

    public function destroy(Page $page)
    {
        if ($page->delete()) {
            return 1;
        }
    }
    public function run($group, $slug = null, Request $request) {
        return $this->render("main", $group, $slug, $request);
    }

    public function render($renderer = "main", $group, $slug = null, Request $request) {
        if(!is_numeric($group)) {
            $groupObj = Group::where('slug','=',$group)->where('site_id','=',config('app.site')->id)->first();
            if (is_null($groupObj)) { abort(404);}

            if (Auth::user() && Auth::user()->group_admin($groupObj->id)) {
                $groupObj->load(array('composites'=>function($query){
                    $query->with(array('group'=>function($query){
                    $query->select('name','id');
                }))->select('group_id','composite_group_id');
                }));
            }else{
                $groupObj->load('composites');
            }            
			$group = $groupObj->id;
		}else{
            if (Auth::user() && Auth::user()->group_admin($group)) {
                $groupObj = Group::with(array('composites'=>function($query){
                    $query->with(array('group'=>function($query){
                    $query->select('name','id');
                }))->select('group_id','composite_group_id');
                }))->where('id','=',$group)->where('site_id','=',config('app.site')->id)->first();
                if (is_null($groupObj)) { abort(404);}
                $group = $groupObj->id;
            }else{
                $groupObj = Group::with('composites')->where('id','=',$group)->where('site_id','=',config('app.site')->id)->first();
            }
        }
        if (is_null($groupObj)) { abort(404);}

        if(isset($slug)) {
            $myPage = Page::where('group_id','=', $group)->where('slug', '=', $slug)->first();
        } else{
            // Moved to Redirect Function
        }

        // Get a list of all the micro app instances on the page
        $uapp_instances = [];

        $user = Auth::user();
        if ($user) {
            $groups = array_merge($user->groups,$user->content_admin_groups,$user->apps_admin_groups);
        } else {
            $groups = [];
        }

        if(isset($myPage->content)){
            $tempPage = $myPage->content;
            if(isset($myPage->content->sections)) {
                foreach($myPage->content->sections as $column_index => $column) {
                    foreach($column as $widget_index => $widget) {
                        if(	isset($widget->limit) && $widget->limit &&
                            (!$user || !in_array ($myPage->group_id , $user->content_admin_groups )) &&
                            !count(array_intersect ($widget->group->ids, $groups ))) {
                            unset($tempPage->sections[$column_index][$widget_index]);
                        }else{
                            if ($widget->widgetType === 'uApp') {
                                if(isset($widget->app_id)) {
                                    $uapp_instances[] = $widget->app_id;
                                }
                            }
                        }
                    }
                    $tempPage->sections[$column_index] = array_values($tempPage->sections[$column_index]);
                }
            }

            $myPage->content = $tempPage;
        }
        if(!isset($myPage)){
            abort(404);
        }
        if($myPage->public == 0) {
            if(!Auth::user()){           
                $return = $this->customAuth->authenticate($request);
                if(isset($return)){
                    return $return;
                }
            }
            $this->authorize('get', $myPage);
        }

        if (Auth::check()) { /* User is Authenticated */
            $current_user = Auth::user();
            $apps = AppInstance::whereIn('id', $uapp_instances)->with('app')->get();
            $links = Group::AppsPages()->where('unlisted','=',0)->orderBy('order')->get();
        } else { /* User is not Authenticated */
            $current_user = new User;
            $apps = AppInstance::whereIn('id', $uapp_instances)->where('public', '=', 1)->with('app')->get();
            $links = Group::publicAppsPages()->where('unlisted','=',0)->orderBy('order')->get();
        }


        $scripts = [];
        $styles = [];
        foreach($apps as $key => $app) {
            $apps[$key]->findVersion();
            if($apps[$key]->app->code && isset($apps[$key]->app->code->resources) ) {
                foreach($apps[$key]->app->code->resources as $resource){
                    if($resource->modifier == "script"){
                        $scripts[$resource->name] = array("src"=>$resource->path,"name"=>$resource->name);
                    }else if($resource->modifier == "css"){
                        $styles[$resource->name] =  array("src"=>$resource->path,"name"=>$resource->name);
                    }
                }
            }
        }
        
        if($myPage != null) {
            if(!isset($myPage->content)){
                $config = '""';
            }else{
                $config = $myPage->content;
            }            
            if(!isset($myPage->mobile_order)){
                $myPage->mobile_order = [];
            }
            $template = new Templater();
            return $template->render([
                'mygroups'=>$links, 
                'apps'=>$apps,
                'name'=>$myPage->name, 
                'slug'=>$myPage->slug, 
                'config'=>$config, 
                'data'=>$myPage->mobile_order, 
                'id'=>$myPage->id,
                'group'=>$groupObj,
                'scripts'=>$scripts,
                'styles'=>$styles,
                'template'=>$renderer
            ]);
        }
        abort(404,'Page not found');
    }


    public function redirect($group, Request $request) {
        // dd('here');
        if(!is_numeric($group)) {
            $groupObj = Group::where('slug','=',$group)->where('site_id','=',config('app.site')->id)->first();
		}else{
            $groupObj = Group::where('id','=',$group)->where('site_id','=',config('app.site')->id)->first();
        }
        if (!is_null($groupObj)) {
            // Redirect to first page or first app in this group
            $myPage = Page::where('group_id','=', $groupObj->id)->orderBy('order', 'asc')->first();
            if(!is_null($myPage)){
                return redirect('/page/'.strtolower($groupObj->slug).'/'.strtolower($myPage->slug));
            } else {
                $myAppInstance = AppInstance::where('group_id','=', $groupObj->id)->orderBy('order', 'asc')->first();
                if(!is_null($myAppInstance)){
                    return redirect('/app/'.strtolower($groupObj->slug).'/'.strtolower($myAppInstance->slug));
                } else {
                    abort(404);
                }
            }
        } else {
            abort(404);
        }
    
    }
}
