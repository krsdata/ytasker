<?php
namespace Modules\Api\Resources;

use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Log\Writer;
use Monolog\Logger as Monolog;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;
use App\Models\CategoryDashboard;
use Mail,View,Redirect,Validator,Response; 
use Auth,Crypt,okie,Hash,Lang,JWTAuth,Input,Closure,URL; 
use JWTExceptionTokenInvalidException; 
use Illuminate\Http\Dispatcher; 
use Modules\Api\Helpers\Helper as Helper;
use Modules\Admin\Models\User;
use Modules\Admin\Models\Tasks;

/**
 * @author 
 */
class TaskResource extends BaseApiResource {

    protected $stockSettings = array();
    protected $modelNumber = '';

    // return object of userModel
    protected function getModel() {
        return new Task();
    }
    // get all user
    public function getUser($id=null) {

    //    return $this->getModel()->getUserDetail($id);
    }

    public function createTask($request)
    {
        $post_request = $request->all();
        
        if (!empty($post_request)) 
        {
            if ($request->get('client_id')) {
                $client_id = $request->get('client_id');
                $user_data = User::find($client_id);
                if (empty($user_data)) {
                    return
                        [ 
                        "status"  => '0',
                        'code'    => '200',
                        "message" => 'No match found for the given client id.',
                        'data'    => []
                        ];
                    
                } else {
                    if($request->get('title') && $request->get('description') && $request->get('due_date') && $request->get('client_id')  && $request->get('people_required') && $request->get('budget') && $request->get('budget_type') ){

                        $task = new Tasks;

                        $date = $request->get('due_date');
                        $task->title = $request->get('title');
                        $task->description = $request->get('description');
                        $due_date = Carbon::createFromFormat('m/d/Y', $date);
                        $task->client_id = $request->get('client_id');
                        $task->due_date = $due_date;
                        $task->people_required = $request->get('people_required');
                        $task->budget = $request->get('budget');
                        $task->budget_type = $request->get('budget_type');
                        $task->status = '0';

                        $task->location = !empty($request->get('location')) ?$task->location = $request->get('location') : $task->location = NULL;
                        !empty($request->get('country')) ? $task->country = $request->get('country') : $task->country = NULL;
                        !empty($request->get('city')) ?  $task->city = $request->get('city') : $task->city = NULL;
                        !empty($request->get('address')) ?$task->address = $request->get('address') : $task->address = NULL;
                        !empty($request->get('zipcode')) ?$task->zipcode = $request->get('zipcode') : $task->zipcode = NULL;

                        $task->save();

                        $status  = 1;
                        $code    = 200;
                        $message = 'Task  successfully inserted.';
                        $data    = [];
                    
                    } else {
                        $status  = 0;
                        $code    = 400;
                        $message = 'Required request params not found.';
                        $data    = [];
                    }

                }
            } else {
                 $status  = 0;
                $code    = 400;
                $message = 'Unable to add task, client id field is empty.';
                $data    = [];
            }  
        } else {
            $status  = 0;
            $code    = 400;
            $message = 'Unable to add task, no post data found.';
            $data    = [];
        }
        return 
                            [ 
                            "status"  =>$status,
                            'code'    => $code,
                            "message" =>$message,
                            'data'    => $data
                            ];
                       

    }

    public function getAllTasks(){
        $tasks = Tasks::all();

        if(count($tasks)){
            $status  =  1;
            $code    =  200;
            $message =  "List of all tasks.";
            $data    =  $tasks;
        } else {
            $status  =  0;
            $code    =  204;
            $message =  "No tasks found.";
            $data    =  [];
        }

        return [ 
                    "status"  =>$status,
                    'code'    => $code,
                    "message" =>$message,
                    'data'    => $data
                    ];
    }

    public function getOpenTasks($request){

        dd($request->route('page'));
        $tasks = Tasks::where('status', 0)
                        ->offset(0)
                        ->limit(25)
                        ->get();

        if(count($tasks)){
            $status  =  1;
            $code    =  200;
            $message =  "List of all open tasks.";
            $data    =  $tasks;
        } else {
            $status  =  0;
            $code    =  204;
            $message =  "No open tasks found.";
            $data    =  [];
        }

        return [ 
                    "status"  =>$status,
                    'code'    => $code,
                    "message" =>$message,
                    'data'    => $data
                    ];
    }

    // status '0'=>'open','1'=>'completed','2'=>'in-progress'
    public function getRecentTasks(){
        $tasks = Tasks::where('status', 1)
               ->orderBy('id', 'desc')
               ->take(8)
               ->get();

        if(count($tasks)){
            $status  =  1;
            $code    =  200;
            $message =  "List of recently completed tasks.";
            $data    =  $tasks;
        } else {
            $status  =  0;
            $code    =  204;
            $message =  "No tasks found.";
            $data    =  [];
        }

        return      [ 
                    "status"  =>$status,
                    'code'    => $code,
                    "message" =>$message,
                    'data'    => $data
                    ];
    }

    public function getUserTasks(Request $request)
    {
       $user_id = $request->user_id;

        if($user_id)
        {
            $user_tasks  =   Tasks::where('user_id',$user_id)->get();

            if(count($user_tasks)){
                $status  =  1;
                $code    =  200;
                $message =  "List of tasks posted by user";
                $data    =  $user_tasks;
            } else {
                $status  =  0;
                $code    =  204;
                $message =  "No tasks found for the given user.";
                $data    =  [];
            }
            
        } else {

            $status  =  0;
            $code    =  500;
            $message =  "Invalid User ID."; 
            $data    =  [];

        }
        return [ 
                "status"  =>$status,
                'code'    => $code,
                "message" =>$message,
                'data'    => $data
                ];
    }
}